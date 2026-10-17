<?php
/**
 * ============================================================================
 * STARDUST ENGINE CMS - ALBUM RELEASE TEMPLATE
 * ============================================================================
 * 
 * ARCHITECTURE OVERVIEW:
 * This view acts as a "Product & Media Page" template for musicians, podcasters,
 * and digital creators. It showcases how to integrate the CMS's DSP / Merch
 * routing component (`store-button.php`) and the persistent Stardust Audio Player
 * tracklist component (`_tracklist-downloader.php`) into a single layout.
 * 
 * OUT-OF-THE-BOX LOCAL DEMO:
 * Points `$album_path_web` to `/assets/json/sample-album`, which contains a
 * complete local `album.json`, `tracks.json`, SVG cover art, and Markdown lyrics
 * so developers can test the audio player and lyrics modal immediately after cloning!
 * ============================================================================
 */

// 1. Component Configuration: Store Button (`includes/components/store-button.php`)
$storeProps = [
    'type'    => 'album',
    'size'    => 'medium',
    'spotify' => 'sample-spotify-id',
    'apple'   => 'sample-apple-id',
    'amazon'  => 'sample-amazon-id',
    'youtube' => 'sample-youtube-id',
    'vinyl'   => 'https://example.com/store/vinyl-edition',
    'cd'      => 'https://example.com/store/cd-edition',
    'apparel' => '' // Leave blank to hide the apparel link
];

// 2. Component Configuration: Tracklist Downloader (`includes/components/_tracklist-downloader.php`)
// Points to the included local sample album payload in `/assets/json/sample-album`
$album_path_web = '/assets/json/sample-album';
?>

<div class="container py-4">
    
    <!-- Semantic Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-4">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item"><a href="/project" class="text-decoration-none">Project</a></li>
        <li class="breadcrumb-item active" aria-current="page">Neon Horizon (Audio Demo)</li>
      </ol>
    </nav>

    <div class="row mb-5 align-items-center">
        <!-- Album Artwork Section -->
        <div class="col-md-4 mb-4 mb-md-0">
            <img src="<?php echo htmlspecialchars($album_path_web); ?>/album-art.svg" 
                 alt="Neon Horizon Sample Album Cover" 
                 class="img-fluid rounded-3 shadow-lg w-100 border border-secondary-subtle"
                 style="aspect-ratio: 1/1; object-fit: cover;">
        </div>
        
        <!-- Album Header & E-Commerce Component Section -->
        <div class="col-md-8 px-md-4">
            <span class="badge bg-primary-subtle text-primary-emphasis rounded-pill px-3 py-1 mb-2 font-monospace">
                PERSISTENT SPA AUDIO MODULE DEMO
            </span>
            <h1 class="display-5 fw-bold text-body-emphasis mb-1">Neon Horizon</h1>
            <h2 class="h4 text-body-secondary mb-3">Horizon Synth Collective</h2>
            
            <p class="lead text-body-secondary mb-4">
                Click <strong>Play</strong> on any track below to launch the floating Sticky Audio Player, then click any link in the sidebar or header—notice how audio playback continues without interruption as the Elara SPA router swaps the page!
            </p>

            <!-- Inject the DSP / Merch Routing Component -->
            <div class="mb-3">
                <?php include ROOT_PATH . '/includes/components/store-button.php'; ?>
            </div>
        </div>
    </div>

    <!-- Audio Player & Tracklist Component Section -->
    <div class="row">
        <div class="col-12">
            <?php include ROOT_PATH . '/includes/components/_tracklist-downloader.php'; ?>
        </div>
    </div>

</div>