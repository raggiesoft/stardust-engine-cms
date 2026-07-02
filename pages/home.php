<?php
// pages/home.php
// The Global Root Document (Mapped to '/')

// Elara automatically injects variables from settings.json and routes.json.
// Example: $siteName, $pageTitle, $cdnBaseUrl
?>

<div class="container-fluid py-5 bg-body-tertiary border-bottom">
    <div class="row justify-content-center">
        <div class="col-lg-8 text-center">
            <h1 class="display-4 fw-bold text-body-emphasis mb-3">
                Welcome to <?php echo htmlspecialchars($siteName); ?>
            </h1>
            <p class="lead text-body-secondary mb-4">
                This page is being rendered by the Stardust Engine CMS. It demonstrates a lightweight, zero-database architecture prioritizing speed and accessibility.
            </p>
            <div class="d-flex justify-content-center gap-3">
                <a href="/project" class="btn btn-primary btn-lg px-4 rounded-pill shadow-sm">
                    View Project Section
                </a>
                <a href="https://github.com/raggiesoft/stardust-engine-cms" target="_blank" rel="noopener noreferrer" class="btn btn-outline-secondary btn-lg px-4 rounded-pill shadow-sm">
                    <i class="fa-brands fa-github me-2" aria-hidden="true"></i>Documentation
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-4 text-center">
                    <div class="text-primary mb-3">
                        <svg width="48" height="48" fill="currentColor" class="bi bi-rocket-takeoff" viewBox="0 0 16 16" aria-hidden="true">
                          <path d="M9.752 6.193c.599.6 1.73.437 2.528-.362s.96-1.932.362-2.531c-.599-.6-1.73-.438-2.528.361-.798.8-.96 1.933-.362 2.532"/>
                          <path d="M15.811 3.312c-.363 1.534-1.336 3.195-2.06 4.24-.037.051-.08.096-.127.135l-2.727 2.728 2.513 2.512a.55.55 0 0 1-.773.778l-2.3-2.3-2.728 2.728c-.039.047-.084.09-.135.127-1.045.724-2.706 1.697-4.24 2.06-.48.114-.946.06-1.314-.148-.368-.208-.59-.576-.64-1.033a.55.55 0 0 1 .152-.423l2.3-2.3-.614-.614a.55.55 0 0 1 .773-.778l.614.614 2.3-2.3a.55.55 0 0 1 .423-.152c.457.05.825.272 1.033.64.208.368.262.834.148 1.314zM6.55 12.18c-.378-.54-1.03-1.428-1.57-2.096L1.87 13.193a.2.2 0 0 0 .044.296c.148.083.376.136.686.062 1.055-.246 2.62-1.127 3.95-2.37z"/>
                        </svg>
                    </div>
                    <h2 class="h5 fw-bold">Elara SPA Router</h2>
                    <p class="small text-body-secondary mb-0">Navigate between pages instantly without hard browser refreshes.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-4 text-center">
                    <div class="text-success mb-3">
                        <svg width="48" height="48" fill="currentColor" class="bi bi-universal-access-circle" viewBox="0 0 16 16" aria-hidden="true">
                          <path d="M8 4.143A1.071 1.071 0 1 0 8 2a1.071 1.071 0 0 0 0 2.143m-4.668 1.47 3.24.316v2.5l-.323 4.585A.383.383 0 0 0 7 13.14l.826-4.017c.045-.18.301-.18.346 0l.826 4.017a.383.383 0 0 0 .743-.126L9.42 8.43v-2.5l3.24-.316a.387.387 0 0 0 .28-.521c-.131-.322-.495-.494-.859-.44l-4.084.597-4.084-.597c-.364-.054-.728.118-.859.44a.387.387 0 0 0 .28.521Z"/>
                          <path d="M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0M1 8a7 7 0 1 1 14 0A7 7 0 0 1 1 8"/>
                        </svg>
                    </div>
                    <h2 class="h5 fw-bold">WCAG 2.1 AA Compliant</h2>
                    <p class="small text-body-secondary mb-0">Semantic HTML and ARIA landmarks embedded at the core level.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-4 text-center">
                    <div class="text-info mb-3">
                        <svg width="48" height="48" fill="currentColor" class="bi bi-braces" viewBox="0 0 16 16" aria-hidden="true">
                          <path d="M2.114 8.063V7.9c1.005-.102 1.497-.615 1.497-1.6V4.503c0-1.094.39-1.538 1.354-1.538h.273V2h-.376C3.25 2 2.49 2.759 2.49 4.352v1.524c0 1.094-.376 1.456-1.49 1.456v1.299c1.114 0 1.49.362 1.49 1.456v1.524c0 1.593.759 2.352 2.372 2.352h.376v-.964h-.273c-.964 0-1.354-.444-1.354-1.538V9.663c0-.984-.492-1.497-1.497-1.6zM13.886 7.9v.163c-1.005.103-1.497.616-1.497 1.6v1.798c0 1.094-.39 1.538-1.354 1.538h-.273v.964h.376c1.613 0 2.372-.759 2.372-2.352v-1.524c0-1.094.376-1.456 1.49-1.456V7.332c-1.114 0-1.49-.362-1.49-1.456V4.352C13.51 2.759 12.75 2 11.138 2h-.376v.964h.273c.964 0 1.354.444 1.354 1.538V6.3c0 .984.492 1.497 1.497 1.6"/>
                        </svg>
                    </div>
                    <h2 class="h5 fw-bold">JSON Routing</h2>
                    <p class="small text-body-secondary mb-0">Hierarchical configuration ensures your code stays DRY and maintainable.</p>
                </div>
            </div>
        </div>
    </div>
</div>