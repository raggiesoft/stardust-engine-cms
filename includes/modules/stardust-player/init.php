<?php
// includes/modules/stardust-player/init.php
// Initializes the Stardust Player and handles asset routing

// 1. Inject the HTML UI (The DOM elements the JS needs)
$playerUI = __DIR__ . '/sticky-player.php';
if (file_exists($playerUI)) {
    echo '<div id="global-player-zone" class="fixed-bottom" style="z-index: 1050;">';
    include $playerUI;
    echo '</div>';
}

// 2. Asset Resolution Strategy (CDN vs Local)
// $cdnBaseUrl is globally available from elara.php

$preferCdn = true; // This could eventually be read from a module-specific config.json
$jsVersion = time(); // Cache busting

if ($preferCdn && !empty($cdnBaseUrl)) {
    // Route to the high-speed CDN
    $jsPath = rtrim($cdnBaseUrl, '/') . '/engine-room-records/js/stardust-player.js';
} else {
    // Fallback to the local repository file if no CDN is defined
    $jsPath = '/includes/modules/stardust-player/stardust-player.js'; 
}
?>

<script src="<?php echo htmlspecialchars($jsPath); ?>?v=<?php echo $jsVersion; ?>"></script>
<script>
    // Initialize global registry safely
    window.STARDUST_PLAYLIST = window.STARDUST_PLAYLIST || [];
</script>