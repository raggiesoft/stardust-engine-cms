<?php
// includes/header.php
// Stardust Engine CMS - Generic Boilerplate

// 1. Resolve Context
$site  = $currentSite ?? 'default';
$theme = $currentPageTheme ?? 'light';

// --- FORCE DARK MODE LOGIC ---
$dark_themes = $settings['forceDarkThemes'] ?? ['dark'];
$force_dark_mode = in_array($theme, $dark_themes);

// --- 2. BRAND FONT LOGIC ---
$font_stack = $pageConfig['brandFont'] ?? $settings['brandFont'] ?? ['system-ui', 'sans-serif'];
if (!is_array($font_stack) || empty($font_stack)) { $font_stack = ['system-ui', 'sans-serif']; }

$css_font_parts = array_map(function($font) {
    $generics = ['serif', 'sans-serif', 'monospace', 'cursive', 'fantasy', 'system-ui'];
    return in_array(strtolower($font), $generics) ? $font : "'$font'";
}, $font_stack);

$brand_font_css = implode(', ', $css_font_parts);

// 3. Critical Images
$critical_images = [];
if (!empty($pageConfig['navbarBrandLogo'])) $critical_images[] = $pageConfig['navbarBrandLogo'];
elseif (!empty($navbarBrandLogo)) $critical_images[] = $navbarBrandLogo;
if (!empty($pageConfig['navbarBrandLogoDark'])) $critical_images[] = $pageConfig['navbarBrandLogoDark'];
?>
<!doctype html>
<html lang="en" class="h-100" <?php echo $force_dark_mode ? 'data-bs-theme="dark"' : ''; ?>>
  <head>
    
    <?php 
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

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <title><?php echo htmlspecialchars($pageTitle ?? $settings['siteName']); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($ogDescription ?? ''); ?>">
    
    <!-- Open Graph Metadata -->
    <meta property="og:title" content="<?php echo htmlspecialchars($ogTitle ?? $pageTitle); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($ogDescription ?? ''); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($ogImage ?? ''); ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars($ogUrl ?? "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"); ?>">

    <link rel="canonical" href="<?php echo htmlspecialchars($ogUrl ?? "https://" . $_SERVER['HTTP_HOST'] . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)); ?>">

    <?php
    // Generic Default Schema
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

    <!-- LOCAL CSS QUEUE -->
    <link href="/assets/css/bootstrap.css" rel="stylesheet">
    <link href="/assets/css/root.css" rel="stylesheet">
    <link href="/assets/css/safety-net.css" rel="stylesheet">

    <!-- USER MUST SUPPLY THEIR OWN FONT AWESOME KIT -->
    <!-- <link rel="stylesheet" href="YOUR_FONTAWESOME_URL_HERE" crossorigin="anonymous"> -->

    <?php foreach ($critical_images as $imgUrl): ?>
        <?php if($imgUrl): ?><link rel="preload" as="image" href="<?php echo $imgUrl; ?>"><?php endif; ?>
    <?php endforeach; ?>
    
    <meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#121212" media="(prefers-color-scheme: dark)">

    <script src="/assets/js/elara-spa.js" defer></script>    
    
    <style>
        .brand-font { font-family: <?php echo $brand_font_css; ?> !important; }
        
        /* Elara Pre-Loader */
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
        
        /* Logo Filters */
        .navbar-brand-corporate-img { mix-blend-mode: multiply; }
        [data-bs-theme="dark"] .navbar-brand-corporate-img {
            filter: invert(1) grayscale(100%); mix-blend-mode: screen;
        }

        /* --- HAMBURGER MENU ANIMATION --- */
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
    (function() {
        const isForcedByServer = document.documentElement.hasAttribute('data-bs-theme');
        const getPreferredTheme = () => {
            const storedTheme = localStorage.getItem('theme');
            if (storedTheme) return storedTheme;
            return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        };
        const setTheme = theme => {
            if (!isForcedByServer) {
                document.documentElement.setAttribute('data-bs-theme', theme);
            }
        };
        setTheme(getPreferredTheme());
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
            const storedTheme = localStorage.getItem('theme');
            if (storedTheme !== 'light' && storedTheme !== 'dark') setTheme(getPreferredTheme());
        });
    })();
    </script>
  </head>
  
  <body class="d-flex flex-column h-100">
    <a href="#main-content" class="visually-hidden-focusable p-3 m-2 bg-primary text-white rounded position-absolute start-0 top-0 z-3 text-decoration-none fw-bold">
        Skip to Main Content
    </a>
    
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
    (function() {
        const loader = document.getElementById('page-loader');
        const bar = document.getElementById('loader-bar');
        const text = document.getElementById('loader-text');
        let progress = 0; let progressInterval;

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
                
                if(text) {
                    if (progress < 30) text.innerText = "> ESTABLISHING UPLINK...";
                    else if (progress < 50) text.innerText = "> HANDSHAKING...";
                    else if (progress < 70) text.innerText = "> DECRYPTING STREAM...";
                    else text.innerText = "> AWAITING RESPONSE...";
                }
            }, 50);
        }

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

        document.addEventListener('elara:navigating', () => {
            if(loader) {
                loader.classList.remove('loader-hidden');
                if(text) text.innerText = "> INITIALIZING JUMP...";
                startHeartbeat();
            }
        });

        document.addEventListener('elara:loaded', finishLoad);

        window.addEventListener('pageshow', (event) => {
            if (event.persisted && loader) loader.classList.add('loader-hidden');
        });

        document.addEventListener('DOMContentLoaded', () => {
            if (loader && !loader.classList.contains('loader-hidden')) startHeartbeat();
        });
        
        window.addEventListener('load', finishLoad);
    })();
    </script>
    
    <header>
      <nav class="navbar navbar-expand-md sticky-top border-bottom border-primary border-opacity-50 bg-body">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="<?php echo htmlspecialchars($pageConfig['navbarBrandLink'] ?? $navbarBrandLink ?? '/'); ?>">
                <?php 
                $logoLight = $pageConfig['navbarBrandLogo'] ?? $settings['navbarBrandLogo'] ?? $navbarBrandLogo ?? '';
                $logoDark  = $pageConfig['navbarBrandLogoDark'] ?? '';
                ?>
                <?php if (!empty($logoLight) && !empty($logoDark)): ?>
                    <img src="<?php echo htmlspecialchars($logoLight); ?>" 
                        alt="<?php echo htmlspecialchars($pageConfig['navbarBrandAlt'] ?? 'Logo'); ?>" 
                        height="30" width="30"
                        class="theme-img-light me-2 align-text-top <?php echo htmlspecialchars($pageConfig['navbarBrandClass'] ?? ''); ?>">
                    
                    <img src="<?php echo htmlspecialchars($logoDark); ?>" 
                        alt="<?php echo htmlspecialchars($pageConfig['navbarBrandAlt'] ?? 'Logo Dark'); ?>" 
                        height="30" width="30"
                        class="theme-img-dark me-2 align-text-top <?php echo htmlspecialchars($pageConfig['navbarBrandClass'] ?? ''); ?>">
                <?php elseif (!empty($logoLight)): ?>
                    <img src="<?php echo htmlspecialchars($logoLight); ?>" 
                        alt="<?php echo htmlspecialchars($pageConfig['navbarBrandAlt'] ?? 'Logo'); ?>" 
                        height="30" width="30"
                        class="me-2 align-text-top <?php echo htmlspecialchars($pageConfig['navbarBrandClass'] ?? ''); ?>">
                <?php endif; ?>
                
                <span class="fw-bold text-uppercase brand-font">
                <?php echo strip_tags($pageConfig['navbarBrandText'] ?? $settings['siteName'] ?? 'Elara Site', '<span>'); ?>
                </span>
            </a>
                    
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
             <div class="hamburger-icon">
                <span></span>
                <span></span>
                <span></span>
            </div>
          </button>
          
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