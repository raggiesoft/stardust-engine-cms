<?php
/**
 * COMPONENT: _tracklist-downloader.php
 * VERSION: 10.3 (Schema.org Deep Metadata, Store Routing & DSP Exemption Logic)
 *
 * LICENSE:
 * The architecture and code of this file are licensed under the MIT License.
 * Copyright (c) 2026 Michael P. Ragsdale / RaggieSoft
 * * The underlying narrative, lore, and music tracks delivered by this component 
 * are licensed under Creative Commons Attribution-ShareAlike 4.0 International (CC BY-SA 4.0),
 * except where commercial distribution exemptions apply (e.g., DSP streaming links).
 */

// ==============================================================================
//  FEATURE FLAG: THE VAULT PAYWALL
// ==============================================================================
$vault_active = false; 
$vault_under_construction = true; // Set to TRUE to hide ONLY the massive master files temporarily
// ==============================================================================

$base_web_path = 'https://assets.raggiesoft.com' . $album_path_web;
$tracks_json_url = $base_web_path . '/tracks.json?v=' . time();
$album_json_url = $base_web_path . '/album.json?v=' . time();

$tracks_json_content = @file_get_contents($tracks_json_url);
$album_json_content = @file_get_contents($album_json_url);

if ($tracks_json_content === false || $album_json_content === false) {
    echo '<div class="alert alert-danger"><strong>Error:</strong> Tracklist data not found on CDN.</div>';
    return;
}

$tracks_data = json_decode($tracks_json_content, true);
$album_data = json_decode($album_json_content, true);
$raw_tracks = $tracks_data['tracks'];

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

// --- TIMELINE LOGIC (SCHEMA.ORG INTEGRATION) ---
$narrative_date = !empty($album_data['temporalCoverage']) ? $album_data['temporalCoverage'] : '1900-01-01';
$real_release_date = !empty($album_data['datePublished']) ? $album_data['datePublished'] : 'TBA';

$narrative_year = substr($narrative_date, 0, 4);
$real_release_year = $real_release_date !== 'TBA' ? substr(trim($real_release_date), -4) : 'TBA';

$album_name = isset($album_data['name']) ? $album_data['name'] : 'Unknown Album';
$archive_base_name = get_archive_name($album_name, $narrative_year);

// --- METADATA TRANSLATION (SCHEMA.ORG INTEGRATION) ---
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

$friendly_release = isset($release_map[$raw_release_type]) ? $release_map[$raw_release_type] : 'Full Length';
$friendly_production = isset($production_map[$raw_production_type]) ? $production_map[$raw_production_type] : 'Studio Album';

// --- DSP STREAMING IDS & STORE LINKS (SINGLE SOURCE OF TRUTH) ---
$stream_spotify_id = '';
$stream_apple_id   = '';
$stream_amazon_id  = '';
$stream_youtube_id = '';
$store_standard_url = '';
$store_audiophile_url = '';
$dsp_exempt = false;
$dsp_notice = '';

// Step up one directory from the album path to target the artist's root folder
$artist_path_web = dirname($album_path_web);
$albums_master_url = 'https://assets.raggiesoft.com' . $artist_path_web . '/albums.json?v=' . time();
$albums_master_content = @file_get_contents($albums_master_url);

if ($albums_master_content !== false) {
    $master_data = json_decode($albums_master_content, true);
    $current_slug = basename($album_path_web);
    
    foreach ($master_data as $era) {
        if (!empty($era['albums'])) {
            foreach ($era['albums'] as $master_album) {
                // Match by explicitly defined folder, URL slug, or exact Album Name
                $check_slug = isset($master_album['folder']) ? $master_album['folder'] : basename($master_album['url']);
                
                if ($check_slug === $current_slug || $master_album['title'] === $album_name) {
                    $stream_spotify_id = $master_album['spotifyId'] ?? '';
                    $stream_apple_id   = $master_album['appleId'] ?? '';
                    $stream_amazon_id  = $master_album['amazonId'] ?? '';
                    $stream_youtube_id = $master_album['youtubeId'] ?? '';
                    
                    // Pull the Fourthwall Store URLs
                    $store_standard_url = $master_album['storeStandardUrl'] ?? '';
                    $store_audiophile_url = $master_album['storeAudiophileUrl'] ?? '';
                    
                    // Check for DSP exemptions
                    $dsp_exempt = $master_album['dspExempt'] ?? false;
                    $dsp_notice = $master_album['dspNotice'] ?? 'This release is intentionally withheld from commercial streaming algorithms.';
                    break 2; // Match found, break out of both loops
                }
            }
        }
    }
}

