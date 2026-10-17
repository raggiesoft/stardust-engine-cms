<?php
/**
 * ============================================================================
 * COMPONENT: radio.php
 * CONTEXT: Global Radio / Multi-Source Broadcast Console & Parsedown Showcase
 *
 * PURPOSE:
 * Acts as an automated DJ by aggregating flat-file JSON tracklists from multiple
 * catalogs, deduplicating tracks via ISRC codes, and applying a smart shuffle
 * algorithm to generate a continuous broadcast queue.
 *
 * OUT-OF-THE-BOX LOCAL FALLBACK:
 * If no external CDN catalog is configured in `data/settings.json`, this page
 * automatically seeds the broadcast queue from the local `/assets/json/sample-album`
 * payload so developers can test the radio console immediately after cloning.
 * ============================================================================
 */

$pageTitle = "Global Radio Console | Stardust Engine CMS";

// 1. CONFIGURATION
$cdn_root = !empty($cdnBaseUrl) ? rtrim($cdnBaseUrl, '/') : ''; 

$station_roster = [
    'artist-one', 
    'artist-two',
    'podcast-network'
];

$master_playlist = []; 
$seen_isrcs = []; 
$shuffle_blocks = []; 

// 2. REMOTE CDN AGGREGATOR LOGIC (Runs when $cdnBaseUrl is configured)
if (!empty($cdn_root)) {
    foreach ($station_roster as $catalog_slug) {
        $albums_json_url = "{$cdn_root}/catalog/{$catalog_slug}/albums.json";
        $albums_json_content = @file_get_contents($albums_json_url);
        
        if ($albums_json_content) {
            $master_data = json_decode($albums_json_content, true);
            if (is_array($master_data)) {
                foreach ($master_data as $era) {
                    if (!empty($era['albums'])) {
                        foreach ($era['albums'] as $album) {
                            if (isset($album['status']) && $album['status'] === 'CANCELED') continue;

                            $album_folder = $album['folder'] ?? basename($album['url']);
                            $base_path = "{$cdn_root}/catalog/{$catalog_slug}/{$album_folder}";
                            
                            $tracks_json = @file_get_contents("{$base_path}/tracks.json");
                            $album_json = @file_get_contents("{$base_path}/album.json");

                            if ($tracks_json && $album_json) {
                                $tracks_data = json_decode($tracks_json, true);
                                $meta_data = json_decode($album_json, true);
                                $raw_tracks = $tracks_data['tracks'] ?? $tracks_data;
                                
                                $artist_name = $meta_data['byArtist']['name'] ?? 'Unknown Artist';
                                $album_title = $meta_data['name'] ?? 'Unknown Release';
                                $is_sequential = isset($meta_data['isSequential']) && $meta_data['isSequential'] === true;
                                $sequential_chunk = [];

                                foreach ($raw_tracks as $track) {
                                    $fn = $track['fileName'] ?? ($track['filename'] ?? null);
                                    if (!$fn) continue;

                                    $isrc = $track['isrc'] ?? '';
                                    if (!empty($isrc)) {
                                        if (in_array($isrc, $seen_isrcs, true)) continue; 
                                        $seen_isrcs[] = $isrc; 
                                    }

                                    $track_payload = [
                                        'title' => $track['title'],
                                        'artist' => $artist_name,
                                        'album' => $album_title,
                                        'artwork' => "{$base_path}/album-art.jpg",
                                        'src' => "{$base_path}/web-mp3/{$fn}.mp3",
                                        'lyrics' => "{$base_path}/lyrics/{$fn}.md",
                                        'duration' => $track['duration'] ?? '',
                                        'isrc' => $isrc
                                    ];

                                    if ($is_sequential) {
                                        $sequential_chunk[] = $track_payload;
                                    } else {
                                        $shuffle_blocks[] = [$track_payload];
                                    }
                                }
                                
                                if ($is_sequential && !empty($sequential_chunk)) {
                                    $shuffle_blocks[] = $sequential_chunk;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

// 3. LOCAL SAMPLE FALLBACK (Ensures out-of-the-box functionality without a remote CDN)
if (empty($shuffle_blocks) && defined('ROOT_PATH')) {
    $sample_dir = ROOT_PATH . '/assets/json/sample-album';
    if (file_exists($sample_dir . '/tracks.json') && file_exists($sample_dir . '/album.json')) {
        $tracks_data = json_decode(file_get_contents($sample_dir . '/tracks.json'), true);
        $meta_data = json_decode(file_get_contents($sample_dir . '/album.json'), true);
        $raw_tracks = $tracks_data['tracks'] ?? [];
        foreach ($raw_tracks as $track) {
            $fn = $track['fileName'];
            $shuffle_blocks[] = [[
                'title' => $track['title'],
                'artist' => $meta_data['byArtist']['name'] ?? 'Horizon Synth Collective',
                'album' => $meta_data['name'] ?? 'Neon Horizon',
                'artwork' => '/assets/json/sample-album/album-art.svg',
                'src' => '/assets/json/sample-album/web-mp3/' . $fn . '.mp3',
                'lyrics' => '/assets/json/sample-album/lyrics/' . $fn . '.md',
                'duration' => $track['duration'] ?? '04:00',
                'legacyTier' => $track['legacyTier'] ?? null,
                'loreNote' => $track['loreNote'] ?? '',
                'isrc' => $track['isrc'] ?? ''
            ]];
        }
    }
}

// 4. THE SMART SHUFFLE
shuffle($shuffle_blocks);

$master_playlist = [];
foreach ($shuffle_blocks as $block) {
    foreach ($block as $track) {
        $master_playlist[] = $track;
    }
}

// --- CALCULATE TOTAL BROADCAST DURATION ---
$total_seconds = 0;
foreach ($master_playlist as $track) {
    if (!empty($track['duration'])) {
        $parts = explode(':', $track['duration']);
        if (count($parts) === 2) {
            $total_seconds += ((int)$parts[0] * 60) + (int)$parts[1];
        } elseif (count($parts) === 3) {
            $total_seconds += ((int)$parts[0] * 3600) + ((int)$parts[1] * 60) + (int)$parts[2];
        }
    }
}

$d = floor($total_seconds / 86400);
$h = floor(($total_seconds % 86400) / 3600);
$m = floor(($total_seconds % 3600) / 60);

$time_strings = [];
if ($d > 0) $time_strings[] = $d . ($d === 1 ? " Day" : " Days");
if ($h > 0) $time_strings[] = $h . ($h === 1 ? " Hour" : " Hours");
if ($m > 0 || empty($time_strings)) $time_strings[] = $m . " Minutes";

$block_time = implode(', ', $time_strings);
$context_string = "Smart-shuffled flat-file broadcast queue ready.";
?>

<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-4">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item"><a href="/project" class="text-decoration-none">Project</a></li>
        <li class="breadcrumb-item active" aria-current="page">Global Radio Console</li>
      </ol>
    </nav>

    <div class="p-4 p-md-5 text-center rounded-4 border border-secondary-subtle mb-5" 
         data-bs-theme="dark"
         style="background: linear-gradient(135deg, #0b0f19 0%, #1e1b4b 100%); color: #f8fafc;">
        
        <h1 class="display-5 fw-bold text-uppercase text-warning mb-2">
            <i class="fa-solid fa-tower-broadcast me-2" aria-hidden="true"></i>Global Radio Console
        </h1>
        <p class="lead text-light opacity-75 font-monospace mb-2">
            Multi-Catalog JSON Aggregator // <span class="text-success fw-bold"><i class="fa-solid fa-circle text-danger blink-me fs-6 me-1" aria-hidden="true"></i>LIVE DEMO</span>
        </p>
        
        <div class="mt-3 text-light opacity-75 font-monospace small" style="letter-spacing: 0.5px;">
            <i class="fa-solid fa-clock me-2 text-info" aria-hidden="true"></i>ESTIMATED QUEUE RUNTIME: <span class="text-white fw-bold"><?php echo htmlspecialchars($block_time); ?></span>
            <div class="mt-1 fst-italic" style="font-size: 0.9em;">
                // <?php echo htmlspecialchars($context_string); ?>
            </div>
        </div>
        
        <div class="mt-4">
            <button type="button" class="btn btn-lg btn-warning fw-bold rounded-pill px-5 shadow-sm btn-play-index" data-index="0">
                <i class="fa-solid fa-play me-2" aria-hidden="true"></i>START BROADCAST
            </button>
        </div>
    </div>
    
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card bg-body-tertiary border-secondary-subtle shadow-sm">
                <div class="card-header bg-transparent border-bottom border-secondary-subtle p-3 d-flex justify-content-between align-items-center">
                    <h2 class="h5 text-body-emphasis mb-0 text-uppercase"><i class="fa-solid fa-list-ul me-2 text-primary" aria-hidden="true"></i>Shuffled Broadcast Queue</h2>
                    <span class="badge bg-primary font-monospace"><?php echo count($master_playlist); ?> Tracks</span>
                </div>
                
                <div class="card-body p-0" style="max-height: 700px; overflow-y: auto;">
                    <div class="list-group list-group-flush bg-transparent">
                        <?php foreach ($master_playlist as $index => $track): ?>
                            <button type="button" 
                                    class="list-group-item list-group-item-action bg-transparent border-secondary-subtle track-row d-flex align-items-center p-3 btn-play-index"
                                    id="track-row-<?php echo (int)$index; ?>"
                                    data-index="<?php echo (int)$index; ?>">
                                
                                <div class="me-3 text-body-secondary font-monospace fw-bold" style="width: 30px;">
                                    <?php echo str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT); ?>
                                </div>
                                
                                <img src="<?php echo htmlspecialchars($track['artwork']); ?>" class="rounded shadow-sm me-3 border border-secondary-subtle" style="width: 50px; height: 50px; object-fit: cover;" alt="<?php echo htmlspecialchars($track['album']); ?> Artwork">
                                
                                <div class="flex-grow-1 text-start">
                                    <div class="text-body-emphasis fs-5 mb-1"><strong><?php echo htmlspecialchars($track['title']); ?></strong></div>
                                    <div class="small text-primary text-uppercase fw-semibold"><i class="fa-solid fa-microphone-lines me-1" aria-hidden="true"></i><?php echo htmlspecialchars($track['artist']); ?></div>
                                </div>

                                <div class="ms-3 ms-md-5 text-end d-none d-sm-block">
                                    <div class="small text-body-secondary font-monospace mb-1"><i class="fa-solid fa-compact-disc me-1" aria-hidden="true"></i><?php echo htmlspecialchars($track['album']); ?></div>
                                    <?php if (!empty($track['duration'])): ?>
                                        <div class="small text-body-secondary fw-semibold">
                                            <i class="fa-regular fa-clock me-1" aria-hidden="true"></i><?php echo htmlspecialchars($track['duration']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="ms-4 ps-3 border-start border-secondary-subtle">
                                    <i class="play-indicator fa-solid fa-circle-play fs-3 text-secondary opacity-50" aria-hidden="true"></i>
                                </div>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .blink-me {
        animation: blinker 1.5s linear infinite;
    }
    @keyframes blinker {
        50% { opacity: 0; }
    }
    @media (prefers-reduced-motion: reduce) {
        .blink-me { animation: none; }
    }
    .track-row:hover .play-indicator {
        color: var(--bs-primary) !important;
        opacity: 1 !important;
    }
</style>

<script>
    (function() {
        const queue = <?php echo json_encode($master_playlist, JSON_UNESCAPED_SLASHES); ?>;
        window.STARDUST_PLAYLIST = queue;
        setTimeout(() => {
            const event = new CustomEvent('stardust:playlist-update', { detail: { playlist: queue } });
            document.dispatchEvent(event);
        }, 50);
    })();
</script>