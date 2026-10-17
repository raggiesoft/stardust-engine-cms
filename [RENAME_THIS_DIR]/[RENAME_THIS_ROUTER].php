<?php
ob_start(); 
/**
 * ============================================================================
 * STARDUST ENGINE CMS - CORE SPA ROUTER
 * ============================================================================
 * 
 * ARCHITECTURE OVERVIEW:
 * This is the foundational front-controller router for the Stardust Engine CMS.
 * It intercepts all page requests, parses flat-file JSON configurations,
 * resolves view/asset inheritance, and renders the composite layout
 * (Header -> Sidebar -> Main Content -> Footer -> Persistent Modules).
 * 
 * CORE RESPONSIBILITIES:
 * 1. Global Settings: Loads configuration from `data/settings.json` (falling
 *    back to `data/settings.example.json` if `settings.json` has not been created yet).
 * 2. Recursive Route Parsing: Scans `data/routes/` for active `.json` route maps,
 *    merging them into a master route table while applying `common` DRY blocks.
 * 3. Asset Inheritance: Resolves sidebars, headers, and footers based on URL path 
 *    hierarchy using `resolveAsset()`.
 * 4. Smart Auto-Discovery: If an explicit route isn't defined, it attempts to
 *    find a matching physical `.php` view in the `/pages/` directory.
 * 
 * SECURITY NOTICE (PRODUCTION): 
 * In production Nginx deployments, rename `[RENAME_THIS_DIR]` and
 * `[RENAME_THIS_ROUTER].php` to custom names and update your Nginx `try_files`
 * directive accordingly. For local development, run `php -S localhost:8000 router.php`
 * from the repository root.
 * ============================================================================
 */

// 1. Establish the Environment Root
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

$request_uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

// Normalize trailing slashes to prevent duplicate routing issues
if (strlen($request_uri) > 1) {
    $request_uri = rtrim($request_uri, '/');
}

// --- 1. LOAD GLOBAL SETTINGS ---
$settingsFile = ROOT_PATH . '/data/settings.json';
if (!file_exists($settingsFile)) {
    $settingsFile = ROOT_PATH . '/data/settings.example.json';
}

if (!file_exists($settingsFile)) {
    http_response_code(500);
    die('Critical Error: Configuration file (data/settings.json) missing.');
}

$settings = json_decode(file_get_contents($settingsFile), true);

if (json_last_error() !== JSON_ERROR_NONE || !is_array($settings)) {
    http_response_code(500);
    die('Critical Error: Invalid JSON syntax in settings.json');
}

// Extract Core Globals for Layout context
$siteName     = $settings['siteName'] ?? 'Stardust Engine CMS'; 
$projectSlug  = $settings['projectSlug'] ?? 'stardust-engine-cms'; 
$cdnBaseUrl   = rtrim($settings['cdnBaseUrl'] ?? '', '/');
$defaultTheme = $settings['defaultTheme'] ?? 'light'; 

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host   = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';

// Build Defaults Array for fallback during rendering
$defaults = array_merge($settings['defaults'] ?? [], [
    'title'           => $siteName, 
    'theme'           => $defaultTheme,
    'site'            => $projectSlug,
    'ogImage'         => $cdnBaseUrl . ($settings['defaults']['ogImage'] ?? '/assets/images/og-default.svg'),
    'ogUrl'           => $scheme . '://' . $host . $request_uri,
    'navbarBrandLogo' => !empty($settings['defaults']['navbarBrandLogo']) ? ($cdnBaseUrl . $settings['defaults']['navbarBrandLogo']) : ''
]);

// --- 2. LOAD & MERGE ROUTE FILES (RECURSIVE) ---
$masterRoutes = [];
$routeFiles = [];