$has_active_streams = !empty($stream_spotify_id) || !empty($stream_apple_id) || !empty($stream_amazon_id) || !empty($stream_youtube_id);
// ----------------------

$album_art_url = $base_web_path . "/album-art.jpg?v=" . time();
$js_playlist = []; 
?>
<script type="application/ld+json">
    <?php echo $album_json_content; ?>
</script>
<div class="card bg-body-tertiary border-secondary-subtle mb-5 shadow-sm">
    <div class="card-body p-3">
        
        <div class="row g-2 align-items-center border-bottom border-secondary-subtle pb-3 mb-3">
            <div class="col-12 col-md-6">
                <div class="text-body fs-6 mb-2">
                    <i class="fa-solid fa-timeline me-2 text-info"></i> <strong>Narrative Era:</strong> <span class="badge bg-info text-dark ms-1" style="font-size: 0.9em;"><?php echo htmlspecialchars($narrative_year); ?></span>
                </div>
                <div class="text-body fs-6">
                    <i class="fa-solid fa-record-vinyl me-2 text-warning"></i> <strong>Format:</strong> <span class="badge bg-dark text-warning ms-1" style="font-size: 0.9em; border: 1px solid var(--bs-warning);"><?php echo htmlspecialchars($friendly_production); ?></span>
                </div>
            </div>
            
            <div class="col-12 col-md-6 text-md-end">
                <div class="text-body fs-6 mb-2">
                    <i class="fa-solid fa-calendar-check me-2 text-success"></i> <strong>DSP Release:</strong> <span class="badge bg-success-subtle text-success-emphasis ms-1" style="font-size: 0.9em; border: 1px solid var(--bs-success-border-subtle);"><?php echo htmlspecialchars($real_release_date); ?></span>
                </div>
                <div class="text-body fs-6">
                    <i class="fa-solid fa-music me-2 text-primary"></i> <strong>Length:</strong> <span class="badge bg-primary-subtle text-primary-emphasis ms-1 me-2" style="font-size: 0.9em; border: 1px solid var(--bs-primary-border-subtle);"><?php echo htmlspecialchars($friendly_release); ?></span>
                    <?php if ($album_upc): ?>
                        <span class="badge bg-secondary-subtle text-secondary-emphasis" style="font-size: 0.85em; border: 1px solid var(--bs-secondary-border-subtle);" title="Universal Product Code">UPC: <?php echo htmlspecialchars($album_upc); ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <?php if (!empty($album_data['description'])): ?>
        <div class="mt-3 mb-2 px-3 py-2 border-start border-3 border-primary bg-body rounded-end">
            <p class="mb-0 fst-italic text-body-secondary small">
                <?php echo htmlspecialchars($album_data['description']); ?>
            </p>
        </div>
        <?php endif; ?>

        <div class="d-flex align-items-start mt-3">
            <i class="fa-solid fa-circle-info text-secondary mt-1 me-3 fs-5"></i>
            <p class="small text-body-secondary mb-0 lh-sm">
                <strong>ARCHIVIST NOTE:</strong> <em>The Stardust Engine</em> is a narrative-driven musical universe. The <strong>Narrative Era</strong> denotes when the album was recorded by Ryan and Cassidy within the fictional history of the band. The <strong>DSP Release</strong> reflects the legal copyright date when the audio files were officially pressed and distributed to global streaming platforms.
            </p>
        </div>
    </div>
</div>

