<?php
/**
 * =========================================================================================
 * ARCHITECTURAL OVERVIEW: header.php
 * =========================================================================================
 * This script serves as the generic HTML `<head>` and top-level `<header>` boilerplate
 * for the Stardust Engine CMS. It handles dynamic theme resolution, brand font generation,
 * metadata rendering (SEO, OpenGraph, Schema.org), critical asset preloading, and the
 * instantiation of the Elara Single Page Application (SPA) lifecycle hooks.
 * 
 * FUTURE MAINTENANCE NOTES:
 * - When altering the theme logic, ensure compatibility with `data-bs-theme`.
 * - The loader mechanism (`#page-loader`) relies heavily on custom JS heartbeat logic.
 *   Changes to its timing may affect perceived performance during Elara SPA transitions.
 * - Hamburger menu animation uses a custom 3-span CSS transform setup instead of native
 *   Bootstrap icons. Keep `.hamburger-icon` CSS aligned with any new toggle states.
 * =========================================================================================
 */

// -----------------------------------------------------------------------------------------
// 1. Context Resolution & Theme Bootstrapping
// -----------------------------------------------------------------------------------------
// Default fallbacks are provided if the global scope is missing context variables.
$site  = $currentSite ?? 'default';
$theme = $currentPageTheme ?? 'light';

// --- FORCE DARK MODE LOGIC ---
// Extract the defined dark themes from the global settings (falling back to ['dark']).
$dark_themes = $settings['forceDarkThemes'] ?? ['dark'];
// Determine whether the current theme dictates a forced dark mode override.
$force_dark_mode = in_array($theme, $dark_themes);

// -----------------------------------------------------------------------------------------
// 2. Brand Font Initialization
// -----------------------------------------------------------------------------------------
// Retrieve the brand font stack from the specific page configuration, global settings,
// or use a safe system fallback.
$font_stack = $pageConfig['brandFont'] ?? $settings['brandFont'] ?? ['system-ui', 'sans-serif'];

// Validate the font stack integrity to prevent malformed CSS output.
if (!is_array($font_stack) || empty($font_stack)) { 
    $font_stack = ['system-ui', 'sans-serif']; 
}

// Map individual font strings to CSS-safe representations, quoting non-generic families.
$css_font_parts = array_map(function($font) {
    $generics = ['serif', 'sans-serif', 'monospace', 'cursive', 'fantasy', 'system-ui'];
    return in_array(strtolower($font), $generics) ? $font : "'$font'";
}, $font_stack);

// Combine the sanitized font list into a finalized font-family string.
$brand_font_css = implode(', ', $css_font_parts);

