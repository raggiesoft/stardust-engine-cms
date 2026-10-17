<?php
/**
 * =========================================================================================
 * ARCHITECTURAL OVERVIEW: header.php
 * =========================================================================================
 * This script serves as the global HTML `<head>` and top-level `<header>` shell
 * for the Stardust Engine CMS. It handles dynamic theme resolution, brand font generation,
 * metadata rendering (SEO, OpenGraph, Twitter Cards, Schema.org), critical asset preloading,
 * and the instantiation of the Elara Single Page Application (SPA) lifecycle hooks.
 * 
 * OUT-OF-THE-BOX DEVELOPER EXPERIENCE:
 * - Loads Bootstrap 5.3.8 CSS, Bootstrap Icons 1.11.3, and Font Awesome Free 6.7.2 via
 *   public CDNs so cloned repositories work immediately with zero private tokens required.
 * - Supports optional `fontAwesomeKit` and `webAwesomeKit` overrides in `data/settings.json`.
 *
 * FUTURE MAINTENANCE NOTES:
 * - When altering the theme logic, ensure compatibility with `data-bs-theme`.
 * - The loader mechanism (`#page-loader`) relies on custom JS heartbeat logic.
 *   Changes to its timing may affect perceived performance during Elara SPA transitions.
 * - Hamburger menu animation uses a custom 3-span CSS transform setup. Keep `.hamburger-icon`
 *   CSS aligned with any new toggle states.
 * =========================================================================================
 */

// -----------------------------------------------------------------------------------------
// 1. Context Resolution & Theme Bootstrapping
// -----------------------------------------------------------------------------------------
$site  = $currentSite ?? 'default';
$theme = $currentPageTheme ?? 'light';

// --- FORCE DARK MODE LOGIC ---
$dark_themes = $settings['forceDarkThemes'] ?? ['dark'];
$force_dark_mode = in_array($theme, $dark_themes, true);

// -----------------------------------------------------------------------------------------
// 2. Brand Font Initialization
// -----------------------------------------------------------------------------------------
$font_stack = $pageConfig['brandFont'] ?? $settings['brandFont'] ?? ['system-ui', 'sans-serif'];

if (!is_array($font_stack) || empty($font_stack)) { 
    $font_stack = ['system-ui', 'sans-serif']; 
}

$css_font_parts = array_map(function($font) {
    $generics = ['serif', 'sans-serif', 'monospace', 'cursive', 'fantasy', 'system-ui'];
    return in_array(strtolower($font), $generics, true) ? $font : "'$font'";
}, $font_stack);

$brand_font_css = implode(', ', $css_font_parts);

// -----------------------------------------------------------------------------------------
// 3. Critical Image Preloading Collection
// -----------------------------------------------------------------------------------------
$critical_images = [];
if (!empty($pageConfig['navbarBrandLogo'])) {
    $critical_images[] = $pageConfig['navbarBrandLogo'];
} elseif (!empty($navbarBrandLogo)) {
    $critical_images[] = $navbarBrandLogo;
}
if (!empty($pageConfig['navbarBrandLogoDark'])) {
    $critical_images[] = $pageConfig['navbarBrandLogoDark'];
}