<div class="card border-secondary mb-4 bg-transparent">
    <div class="card-header bg-body-tertiary border-secondary d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-uppercase"><i class="fa-solid fa-headphones me-2"></i>Stream the Album</h5>
    </div>
    <div class="card-body">
        <?php if ($dsp_exempt): ?>
            <div class="alert alert-warning border-warning bg-warning-subtle text-warning-emphasis py-2 px-3 mb-0" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i><strong>VAULT EXCLUSIVE:</strong> <?php echo htmlspecialchars($dsp_notice); ?>
            </div>
        <?php else: ?>
            <?php if ($has_active_streams): ?>
                <p class="text-success small mb-3"><strong>Support the band!</strong> Listen to the official release on your favorite streaming platform below.</p>
            <?php else: ?>
                <p class="text-muted small mb-3">Links will become active once the album clears the global distribution network.</p>
            <?php endif; ?>

            <div class="d-flex gap-2 flex-wrap">
                <?php if (!empty($stream_spotify_id)): ?>
                    <a href="https://open.spotify.com/album/<?php echo htmlspecialchars($stream_spotify_id); ?>" target="_blank" class="btn btn-outline-success"><i class="fa-brands fa-spotify me-2"></i>Spotify</a>
                <?php else: ?>
                    <a href="#" class="btn btn-outline-success disabled"><i class="fa-brands fa-spotify me-2"></i>Spotify</a>
                <?php endif; ?>
                
                <?php if (!empty($stream_apple_id)): ?>
                    <a href="https://music.apple.com/us/album/<?php echo htmlspecialchars($stream_apple_id); ?>" target="_blank" class="btn btn-outline-danger"><i class="fa-brands fa-apple me-2"></i>Apple Music</a>
                <?php else: ?>
                    <a href="#" class="btn btn-outline-danger disabled"><i class="fa-brands fa-apple me-2"></i>Apple Music</a>
                <?php endif; ?>

                <?php if (!empty($stream_amazon_id)): ?>
                    <a href="https://music.amazon.com/albums/<?php echo htmlspecialchars($stream_amazon_id); ?>" target="_blank" class="btn btn-outline-info"><i class="fa-brands fa-amazon me-2"></i>Amazon Music</a>
                <?php else: ?>
                    <a href="#" class="btn btn-outline-info disabled"><i class="fa-brands fa-amazon me-2"></i>Amazon Music</a>
                <?php endif; ?>

                <?php if (!empty($stream_youtube_id)): ?>
                    <a href="https://music.youtube.com/playlist?list=<?php echo htmlspecialchars($stream_youtube_id); ?>" target="_blank" class="btn btn-outline-danger"><i class="fa-brands fa-youtube me-2"></i>YouTube Music</a>
                <?php else: ?>
                    <a href="#" class="btn btn-outline-danger disabled"><i class="fa-brands fa-youtube me-2"></i>YouTube Music</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="card border-secondary mb-5 bg-transparent">
    <div class="card-header bg-body-tertiary border-secondary d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-uppercase"><i class="fa-duotone fa-vault me-2"></i>Digital Archives & Studio Masters</h5>
        <div>
            <span class="badge bg-secondary me-2" title="Creative Commons Attribution-ShareAlike 4.0 International">CC BY-SA 4.0</span>
        </div>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">Support the band and own the master tapes. High-fidelity payloads are available directly from the Engine Room storefront.</p>
        
        <div class="d-flex gap-2 flex-wrap">
            <?php if (!empty($store_standard_url)): ?>
                <a href="<?php echo htmlspecialchars($store_standard_url); ?>" target="_blank" class="btn btn-outline-info">
                    <i class="fa-solid fa-file-zipper me-2"></i>Standard Archive (MP3/OGG)
                </a>
            <?php endif; ?>
            
            <?php if (!empty($store_audiophile_url)): ?>
                <a href="<?php echo htmlspecialchars($store_audiophile_url); ?>" target="_blank" class="btn btn-outline-warning">
                    <i class="fa-solid fa-waveform-lines me-2"></i>Audiophile Vault (WAV)
                </a>
            <?php endif; ?>

            <?php if (empty($store_standard_url) && empty($store_audiophile_url)): ?>
                <span class="btn btn-outline-secondary disabled">
                    <i class="fa-solid fa-clock me-2"></i>Archives Pending Processing
                </span>
            <?php endif; ?>
        </div>
    </div>
</div>

<h3 class="h4 fw-bold text-uppercase text-muted mb-3">
    <i class="fa-duotone fa-list-music me-2"></i>Tracklist & Lyrics
</h3>

