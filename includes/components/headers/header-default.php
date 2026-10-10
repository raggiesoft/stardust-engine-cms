<?php
/**
 * Architectural Block Comment:
 * File: header-default.php
 * Purpose:
 *     Serves as the default fallback header navigation menu (navbar items) for the Stardust Engine CMS.
 *     It dynamically calculates the active route to apply styling highlights and renders a Bootstrap-compatible 
 *     dropdown hierarchy.
 * 
 * Design Decisions & Future Maintenance:
 *     - Active State Parsing: Uses PHP's `parse_url` to inspect the `$_SERVER['REQUEST_URI']`. It evaluates the 
 *       root path and sub-paths to toggle the `active` and `fw-bold` CSS classes on the corresponding nav elements.
 *     - Trailing Slashes: Automatically strips trailing slashes (`rtrim`) from the URI to ensure strict route matching 
 *       (e.g., matching `/project/` with `/project`).
 *     - Output Structure: Notice that this file only outputs the inner `<ul>` items (`navbar-nav`), not the wrapping 
 *       `<nav>` or brand logos. The parent layout shell is responsible for providing the overarching navbar container.
 */

// 1. Determine Active States for UI Highlighting
// Extract just the path portion of the URI (ignoring query parameters).
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Normalize the URI by removing any trailing slashes, unless it's strictly the root '/'.
if (strlen($request_uri) > 1) {
    $request_uri = rtrim($request_uri, '/');
}

// Evaluate boolean flags based on the current parsed path.
$isHome = ($request_uri === '/' || $request_uri === '/home');
$isProject = str_starts_with($request_uri, '/project');
?>

<ul class="navbar-nav ms-auto mb-2 mb-md-0">
  
  <!-- Primary Home Link -->
  <li class="nav-item">
    <!-- Inject 'active fw-bold' classes if the evaluated $isHome flag is true -->
    <a class="nav-link <?php echo $isHome ? 'active fw-bold' : ''; ?>" href="/">
        <i class="fa-solid fa-house me-2 text-primary" aria-hidden="true"></i>Home
    </a>
  </li>

  <!-- Dropdown Menu for Examples/Project Routes -->
  <li class="nav-item dropdown">
    <!-- Inject 'active fw-bold' classes if the evaluated $isProject flag is true -->
    <a class="nav-link dropdown-toggle <?php echo $isProject ? 'active fw-bold' : ''; ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fa-solid fa-folder me-2 text-warning" aria-hidden="true"></i>Examples
    </a>
    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-secondary-subtle">
      <!-- Standard routes inside the project directory -->
      <li><a class="dropdown-item" href="/project"><i class="fa-solid fa-file-lines me-2 text-secondary" aria-hidden="true"></i>Project Overview</a></li>
      <li><a class="dropdown-item" href="/project/about"><i class="fa-solid fa-circle-info me-2 text-info" aria-hidden="true"></i>About Us</a></li>
      <li><hr class="dropdown-divider"></li>
      <li><a class="dropdown-item" href="/project/portfolio"><i class="fa-solid fa-briefcase me-2 text-primary" aria-hidden="true"></i>Portfolio</a></li>
    </ul>
  </li>

  <!-- External Source Code Link -->
  <li class="nav-item">
    <a class="nav-link" href="https://github.com/raggiesoft/stardust-engine-cms" target="_blank" rel="noopener noreferrer">
        <i class="fa-brands fa-github me-2" aria-hidden="true"></i>Source Code
    </a>
  </li>

</ul>