// Resolve canonical URL safely for both CLI dev server and production HTTPS
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$resolvedCanonicalUrl = $ogUrl ?? ($scheme . '://' . $host . $requestPath);
$resolvedSiteName = $settings['siteName'] ?? 'Stardust Engine CMS';
$resolvedTitle = $pageTitle ?? $resolvedSiteName;
$resolvedOgTitle = $ogTitle ?? $resolvedTitle;
$resolvedOgDesc = $ogDescription ?? 'A blazing-fast, zero-database flat-file CMS powered by PHP 8.5 and the Elara Vanilla JS SPA Router.';
$resolvedOgImage = $ogImage ?? ($scheme . '://' . $host . '/assets/images/og-default.svg');
?>
<!doctype html>
<html lang="en" class="h-100" <?php echo $force_dark_mode ? 'data-bs-theme="dark"' : ''; ?>>
  <head>
    
    <?php 
    /**
     * -------------------------------------------------------------------------------------
     * Analytics Integration (Google Tag Manager)
     * -------------------------------------------------------------------------------------
     * Injects the gtag.js tracking code solely if explicitly enabled and configured
     * with a valid tracking ID in `data/settings.json`.
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
          window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments);}
          gtag('js', new Date());
          gtag('config', '<?php echo htmlspecialchars($settings['analytics']['trackingId']); ?>');
        </script>
    <?php endif; ?>

    <!-- Standard meta definitions for character set and responsive viewport scaling -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Render the dynamically resolved page title -->
    <title><?php echo htmlspecialchars($resolvedTitle); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($resolvedOgDesc); ?>">
    
    <!-- Open Graph / Social Media Metadata Block -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?php echo htmlspecialchars($resolvedSiteName); ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($resolvedOgTitle); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($resolvedOgDesc); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($resolvedOgImage); ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars($resolvedCanonicalUrl); ?>">

    <!-- Twitter Card Metadata Block -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($resolvedOgTitle); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($resolvedOgDesc); ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($resolvedOgImage); ?>">

    <!-- Authoritative Canonical URL -->
    <link rel="canonical" href="<?php echo htmlspecialchars($resolvedCanonicalUrl); ?>">

    <?php
    /**
     * -------------------------------------------------------------------------------------
     * Structured Data Injection (Schema.org)
     * -------------------------------------------------------------------------------------
     */
    $schema = [
        "@context" => "https://schema.org",
        "@type" => "WebPage",
        "name" => $resolvedTitle,
        "description" => $resolvedOgDesc,
        "url" => $resolvedCanonicalUrl
    ];
    ?>
    <script type="application/ld+json">
        <?php echo json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT); ?>
    </script>

    <?php
    /**
     * -------------------------------------------------------------------------------------
     * Core CSS Asset Queue (Bootstrap 5.3.8 + Icons + Stardust Design System)
     * -------------------------------------------------------------------------------------
     * Uses public CDNs out-of-the-box so developers cloning the repo need zero paid keys.
     */
    ?>
    <!-- 1. Bootstrap 5.3.8 Core CSS & Bootstrap Icons 1.11.3 (Free Public CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" crossorigin="anonymous">

    <!-- 2. Font Awesome Icons (Uses custom Pro Kit if configured in settings.json, otherwise Free 6.7.2 CDN) -->
    <?php if (!empty($settings['fontAwesomeKit'])): ?>
        <script src="<?php echo htmlspecialchars($settings['fontAwesomeKit']); ?>" crossorigin="anonymous"></script>
    <?php else: ?>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet" crossorigin="anonymous" referrerpolicy="no-referrer">
    <?php endif; ?>

    <!-- 3. Optional Web Awesome Kit (If configured in settings.json) -->
    <?php if (!empty($settings['webAwesomeKit'])): ?>
        <script src="<?php echo htmlspecialchars($settings['webAwesomeKit']); ?>" crossorigin="anonymous"></script>
    <?php endif; ?>

    <!-- 4. Local Stardust Engine Stylesheets -->
    <link href="/assets/css/root.css" rel="stylesheet">
    <link href="/assets/css/safety-net.css" rel="stylesheet">
    <link href="/assets/css/header.css" rel="stylesheet">
    <link href="/assets/css/footer.css" rel="stylesheet">
    <link href="/assets/css/extras.css" rel="stylesheet">

    <?php 
    // Optional route-specific theme override from routes.json
    if (!empty($currentPageTheme) && $currentPageTheme !== 'light' && $currentPageTheme !== 'dark' && $currentPageTheme !== '/assets/css/root.css'): ?>
        <link href="<?php echo htmlspecialchars($currentPageTheme); ?>" rel="stylesheet">
    <?php endif; ?>

    <?php 
    // Preload critical brand images
    foreach ($critical_images as $imgUrl): ?>
        <?php if ($imgUrl): ?><link rel="preload" as="image" href="<?php echo htmlspecialchars($imgUrl); ?>"><?php endif; ?>
    <?php endforeach; ?>
    
    <!-- Mobile browser theme colors -->
    <meta name="theme-color" content="#f8f9fa" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#0b0f19" media="(prefers-color-scheme: dark)">

    <!-- Load the primary Elara SPA routing script asynchronously -->
    <script src="/assets/js/elara-spa.js" defer></script>    
    
    <style>
        /* Inject the computed brand font family */
        .brand-font { font-family: <?php echo $brand_font_css; ?> !important; }
        
        /* --- Elara Pre-Loader Overlay --- */
        #page-loader {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-color: var(--bs-body-bg); color: var(--bs-body-color);
            z-index: 99999; display: flex; flex-direction: column; justify-content: center; align-items: center;
            opacity: 1; visibility: visible; transition: opacity 0.35s ease-in-out, visibility 0s 0s;
        }
        #page-loader.loader-hidden {
            opacity: 0; visibility: hidden; pointer-events: none; transition: opacity 0.35s ease-in-out, visibility 0s 0.35s;
        }
        .loader-progress-container {
            width: 280px; height: 4px; margin-top: 20px; position: relative; overflow: hidden;
            background-color: rgba(var(--bs-secondary-rgb), 0.2); border-radius: 2px;
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
        .navbar-toggler:focus-visible { outline: 2px solid var(--bs-primary); outline-offset: 2px; box-shadow: none; }
        
        .hamburger-icon {
            width: 28px;
            height: 20px;
            position: relative;
            transform: rotate(0deg);
            transition: .35s ease-in-out;
            cursor: pointer;
        }

        .hamburger-icon span {
            display: block;
            position: absolute;
            height: 2px;
            width: 100%;
            background: var(--bs-body-color); 
            border-radius: 9px;
            opacity: 1;
            left: 0;
            transform: rotate(0deg);
            transition: .25s ease-in-out;
        }

        .hamburger-icon span:nth-child(1) { top: 0px; }
        .hamburger-icon span:nth-child(2) { top: 9px; }
        .hamburger-icon span:nth-child(3) { top: 18px; }

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
        .theme-img-light { display: inline-block !important; }
        .theme-img-dark { display: none !important; }
        [data-bs-theme="dark"] .theme-img-light { display: none !important; }
        [data-bs-theme="dark"] .theme-img-dark { display: inline-block !important; }

        @media (prefers-color-scheme: dark) {
            html:not([data-bs-theme="light"]) .theme-img-light { display: none !important; }
            html:not([data-bs-theme="light"]) .theme-img-dark { display: inline-block !important; }
        }
    </style>

    <script>
    /**
     * Client-Side Theme Initializer (Synchronous IIFE to prevent FOUC)
     */
    (function() {
        const isForcedByServer = document.documentElement.hasAttribute('data-bs-theme');
        
        const getPreferredTheme = () => {
            try {
                const storedTheme = localStorage.getItem('theme');
                if (storedTheme === 'light' || storedTheme === 'dark') return storedTheme;
            } catch (e) {}
            return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        };
        
        const setTheme = theme => {
            if (!isForcedByServer) {
                document.documentElement.setAttribute('data-bs-theme', theme);
            }
        };
        
        setTheme(getPreferredTheme());
        
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
            try {
                const storedTheme = localStorage.getItem('theme');
                if (storedTheme !== 'light' && storedTheme !== 'dark') setTheme(getPreferredTheme());
            } catch (e) {
                setTheme(getPreferredTheme());
            }
        });
    })();
    </script>
  </head>
  
  <body class="d-flex flex-column min-vh-100">
    
    <!-- Accessibility focus anchor: Skip to main content link for keyboard and screen reader users -->
    <a href="#main-content" class="visually-hidden-focusable p-3 m-2 bg-primary text-white rounded position-absolute start-0 top-0 z-3 text-decoration-none fw-bold shadow">
        Skip to Main Content
    </a>
    
    <!-- Page Loading Overlay -->
    <div id="page-loader" aria-hidden="true">
        <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
        <h4 class="text-uppercase fw-bold brand-font" style="letter-spacing: 2px;">
            <?php echo htmlspecialchars($pageConfig['siteName'] ?? $settings['siteName'] ?? 'Stardust Engine'); ?>
        </h4>
        <div class="loader-progress-container">
            <div class="loader-progress-bar" id="loader-bar"></div>
        </div>
        <div class="text-secondary font-monospace small mt-2" id="loader-text">&gt; INITIALIZING...</div>
    </div>
    
    <script>
    /**
     * Pre-Loader Orchestration Script
     * Hooks into Elara SPA lifecycle events (`elara:navigating` and `elara:loaded`).
     */
    (function() {
        const loader = document.getElementById('page-loader');
        const bar = document.getElementById('loader-bar');
        const text = document.getElementById('loader-text');
        let progress = 0;
        let progressInterval;

        function startHeartbeat() {
            if (progressInterval) clearInterval(progressInterval);
            progress = 15; 
            if (bar) { bar.style.width = '15%'; bar.style.opacity = '1'; }
            
            progressInterval = setInterval(() => {
                let step = (95 - progress) / 40; 
                if (step < 0.2) step = 0.2; 
                progress += step; 
                if (progress > 95) progress = 95; 
                if (bar) bar.style.width = progress + '%';
                
                if (text) {
                    if (progress < 35) text.innerText = "> ESTABLISHING UPLINK...";
                    else if (progress < 65) text.innerText = "> RESOLVING JSON ROUTE...";
                    else text.innerText = "> HYDRATING VIEW...";
                }
            }, 40);
        }

        function finishLoad() {
            if (progressInterval) clearInterval(progressInterval);
            if (bar) bar.style.width = '100%';
            if (text) text.innerText = "> READY.";
            
            setTimeout(() => { 
                if (loader) loader.classList.add('loader-hidden'); 
                setTimeout(() => { 
                    if (bar) { bar.style.width = '0%'; bar.style.opacity = '0'; }
                }, 350);
            }, 150);
        }

        document.addEventListener('elara:navigating', () => {
            if (loader) {
                loader.classList.remove('loader-hidden');
                if (text) text.innerText = "> INITIALIZING JUMP...";
                startHeartbeat();
            }
        });

        document.addEventListener('elara:loaded', finishLoad);

        window.addEventListener('pageshow', (event) => {
            if (event.persisted && loader) loader.classList.add('loader-hidden');
        });

        document.addEventListener('DOMContentLoaded', () => {
            if (loader && !loader.classList.contains('loader-hidden')) {
                startHeartbeat();
                finishLoad();
            }
        });
        
        window.addEventListener('load', finishLoad);
    })();
    </script>
    
    <!-- Primary Global Navigation Header -->
    <header>
      <nav class="navbar navbar-expand-md sticky-top border-bottom bg-body" aria-label="Primary Navigation">
        <div class="container">
            
            <!-- Navbar Branding Entity -->
            <a class="navbar-brand d-flex align-items-center gap-2" href="<?php echo htmlspecialchars($pageConfig['navbarBrandLink'] ?? $navbarBrandLink ?? '/'); ?>">
                <?php 
                $logoLight = $pageConfig['navbarBrandLogo'] ?? $settings['navbarBrandLogo'] ?? $navbarBrandLogo ?? '';
                $logoDark  = $pageConfig['navbarBrandLogoDark'] ?? '';
                ?>
                
                <?php if (!empty($logoLight) && !empty($logoDark)): ?>
                    <img src="<?php echo htmlspecialchars($logoLight); ?>" 
                        alt="<?php echo htmlspecialchars($pageConfig['navbarBrandAlt'] ?? 'Logo'); ?>" 
                        height="30" width="30"
                        class="theme-img-light align-text-top <?php echo htmlspecialchars($pageConfig['navbarBrandClass'] ?? ''); ?>">
                    
                    <img src="<?php echo htmlspecialchars($logoDark); ?>" 
                        alt="<?php echo htmlspecialchars($pageConfig['navbarBrandAlt'] ?? 'Logo Dark'); ?>" 
                        height="30" width="30"
                        class="theme-img-dark align-text-top <?php echo htmlspecialchars($pageConfig['navbarBrandClass'] ?? ''); ?>">
                <?php elseif (!empty($logoLight)): ?>
                    <img src="<?php echo htmlspecialchars($logoLight); ?>" 
                        alt="<?php echo htmlspecialchars($pageConfig['navbarBrandAlt'] ?? 'Logo'); ?>" 
                        height="30" width="30"
                        class="align-text-top <?php echo htmlspecialchars($pageConfig['navbarBrandClass'] ?? ''); ?>">
                <?php else: ?>
                    <i class="fa-solid fa-rocket text-primary" aria-hidden="true"></i>
                <?php endif; ?>
                
                <span class="fw-bold text-uppercase brand-font">
                    <?php echo strip_tags($pageConfig['navbarBrandText'] ?? $settings['siteName'] ?? 'Stardust Engine', '<span>'); ?>
                </span>
            </a>
                    
          <!-- Custom Hamburger Toggle Button for Mobile Navigation Collapse -->
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
             <div class="hamburger-icon" aria-hidden="true">
                <span></span>
                <span></span>
                <span></span>
            </div>
          </button>
          
          <!-- Navbar Menu Injection -->
          <div class="collapse navbar-collapse" id="navbarCollapse">
            <?php 
                if (isset($currentHeaderMenu) && file_exists($currentHeaderMenu)) {
                    include $currentHeaderMenu;
                } else {
                    $defaultHeader = ROOT_PATH . '/includes/components/headers/header-default.php';
                    if (file_exists($defaultHeader)) { include $defaultHeader; }
                }
            ?>
          </div>
        </div>
      </nav>
    </header>