if (is_dir(ROOT_PATH . '/data/routes')) {
    $directory = new RecursiveDirectoryIterator(ROOT_PATH . '/data/routes', FilesystemIterator::SKIP_DOTS);
    $iterator  = new RecursiveIteratorIterator($directory);
    $regex     = new RegexIterator($iterator, '/^.+\.json$/i', RecursiveRegexIterator::GET_MATCH);

    foreach ($regex as $file) {
        // Ignore *.example.json templates when active route files are present
        if (str_ends_with(strtolower($file[0]), '.example.json')) {
            continue;
        }
        $routeFiles[] = $file[0];
    }

    // Fallback to route.example.json only if no non-example route files exist
    if (empty($routeFiles) && file_exists(ROOT_PATH . '/data/routes/route.example.json')) {
        $routeFiles[] = ROOT_PATH . '/data/routes/route.example.json';
    }
}

foreach ($routeFiles as $file) {
    $jsonContent = file_get_contents($file);
    $fileData = json_decode($jsonContent, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Stardust Engine Error: Invalid JSON in " . basename($file) . " - " . json_last_error_msg());
        continue;
    }

    if (is_array($fileData)) {
        // --- 2A. INHERITANCE LOGIC (The "DRY" Common Block) ---
        if (isset($fileData['common']) && is_array($fileData['common'])) {
            $commonConfig = $fileData['common'];
            unset($fileData['common']);

            foreach ($fileData as $routeKey => $routeConfig) {
                if (is_array($routeConfig)) {
                    $fileData[$routeKey] = array_merge($commonConfig, $routeConfig);
                }
            }
        }
        $masterRoutes = array_merge($masterRoutes, $fileData);
    }
}

/**
 * --- HELPER: RESOLVE ASSET INHERITANCE ---
 * Recursively climbs the URL path to find the closest matching asset (sidebar/header/footer).
 */
if (!function_exists('resolveAsset')) {
    function resolveAsset($map, $currentUri) {
        if (!is_array($map)) return null;
        $path = rtrim(parse_url($currentUri, PHP_URL_PATH) ?: '/', '/');
        while ($path !== '' && $path !== '.' && $path !== '/') {
            if (isset($map[$path])) return $map[$path];
            $path = dirname($path);
            $path = str_replace('\\', '/', $path);
        }
        if (isset($map['/'])) return $map['/'];
        return null;
    }
}

// --- 3. SMART ROUTER LOGIC ---

// A. Check for Explicit Configuration in JSON
$pageConfig = $masterRoutes[$request_uri] ?? [];

// B. Auto-Discovery Logic: If not explicitly configured, look for a matching physical file
if (!isset($pageConfig['view'])) {
    $potentialPath = ($request_uri === '/') ? 'pages/home' : 'pages' . $request_uri;
    
    if (file_exists(ROOT_PATH . '/' . $potentialPath . '.php')) {
        $pageConfig['view'] = $potentialPath;
    } elseif (is_dir(ROOT_PATH . '/' . $potentialPath)) {
        if (file_exists(ROOT_PATH . '/' . $potentialPath . '/overview.php')) {
            $pageConfig['view'] = $potentialPath . '/overview';
            $isIndexPage = true;
        } elseif (file_exists(ROOT_PATH . '/' . $potentialPath . '/home.php')) {
            $pageConfig['view'] = $potentialPath . '/home';
            $isIndexPage = true;
        }
    }
}

// C. Sidebar Intelligence: Determine sidebar rendering logic
if (isset($pageConfig['view'])) {
    $sidebarMap = $settings['sidebarMap'] ?? [];
    
    // Allow `"sidebar": false` shorthand to explicitly disable the sidebar on a child route
    if (array_key_exists('sidebar', $pageConfig) && ($pageConfig['sidebar'] === false || $pageConfig['sidebar'] === 'none')) {
        $pageConfig['showSidebar'] = false;
        $pageConfig['sidebar'] = 'sidebar-default';
    } elseif (!isset($pageConfig['sidebar'])) {
        $resolvedSidebar = resolveAsset($sidebarMap, $request_uri);
        if ($resolvedSidebar) $pageConfig['sidebar'] = $resolvedSidebar;
    }

    if (!isset($pageConfig['showSidebar'])) {
        if (isset($pageConfig['sidebar']) && $pageConfig['sidebar'] !== 'sidebar-default') {
             $pageConfig['showSidebar'] = true;
        } elseif (!empty($isIndexPage)) {
            $pageConfig['showSidebar'] = false;
        }
    }

    // Auto-Title Generation from the URL slug if none provided
    if (!isset($pageConfig['title'])) {
        $slug = basename($request_uri);
        $pageConfig['title'] = ($slug === '' || $slug === 'index.php')
            ? 'Home | ' . $siteName
            : ucwords(str_replace('-', ' ', $slug)) . ' | ' . $siteName;
    }
}

