<?php
/**
 * ============================================================================
 * COMPONENT: header-default.php
 * CONTEXT: Default Global Navigation Menu
 *
 * PURPOSE:
 *     Serves as the default header navigation menu (navbar items) for the
 *     Stardust Engine CMS. Dynamically calculates the active route to apply
 *     WCAG-compliant styling highlights and exposes all included sample views.
 * 
 * DESIGN DECISIONS & FUTURE MAINTENANCE:
 *     - Active State Parsing: Uses PHP's `parse_url` to inspect `$_SERVER['REQUEST_URI']`
 *       and sets `aria-current="page"` alongside `.active.fw-bold`.
 *     - Includes a built-in Light/Dark theme switcher button (`[data-stardust-theme-toggle]`)
 *       handled via global event delegation in `includes/footer.php`.
 * ============================================================================
 */

// 1. Determine Active States for UI Highlighting
$request_uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

if (strlen($request_uri) > 1) {
    $request_uri = rtrim($request_uri, '/');
}

$isHome = ($request_uri === '/' || $request_uri === '/home');
$isProject = str_starts_with($request_uri, '/project');
?>

<ul class="navbar-nav ms-auto mb-2 mb-md-0 align-items-md-center gap-md-1">
  
  <!-- Primary Home Link -->
  <li class="nav-item">
    <a class="nav-link <?php echo $isHome ? 'active fw-bold' : ''; ?>" <?php echo $isHome ? 'aria-current="page"' : ''; ?> href="/">
        <i class="fa-solid fa-house me-1 text-primary" aria-hidden="true"></i>Home
    </a>
  </li>

  <!-- Dropdown Menu for Included CMS Demo Routes -->
  <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle <?php echo $isProject ? 'active fw-bold' : ''; ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fa-solid fa-compass me-1 text-primary" aria-hidden="true"></i>Examples &amp; Demos
    </a>
    <ul class="dropdown-menu dropdown-menu-end shadow border-secondary-subtle">
      <li><h6 class="dropdown-header text-uppercase small fw-bold">Routing &amp; Inheritance</h6></li>
      <li>
        <a class="dropdown-item <?php echo $request_uri === '/project' ? 'active' : ''; ?>" href="/project">
            <i class="fa-solid fa-diagram-project fa-fw me-2 text-primary" aria-hidden="true"></i>Project Overview (Common Config)
        </a>
      </li>
      <li>
        <a class="dropdown-item <?php echo $request_uri === '/project/about' ? 'active' : ''; ?>" href="/project/about">
            <i class="fa-solid fa-Sliders fa-fw me-2 text-info" aria-hidden="true"></i>Sub-Directory Override (<code>/about</code>)
        </a>
      </li>
      <li>
        <a class="dropdown-item <?php echo $request_uri === '/project/dynamic-directory' ? 'active' : ''; ?>" href="/project/dynamic-directory">
            <i class="fa-solid fa-folder-tree fa-fw me-2 text-success" aria-hidden="true"></i>Auto-Indexing Directory Scanner
        </a>
      </li>
      <li>
        <a class="dropdown-item <?php echo $request_uri === '/project/secret-page' ? 'active' : ''; ?>" href="/project/secret-page">
            <i class="fa-solid fa-mask fa-fw me-2 text-warning" aria-hidden="true"></i>Decoupled Route Mapping
        </a>
      </li>
      <li><hr class="dropdown-divider"></li>
      <li><h6 class="dropdown-header text-uppercase small fw-bold">Built-In Media Modules</h6></li>
      <li>
        <a class="dropdown-item <?php echo $request_uri === '/project/album-releases' ? 'active' : ''; ?>" href="/project/album-releases">
            <i class="fa-solid fa-compact-disc fa-fw me-2 text-primary" aria-hidden="true"></i>Stardust Audio Player &amp; Tracklist
        </a>
      </li>
      <li>
        <a class="dropdown-item <?php echo $request_uri === '/project/radio' ? 'active' : ''; ?>" href="/project/radio">
            <i class="fa-solid fa-tower-broadcast fa-fw me-2 text-danger" aria-hidden="true"></i>Live Radio &amp; Parsedown Demo
        </a>
      </li>
    </ul>
  </li>

  <!-- External Source Code Link -->
  <li class="nav-item">
    <a class="nav-link" href="https://github.com/raggiesoft/stardust-engine-cms" target="_blank" rel="noopener noreferrer">
        <i class="fa-brands fa-github me-1" aria-hidden="true"></i>GitHub
    </a>
  </li>

  <!-- Persistent Theme Toggle Button -->
  <li class="nav-item ms-md-2 mt-2 mt-md-0">
    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-stardust-theme-toggle aria-label="Toggle Light or Dark Color Theme">
        <i class="fa-solid fa-circle-half-stroke me-1" aria-hidden="true"></i>Theme
    </button>
  </li>

</ul>