<?php
/**
 * =========================================================================================
 * ARCHITECTURAL OVERVIEW: stardust-player/init.php
 * =========================================================================================
 * This initialization script acts as the entry point for the "Stardust Player" module.
 * It is dynamically invoked by the central Stardust Module Loader (typically from footer.php).
 * 
 * CORE RESPONSIBILITIES:
 * 1. DOM Injection: Embeds the required global HTML container (`sticky-player.php`) into 
 *    the application layout if it exists.
 * 2. Asset Resolution Strategy: Determines whether to load the core JavaScript engine from 
 *    a high-speed Content Delivery Network (CDN) or fallback to a local repository file.
 * 3. Scope Bootstrapping: Ensures the global JavaScript namespace (`STARDUST_PLAYLIST`) 
 *    is safely instantiated before the core engine begins parsing it.
 * 
 * FUTURE MAINTENANCE NOTES:
 * - Module specific configurations (like `$preferCdn`) might be abstracted to a dedicated 
 *   JSON file (e.g., `config.json`) within this module's directory in the future.
 * - The `$cdnBaseUrl` dependency assumes a specific naming structure within the CDN for
 *   the player script (`engine-room-records/js/stardust-player.js`).
 * =========================================================================================
 */

// -----------------------------------------------------------------------------------------
// 1. Inject the HTML UI
// -----------------------------------------------------------------------------------------
// The JS player heavily relies on specific DOM elements present in this markup.
// We embed this in a fixed-bottom global zone, heavily elevating its z-index so it floats
// above standard Elara SPA page content.
$playerUI = __DIR__ . '/sticky-player.php';
if (file_exists($playerUI)) {
    echo '<div id="global-player-zone" class="fixed-bottom" style="z-index: 1050;">';
    include $playerUI;
    echo '</div>';
}

// -----------------------------------------------------------------------------------------
// 2. Asset Resolution Strategy (CDN vs Local)
// -----------------------------------------------------------------------------------------
// $cdnBaseUrl is assumed to be globally available, typically injected by the core `elara.php` 
// bootstrap process before modules are loaded.

// Flag determining if the module should attempt to pull static assets from an external CDN
$preferCdn = true; // This could eventually be read from a module-specific config.json

// Cache busting variable to ensure clients fetch the latest player script version on reload
$jsVersion = time(); 

// Evaluate routing path based on preference and global CDN availability
if ($preferCdn && !empty($cdnBaseUrl)) {
    // Route to the high-speed CDN, stripping any trailing slashes from the base URL 
    // to prevent malformed double-slash routing issues.
    $jsPath = rtrim($cdnBaseUrl, '/') . '/engine-room-records/js/stardust-player.js';
} else {
    // Fallback to the local repository file if no CDN is defined or preferred
    $jsPath = '/includes/modules/stardust-player/stardust-player.js'; 
}
?>

<!-- Output the resolved JavaScript module via standard script tag inclusion -->
<script src="<?php echo htmlspecialchars($jsPath); ?>?v=<?php echo $jsVersion; ?>"></script>

<script>
    /**
     * -------------------------------------------------------------------------------------
     * Namespace Safe-Guarding
     * -------------------------------------------------------------------------------------
     * Initialize the global playlist registry safely to prevent `undefined` reference
     * exceptions if the core player attempts to read the state before standard page 
     * components push to it.
     */
    window.STARDUST_PLAYLIST = window.STARDUST_PLAYLIST || [];
</script>