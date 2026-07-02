<?php
// errors/404.php
// Context: "Page Not Found"

$is_standalone = !defined('ROOT_PATH');

if ($is_standalone) {
    define('ROOT_PATH', realpath(__DIR__ . '/../../'));
    http_response_code(404);
    $pageTitle = "404 Not Found | Stardust Engine CMS";
    require_once ROOT_PATH . '/includes/header.php';
    echo '<div class="container-fluid flex-grow-1 d-flex"><div class="row flex-grow-1"><main id="main-content" class="col-12 p-0">';
}
?>

<div class="container py-5 d-flex flex-column justify-content-center" style="min-height: 70vh;">
    <div class="row justify-content-center text-center">
        <div class="col-lg-8">
            
            <div class="mb-4">
                <i class="fa-solid fa-satellite-dish fa-5x text-danger opacity-50" aria-hidden="true"></i>
            </div>

            <h1 class="display-1 fw-bold text-danger mb-0" style="letter-spacing: 5px;">404</h1>
            <h2 class="h3 text-uppercase text-body-secondary font-monospace mb-4">
                <span class="text-danger">>></span> SIGNAL LOST
            </h2>

            <div class="card bg-body-tertiary p-4 border-danger border-opacity-50 text-start mb-5 mx-auto shadow-sm" style="max-width: 600px;">
                <div class="text-danger fw-bold mb-2 border-bottom border-danger border-opacity-25 pb-2">
                    <i class="fa-solid fa-triangle-exclamation me-2" aria-hidden="true"></i>System Alert // Routing Failure
                </div>
                <div class="text-body">
                    <p class="mb-2"><strong>ERROR:</strong> The requested resource could not be located on this server.</p>
                    <p class="mb-0 text-body-secondary small">This may be due to a broken link, a missing JSON route map, or the file has been moved or deleted.</p>
                </div>
            </div>

            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="/" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="fa-solid fa-radar me-2" aria-hidden="true"></i>Return to Base
                </a>
            </div>

        </div>
    </div>
</div>

<?php
if ($is_standalone) {
    echo '</main></div></div>';
    require_once ROOT_PATH . '/includes/footer.php';
}
?>