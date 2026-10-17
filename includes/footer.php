<?php
/**
 * =========================================================================================
 * ARCHITECTURAL OVERVIEW: footer.php
 * =========================================================================================
 * Closes the standard HTML wrapper initiated by `header.php`. It orchestrates the inclusion
 * of dynamic, site-specific visual footers, outputs a mandatory generic legal/copyright band,
 * executes the Stardust Module Loader to inject plugins (e.g., sticky audio players), and 
 * appends Bootstrap 5.3.8 JS + global/page-specific JS assets prior to closing the document body.
 * 
 * FUTURE MAINTENANCE NOTES:
 * - Ensure that `$settings['activeModules']` maintains a consistent directory structure
 *   so that the `init.php` autoloader does not fail.
 * - Uses global event delegation on `document.body` for `[data-stardust-theme-toggle]` and
 *   `#btn-theme-toggle` so theme switching survives Elara SPA DOM swaps without Ghost DOM bugs.
 * =========================================================================================
 */

// -----------------------------------------------------------------------------------------
// 1. Resolve Visual Footer Component
// -----------------------------------------------------------------------------------------
if (isset($pageConfig['footer'])) {
    $footerFile = $pageConfig['footer'];
} else {
    $footerMap = $settings['footerMap'] ?? [];
    $footerFile = resolveAsset($footerMap, $request_uri) ?? 'footer-default';
}

$currentFooter = ROOT_PATH . '/includes/components/footers/' . $footerFile . '.php';
if (!file_exists($currentFooter)) {
    $currentFooter = ROOT_PATH . '/includes/components/footers/footer-default.php';
}

// -----------------------------------------------------------------------------------------
// 2. Dark Mode Legal Band Determination
// -----------------------------------------------------------------------------------------
$dark_themes = $settings['forceDarkThemes'] ?? ['dark'];
$isDarkTheme = (isset($currentPageTheme) && in_array($currentPageTheme, $dark_themes, true));
?>

<footer id="elara-master-footer" class="mt-auto">
    <!-- Container for dynamically injected primary visual footer layouts -->
    <div id="visual-footer-container">
        <?php 
        if (file_exists($currentFooter)) {
            include $currentFooter; 
        }
        ?>
    </div>

    <!-- Generic Legal & Attribution Band -->
    <div id="global-legal-band" class="bg-body-secondary border-top py-3 position-relative z-1" <?php echo $isDarkTheme ? 'data-bs-theme="dark"' : ''; ?>>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 text-center text-lg-start small text-body mb-3 mb-lg-0">
                    <div>
                        <span class="fw-bold">&copy; <?php echo date("Y"); ?> <?php echo htmlspecialchars($settings['siteName'] ?? 'Stardust Engine CMS'); ?>.</span>
                        <span class="mx-2 opacity-50 d-none d-lg-inline">|</span>
                        
                        <span class="d-block d-lg-inline mt-1 mt-lg-0" style="font-size: 0.9em;">
                            Powered by <a href="https://github.com/raggiesoft/stardust-engine-cms" target="_blank" rel="noopener noreferrer" class="text-decoration-none link-body-emphasis border-bottom fw-bold">The Stardust Engine CMS</a>
                        </span>
                    </div>
                    <div class="mt-1 text-body-secondary" style="font-size: 0.85em;">
                        Open-source flat-file architecture released under the MIT License.
                    </div>
                </div>
                
                <!-- Quick Architecture & Demo Links -->
                <div class="col-lg-6 text-center text-lg-end small mt-2 mt-lg-0">
                    <a href="/project" class="text-decoration-none link-body-emphasis me-3">Documentation</a>
                    <a href="/project/album-releases" class="text-decoration-none link-body-emphasis me-3">Audio Module</a>
                    <a href="https://github.com/raggiesoft/stardust-engine-cms" target="_blank" rel="noopener noreferrer" class="text-decoration-none link-body-emphasis">GitHub</a>
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

<!-- Bootstrap 5.3.8 JS Bundle (Popper included — powers navbar collapse, dropdowns, and modals) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

<!-- Global Theme Toggle Delegation & Elara SPA Lifecycle Hooks -->
<script>
(function() {
    /**
     * Global Event Delegation for Theme Toggling (`#btn-theme-toggle` and `[data-stardust-theme-toggle]`).
     * Bound once on `document.body` so it remains active across all Elara SPA navigations.
     */
    if (!window.__STARDUST_THEME_DELEGATED) {
        window.__STARDUST_THEME_DELEGATED = true;

        document.body.addEventListener('click', function(e) {
            const toggleBtn = e.target.closest('#btn-theme-toggle, [data-stardust-theme-toggle]');
            if (!toggleBtn) return;

            e.preventDefault();
            const htmlEl = document.documentElement;
            const currentTheme = htmlEl.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

            htmlEl.setAttribute('data-bs-theme', newTheme);
            try {
                localStorage.setItem('theme', newTheme);
            } catch (err) {}

            // Synchronize any open modal theme attributes
            const lyricsModal = document.getElementById('lyricsModal');
            if (lyricsModal) {
                lyricsModal.setAttribute('data-bs-theme', newTheme);
            }
        });
    }

    document.addEventListener('elara:loaded', function() {
        // Ping Google Analytics to log the virtual SPA pageview if configured
        if (typeof gtag === 'function') {
            gtag('config', '<?php echo htmlspecialchars($settings['analytics']['trackingId'] ?? ''); ?>', {
                'page_path': window.location.pathname
            });
        }
    });
})();
</script>

<?php 
// Page-Specific Script Injection
if (isset($pageConfig['scripts']) && is_array($pageConfig['scripts'])): ?>
    <?php foreach ($pageConfig['scripts'] as $script): ?>
        <script src="<?php echo htmlspecialchars($script); ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>