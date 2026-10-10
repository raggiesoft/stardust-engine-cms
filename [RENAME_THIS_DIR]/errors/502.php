<?php
/**
 * ============================================================================
 * COMPONENT: 502.php
 * CONTEXT: "Bad Gateway" / Upstream Error View
 *
 * PURPOSE:
 * Displays a themed 502 Bad Gateway error page when the web server (acting as a
 * reverse proxy) receives an invalid response from an upstream server (e.g. PHP-FPM).
 *
 * ARCHITECTURAL ROLE:
 * Loaded dynamically by the web server (e.g. Apache ErrorDocument, Nginx proxy_pass
 * fallback) when the application layer crashes or drops the connection prematurely.
 *
 * KEY COMPONENTS:
 * - Standalone Check: Inspects `ROOT_PATH` to see if loaded independently of the
 *   CMS router. If true, sets the 502 HTTP response code and embeds the error
 *   within the global header/footer structure.
 * - Thematic UI: Styled as a "Network Error // Upstream Proxy" to keep the
 *   narrative consistent during technical failures.
 *
 * MAINTENANCE NOTES:
 * - Ensure `$is_standalone` logic remains at the very top of the script so HTTP
 *   headers can be sent correctly without "headers already sent" warnings.
 * ============================================================================
 */

$is_standalone = !defined('ROOT_PATH');

if ($is_standalone) {
    define('ROOT_PATH', realpath(__DIR__ . '/../../'));
    http_response_code(502);
    $pageTitle = "502 Bad Gateway | Stardust Engine CMS";
    require_once ROOT_PATH . '/includes/header.php';
    echo '<div class="container-fluid flex-grow-1 d-flex"><div class="row flex-grow-1"><main id="main-content" class="col-12 p-0">';
}
?>

<div class="container py-5 d-flex flex-column justify-content-center" style="min-height: 70vh;">
    <div class="row justify-content-center text-center">
        <div class="col-lg-8">
            
            <div class="mb-4">
                <i class="fa-solid fa-network-wired fa-5x text-warning opacity-75" aria-hidden="true"></i>
            </div>

            <h1 class="display-1 fw-bold text-warning mb-0" style="letter-spacing: 5px;">502</h1>
            <h2 class="h3 text-uppercase text-body-secondary font-monospace mb-4">
                <span class="text-warning">>></span> BAD GATEWAY
            </h2>
            
            <div class="card bg-body-tertiary p-4 border-warning border-opacity-50 text-start mb-5 mx-auto shadow-sm" style="max-width: 600px;">
                <div class="text-warning fw-bold mb-2 border-bottom border-warning border-opacity-25 pb-2">
                    <i class="fa-solid fa-triangle-exclamation me-2" aria-hidden="true"></i>Network Error // Upstream Proxy
                </div>
                <div class="text-body">
                    <p class="mb-2"><strong>UPLINK FAILED:</strong> The server, while acting as a gateway or proxy, received an invalid response from the upstream server.</p>
                    <p class="mb-0 text-body-secondary small">This usually indicates an issue with PHP-FPM or the underlying application socket. Please try refreshing your connection.</p>
                </div>
            </div>
            
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="javascript:location.reload();" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="fa-solid fa-rotate-right me-2" aria-hidden="true"></i>Retry Connection
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