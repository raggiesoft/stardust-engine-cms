<?php
/**
 * ============================================================================
 * STARDUST ENGINE CMS - LOCAL DEVELOPMENT SERVER ROUTER (`router.php`)
 * ============================================================================
 * 
 * PURPOSE:
 * Allows any developer who clones this repository to launch a complete local
 * development server immediately using PHP's built-in web server:
 * 
 *   php -S localhost:8000 router.php
 * 
 * ARCHITECTURAL ROLE:
 * - Static Asset Passthrough: Serves existing static files in `/assets/` directly.
 * - Security Block: Blocks direct HTTP access to `/includes/`, `/data/`, and `/devops/`
 *   (mirroring the production `nginx.conf.example` rules).
 * - Front Controller Delegation: Forwards all clean URLs (`/`, `/project`,
 *   `/project/album-releases`, etc.) to the core Stardust Engine router.
 * ============================================================================
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$requestedFile = __DIR__ . $uri;

// 1. Block direct access to protected internal directories (mirrors nginx.conf.example)
if (preg_match('#^/(includes/components|includes/header\.php|includes/footer\.php|data|devops|\.git)#i', $uri)) {
    http_response_code(403);
    require __DIR__ . '/[RENAME_THIS_DIR]/errors/403.php';
    return true;
}

// 2. Serve static files (CSS, JS, SVG, images, JSON/MD in /assets/ or /includes/modules/) directly
if ($uri !== '/' && is_file($requestedFile) && !str_ends_with(strtolower($requestedFile), '.php')) {
    return false;
}

// 3. Delegate all application routes to the core Stardust Engine front-controller
require __DIR__ . '/[RENAME_THIS_DIR]/[RENAME_THIS_ROUTER].php';
