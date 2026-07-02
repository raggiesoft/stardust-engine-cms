<?php
ob_start(); 
/**
 * Stardust Engine CMS - Core SPA Router
 * 
 * SECURITY NOTICE: 
 * This file and its parent directory MUST be renamed upon deployment to prevent
 * automated vulnerability scanning. Do not leave this file as `elara.php`.
 * Update your Nginx `try_files` block to match your custom names.
 */

// 1. Establish the Environment Root
// Dynamically resolves to the parent directory regardless of what the user names this folder.
define('ROOT_PATH', dirname(__DIR__));
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Normalize trailing slashes
if (strlen($request_uri) > 1) {
    $request_uri = rtrim($request_uri, '/');
}

// --- 1. LOAD GLOBAL SETTINGS ---
$settingsFile = ROOT_PATH . '/data/settings.json';

if (!file_exists($settingsFile)) {
    die('Critical Error: Configuration file (data/settings.json) missing. Please copy settings.example.json to settings.json.');
}

$settings = json_decode(file_get_contents($settingsFile), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    die('Critical Error: Invalid JSON syntax in settings.json');
}

// Extract Core Globals
$siteName = $settings['siteName']; 
$projectSlug = $settings['projectSlug']; 
$cdnBaseUrl = $settings['cdnBaseUrl'];
$defaultTheme = $settings['defaultTheme']; 

// Build Defaults Array
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

// SCAN: Recursively find ALL .json files inside /data/routes/
$routeFiles = [];
$directory = new RecursiveDirectoryIterator(ROOT_PATH . '/data/routes');
$iterator = new RecursiveIteratorIterator($directory);
$regex = new RegexIterator($iterator, '/^.+\.json$/i', RecursiveRegexIterator::GET_MATCH);

foreach ($regex as $file) {
    $routeFiles[] = $file[0];
}
foreach ($routeFiles as $file) {
    $jsonContent = file_get_contents($file);
    $fileData = json_decode($jsonContent, true);

    // FIX: Log JSON errors so they aren't silent
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Stardust Engine Error: Invalid JSON in " . basename($file) . " - " . json_last_error_msg());
        continue; // Skip bad file
    }

    if (is_array($fileData)) {
        
        // --- 2A. INHERITANCE LOGIC (The "DRY" Fix) ---
        if (isset($fileData['common'])) {
            $commonConfig = $fileData['common'];
            unset($fileData['common']); // Remove 'common' key

            foreach ($fileData as $routeKey => $routeConfig) {
                // Merge: Specific settings overwrite Common settings
                $fileData[$routeKey] = array_merge($commonConfig, $routeConfig);
            }
        }
        $masterRoutes = array_merge($masterRoutes, $fileData);
    }
}

// --- HELPER: RESOLVE ASSET INHERITANCE ---
function resolveAsset($map, $currentUri) {
    $path = rtrim(parse_url($currentUri, PHP_URL_PATH), '/');
    while ($path !== '' && $path !== '.' && $path !== '/') {
        if (isset($map[$path])) return $map[$path];
        $path = dirname($path);
        $path = str_replace('\\', '/', $path);
    }
    if (isset($map['/'])) return $map['/'];
    return null;
}

// --- 3. SMART ROUTER LOGIC ---

// A. Check for Explicit Configuration
$pageConfig = $masterRoutes[$request_uri] ?? [];

// B. Auto-Discovery Logic
if (!isset($pageConfig['view'])) {
    $potentialPath = 'pages' . $request_uri;
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

// C. Sidebar Intelligence
if (isset($pageConfig['view'])) {
    $sidebarMap = $settings['sidebarMap'] ?? [];
    
    if (!isset($pageConfig['sidebar'])) {
        $resolvedSidebar = resolveAsset($sidebarMap, $request_uri);
        if ($resolvedSidebar) $pageConfig['sidebar'] = $resolvedSidebar;
    }

    if (!isset($pageConfig['showSidebar'])) {
        if (isset($pageConfig['sidebar']) && $pageConfig['sidebar'] !== 'sidebar-default') {
             $pageConfig['showSidebar'] = true;
        } elseif (isset($isIndexPage) && $isIndexPage) {
            $pageConfig['showSidebar'] = false;
        }
    }

    // Auto-Title
    if (!isset($pageConfig['title'])) {
        $slug = basename($request_uri);
        $pageConfig['title'] = ($slug === '' || $slug === 'index.php') ? 'Home - ' . $siteName : ucwords(str_replace('-', ' ', $slug)) . ' - ' . $siteName;
    }
}

// --- 4. RENDER ---
$config = array_merge($defaults, $pageConfig);

if ($config['view'] === 'errors/500') {
    http_response_code(500);
} elseif ($config['view'] === 'errors/404') {
    http_response_code(404);
}

// Extract variables for View
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
if (isset($pageConfig['headerMenu'])) {
    $headerFile = $pageConfig['headerMenu'];
} else {
    $headerMap = $settings['headerMap'] ?? [];
    $headerFile = resolveAsset($headerMap, $request_uri) ?? 'header-default';
}
$currentHeaderMenu = ROOT_PATH . '/includes/components/headers/' . $headerFile . '.php';

// Sidebar Resolution
$currentSidebar = ROOT_PATH . '/includes/components/sidebars/' . $config['sidebar'] . '.php';

require_once ROOT_PATH . '/includes/header.php';

echo '<div id="elara-layout-wrapper" class="container-fluid flex-grow-1 d-flex p-0">';
echo '  <div class="row flex-grow-1 m-0 w-100">';

if ($showSidebar && file_exists($currentSidebar)) {
    echo '    <aside class="col-md-3 col-lg-2 d-none d-md-block bg-body-tertiary border-end p-3">';
    require_once $currentSidebar;
    echo '    </aside>';
    echo '    <main id="main-content" class="col-md-9 col-lg-10 p-4" tabindex="-1" style="outline:none;">';
} else {
    echo '    <main id="main-content" class="col-12 p-0" tabindex="-1" style="outline:none;">'; 
}

// Dynamic View Resolution & Error Fallback
if (file_exists(ROOT_PATH . '/' . $config['view'] . '.php')) {
    require_once ROOT_PATH . '/' . $config['view'] . '.php';
} else {
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

require_once ROOT_PATH . '/includes/footer.php';
ob_end_flush();
?>