<div class="tracklist-wrapper mb-5">
    <?php 
    $current_disc = null;
    $current_suite = null;
    $is_list_open = false;

    foreach ($raw_tracks as $index => $track): 
        
        $disc = isset($track['disc']) ? $track['disc'] : 1;
        $disc_name = isset($track['discName']) ? $track['discName'] : '';
        $suite_name = isset($track['suiteName']) ? $track['suiteName'] : '';
        $isrc_code = isset($track['isrc']) ? $track['isrc'] : '';

        // ==========================================
        // DISC HEADER LOGIC
        // ==========================================
        if ($disc !== $current_disc) {
            if ($is_list_open) {
                echo '</div>'; // Close previous list-group
            }
            $current_disc = $disc;
            $current_suite = null; // Reset suite on new disc
            
            $display_disc_name = !empty($disc_name) ? " — <span class='text-body-secondary fs-5'>" . htmlspecialchars($disc_name) . "</span>" : "";
            
            echo '<div class="mt-4 mb-3 pb-2 border-bottom border-secondary-subtle">';
            echo '<h4 class="text-info-emphasis fw-bold mb-0"><i class="fa-duotone fa-compact-disc me-2"></i>Disc ' . $disc . $display_disc_name . '</h4>';
            echo '</div>';
            
            echo '<div class="list-group list-group-flush bg-transparent">';
            $is_list_open = true;
        }

        // ==========================================
        // SUITE HEADER LOGIC
        // ==========================================
        if (!empty($suite_name) && $suite_name !== $current_suite) {
            $current_suite = $suite_name;
            echo '<div class="list-group-item bg-secondary-subtle border-start border-3 border-info py-2 mt-3 mb-1">';
            echo '<h6 class="text-uppercase text-secondary mb-0 fw-bold"><i class="fa-solid fa-layer-group me-2"></i>' . htmlspecialchars($suite_name) . '</h6>';
            echo '</div>';
        } elseif (empty($suite_name) && $current_suite !== null) {
            $current_suite = null; // We've exited a suite
        }

        // ==========================================
        // TRACK ROW GENERATION
        // ==========================================
        $base_name = $track['fileName'];
        $version_string = "?v=" . time(); 
        $lyrics_url = $base_web_path . '/lyrics/' . $base_name . '.md' . $version_string;
        $dl_web_mp3 = $base_web_path . '/web-mp3/' . $base_name . '.mp3' . $version_string;

        // URL Routing based on Feature Flag
        if ($vault_active) {
            $player_src = $dl_web_mp3;
            $gateway_base = "/engine-room/api/download.php?album=" . $archive_base_name . "&track=" . $base_name;
            $dl_mp3 = $gateway_base . "&format=mp3";
            $dl_ogg = $gateway_base . "&format=ogg";
            $dl_wav = $gateway_base . "&format=wav";
        } else {
            $player_src = $dl_web_mp3;
            $dl_mp3 = $base_web_path . '/vault/mp3/' . $base_name . '.mp3' . $version_string;
            $dl_ogg = $base_web_path . '/vault/ogg/' . $base_name . '.ogg' . $version_string;
            $dl_wav = $base_web_path . '/vault/wav/' . $base_name . '.wav'; 
        }

        $legacy_tier = isset($track['legacyTier']) ? $track['legacyTier'] : null;
        $lore_note = isset($track['loreNote']) ? $track['loreNote'] : '';
        $duration = isset($track['duration']) ? $track['duration'] : '';

        // Add to JS Playlist (SCHEMA.ORG INTEGRATION)
        $artist_name = isset($album_data['byArtist']['name']) ? $album_data['byArtist']['name'] : 'Unknown Artist';
        
        $js_playlist[] = [
            'title' => $track['title'],
            'artist' => $artist_name,
            'album' => $album_name,
            'src' => $player_src,
            'artwork' => $album_art_url,
            'lyrics' => $lyrics_url,
            'legacyTier' => $legacy_tier,
            'loreNote' => $lore_note,
            'duration' => $duration
        ];

        $indent_class = !empty($current_suite) ? "ms-4 border-start-0 ps-3" : "";
        ?>
        
        <div class="list-group-item bg-transparent border-secondary text-muted py-3 track-row <?php echo $indent_class; ?>" id="track-row-<?php echo $index; ?>" data-isrc="<?php echo htmlspecialchars($isrc_code); ?>">
            <div class="row align-items-center">
                <div class="col-md-7 mb-2 mb-md-0">
                    <div class="d-flex align-items-center flex-wrap">
                        <span class="text-secondary fw-bold me-3" style="width: 25px;"><?php echo $track['track']; ?>.</span>
                        <div>
                            <strong class="text-body fs-5 d-inline-block me-2"><?php echo htmlspecialchars($track['title']); ?></strong>
                            
                            <?php if (!empty($duration)): ?>
                                <span class="text-secondary small fw-medium me-2" title="Track Length">
                                    <i class="fa-duotone fa-clock me-1" style="--fa-primary-opacity: 0.4;"></i><?php echo htmlspecialchars($duration); ?>
                                </span>
                            <?php endif; ?>

                            <?php 
                            if ($legacy_tier): 
                                $badge_class = 'bg-secondary-subtle text-secondary-emphasis'; 
                                if ($legacy_tier === 'Chart Smash') $badge_class = 'bg-success-subtle text-success-emphasis';
                                if ($legacy_tier === 'Fan Anthem') $badge_class = 'bg-warning-subtle text-warning-emphasis';
                                if ($legacy_tier === 'Deep Cut') $badge_class = 'bg-info-subtle text-info-emphasis';
                                if ($legacy_tier === 'The Dud' || $legacy_tier === 'Studio Filler') $badge_class = 'bg-danger-subtle text-danger-emphasis';
                                if ($legacy_tier === 'Vault Track') $badge_class = 'bg-dark text-warning border border-warning';
                                
                                $safe_lore_attr = htmlspecialchars($lore_note, ENT_QUOTES);
                                $wcag_attrs = $lore_note 
                                    ? 'title="' . $safe_lore_attr . '" aria-label="Legacy Tier: ' . htmlspecialchars($legacy_tier) . '. Lore Note: ' . $safe_lore_attr . '" tabindex="0"'
                                    : 'aria-label="Legacy Tier: ' . htmlspecialchars($legacy_tier) . '" tabindex="0"';
                            ?>
                                <span class="badge <?php echo $badge_class; ?> align-text-bottom" style="font-size: 0.55em; letter-spacing: 0.5px; cursor: help;" <?php echo $wcag_attrs; ?>>
                                    <span aria-hidden="true"><?php echo strtoupper($legacy_tier); ?></span>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-5 text-end mt-2 mt-md-0">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-primary btn-play-index" data-index="<?php echo $index; ?>"><i class="fa-duotone fa-play me-2"></i>Play</button>
                        <button type="button" class="btn btn-sm btn-outline-info btn-view-lyrics" data-title="<?php echo htmlspecialchars($track['title']); ?>" data-url="<?php echo $lyrics_url; ?>"><i class="fa-duotone fa-book-open me-2"></i>Lyrics</button>
                        
                        <div class="btn-group" role="group">
                            <?php if ($vault_active): ?>
                                <button type="button" class="btn btn-sm btn-outline-warning dropdown-toggle" data-bs-toggle="dropdown" title="Vault Access Required"><i class="fa-solid fa-lock"></i></button>
                            <?php else: ?>
                                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown"><i class="fa-duotone fa-download"></i></button>
                            <?php endif; ?>
                            
                            <ul class="dropdown-menu dropdown-menu-end bg-dark border-secondary shadow">
                                <li><h6 class="dropdown-header text-secondary"><i class="fa-solid fa-broadcast-tower me-1"></i> Public Stream</h6></li>
                                <li><a class="dropdown-item text-light license-gate" download href="<?php echo $dl_web_mp3; ?>">MP3 (128kbps)</a></li>
                                
                                <?php if (!$vault_under_construction): ?>
                                    <li><hr class="dropdown-divider border-secondary"></li>
                                    <li><h6 class="dropdown-header <?php echo $vault_active ? 'text-warning' : 'text-white'; ?>"><i class="fa-solid <?php echo $vault_active ? 'fa-vault' : 'fa-compact-disc'; ?> me-1"></i> <?php echo $vault_active ? 'Premium Vault' : 'Master Tapes'; ?></h6></li>
                                    <li><a class="dropdown-item text-light license-gate" href="<?php echo $dl_mp3; ?>">MP3 <?php echo $vault_active ? '(V0)' : '(V0)'; ?></a></li>
                                    <li><a class="dropdown-item text-light license-gate" href="<?php echo $dl_ogg; ?>">OGG <?php echo $vault_active ? '(Q9)' : '(Q9)'; ?></a></li>
                                    <li><hr class="dropdown-divider border-secondary"></li>
                                    <li><a class="dropdown-item text-light license-gate" href="<?php echo $dl_wav; ?>">WAV (Lossless)</a></li>
                                <?php endif; ?>
                            </ul>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    
    <?php if ($is_list_open) echo '</div>'; // Close final list-group ?>
</div>

<script>
    (function() {
        const newPlaylist = <?php echo json_encode($js_playlist); ?>;
        window.STARDUST_PLAYLIST = newPlaylist;
        const event = new CustomEvent('stardust:playlist-update', { detail: { playlist: newPlaylist } });
        document.dispatchEvent(event);
    })();
</script>