<?php
// includes/components/headers/header-default.php
// Generic fallback header for the Stardust Engine CMS

// 1. Determine Active States for UI Highlighting
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (strlen($request_uri) > 1) {
    $request_uri = rtrim($request_uri, '/');
}

$isHome = ($request_uri === '/' || $request_uri === '/home');
$isProject = str_starts_with($request_uri, '/project');
?>

<ul class="navbar-nav ms-auto mb-2 mb-md-0">
  
  <li class="nav-item">
    <a class="nav-link <?php echo $isHome ? 'active fw-bold' : ''; ?>" href="/">
        <i class="fa-solid fa-house me-2 text-primary" aria-hidden="true"></i>Home
    </a>
  </li>

  <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle <?php echo $isProject ? 'active fw-bold' : ''; ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fa-solid fa-folder me-2 text-warning" aria-hidden="true"></i>Examples
    </a>
    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-secondary-subtle">
      <li><a class="dropdown-item" href="/project"><i class="fa-solid fa-file-lines me-2 text-secondary" aria-hidden="true"></i>Project Overview</a></li>
      <li><a class="dropdown-item" href="/project/about"><i class="fa-solid fa-circle-info me-2 text-info" aria-hidden="true"></i>About Us</a></li>
      <li><hr class="dropdown-divider"></li>
      <li><a class="dropdown-item" href="/project/portfolio"><i class="fa-solid fa-briefcase me-2 text-primary" aria-hidden="true"></i>Portfolio</a></li>
    </ul>
  </li>

  <li class="nav-item">
    <a class="nav-link" href="https://github.com/raggiesoft/stardust-engine-cms" target="_blank" rel="noopener noreferrer">
        <i class="fa-brands fa-github me-2" aria-hidden="true"></i>Source Code
    </a>
  </li>

</ul>