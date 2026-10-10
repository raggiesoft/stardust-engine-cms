<?php
ob_start(); 
/**
 * ============================================================================
 * STARDUST ENGINE CMS - CORE SPA ROUTER
 * ============================================================================
 * 
 * ARCHITECTURE OVERVIEW:
 * This is the foundational router for the entire Stardust Engine CMS.
 * It is responsible for intercepting all requests, parsing JSON configurations,
 * resolving view/asset inheritance, and rendering the final composite layout
 * (Header -> Sidebar -> Main Content -> Footer).
 * 
 * CORE RESPONSIBILITIES:
 * 1. Global Settings: Loads configuration from `data/settings.json`.
 * 2. Recursive Route Parsing: Scans `data/routes/` for `.json` files, merging
 *    them into a master route table.
 * 3. Asset Inheritance: Resolves sidebars and headers based on URL path 
 *    hierarchy using `resolveAsset()`.
 * 4. Smart Auto-Discovery: If an explicit route isn't defined, it attempts to
 *    find a matching physical `.php` view in the `/pages/` directory.
 * 5. Layout Rendering: Dynamically includes the correct structural components.
 * 
 * SECURITY NOTICE: 
 * This file and its parent directory MUST be renamed upon deployment to prevent
 * automated vulnerability scanning. Do not leave this file as `elara.php`.
 * Update your Nginx `try_files` block to match your custom names.
 * ============================================================================
 */

// 1. Establish the Environment Root
// Dynamically resolves to the parent directory regardless of what the user names this folder.
// This allows the system to remain modular and secure.
define('ROOT_PATH', dirname(__DIR__));
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Normalize trailing slashes to prevent duplicate routing issues
if (strlen($request_uri) > 1) {
    $request_uri = rtrim($request_uri, '/');
}

// --- 1. LOAD GLOBAL SETTINGS ---
$settingsFile = ROOT_PATH . '/data/settings.json';

// Enforce configuration existence
if (!file_exists($settingsFile)) {
    die('Critical Error: Configuration file (data/settings.json) missing. Please copy settings.example.json to settings.json.');
}

$settings = json_decode(file_get_contents($settingsFile), true);

// Validate JSON syntax for global settings
if (json_last_error() !== JSON_ERROR_NONE) {
    die('Critical Error: Invalid JSON syntax in settings.json');
}

// Extract Core Globals for Layout context
$siteName = $settings['siteName']; 
$projectSlug = $settings['projectSlug']; 
$cdnBaseUrl = $settings['cdnBaseUrl'];
$defaultTheme = $settings['defaultTheme']; 

// Build Defaults Array for fallback during rendering
$defaults = array_merge($settings['defaults'], [
    'title' => $siteName, 
    'theme' => $defaultTheme,
    'site' => $projectSlug,
    'ogImage' => $cdnBaseUrl . ($settings['defaults']['ogImage'] ?? ''),
    'ogUrl' => "https://" . $_SERVER['HTTP_HOST'] . $request_uri,
    'navbarBrandLogo' => $cdnBaseUrl . ($settings['defaults']['navbarBrandLogo'] ?? '')
]);

// --- 2. LOAD & MERGE ROUTE FILES (RECURSIVE) ---
$masterRoutes = [];

// SCAN: Recursively find ALL .json files inside /data/routes/ directory
$routeFiles = [];
$directory = new RecursiveDirectoryIterator(ROOT_PATH . '/data/routes');
$iterator = new RecursiveIteratorIterator($directory);
// Only match `.json` files
$regex = new RegexIterator($iterator, '/^.+\.json$/i', RecursiveRegexIterator::GET_MATCH);

foreach ($regex as $file) {
    $routeFiles[] = $file[0];
}

// Parse each route file and construct the routing table
foreach ($routeFiles as $file) {
    $jsonContent = file_get_contents($file);
    $fileData = json_decode($jsonContent, true);

    // FIX: Log JSON errors so they aren't silent, which makes debugging easier
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Stardust Engine Error: Invalid JSON in " . basename($file) . " - " . json_last_error_msg());
        continue; // Skip bad file rather than halting the system
    }

    if (is_array($fileData)) {
        
        // --- 2A. INHERITANCE LOGIC (The "DRY" Fix) ---
        // Processes the 'common' block to apply default settings across all routes defined in a file
        if (isset($fileData['common'])) {
            $commonConfig = $fileData['common'];
            unset($fileData['common']); // Remove 'common' key so it isn't treated as a route

            foreach ($fileData as $routeKey => $routeConfig) {
                // Merge: Specific settings overwrite Common settings
                $fileData[$routeKey] = array_merge($commonConfig, $routeConfig);
            }
        }
        // Incorporate into the main routing dictionary
        $masterRoutes = array_merge($masterRoutes, $fileData);
    }
}

/**
 * --- HELPER: RESOLVE ASSET INHERITANCE ---
 * Recursively climbs the URL path to find the closest matching asset (sidebar/header).
 * E.g., for `/admin/users/edit`, it checks `/admin/users/edit`, then `/admin/users`, 
 * then `/admin`, and finally `/`.
 */
