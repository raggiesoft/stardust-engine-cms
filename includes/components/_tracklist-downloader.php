<?php
/**
 * ============================================================================
 * COMPONENT: _tracklist-downloader.php
 * VERSION: 11.0 (Open-Source CMS Edition — Local & CDN Hybrid Resolution)
 *
 * PURPOSE:
 * Generates the interactive tracklist interface for albums, handles streaming
 * link routing, manages Schema.org metadata integration, and dispatches the
 * tracklist payload to `stardust-player.js` via the `stardust:playlist-update` event.
 *
 * ARCHITECTURAL ROLE:
 * Resolves `tracks.json` and `album.json` from the local filesystem first
 * (`ROOT_PATH . $album_path_web`) so cloned repositories work out-of-the-box
 * with zero external CDN dependencies, and seamlessly falls back to `$cdnBaseUrl`
 * when configured for production edge storage.
 *
 * LICENSE:
 * Released under the MIT License.
 * ============================================================================
 */

$vault_active = false; 
$vault_under_construction = true;

// Resolve local filesystem path first, then fallback to $cdnBaseUrl if configured
$local_album_dir = defined('ROOT_PATH') ? ROOT_PATH . $album_path_web : '';
$cdn_root = !empty($cdnBaseUrl) ? rtrim($cdnBaseUrl, '/') : '';
$base_web_path = $cdn_root . $album_path_web;

if ($local_album_dir && file_exists($local_album_dir . '/tracks.json') && file_exists($local_album_dir . '/album.json')) {
    $tracks_json_content = file_get_contents($local_album_dir . '/tracks.json');
    $album_json_content = file_get_contents($local_album_dir . '/album.json');
    $base_web_path = $album_path_web;
} else {
    $tracks_json_url = $base_web_path . '/tracks.json?v=' . time();
    $album_json_url = $base_web_path . '/album.json?v=' . time();
    $tracks_json_content = @file_get_contents($tracks_json_url);
    $album_json_content = @file_get_contents($album_json_url);
}

if ($tracks_json_content === false || $album_json_content === false) {
    echo '<div class="alert alert-warning border-warning"><i class="fa-solid fa-triangle-exclamation me-2" aria-hidden="true"></i><strong>Tracklist Notice:</strong> Tracklist metadata (<code>tracks.json</code> / <code>album.json</code>) was not found at <code>' . htmlspecialchars($album_path_web) . '</code>.</div>';
    return;
}

$tracks_data = json_decode($tracks_json_content, true);
$album_data = json_decode($album_json_content, true);
$raw_tracks = $tracks_data['tracks'] ?? [];

if (!function_exists('get_web_safe_title')) {
    function get_web_safe_title($title) {
        $title = strtolower($title);
        $title = preg_replace('/[^\w\s-]/', '', $title);
        $title = preg_replace('/[\s_]+/', '-', $title);
        return preg_replace('/-+/', '-', $title);
    }
}

if (!function_exists('get_archive_name')) {
    function get_archive_name($album_name, $year) {
        $safe_name = get_web_safe_title($album_name);
        return $year . '-' . $safe_name;
    }
}

// --- TIMELINE & RELEASE METADATA (SCHEMA.ORG INTEGRATION) ---
$narrative_date = !empty($album_data['temporalCoverage']) ? $album_data['temporalCoverage'] : '1989-01-01';
$real_release_date = !empty($album_data['datePublished']) ? $album_data['datePublished'] : 'TBA';

$narrative_year = substr($narrative_date, 0, 4);
$album_name = $album_data['name'] ?? 'Sample Album';
$archive_base_name = get_archive_name($album_name, $narrative_year);

$raw_release_type = isset($album_data['albumReleaseType']) ? basename($album_data['albumReleaseType']) : 'AlbumRelease';
$raw_production_type = isset($album_data['albumProductionType']) ? basename($album_data['albumProductionType']) : 'StudioAlbum';
$album_upc = !empty($album_data['gtin12']) ? $album_data['gtin12'] : (!empty($album_data['identifier']) ? $album_data['identifier'] : null);

$release_map = [
    'EPRelease' => 'EP',
    'SingleRelease' => 'Single',
    'BroadcastRelease' => 'Broadcast',
    'AlbumRelease' => 'Full Length'
];

