<?php
/**
 * ============================================================================
 * COMPONENT: sidebar-default.php
 * CONTEXT: Default Contextual Sidebar Navigation
 *
 * PURPOSE:
 *     Provides the default sidebar navigation component for the Stardust Engine CMS.
 *     Renders alongside the main content area in multi-column layouts, offering 
 *     contextual navigation to all included sample views and documentation.
 * ============================================================================
 */

$sidebar_uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if (strlen($sidebar_uri) > 1) {
    $sidebar_uri = rtrim($sidebar_uri, '/');
}
?>

<!-- Local Navigation Block -->
<div class="card border-0 bg-transparent mb-4">
    <div class="card-body p-0 p-xl-2">
        <h2 class="h6 text-uppercase fw-bold text-body-secondary mb-3 small border-bottom border-secondary-subtle pb-2">
            <i class="fa-solid fa-map-location-dot me-2 text-primary" aria-hidden="true"></i>Sample Routes
        </h2>
        <ul class="nav flex-column small gap-1">
            <li class="nav-item">
                <a class="nav-link link-body-emphasis rounded <?php echo $sidebar_uri === '/' ? 'fw-bold bg-body-secondary' : ''; ?>" href="/">
                    <i class="fa-solid fa-house fa-fw me-2 text-primary" aria-hidden="true"></i>Return Home
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link link-body-emphasis rounded <?php echo $sidebar_uri === '/project' ? 'fw-bold bg-body-secondary' : ''; ?>" href="/project">
                    <i class="fa-solid fa-folder fa-fw me-2 text-warning" aria-hidden="true"></i>Project Overview
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link link-body-emphasis rounded <?php echo $sidebar_uri === '/project/about' ? 'fw-bold bg-body-secondary' : ''; ?>" href="/project/about">
                    <i class="fa-solid fa-circle-info fa-fw me-2 text-info" aria-hidden="true"></i>About (Full-Width Override)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link link-body-emphasis rounded <?php echo $sidebar_uri === '/project/album-releases' ? 'fw-bold bg-body-secondary' : ''; ?>" href="/project/album-releases">
                    <i class="fa-solid fa-compact-disc fa-fw me-2 text-primary" aria-hidden="true"></i>Album &amp; Tracklist Demo
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link link-body-emphasis rounded <?php echo $sidebar_uri === '/project/radio' ? 'fw-bold bg-body-secondary' : ''; ?>" href="/project/radio">
                    <i class="fa-solid fa-tower-broadcast fa-fw me-2 text-danger" aria-hidden="true"></i>Radio &amp; Markdown Demo
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link link-body-emphasis rounded <?php echo $sidebar_uri === '/project/dynamic-directory' ? 'fw-bold bg-body-secondary' : ''; ?>" href="/project/dynamic-directory">
                    <i class="fa-solid fa-folder-tree fa-fw me-2 text-success" aria-hidden="true"></i>Dynamic Directory Demo
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link link-body-emphasis rounded <?php echo $sidebar_uri === '/project/secret-page' ? 'fw-bold bg-body-secondary' : ''; ?>" href="/project/secret-page">
                    <i class="fa-solid fa-mask fa-fw me-2 text-warning" aria-hidden="true"></i>Decoupled View Mapping
                </a>
            </li>
        </ul>
    </div>
</div>

<!-- External Links Block -->
<div class="card border-0 bg-transparent">
    <div class="card-body p-0 p-xl-2">
        <h2 class="h6 text-uppercase fw-bold text-body-secondary mb-3 small border-bottom border-secondary-subtle pb-2">
            <i class="fa-brands fa-github me-2" aria-hidden="true"></i>External Links
        </h2>
        <ul class="nav flex-column small gap-1">
            <li class="nav-item">
                <a class="nav-link link-secondary rounded" href="https://github.com/raggiesoft/stardust-engine-cms" target="_blank" rel="noopener noreferrer">
                    <span class="d-flex align-items-center justify-content-between">
                        <span><i class="fa-solid fa-book-open me-2" aria-hidden="true"></i>GitHub Repository</span>
                        <i class="fa-solid fa-arrow-up-right-from-square small opacity-50" aria-hidden="true"></i>
                    </span>
                </a>
            </li>
        </ul>
    </div>
</div>