function resolveAsset($map, $currentUri) {
    $path = rtrim(parse_url($currentUri, PHP_URL_PATH), '/');
    while ($path !== '' && $path !== '.' && $path !== '/') {
        if (isset($map[$path])) return $map[$path];
        $path = dirname($path);
        // Normalize slashes for Windows compatibility
        $path = str_replace('\\', '/', $path);
    }
    // Fallback to root configuration
    if (isset($map['/'])) return $map['/'];
    return null;
}

// --- 3. SMART ROUTER LOGIC ---

// A. Check for Explicit Configuration in JSON
$pageConfig = $masterRoutes[$request_uri] ?? [];

// B. Auto-Discovery Logic: If not explicitly configured, look for a matching physical file
if (!isset($pageConfig['view'])) {
    $potentialPath = 'pages' . $request_uri;
    
    // Check if direct file exists
    if (file_exists(ROOT_PATH . '/' . $potentialPath . '.php')) {
        $pageConfig['view'] = $potentialPath;
    } 
    // If it's a directory, check for default index files (overview.php or home.php)
    elseif (is_dir(ROOT_PATH . '/' . $potentialPath)) {
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
    
    // Resolve the appropriate sidebar based on the URL path
    if (!isset($pageConfig['sidebar'])) {
        $resolvedSidebar = resolveAsset($sidebarMap, $request_uri);
        if ($resolvedSidebar) $pageConfig['sidebar'] = $resolvedSidebar;
    }

    // Determine if the sidebar should be visible
    if (!isset($pageConfig['showSidebar'])) {
        if (isset($pageConfig['sidebar']) && $pageConfig['sidebar'] !== 'sidebar-default') {
             $pageConfig['showSidebar'] = true;
        } elseif (isset($isIndexPage) && $isIndexPage) {
            // Usually hide sidebar on generic index pages
            $pageConfig['showSidebar'] = false;
        }
    }

    // Auto-Title Generation from the URL slug if none provided
    if (!isset($pageConfig['title'])) {
        $slug = basename($request_uri);
        $pageConfig['title'] = ($slug === '' || $slug === 'index.php') ? 'Home - ' . $siteName : ucwords(str_replace('-', ' ', $slug)) . ' - ' . $siteName;
    }
}

// --- 4. RENDER ---
// Finalize configuration context
$config = array_merge($defaults, $pageConfig);

// Handle HTTP Status overrides for error pages
if ($config['view'] === 'errors/500') {
    http_response_code(500);
} elseif ($config['view'] === 'errors/404') {
    http_response_code(404);
}

// Extract variables into global scope for View files to access
$pageTitle = $config['title'];
$currentPageTheme = $config['theme'];
$showSidebar = $config['showSidebar'];
$currentSite = $config['site'];
$ogTitle = $config['ogTitle'];
$ogDescription = $config['ogDescription'];
$ogImage = $config['ogImage'];
$ogUrl = $config['ogUrl'];
$navbarBrandLogo = $config['navbarBrandLogo'];
$navbarBrandText = $config['navbarBrandText'];
$navbarBrandLink = $config['navbarBrandLink'];
$navbarBrandAlt = $config['navbarBrandAlt'];
$navbarBrandClass = $config['navbarBrandClass'];

// --- HEADER RESOLUTION ---
// Resolve which header component to include
if (isset($pageConfig['headerMenu'])) {
    $headerFile = $pageConfig['headerMenu'];
} else {
    $headerMap = $settings['headerMap'] ?? [];
    $headerFile = resolveAsset($headerMap, $request_uri) ?? 'header-default';
}
$currentHeaderMenu = ROOT_PATH . '/includes/components/headers/' . $headerFile . '.php';

// Sidebar Resolution
$currentSidebar = ROOT_PATH . '/includes/components/sidebars/' . $config['sidebar'] . '.php';

// 1. Output Header
require_once ROOT_PATH . '/includes/header.php';

// 2. Output Main Wrapper
echo '<div id="elara-layout-wrapper" class="container-fluid flex-grow-1 d-flex p-0">';
echo '  <div class="row flex-grow-1 m-0 w-100">';

// 3. Output Sidebar (if configured and exists)
if ($showSidebar && file_exists($currentSidebar)) {
    echo '    <aside class="col-md-3 col-lg-2 d-none d-md-block bg-body-tertiary border-end p-3">';
    require_once $currentSidebar;
    echo '    </aside>';
    echo '    <main id="main-content" class="col-md-9 col-lg-10 p-4" tabindex="-1" style="outline:none;">';
} else {
    echo '    <main id="main-content" class="col-12 p-0" tabindex="-1" style="outline:none;">'; 
}

// 4. Dynamic View Resolution & Error Fallback
// Includes the main content for the requested page
if (file_exists(ROOT_PATH . '/' . $config['view'] . '.php')) {
    require_once ROOT_PATH . '/' . $config['view'] . '.php';
} else {
    // Render 404 if the view file does not exist
    http_response_code(404);
    // FALLBACK FIX: Look dynamically inside the current custom directory
    if(file_exists(__DIR__ . '/errors/404.php')) {
        require_once __DIR__ . '/errors/404.php';
    } else {
        echo "404 Not Found - Stardust Engine Error Document Missing";
    }
}

echo '    </main>'; 
echo '  </div>'; 
echo '</div>'; 

// 5. Output Footer
require_once ROOT_PATH . '/includes/footer.php';
ob_end_flush(); // Send buffered output to browser
?>