<?php
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
    
    <!-- Semantic Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-4">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item"><a href="/project" class="text-decoration-none">Discography</a></li>
        <li class="breadcrumb-item active" aria-current="page">Electric Color</li>
      </ol>
    </nav>

    <div class="row mb-5 align-items-center">
        <!-- Album Artwork -->
        <div class="col-md-4 mb-4 mb-md-0">
            <img src="https://assets.example.com<?php echo htmlspecialchars($album_path_web); ?>/album-art.jpg" 
                 alt="Electric Color Album Cover" 
                 class="img-fluid rounded-3 shadow-lg w-100"
                 style="aspect-ratio: 1/1; object-fit: cover;">
        </div>
        
        <!-- Album Header & E-Commerce Module -->
        <div class="col-md-8 px-md-4">
            <h1 class="display-4 fw-bold text-body-emphasis mb-2">Electric Color</h1>
            <h2 class="h4 text-muted mb-4">The Stardust Engine</h2>
            
            <p class="lead mb-4">
                A massive 1980s-inspired rock opera showcasing the power of the Stardust Audio Engine.
            </p>

            <!-- Inject the DSP / Merch Routing Module -->
            <div class="mb-4">
                <?php include ROOT_PATH . '/includes/modules/store-button/store-button.php'; ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <!-- Inject the Tracklist & Media Session Module -->
            <?php include ROOT_PATH . '/includes/modules/stardust-player/tracklist-downloader.php'; ?>
        </div>
    </div>

</div>