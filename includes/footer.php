<?php
/**
 * =========================================================================================
 * ARCHITECTURAL OVERVIEW: footer.php
 * =========================================================================================
 * Closes the standard HTML wrapper initiated by `header.php`. It orchestrates the inclusion
 * of dynamic, site-specific visual footers, outputs a mandatory generic legal/copyright band,
 * executes the Stardust Module Loader to inject plugins (e.g., sticky audio players), and 
 * appends global/page-specific JS assets prior to closing the document body.
 * 
 * FUTURE MAINTENANCE NOTES:
 * - Ensure that `$settings['activeModules']` maintains a consistent directory structure
 *   so that the `init.php` autoloader does not fail.
 * - The legal band utilizes fixed Bootstrap margin/padding classes; changes to standard
 *   theme templates might require manual visual alignment here.
 * =========================================================================================
 */

// -----------------------------------------------------------------------------------------
// 1. Resolve Visual Footer Component
// -----------------------------------------------------------------------------------------
// Determine which specific visual footer layout to render based on page configs or URL maps.
if (isset($pageConfig['footer'])) {
    $footerFile = $pageConfig['footer'];
} else {
    // If no explicit footer is declared, attempt to map via request URI.
    $footerMap = $settings['footerMap'] ?? [];
    $footerFile = resolveAsset($footerMap, $request_uri) ?? 'footer-default';
}

// Construct the absolute path to the designated footer component.
$currentFooter = ROOT_PATH . '/includes/components/footers/' . $footerFile . '.php';
if (!file_exists($currentFooter)) {
    // Provide a safe fallback if the specifically mapped footer component is missing.
    $currentFooter = ROOT_PATH . '/includes/components/footers/footer-default.php';
}

// -----------------------------------------------------------------------------------------
// 2. Dark Mode Legal Band Determination
// -----------------------------------------------------------------------------------------
// Determine if the current theme demands a forced dark mode override for the legal band.
$dark_themes = $settings['forceDarkThemes'] ?? ['dark'];
$isDarkTheme = (isset($currentPageTheme) && in_array($currentPageTheme, $dark_themes));
?>

<footer id="elara-master-footer">
    <!-- Container for dynamically injected primary visual footer layouts -->
    <div id="visual-footer-container">
        <?php 
        // Execute and render the resolved footer component HTML
        if (file_exists($currentFooter)) {
            include $currentFooter; 
        }
        ?>
    </div>

    <?php
    /**
     * -------------------------------------------------------------------------------------
     * Generic Legal Band
     * -------------------------------------------------------------------------------------
     * An invariant element containing standard copyright notices, platform attribution, 
     * and standardized legal links (Privacy Policy, Terms of Service, Contact).
     */
    ?>
    <div id="global-legal-band" class="bg-body-secondary border-top py-3 position-relative z-1" <?php echo $isDarkTheme ? 'data-bs-theme="dark"' : ''; ?>>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 text-center text-lg-start small text-body mb-3 mb-lg-0">
                    <div>
                        <!-- Dynamic year generation and configured site name output -->
                        <span class="fw-bold">&copy; <?php echo date("Y"); ?> <?php echo htmlspecialchars($settings['siteName'] ?? 'Stardust Engine CMS'); ?>.</span>
                        <span class="mx-2 opacity-50 d-none d-lg-inline">|</span>
                        
                        <!-- Engine Attribution -->
                        <span class="opacity-75 d-block d-lg-inline mt-1 mt-lg-0" style="font-size: 0.9em;">
                            Powered by <a href="https://github.com/raggiesoft/stardust-engine-cms" target="_blank" rel="noopener noreferrer" class="text-decoration-none link-body-emphasis border-bottom fw-bold">The Stardust Engine</a>
                        </span>
                    </div>
                    <div class="mt-2 text-body-secondary opacity-75" style="font-size: 0.85em;">
                        Released under the MIT License.
                    </div>
                </div>
                
                <!-- Mandatory Legal Policy Link Routing -->
                <div class="col-lg-6 text-center text-lg-end small mt-2 mt-lg-0">
                    <a href="/privacy" class="text-decoration-none link-body-emphasis me-3 hover-opacity">Privacy Policy</a>
                    <a href="/terms" class="text-decoration-none link-body-emphasis me-3 hover-opacity">Terms of Service</a>
                    <a href="/contact" class="text-decoration-none link-body-emphasis hover-opacity">Contact</a>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- 
======================================================================
STARDUST MODULE LOADER
Dynamically injects UI/Scripts for active modules defined in settings.json
====================================================================== 
-->
<?php 
/**
 * -------------------------------------------------------------------------------------
 * Modular Ecosystem Injection
 * -------------------------------------------------------------------------------------
 * Iterates through the list of active modules declared in the global JSON settings.
 * If a matching initialized script (init.php) exists in the modules directory, it is 
 * included, allowing decoupled plugins to register global DOM elements or script tags.
 */
if (isset($settings['activeModules']) && is_array($settings['activeModules'])) {
    foreach ($settings['activeModules'] as $module) {
        $moduleInitPath = ROOT_PATH . "/includes/modules/" . basename($module) . "/init.php";
        if (file_exists($moduleInitPath)) {
            include $moduleInitPath;
        }
    }
}
?>

<?php
/**
 * -------------------------------------------------------------------------------------
 * Elara SPA Lifecycle Hooks (Footer Additions)
 * -------------------------------------------------------------------------------------
 * Registers events to fire specific actions when the SPA engine completes a virtual 
 * navigation cycle. Primarily used here to trigger virtual pageviews in Google Analytics.
 */
?>
<!-- Elara SPA Lifecycle Hooks -->
<script>
document.addEventListener('elara:loaded', function() {
    // Ping Google Analytics to log the virtual SPA pageview
    if (typeof gtag === 'function') {
        gtag('config', '<?php echo htmlspecialchars($settings['analytics']['trackingId'] ?? ''); ?>', {
            'page_path': window.location.pathname
        });
    }
});
</script>

<?php 
/**
 * -------------------------------------------------------------------------------------
 * Page-Specific Script Injection
 * -------------------------------------------------------------------------------------
 * Safely outputs an array of external script URLs configured within the current page 
 * route context, appending them sequentially before the closing body tag.
 */
if (isset($pageConfig['scripts']) && is_array($pageConfig['scripts'])): ?>
    <?php foreach ($pageConfig['scripts'] as $script): ?>
        <script src="<?php echo htmlspecialchars($script); ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>