$production_map = [
    'LiveAlbum' => 'Live Album',
    'CompilationAlbum' => 'Compilation',
    'SoundtrackAlbum' => 'Soundtrack',
    'MixtapeAlbum' => 'Mixtape',
    'RemixAlbum' => 'Remix Album',
    'StudioAlbum' => 'Studio Album'
];

$friendly_release = $release_map[$raw_release_type] ?? 'Full Length';
$friendly_production = $production_map[$raw_production_type] ?? 'Studio Album';

// --- DSP STREAMING IDS & STORE LINKS ---
$stream_spotify_id = '';
$stream_apple_id   = '';
$stream_amazon_id  = '';
$stream_youtube_id = '';
$store_standard_url = '';
$store_audiophile_url = '';
$dsp_exempt = false;
$dsp_notice = '';

$artist_path_web = dirname($album_path_web);
$local_master_json = defined('ROOT_PATH') ? ROOT_PATH . $artist_path_web . '/albums.json' : '';
if ($local_master_json && file_exists($local_master_json)) {
    $albums_master_content = file_get_contents($local_master_json);
} elseif (!empty($cdn_root)) {
    $albums_master_content = @file_get_contents($cdn_root . $artist_path_web . '/albums.json?v=' . time());
} else {
    $albums_master_content = false;
}

if ($albums_master_content !== false) {
    $master_data = json_decode($albums_master_content, true);
    $current_slug = basename($album_path_web);
    
    if (is_array($master_data)) {
        foreach ($master_data as $era) {
            if (!empty($era['albums'])) {
                foreach ($era['albums'] as $master_album) {
                    $check_slug = $master_album['folder'] ?? basename($master_album['url'] ?? '');
                    if ($check_slug === $current_slug || ($master_album['title'] ?? '') === $album_name) {
                        $stream_spotify_id = $master_album['spotifyId'] ?? '';
                        $stream_apple_id   = $master_album['appleId'] ?? '';
                        $stream_amazon_id  = $master_album['amazonId'] ?? '';
                        $stream_youtube_id = $master_album['youtubeId'] ?? '';
                        $store_standard_url = $master_album['storeStandardUrl'] ?? '';
                        $store_audiophile_url = $master_album['storeAudiophileUrl'] ?? '';
                        $dsp_exempt = $master_album['dspExempt'] ?? false;
                        $dsp_notice = $master_album['dspNotice'] ?? 'This release is intentionally withheld from commercial streaming platforms.';
                        break 2;
                    }
                }
            }
        }
    }
}

// Resolve artwork path (supports SVG or JPG)
if ($local_album_dir && file_exists($local_album_dir . '/album-art.svg')) {
    $album_art_url = $base_web_path . '/album-art.svg';
} else {
    $album_art_url = $base_web_path . '/album-art.jpg';
}

$js_playlist = []; 
?>
<script type="application/ld+json">
    <?php echo $album_json_content; ?>
</script>

<!-- Album Metadata Summary Card -->
<div class="card bg-body-tertiary border-secondary-subtle mb-4 shadow-sm">
    <div class="card-body p-3 p-md-4">
        
        <div class="row g-2 align-items-center border-bottom border-secondary-subtle pb-3 mb-3">
            <div class="col-12 col-md-6">
                <div class="text-body fs-6 mb-2">
                    <i class="fa-solid fa-timeline me-2 text-primary" aria-hidden="true"></i> <strong>Recording Era:</strong> <span class="badge bg-primary-subtle text-primary-emphasis ms-1"><?php echo htmlspecialchars($narrative_year); ?></span>
                </div>
                <div class="text-body fs-6">
                    <i class="fa-solid fa-record-vinyl me-2 text-warning" aria-hidden="true"></i> <strong>Format:</strong> <span class="badge bg-warning-subtle text-warning-emphasis ms-1"><?php echo htmlspecialchars($friendly_production); ?></span>
                </div>
            </div>
            
            <div class="col-12 col-md-6 text-md-end">
                <div class="text-body fs-6 mb-2">
                    <i class="fa-solid fa-calendar-check me-2 text-success" aria-hidden="true"></i> <strong>Release Date:</strong> <span class="badge bg-success-subtle text-success-emphasis ms-1"><?php echo htmlspecialchars($real_release_date); ?></span>
                </div>
                <div class="text-body fs-6">
                    <i class="fa-solid fa-music me-2 text-info" aria-hidden="true"></i> <strong>Length:</strong> <span class="badge bg-info-subtle text-info-emphasis ms-1 me-2"><?php echo htmlspecialchars($friendly_release); ?></span>
                    <?php if ($album_upc): ?>
                        <span class="badge bg-secondary-subtle text-secondary-emphasis" title="Universal Product Code">UPC: <?php echo htmlspecialchars($album_upc); ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <?php if (!empty($album_data['description'])): ?>
        <div class="mt-2 mb-2 px-3 py-2 border-start border-3 border-primary bg-body rounded-end">
            <p class="mb-0 fst-italic text-body-secondary small">
                <?php echo htmlspecialchars($album_data['description']); ?>
            </p>
        </div>
        <?php endif; ?>

        <div class="d-flex align-items-start mt-3">
            <i class="fa-solid fa-circle-info text-primary mt-1 me-3 fs-5" aria-hidden="true"></i>
            <p class="small text-body-secondary mb-0 lh-sm">
                <strong>MODULE ARCHITECTURE NOTE:</strong> This component reads Schema.org-compliant <code>album.json</code> and <code>tracks.json</code> files directly from disk or an edge CDN, injects JSON-LD structured data into the document head, and dispatches the playlist state to the persistent <code>stardust-player.js</code> audio engine.
            </p>
        </div>
    </div>
