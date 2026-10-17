<?php
/**
 * =========================================================================================
 * ARCHITECTURAL OVERVIEW: stardust-player/init.php
 * =========================================================================================
 * This initialization script acts as the entry point for the "Stardust Player" module.
 * It is dynamically invoked by the central Stardust Module Loader in `includes/footer.php`.
 * 
 * CORE RESPONSIBILITIES:
 * 1. DOM Injection: Embeds the required global HTML container (`sticky-player.php`) into 
 *    the application layout so it persists across Elara SPA page transitions.
 * 2. Asset Resolution Strategy: Loads the JavaScript audio engine locally by default,
 *    or from `$cdnBaseUrl` if `$preferCdn` is enabled and `$cdnBaseUrl` is configured.
 * 3. Scope Bootstrapping: Ensures the global JavaScript namespace (`STARDUST_PLAYLIST`) 
 *    is safely instantiated before the core engine begins parsing it.
 * =========================================================================================
 */

// -----------------------------------------------------------------------------------------
// 1. Inject the HTML UI
// -----------------------------------------------------------------------------------------
$playerUI = __DIR__ . '/sticky-player.php';
if (file_exists($playerUI)) {
    echo '<div id="global-player-zone" class="fixed-bottom" style="z-index: 1050; pointer-events: none;">';
    include $playerUI;
    echo '</div>';
}

// -----------------------------------------------------------------------------------------
// 2. Asset Resolution Strategy (Local Default vs Optional CDN)
// -----------------------------------------------------------------------------------------
$preferCdn = false;
$jsVersion = '1.0.0';

if ($preferCdn && !empty($cdnBaseUrl)) {
    $jsPath = rtrim($cdnBaseUrl, '/') . '/assets/js/stardust-player.js';
} else {
    $jsPath = '/includes/modules/stardust-player/stardust-player.js';
}
?>

<!-- Output the resolved JavaScript module -->
<script src="<?php echo htmlspecialchars($jsPath); ?>?v=<?php echo $jsVersion; ?>"></script>

<script>
    window.STARDUST_PLAYLIST = window.STARDUST_PLAYLIST || [];
</script>