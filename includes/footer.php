<?php
// includes/footer.php
// Stardust Engine CMS - Generic MIT Footer

// --- 1. RESOLVE VISUAL FOOTER ---
if (isset($pageConfig['footer'])) {
    $footerFile = $pageConfig['footer'];
} else {
    $footerMap = $settings['footerMap'] ?? [];
    $footerFile = resolveAsset($footerMap, $request_uri) ?? 'footer-default';
}

$currentFooter = ROOT_PATH . '/includes/components/footers/' . $footerFile . '.php';
if (!file_exists($currentFooter)) {
    // Provide a safe fallback if the specific footer is missing
    $currentFooter = ROOT_PATH . '/includes/components/footers/footer-default.php';
}

// Determine if the current theme demands a forced dark legal band
$dark_themes = $settings['forceDarkThemes'] ?? ['dark'];
$isDarkTheme = (isset($currentPageTheme) && in_array($currentPageTheme, $dark_themes));
?>

<footer id="elara-master-footer">
    <div id="visual-footer-container">
        <?php 
        if (file_exists($currentFooter)) {
            include $currentFooter; 
        }
        ?>
    </div>

    <!-- Generic Legal Band -->
    <div id="global-legal-band" class="bg-body-secondary border-top py-3 position-relative z-1" <?php echo $isDarkTheme ? 'data-bs-theme="dark"' : ''; ?>>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 text-center text-lg-start small text-body mb-3 mb-lg-0">
                    <div>
                        <span class="fw-bold">&copy; <?php echo date("Y"); ?> <?php echo htmlspecialchars($settings['siteName'] ?? 'Stardust Engine CMS'); ?>.</span>
                        <span class="mx-2 opacity-50 d-none d-lg-inline">|</span>
                        <span class="opacity-75 d-block d-lg-inline mt-1 mt-lg-0" style="font-size: 0.9em;">
                            Powered by <a href="https://github.com/raggiesoft/stardust-engine-cms" target="_blank" rel="noopener noreferrer" class="text-decoration-none link-body-emphasis border-bottom fw-bold">The Stardust Engine</a>
                        </span>
                    </div>
                    <div class="mt-2 text-body-secondary opacity-75" style="font-size: 0.85em;">
                        Released under the MIT License.
                    </div>
                </div>
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
if (isset($settings['activeModules']) && is_array($settings['activeModules'])) {
    foreach ($settings['activeModules'] as $module) {
        $moduleInitPath = ROOT_PATH . "/includes/modules/" . basename($module) . "/init.php";
        if (file_exists($moduleInitPath)) {
            include $moduleInitPath;
        }
    }
}
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

<?php if (isset($pageConfig['scripts']) && is_array($pageConfig['scripts'])): ?>
    <?php foreach ($pageConfig['scripts'] as $script): ?>
        <script src="<?php echo htmlspecialchars($script); ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>