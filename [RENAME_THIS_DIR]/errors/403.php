<?php
/**
 * ============================================================================
 * COMPONENT: 403.php
 * CONTEXT: "Access Denied" / Security Breach View
 *
 * PURPOSE:
 * Displays a themed 403 Access Denied error page to users when they attempt to
 * access restricted directories, protected files, or resources without proper
 * authorization.
 *
 * ARCHITECTURAL ROLE:
 * Loaded dynamically by the CMS router on permission failure, or accessed
 * directly (standalone) by the web server (e.g. via Apache ErrorDocument)
 * when a direct file request is forbidden by server rules.
 *
 * KEY COMPONENTS:
 * - Standalone Check: Checks `ROOT_PATH` to see if invoked by the web server.
 *   If so, it sets the 403 HTTP status code, bootstraps the environment, and
 *   wraps the error content in the global site theme.
 * - Thematic UI: Utilizes "Access Restricted" / "Security Violation" motifs
 *   to align the error state with the site's narrative framework.
 *
 * MAINTENANCE NOTES:
 * - The `$is_standalone` block must remain at the top to ensure HTTP headers
 *   can be sent cleanly before any output is rendered by the Apache fallback.
 * ============================================================================
 */

$is_standalone = !defined('ROOT_PATH');

if ($is_standalone) {
    define('ROOT_PATH', realpath(__DIR__ . '/../../'));
    http_response_code(403);
    $pageTitle = "403 Access Denied | Stardust Engine CMS";
    require_once ROOT_PATH . '/includes/header.php';
    echo '<div class="container-fluid flex-grow-1 d-flex"><div class="row flex-grow-1"><main id="main-content" class="col-12 p-0">';
}
?>

<div class="container py-5 d-flex flex-column justify-content-center" style="min-height: 70vh;">
    <div class="row justify-content-center text-center">
        <div class="col-lg-8">
            
            <div class="mb-4">
                <i class="fa-solid fa-shield-halved fa-5x text-warning opacity-75" aria-hidden="true"></i>
            </div>

            <h1 class="display-1 fw-bold text-warning mb-0" style="letter-spacing: 5px;">403</h1>
            <h2 class="h3 text-uppercase text-body-secondary font-monospace mb-4">
                <span class="text-warning">>></span> ACCESS RESTRICTED
            </h2>
            
            <div class="card bg-body-tertiary p-4 border-warning border-opacity-50 text-start mb-5 mx-auto shadow-sm" style="max-width: 600px;">
                <div class="text-warning fw-bold mb-2 border-bottom border-warning border-opacity-25 pb-2">
                    <i class="fa-solid fa-fingerprint me-2" aria-hidden="true"></i>Security Violation // Authorization Failed
                </div>
                <div class="text-body">
                    <p class="mb-2"><strong>ACCESS DENIED:</strong> You do not have the required permissions to view this directory or resource.</p>
                    <p class="mb-0 text-body-secondary small">If you believe you should have access, please check your authentication tokens or contact the system administrator.</p>
                </div>
            </div>
            
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="/" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="fa-solid fa-house me-2" aria-hidden="true"></i>Return Home
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