</div>

<h2 class="h4 fw-bold text-uppercase text-body-secondary mb-3">
    <i class="fa-solid fa-list-ul me-2 text-primary" aria-hidden="true"></i>Tracklist &amp; Markdown Lyrics
</h2>

<div class="tracklist-wrapper mb-5">
    <?php 
    $current_disc = null;
    $current_suite = null;
    $is_list_open = false;

    foreach ($raw_tracks as $index => $track): 
        $disc = $track['disc'] ?? 1;
        $disc_name = $track['discName'] ?? '';
        $suite_name = $track['suiteName'] ?? '';
        $isrc_code = $track['isrc'] ?? '';

        // Disc Header Logic
        if ($disc !== $current_disc) {
            if ($is_list_open) {
                echo '</div>';
            }
            $current_disc = $disc;
            $current_suite = null;
            
            $display_disc_name = !empty($disc_name) ? " — <span class='text-body-secondary fs-6'>" . htmlspecialchars($disc_name) . "</span>" : "";
            
            echo '<div class="mt-4 mb-3 pb-2 border-bottom border-secondary-subtle">';
            echo '<h3 class="h5 text-body-emphasis fw-bold mb-0"><i class="fa-solid fa-compact-disc me-2 text-primary" aria-hidden="true"></i>Disc ' . (int)$disc . $display_disc_name . '</h3>';
            echo '</div>';
            
            echo '<div class="list-group list-group-flush bg-transparent">';
            $is_list_open = true;
        }

        // Suite Header Logic
        if (!empty($suite_name) && $suite_name !== $current_suite) {
            $current_suite = $suite_name;
            echo '<div class="list-group-item bg-body-tertiary border-start border-3 border-primary py-2 mt-2 mb-1">';
            echo '<h4 class="h6 text-uppercase text-body-secondary mb-0 fw-bold"><i class="fa-solid fa-layer-group me-2 text-primary" aria-hidden="true"></i>' . htmlspecialchars($suite_name) . '</h4>';
            echo '</div>';
        } elseif (empty($suite_name) && $current_suite !== null) {
            $current_suite = null;
        }

        // Track Row Generation
        $base_name = $track['fileName'];
        $lyrics_url = $base_web_path . '/lyrics/' . $base_name . '.md';
        $dl_web_mp3 = $base_web_path . '/web-mp3/' . $base_name . '.mp3';

        if ($vault_active) {
            $player_src = $dl_web_mp3;
            $gateway_base = "/api/download.php?album=" . urlencode($archive_base_name) . "&track=" . urlencode($base_name);
            $dl_mp3 = $gateway_base . "&format=mp3";
            $dl_ogg = $gateway_base . "&format=ogg";
            $dl_wav = $gateway_base . "&format=wav";
        } else {
            $player_src = $dl_web_mp3;
            $dl_mp3 = $base_web_path . '/vault/mp3/' . $base_name . '.mp3';
            $dl_ogg = $base_web_path . '/vault/ogg/' . $base_name . '.ogg';
            $dl_wav = $base_web_path . '/vault/wav/' . $base_name . '.wav'; 
        }

        $legacy_tier = $track['legacyTier'] ?? null;
        $lore_note = $track['loreNote'] ?? '';
        $duration = $track['duration'] ?? '';
        $artist_name = $album_data['byArtist']['name'] ?? 'Sample Artist';
        
        $js_playlist[] = [
            'title' => $track['title'],
            'artist' => $artist_name,
            'album' => $album_name,
            'src' => $player_src,
            'artwork' => $album_art_url,
            'lyrics' => $lyrics_url,
            'legacyTier' => $legacy_tier,
            'loreNote' => $lore_note,
            'duration' => $duration,
            'isrc' => $isrc_code
        ];

        $indent_class = !empty($current_suite) ? "ms-3 border-start-0 ps-3" : "";
        ?>
        
        <div class="list-group-item bg-transparent border-secondary-subtle py-3 track-row <?php echo $indent_class; ?>" id="track-row-<?php echo (int)$index; ?>" data-isrc="<?php echo htmlspecialchars($isrc_code); ?>">
            <div class="row align-items-center">
                <div class="col-md-7 mb-2 mb-md-0">
                    <div class="d-flex align-items-center flex-wrap">
                        <span class="text-body-secondary fw-bold me-3" style="width: 28px;"><?php echo (int)$track['track']; ?>.</span>
                        <div>
                            <strong class="text-body-emphasis fs-5 d-inline-block me-2"><?php echo htmlspecialchars($track['title']); ?></strong>
                            
                            <?php if (!empty($duration)): ?>
                                <span class="text-body-secondary small fw-medium me-2" title="Track Length">
                                    <i class="fa-regular fa-clock me-1" aria-hidden="true"></i><?php echo htmlspecialchars($duration); ?>
                                </span>
                            <?php endif; ?>

                            <?php 
                            if ($legacy_tier): 
                                $badge_class = 'bg-secondary-subtle text-secondary-emphasis'; 
                                if ($legacy_tier === 'Chart Smash') $badge_class = 'bg-success-subtle text-success-emphasis';
                                if ($legacy_tier === 'Fan Anthem') $badge_class = 'bg-warning-subtle text-warning-emphasis';
                                if ($legacy_tier === 'Deep Cut') $badge_class = 'bg-info-subtle text-info-emphasis';
                                if ($legacy_tier === 'The Dud' || $legacy_tier === 'Studio Filler') $badge_class = 'bg-danger-subtle text-danger-emphasis';
                                
                                $safe_lore_attr = htmlspecialchars($lore_note, ENT_QUOTES);
                                $wcag_attrs = $lore_note 
                                    ? 'title="' . $safe_lore_attr . '" aria-label="Tier: ' . htmlspecialchars($legacy_tier) . '. Note: ' . $safe_lore_attr . '" tabindex="0"'
                                    : 'aria-label="Tier: ' . htmlspecialchars($legacy_tier) . '" tabindex="0"';
                            ?>
                                <span class="badge <?php echo $badge_class; ?> align-text-bottom" style="font-size: 0.65em; letter-spacing: 0.5px; cursor: help;" <?php echo $wcag_attrs; ?>>
                                    <span aria-hidden="true"><?php echo strtoupper(htmlspecialchars($legacy_tier)); ?></span>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-5 text-md-end mt-2 mt-md-0">
                    <div class="btn-group" role="group" aria-label="Controls for <?php echo htmlspecialchars($track['title']); ?>">
                        <button type="button" class="btn btn-sm btn-primary btn-play-index px-3" data-index="<?php echo (int)$index; ?>">
                            <i class="fa-solid fa-play me-1" aria-hidden="true"></i>Play
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary btn-view-lyrics px-3" data-title="<?php echo htmlspecialchars($track['title']); ?>" data-url="<?php echo htmlspecialchars($lyrics_url); ?>">
                            <i class="fa-solid fa-book-open me-1" aria-hidden="true"></i>Lyrics
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    
    <?php if ($is_list_open) echo '</div>'; ?>
</div>

<script>
    (function() {
        const newPlaylist = <?php echo json_encode($js_playlist, JSON_UNESCAPED_SLASHES); ?>;
        window.STARDUST_PLAYLIST = newPlaylist;
        const event = new CustomEvent('stardust:playlist-update', { detail: { playlist: newPlaylist } });
        document.dispatchEvent(event);
    })();
</script>