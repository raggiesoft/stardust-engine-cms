<?php
/**
 * ============================================================================
 * STARDUST ENGINE CMS - ALBUM RELEASE TEMPLATE
 * ============================================================================
 * 
 * ARCHITECTURE OVERVIEW:
 * This view acts as a "Product Page" template, specifically designed for music 
 * artists and labels. It showcases how to seamlessly integrate the CMS's 
 * e-commerce routing module and the Stardust Audio Player module into a single 
 * cohesive layout.
 * 
 * CORE RESPONSIBILITIES:
 * 1. Module Injection: Demonstrates how to define configuration arrays 
 *    (`$storeProps` and `$album_path_web`) and pass them into required PHP 
 *    modules before including them.
 * 2. Visual Layout: Provides a responsive Bootstrap 5 layout featuring album 
 *    artwork, metadata, and breadcrumb navigation for semantic SEO.
 * 3. Audio Integration: Loads the `tracklist-downloader.php` which interfaces 
 *    with the global SPA audio engine (`stardust-player.js`).
 * 
 * USAGE INSTRUCTIONS:
 * - This file is typically mapped via `routes.json` to a semantic URL like 
 *   `/discography/album-name`.
 * - To use for a new album, duplicate this file and update the configuration 
 *   blocks at the top.
 * - Ensure `$album_path_web` points to a valid directory on the CDN containing 
 *   the audio assets and `tracklist.json`.
 * ============================================================================
 */
// pages/project/album-release.php
// Demonstrates loading advanced audio and e-commerce modules

// 1. Module Configuration: Store Button
// Define the specific DSP and physical merch routing for this release.
$storeProps = [
    'type'    => 'album',
    'size'    => 'large',
    'spotify' => 'YOUR_SPOTIFY_ALBUM_ID',
    'apple'   => 'YOUR_APPLE_MUSIC_ID',
    'amazon'  => 'YOUR_AMAZON_ASIN',
    'youtube' => 'YOUR_YOUTUBE_PLAYLIST_ID',
    'vinyl'   => 'https://store.example.com/products/vinyl-edition',
    'cd'      => 'https://store.example.com/products/cd-edition',
    'apparel' => '' // Leave blank to hide the apparel link
];

// 2. Module Configuration: Tracklist Downloader
// Define the relative path to the album's JSON payload on the CDN.
$album_path_web = '/audio-library/artists/stardust-engine/electric-color';
?>

<div class="container py-5">
    
    <!-- Semantic Breadcrumbs: Crucial for SEO and user navigation -->
    <nav aria-label="breadcrumb" class="mb-4">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item"><a href="/project" class="text-decoration-none">Discography</a></li>
        <li class="breadcrumb-item active" aria-current="page">Electric Color</li>
      </ol>
    </nav>

    <div class="row mb-5 align-items-center">
        <!-- Album Artwork Section -->
        <div class="col-md-4 mb-4 mb-md-0">
            <!-- Dynamically references the CDN path defined in the configuration block -->
            <img src="https://assets.example.com<?php echo htmlspecialchars($album_path_web); ?>/album-art.jpg" 
                 alt="Electric Color Album Cover" 
                 class="img-fluid rounded-3 shadow-lg w-100"
                 style="aspect-ratio: 1/1; object-fit: cover;">
        </div>
        
        <!-- Album Header & E-Commerce Module Section -->
        <div class="col-md-8 px-md-4">
            <h1 class="display-4 fw-bold text-body-emphasis mb-2">Electric Color</h1>
            <h2 class="h4 text-muted mb-4">The Stardust Engine</h2>
            
            <p class="lead mb-4">
                A massive 1980s-inspired rock opera showcasing the power of the Stardust Audio Engine.
            </p>

            <!-- Inject the DSP / Merch Routing Module -->
            <!-- The included file will read the $storeProps array defined above -->
            <div class="mb-4">
                <?php include ROOT_PATH . '/includes/modules/store-button/store-button.php'; ?>
            </div>
        </div>
    </div>

    <!-- Audio Player Module Section -->
    <div class="row">
        <div class="col-12">
            <!-- Inject the Tracklist & Media Session Module -->
            <!-- The included file will read the $album_path_web variable defined above -->
            <?php include ROOT_PATH . '/includes/modules/stardust-player/tracklist-downloader.php'; ?>
        </div>
    </div>

</div>