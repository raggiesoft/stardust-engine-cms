<?php
/**
 * ============================================================================
 * COMPONENT: 505.php (Note: Implements 504 Gateway Timeout logic)
 * CONTEXT: "Gateway Timeout" / Connection Latency View
 *
 * PURPOSE:
 * Displays a themed timeout error page when the server fails to receive a timely
 * response from an upstream server (e.g. during heavy backend load or API failure).
 *
 * ARCHITECTURAL ROLE:
 * Serves as the fallback view for 50x timeout errors. Currently named 505.php but
 * explicitly emits a 504 HTTP Response Code, used by Apache ErrorDocument directives.
 *
 * KEY COMPONENTS:
 * - Standalone Check: Injects `ROOT_PATH` and invokes header/footer includes if
 *   accessed directly by the web server outside of the CMS router flow.
 * - Thematic UI: Formatted as a "Connection Latency // Request Dropped" warning
 *   to maintain immersion.
 *
 * MAINTENANCE NOTES:
 * - There is a discrepancy between the filename (505.php) and the HTTP response
 *   code emitted (504). Consider aligning these if updating routing rules.
 * - Do not alter the `$is_standalone` check; it is required for proper rendering
 *   when bypassing the front controller.
 * ============================================================================
 */

$is_standalone = !defined('ROOT_PATH');

if ($is_standalone) {
    define('ROOT_PATH', realpath(__DIR__ . '/../../'));
    http_response_code(504);
    $pageTitle = "504 Gateway Timeout | Stardust Engine CMS";
    require_once ROOT_PATH . '/includes/header.php';
    echo '<div class="container-fluid flex-grow-1 d-flex"><div class="row flex-grow-1"><main id="main-content" class="col-12 p-0">';
}
?>

<div class="container py-5 d-flex flex-column justify-content-center" style="min-height: 70vh;">
    <div class="row justify-content-center text-center">
        <div class="col-lg-8">
            
            <div class="mb-4">
                <i class="fa-solid fa-hourglass-half fa-5x text-secondary opacity-75" aria-hidden="true"></i>
            </div>

            <h1 class="display-1 fw-bold text-secondary mb-0" style="letter-spacing: 5px;">504</h1>
            <h2 class="h3 text-uppercase text-body-secondary font-monospace mb-4">
                <span class="text-secondary">>></span> GATEWAY TIMEOUT
            </h2>
            
            <div class="card bg-body-tertiary p-4 border-secondary border-opacity-50 text-start mb-5 mx-auto shadow-sm" style="max-width: 600px;">
                <div class="text-secondary fw-bold mb-2 border-bottom border-secondary border-opacity-25 pb-2">
                    <i class="fa-solid fa-stopwatch me-2" aria-hidden="true"></i>Connection Latency // Request Dropped
                </div>
                <div class="text-body">
                    <p class="mb-2"><strong>TIMEOUT:</strong> The server did not receive a timely response from the upstream application.</p>
                    <p class="mb-0 text-body-secondary small">This typically occurs when a backend process stalls or external APIs take too long to respond. Please try your request again later.</p>
                </div>
            </div>
            
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="javascript:location.reload();" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="fa-solid fa-rotate-right me-2" aria-hidden="true"></i>Re-Establish Link
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