<?php
// pages/project/radio.php
// Multi-Source Broadcast Console
// Demonstrates flat-file JSON aggregation, deduplication, and sequential block shuffling

$pageTitle = "Global Radio - The Console";

// 1. CONFIGURATION
// Elara injects $cdnBaseUrl globally. Provide a fallback if missing.
$cdn_root = $cdnBaseUrl ?? "https://cdn.example.com"; 

// Define the slugs for the catalogs to query
$station_roster = [
    'artist-one', 
    'artist-two',
    'podcast-network'
];

$master_playlist = []; 
$seen_isrcs = []; 
$shuffle_blocks = []; 

// 2. AGGREGATOR LOGIC
foreach ($station_roster as $catalog_slug) {
    
    $albums_json_url = "{$cdn_root}/catalog/{$catalog_slug}/albums.json";
    $albums_json_content = @file_get_contents($albums_json_url);
    
    if ($albums_json_content) {
        $master_data = json_decode($albums_json_content, true);
        
        if (is_array($master_data)) {
            foreach ($master_data as $era) {
                if (!empty($era['albums'])) {
                    foreach ($era['albums'] as $album) {
                        
                        // Skip unreleased or canceled projects
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
                            
                            // Check if this album demands sequential playback (e.g., Rock Operas, Live Sets)
                            $is_sequential = isset($meta_data['isSequential']) && $meta_data['isSequential'] === true;
                            $sequential_chunk = [];

                            foreach ($raw_tracks as $track) {
                                $fn = $track['fileName'] ?? ($track['filename'] ?? null);
                                if (!$fn) continue;

                                $isrc = $track['isrc'] ?? '';
                                
                                // Deduplication Tracker
                                if (!empty($isrc)) {
                                    if (in_array($isrc, $seen_isrcs)) continue; 
                                    $seen_isrcs[] = $isrc; 
                                }

                                $track_payload = [
                                    'title' => $track['title'],
                                    'artist' => $artist_name,
                                    'album' => $album_title,
                                    'artwork' => "{$base_path}/album-art.jpg?v=" . time(),
                                    'src' => "{$base_path}/web-mp3/{$fn}.mp3?v=" . time(),
                                    'lyrics' => "{$base_path}/lyrics/{$fn}.md?v=" . time(),
                                    'duration' => $track['duration'] ?? ''
                                ];

                                // Group sequential tracks, isolate standard tracks
                                if ($is_sequential) {
                                    $sequential_chunk[] = $track_payload;
                                } else {
                                    $shuffle_blocks[] = [$track_payload];
                                }
                            }
                            
                            // Push the glued chunk to the shuffler as a single discrete block
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

// 3. THE SMART SHUFFLE
// Shuffle the blocks (Standard tracks and sequential concept albums move around as discrete units)
shuffle($shuffle_blocks);

// Flatten the shuffled blocks back into a 1D playlist for the Media Player
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

// Convert to Days, Hours, Minutes
$d = floor($total_seconds / 86400);
$h = floor(($total_seconds % 86400) / 3600);
$m = floor(($total_seconds % 3600) / 60);

$time_strings = [];
if ($d > 0) $time_strings[] = $d . ($d === 1 ? " Day" : " Days");
if ($h > 0) $time_strings[] = $h . ($h === 1 ? " Hour" : " Hours");
if ($m > 0 || empty($time_strings)) $time_strings[] = $m . " Minutes";

$block_time = implode(', ', $time_strings);

// Dynamic UI Context String
if ($d >= 2) {
    $context_string = "A deep-space orbital deployment.";
} elseif ($d >= 1) {
    $context_string = "A multi-day transoceanic transit.";
} elseif ($h >= 12) {
    $context_string = "An ultra-long-haul global flight.";
} elseif ($h >= 6) {
    $context_string = "A full closing shift.";
} elseif ($h >= 3) {
    $context_string = "A long interstate drive.";
} else {
    $context_string = "A quick regional hop.";
}
?>

<div class="container-fluid p-0">
    <div class="p-5 text-center border-bottom border-secondary" 
         style="background: linear-gradient(rgba(13, 17, 23, 0.8), rgba(13, 17, 23, 0.95)), url('https://assets.example.com/images/studio-rack.jpg'); background-size: cover; background-position: center;">
        
        <h1 class="display-2 fw-bold text-uppercase text-warning mb-2" style="font-family: system-ui, sans-serif;">
            <i class="fa-solid fa-signal-stream me-3" aria-hidden="true"></i>Global Radio
        </h1>
        <p class="lead text-secondary font-monospace">Broadcasting from the Mainframe // <span class="text-success fw-bold"><i class="fa-solid fa-circle text-danger blink-me fs-6 pb-1" aria-hidden="true"></i> LIVE</span></p>
        
        <div class="mt-3 text-secondary font-monospace small" style="letter-spacing: 0.5px;">
            <i class="fa-solid fa-plane-departure me-2 text-info" aria-hidden="true"></i>ESTIMATED BLOCK TIME: <span class="text-white fw-bold"><?php echo $block_time; ?></span>
            <div class="mt-1 text-white-50 fst-italic" style="font-size: 0.9em;">
                // <?php echo $context_string; ?>
            </div>
        </div>
        
        <div class="mt-4">
            <button class="btn btn-lg btn-outline-warning rounded-pill px-5 shadow-sm btn-play-index" data-index="0">
                <i class="fa-solid fa-play me-2" aria-hidden="true"></i>TUNE IN
            </button>
        </div>
    </div>
    
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card bg-body-tertiary border-secondary shadow-lg">
                    <div class="card-header bg-transparent border-bottom border-secondary p-3 d-flex justify-content-between align-items-center">
                        <h2 class="h5 text-body-emphasis mb-0 text-uppercase"><i class="fa-solid fa-list-music me-2" aria-hidden="true"></i>The Broadcast Queue</h2>
                        <span class="badge bg-primary text-light font-monospace"><?php echo count($master_playlist); ?> Tracks</span>
                    </div>
                    
                    <div class="card-body p-0" style="max-height: 700px; overflow-y: auto;">
                        <div class="list-group list-group-flush bg-transparent">
                            <?php foreach ($master_playlist as $index => $track): ?>
                                <button type="button" 
                                        class="list-group-item list-group-item-action bg-transparent border-secondary track-row d-flex align-items-center p-3 btn-play-index"
                                        id="track-row-<?php echo $index; ?>"
                                        data-index="<?php echo $index; ?>">
                                    
                                    <div class="me-3 text-secondary font-monospace fw-bold" style="width: 30px;">
                                        <?php echo str_pad($index + 1, 2, '0', STR_PAD_LEFT); ?>
                                    </div>
                                    
                                    <img src="<?php echo htmlspecialchars($track['artwork']); ?>" class="rounded shadow-sm me-3 border border-secondary" style="width: 50px; height: 50px; object-fit: cover;" alt="Artwork">
                                    
                                    <div class="flex-grow-1 text-start">
                                        <div class="text-body-emphasis fs-5 mb-1"><strong><?php echo htmlspecialchars($track['title']); ?></strong></div>
                                        <div class="small text-info text-uppercase fw-semibold"><i class="fa-solid fa-microphone-lines me-1" aria-hidden="true"></i><?php echo htmlspecialchars($track['artist']); ?></div>
                                    </div>

                                    <div class="ms-3 ms-md-5 text-end d-none d-sm-block">
                                        <div class="small text-body-secondary font-monospace mb-1"><i class="fa-solid fa-compact-disc me-1" aria-hidden="true"></i><?php echo htmlspecialchars($track['album']); ?></div>
                                        <?php if (!empty($track['duration'])): ?>
                                            <div class="small text-secondary fw-semibold">
                                                <i class="fa-solid fa-clock me-1" aria-hidden="true"></i><?php echo htmlspecialchars($track['duration']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="ms-4 ps-3 border-start border-secondary">
                                        <i class="play-indicator fa-solid fa-play-circle fs-3 text-secondary opacity-50" aria-hidden="true"></i>
                                    </div>
                                </button>
                            <?php endforeach; ?>
                        </div>
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
    .track-row:hover .play-indicator {
        color: var(--bs-warning) !important;
        opacity: 1;
    }
</style>

<script>
    // Hand off the aggregated playlist to the Stardust Player module
    window.STARDUST_PLAYLIST = <?php echo json_encode($master_playlist); ?>;
    
    setTimeout(() => {
        const event = new CustomEvent('stardust:playlist-update', { detail: { playlist: window.STARDUST_PLAYLIST } });
        document.dispatchEvent(event);
    }, 50);
</script>