// --- 4. RENDER ---
$config = array_merge($defaults, $pageConfig);

if (($config['view'] ?? '') === 'errors/500') {
    http_response_code(500);
} elseif (($config['view'] ?? '') === 'errors/404') {
    http_response_code(404);
}

// Extract variables into global scope for View files to access
$pageTitle        = $config['title'] ?? $siteName;
$currentPageTheme = $config['pageTheme'] ?? ($config['theme'] ?? 'light');
$showSidebar      = !empty($config['showSidebar']);
$currentSite      = $config['site'] ?? $projectSlug;
$ogTitle          = $config['ogTitle'] ?? $pageTitle;
$ogDescription    = $config['ogDescription'] ?? '';
$ogImage          = $config['ogImage'] ?? '';
$ogUrl            = $config['ogUrl'] ?? ($scheme . '://' . $host . $request_uri);
$navbarBrandLogo  = $config['navbarBrandLogo'] ?? '';
$navbarBrandText  = $config['navbarBrandText'] ?? $siteName;
$navbarBrandLink  = $config['navbarBrandLink'] ?? '/';
$navbarBrandAlt   = $config['navbarBrandAlt'] ?? 'Site Logo';
$navbarBrandClass = $config['navbarBrandClass'] ?? '';

// --- HEADER & SIDEBAR RESOLUTION ---
if (isset($pageConfig['headerMenu'])) {
    $headerFile = $pageConfig['headerMenu'];
} else {
    $headerMap  = $settings['headerMap'] ?? [];
    $headerFile = resolveAsset($headerMap, $request_uri) ?? 'header-default';
}
$currentHeaderMenu = ROOT_PATH . '/includes/components/headers/' . $headerFile . '.php';
$currentSidebar    = ROOT_PATH . '/includes/components/sidebars/' . ($config['sidebar'] ?? 'sidebar-default') . '.php';

// 1. Output Header
require_once ROOT_PATH . '/includes/header.php';

// 2. Output Main Wrapper
echo '<div id="elara-layout-wrapper" class="container-fluid flex-grow-1 d-flex p-0">';
echo '  <div class="row flex-grow-1 m-0 w-100">';

// 3. Output Sidebar (if configured and exists)
if ($showSidebar && file_exists($currentSidebar)) {
    echo '    <aside class="col-md-3 col-lg-2 d-none d-md-block bg-body-tertiary border-end p-3" aria-label="Section Navigation">';
    require_once $currentSidebar;
    echo '    </aside>';
    echo '    <main id="main-content" class="col-md-9 col-lg-10 p-4" tabindex="-1" style="outline:none;">';
} else {
    echo '    <main id="main-content" class="col-12 p-0" tabindex="-1" style="outline:none;">'; 
}

// 4. Dynamic View Resolution & Error Fallback
if (!empty($config['view']) && file_exists(ROOT_PATH . '/' . $config['view'] . '.php')) {
    require_once ROOT_PATH . '/' . $config['view'] . '.php';
} else {
    http_response_code(404);
    if (file_exists(__DIR__ . '/errors/404.php')) {
        require_once __DIR__ . '/errors/404.php';
    } else {
        echo '<div class="container py-5"><h1>404 Not Found</h1></div>';
    }
}

echo '    </main>'; 
echo '  </div>'; 
echo '</div>'; 

// 5. Output Footer
require_once ROOT_PATH . '/includes/footer.php';
ob_end_flush();
?>