// -----------------------------------------------------------------------------------------
// 3. Critical Image Preloading Collection
// -----------------------------------------------------------------------------------------
// Gather high-priority image assets (like logos) to hint the browser for immediate download,
// preventing FOUT (Flash of Unstyled Text) or layout shift during initial render.
$critical_images = [];
if (!empty($pageConfig['navbarBrandLogo'])) {
    $critical_images[] = $pageConfig['navbarBrandLogo'];
} elseif (!empty($navbarBrandLogo)) {
    $critical_images[] = $navbarBrandLogo;
}
if (!empty($pageConfig['navbarBrandLogoDark'])) {
    $critical_images[] = $pageConfig['navbarBrandLogoDark'];
}
?>
<!doctype html>
<!-- Render the root HTML tag, applying dark mode forced attributes if resolved -->
<html lang="en" class="h-100" <?php echo $force_dark_mode ? 'data-bs-theme="dark"' : ''; ?>>
  <head>
    
    <?php 
    /**
     * -------------------------------------------------------------------------------------
     * Analytics Integration (Google Tag Manager)
     * -------------------------------------------------------------------------------------
     * Injects the gtag.js tracking code solely if explicitly enabled and configured
     * with a valid tracking ID in the system settings.
     */
    if (
        isset($settings['analytics']['enabled']) && 
        $settings['analytics']['enabled'] === true &&
        !empty($settings['analytics']['trackingId'])
    ): 
    ?>
        <!-- Google tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo htmlspecialchars($settings['analytics']['trackingId']); ?>"></script>
        <script>
          // Initialize dataLayer to capture analytic events
          window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments);}
          gtag('js', new Date());
          gtag('config', '<?php echo htmlspecialchars($settings['analytics']['trackingId']); ?>');
        </script>
    <?php endif; ?>

    <!-- Standard meta definitions for character set and responsive viewport scaling -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Render the dynamically resolved page title or fallback to the site name -->
    <title><?php echo htmlspecialchars($pageTitle ?? $settings['siteName']); ?></title>
    <!-- General meta description for SEO purposes -->
    <meta name="description" content="<?php echo htmlspecialchars($ogDescription ?? ''); ?>">
    
    <?php
    /**
     * -------------------------------------------------------------------------------------
     * Open Graph / Social Media Metadata Block
     * -------------------------------------------------------------------------------------
     * Provides structured data to ensure rich link previews when pages are shared
     * on social networks like Facebook, Twitter, and LinkedIn.
     */
    ?>
    <meta property="og:title" content="<?php echo htmlspecialchars($ogTitle ?? $pageTitle); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($ogDescription ?? ''); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($ogImage ?? ''); ?>">
    <!-- Resolve canonical URL for OG tags, using the current server context as a fallback -->
    <meta property="og:url" content="<?php echo htmlspecialchars($ogUrl ?? "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"); ?>">

    <!-- Inform search engines of the authoritative URL for this content -->
    <link rel="canonical" href="<?php echo htmlspecialchars($ogUrl ?? "https://" . $_SERVER['HTTP_HOST'] . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)); ?>">

    <?php
    /**
     * -------------------------------------------------------------------------------------
     * Structured Data Injection (Schema.org)
     * -------------------------------------------------------------------------------------
     * Outputs a generic WebPage JSON-LD schema block to aid search engine indexing.
     */
    $schema = [
        "@context" => "https://schema.org",
        "@type" => "WebPage",
        "name" => $pageTitle ?? $settings['siteName'],
        "url" => $ogUrl ?? "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"
    ];
    ?>
    <script type="application/ld+json">
        <?php echo json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT); ?>
    </script>

    <?php
    /**
     * -------------------------------------------------------------------------------------
     * Core CSS Asset Queue
     * -------------------------------------------------------------------------------------
     * Loads the fundamental stylesheet backbone including Bootstrap, custom overrides,
     * and specific safety-net structural tweaks.
     */
    ?>
    <!-- LOCAL CSS QUEUE -->
    <link href="/assets/css/bootstrap.css" rel="stylesheet">
    <link href="/assets/css/root.css" rel="stylesheet">
    <link href="/assets/css/safety-net.css" rel="stylesheet">

    <!-- USER MUST SUPPLY THEIR OWN FONT AWESOME KIT -->
    <!-- Note to future developers: Insert `<link>` or `<script>` for Font Awesome here if required -->
    <!-- <link rel="stylesheet" href="YOUR_FONTAWESOME_URL_HERE" crossorigin="anonymous"> -->

    <?php 
    // Loop through gathered critical images to generate preload hints for the browser
    foreach ($critical_images as $imgUrl): ?>
        <?php if($imgUrl): ?><link rel="preload" as="image" href="<?php echo $imgUrl; ?>"><?php endif; ?>
    <?php endforeach; ?>
    
    <!-- Specify mobile browser theme colors dependent on system color scheme preference -->
    <meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#121212" media="(prefers-color-scheme: dark)">

    <!-- Load the primary Elara SPA routing script asynchronously -->
    <script src="/assets/js/elara-spa.js" defer></script>    
    
    <?php
    /**
     * -------------------------------------------------------------------------------------
     * Inline Critical Styling
     * -------------------------------------------------------------------------------------
     * Contains dynamic CSS (like brand fonts) and essential layout structures needed
     * instantaneously, such as the pre-loader overlay and navbar visual behaviors.
     */
    ?>
    <style>
        /* Inject the computed brand font family */
        .brand-font { font-family: <?php echo $brand_font_css; ?> !important; }
        
        /* --- Elara Pre-Loader Overlay --- */
        #page-loader {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-color: var(--bs-body-bg); color: var(--bs-body-color);
            z-index: 99999; display: flex; flex-direction: column; justify-content: center; align-items: center;
            opacity: 1; visibility: visible; transition: opacity 0.5s ease-in-out, visibility 0s 0s;
        }
        #page-loader.loader-hidden {
            opacity: 0; visibility: hidden; transition: opacity 0.5s ease-in-out, visibility 0s 0.5s;
        }
        .loader-progress-container {
            width: 300px; height: 4px; margin-top: 20px; position: relative; overflow: hidden;
            background-color: rgba(var(--bs-secondary-rgb), 0.2); 
        }
        .loader-progress-bar {
            height: 100%; width: 0%; transition: width 0.2s ease;
            background-color: var(--bs-primary); box-shadow: 0 0 10px var(--bs-primary);
        }
        
        /* --- Corporate Logo Filtering Options --- */
        .navbar-brand-corporate-img { mix-blend-mode: multiply; }
        [data-bs-theme="dark"] .navbar-brand-corporate-img {
            filter: invert(1) grayscale(100%); mix-blend-mode: screen;
        }

        /* --- Custom Hamburger Menu Animation Styles --- */
        .navbar-toggler { border: none; padding: 0.5rem; }
        .navbar-toggler:focus { box-shadow: none; }
        
        .hamburger-icon {
            width: 28px;
            height: 20px;
            position: relative;
            transform: rotate(0deg);
            transition: .5s ease-in-out;
            cursor: pointer;
        }

        .hamburger-icon span {
            display: block;
            position: absolute;
            height: 2px;
            width: 100%;
            background: var(--bs-navbar-color); 
            border-radius: 9px;
            opacity: 1;
            left: 0;
            transform: rotate(0deg);
            transition: .25s ease-in-out;
        }

        /* Hamburger layer default positioning */
        .hamburger-icon span:nth-child(1) { top: 0px; }
        .hamburger-icon span:nth-child(2) { top: 9px; }
        .hamburger-icon span:nth-child(3) { top: 18px; }

        /* Animation states for expanded menu (transforms to an 'X' symbol) */
        .navbar-toggler[aria-expanded="true"] .hamburger-icon span:nth-child(1) {
            top: 9px;
            transform: rotate(135deg);
        }
        .navbar-toggler[aria-expanded="true"] .hamburger-icon span:nth-child(2) {
            opacity: 0;
            left: -60px;
        }
        .navbar-toggler[aria-expanded="true"] .hamburger-icon span:nth-child(3) {
            top: 9px;
            transform: rotate(-135deg);
        }

        /* --- Global Theme Image Toggling --- */
        /* Handles selective display of light/dark optimized assets */
        .theme-img-light { display: inline-block !important; }
        .theme-img-dark { display: none !important; }
        [data-bs-theme="dark"] .theme-img-light { display: none !important; }
        [data-bs-theme="dark"] .theme-img-dark { display: inline-block !important; }

        @media (prefers-color-scheme: dark) {
            html:not([data-bs-theme="light"]) .theme-img-light { display: none !important; }
            html:not([data-bs-theme="light"]) .theme-img-dark { display: inline-block !important; }
        }
    </style>

    <?php
    /**
     * -------------------------------------------------------------------------------------
     * Client-Side Theme Initializer
     * -------------------------------------------------------------------------------------
     * This IIFE runs synchronously to prevent a flash of incorrect color scheme.
     * It checks local storage or system preferences to define the document theme.
     */
    ?>
    <script>
    (function() {
        // Detect if the server explicitly injected a theme override
        const isForcedByServer = document.documentElement.hasAttribute('data-bs-theme');
        
        // Resolve user preference (localStorage first, then OS preference)
        const getPreferredTheme = () => {
            const storedTheme = localStorage.getItem('theme');
            if (storedTheme) return storedTheme;
            return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        };
        
        // Apply theme to the document element (unless forcibly overwritten by backend)
        const setTheme = theme => {
            if (!isForcedByServer) {
                document.documentElement.setAttribute('data-bs-theme', theme);
            }
        };
        
        // Execute initial resolution
        setTheme(getPreferredTheme());
        
        // Listen for system level color scheme changes dynamically
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
            const storedTheme = localStorage.getItem('theme');
            if (storedTheme !== 'light' && storedTheme !== 'dark') setTheme(getPreferredTheme());
        });
    })();
    </script>
  </head>
  
  <body class="d-flex flex-column h-100">
    
    <!-- Accessibility focus anchor: Skip to main content link for screen readers -->
    <a href="#main-content" class="visually-hidden-focusable p-3 m-2 bg-primary text-white rounded position-absolute start-0 top-0 z-3 text-decoration-none fw-bold">
        Skip to Main Content
    </a>
    
    <?php
    /**
     * -------------------------------------------------------------------------------------
     * Page Loading Overlay
     * -------------------------------------------------------------------------------------
     * Provides immediate visual feedback while the SPA lifecycle resolves. Includes a 
     * simulated progress bar and text terminal output for atmospheric effect.
     */
    ?>
    <div id="page-loader">
        <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
        <h4 class="text-uppercase fw-bold brand-font" style="letter-spacing: 2px;">
            <?php echo htmlspecialchars($pageConfig['siteName'] ?? $settings['siteName'] ?? 'Loading'); ?>
        </h4>
        <div class="loader-progress-container">
            <div class="loader-progress-bar" id="loader-bar"></div>
        </div>
        <div class="text-secondary font-monospace small mt-2" id="loader-text">> INITIALIZING...</div>
    </div>
    
    <script>
    /**
     * -------------------------------------------------------------------------------------
     * Pre-Loader Orchestration Script
     * -------------------------------------------------------------------------------------
     * Manages the lifecycle of the loading overlay. Hooks into Elara SPA events to 
     * show and hide the overlay, animating a pseudo-progress bar.
     */
    (function() {
        const loader = document.getElementById('page-loader');
        const bar = document.getElementById('loader-bar');
        const text = document.getElementById('loader-text');
        let progress = 0; let progressInterval;

        // Initiates the visual progress loop simulating network activity
        function startHeartbeat() {
            if (progressInterval) clearInterval(progressInterval);
            progress = 10; 
            if(bar) { bar.style.width = '10%'; bar.style.opacity = '1'; }
            
            progressInterval = setInterval(() => {
                let step = (95 - progress) / 80; 
                if (step < 0.1) step = 0.1; 
                progress += step; 
                if (progress > 95) progress = 95; 
                if(bar) bar.style.width = progress + '%';
                
                // Update terminal text based on simulated progress intervals
                if(text) {
                    if (progress < 30) text.innerText = "> ESTABLISHING UPLINK...";
                    else if (progress < 50) text.innerText = "> HANDSHAKING...";
                    else if (progress < 70) text.innerText = "> DECRYPTING STREAM...";
                    else text.innerText = "> AWAITING RESPONSE...";
                }
            }, 50);
        }

        // Concludes the loading sequence and fades out the overlay
        function finishLoad() {
            if (progressInterval) clearInterval(progressInterval);
            if(bar) bar.style.width = '100%';
            if(text) text.innerText = "> CONNECTION ESTABLISHED.";
            
            setTimeout(() => { 
                if(loader) loader.classList.add('loader-hidden'); 
                setTimeout(() => { 
                    if(bar) { bar.style.width = '0%'; bar.style.opacity = '0'; }
                }, 500);
            }, 500);
        }

        // Register Elara SPA routing lifecycle hook (start load)
        document.addEventListener('elara:navigating', () => {
            if(loader) {
                loader.classList.remove('loader-hidden');
                if(text) text.innerText = "> INITIALIZING JUMP...";
                startHeartbeat();
            }
        });

        // Register Elara SPA routing lifecycle hook (end load)
        document.addEventListener('elara:loaded', finishLoad);

        // Handle browser back/forward cache restoration gracefully
        window.addEventListener('pageshow', (event) => {
            if (event.persisted && loader) loader.classList.add('loader-hidden');
        });

        // Start heartbeat immediately if the DOM arrives and loader is visible
        document.addEventListener('DOMContentLoaded', () => {
            if (loader && !loader.classList.contains('loader-hidden')) startHeartbeat();
        });
        
        // Final fallback on complete window load
        window.addEventListener('load', finishLoad);
    })();
    </script>
    
    <?php
    /**
     * -------------------------------------------------------------------------------------
     * Primary Global Navigation Header
     * -------------------------------------------------------------------------------------
     * Wraps the main Bootstrap navbar implementation. The branding (logo/text) resolves 
     * based on active page configurations, seamlessly handling dark/light theme assets.
     */
    ?>
    <header>
      <nav class="navbar navbar-expand-md sticky-top border-bottom border-primary border-opacity-50 bg-body">
        <div class="container-fluid">
            
            <!-- Navbar Branding Entity -->
            <a class="navbar-brand d-flex align-items-center" href="<?php echo htmlspecialchars($pageConfig['navbarBrandLink'] ?? $navbarBrandLink ?? '/'); ?>">
                <?php 
                // Resolve potential image assets for dark/light variations
                $logoLight = $pageConfig['navbarBrandLogo'] ?? $settings['navbarBrandLogo'] ?? $navbarBrandLogo ?? '';
                $logoDark  = $pageConfig['navbarBrandLogoDark'] ?? '';
                ?>
                
                <?php if (!empty($logoLight) && !empty($logoDark)): ?>
                    <!-- Display dual-theme compatible logos via CSS class toggling -->
                    <img src="<?php echo htmlspecialchars($logoLight); ?>" 
                        alt="<?php echo htmlspecialchars($pageConfig['navbarBrandAlt'] ?? 'Logo'); ?>" 
                        height="30" width="30"
                        class="theme-img-light me-2 align-text-top <?php echo htmlspecialchars($pageConfig['navbarBrandClass'] ?? ''); ?>">
                    
                    <img src="<?php echo htmlspecialchars($logoDark); ?>" 
                        alt="<?php echo htmlspecialchars($pageConfig['navbarBrandAlt'] ?? 'Logo Dark'); ?>" 
                        height="30" width="30"
                        class="theme-img-dark me-2 align-text-top <?php echo htmlspecialchars($pageConfig['navbarBrandClass'] ?? ''); ?>">
                <?php elseif (!empty($logoLight)): ?>
                    <!-- Fallback to a single, statically defined logo -->
                    <img src="<?php echo htmlspecialchars($logoLight); ?>" 
                        alt="<?php echo htmlspecialchars($pageConfig['navbarBrandAlt'] ?? 'Logo'); ?>" 
                        height="30" width="30"
                        class="me-2 align-text-top <?php echo htmlspecialchars($pageConfig['navbarBrandClass'] ?? ''); ?>">
                <?php endif; ?>
                
                <!-- Display textual brand name alongside or instead of the logo -->
                <span class="fw-bold text-uppercase brand-font">
                <?php echo strip_tags($pageConfig['navbarBrandText'] ?? $settings['siteName'] ?? 'Elara Site', '<span>'); ?>
                </span>
            </a>
                    
          <!-- Custom Hamburger Toggle Button for Mobile Navigation Collapse -->
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
             <div class="hamburger-icon">
                <span></span>
                <span></span>
                <span></span>
            </div>
          </button>
          
          <?php
          /**
           * ---------------------------------------------------------------------------------
           * Navbar Menu Injection
           * ---------------------------------------------------------------------------------
           * Dynamically includes the resolved specific navigation menu layout or safely 
           * falls back to a default boilerplate header component if unspecified.
           */
          ?>
          <div class="collapse navbar-collapse" id="navbarCollapse">
            <?php 
                if (isset($currentHeaderMenu) && file_exists($currentHeaderMenu)) {
                    include $currentHeaderMenu;
                } else {
                    // Safe fallback if specific nav header is missing
                    $defaultHeader = ROOT_PATH . '/includes/components/headers/header-default.php';
                    if (file_exists($defaultHeader)) { include $defaultHeader; }
                }
            ?>
          </div>
        </div>
      </nav>
    </header>