<?php
/**
 * ============================================================================
 * COMPONENT: overview.php
 * CONTEXT: Project Section / Root Landing View (`/project`)
 *
 * PURPOSE:
 * Serves as the primary landing page for the `/project` namespace, providing
 * structural navigation (breadcrumbs and list groups) to all nested demo views.
 * ============================================================================
 */
?>

<div class="container py-4">
    
    <nav aria-label="breadcrumb" class="mb-4">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Project Overview</li>
      </ol>
    </nav>

    <div class="row">
        <div class="col-12">
            <h1 class="fw-bold mb-3 border-bottom pb-2">Project Overview &amp; Route Inheritance</h1>
            <p class="lead text-body-secondary">
                This section demonstrates inherited routing logic. By defining a <code>common</code> block under <code>"/project"</code> in <code>data/routes/routes.json</code>, all pages within this namespace automatically inherit the contextual sidebar and brand configuration unless explicitly overridden.
            </p>
        </div>
    </div>

    <div class="row mt-4 g-4">
        <div class="col-lg-6">
            <h2 class="h5 fw-bold text-uppercase text-body-secondary mb-3">
                <i class="fa-solid fa-route me-2 text-primary" aria-hidden="true"></i>Routing &amp; Layout Demos
            </h2>
            <div class="list-group shadow-sm">
                <a href="/project/about" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-3">
                    <div>
                        <h3 class="h6 mb-1 fw-bold">About Page (Layout Override)</h3>
                        <small class="text-body-secondary">Overrides the parent sidebar with <code>"sidebar": false</code> for a full-width article layout.</small>
                    </div>
                    <i class="fa-solid fa-chevron-right text-body-secondary ms-3" aria-hidden="true"></i>
                </a>
                <a href="/project/dynamic-directory" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-3">
                    <div>
                        <h3 class="h6 mb-1 fw-bold">Dynamic Directory (<code>$_GET</code> Parameters)</h3>
                        <small class="text-body-secondary">Tests query string routing (<code>?id=alpha</code>) across Elara SPA transitions.</small>
                    </div>
                    <i class="fa-solid fa-chevron-right text-body-secondary ms-3" aria-hidden="true"></i>
                </a>
                <a href="/project/secret-page" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-3">
                    <div>
                        <h3 class="h6 mb-1 fw-bold">Decoupled Route Mapping</h3>
                        <small class="text-body-secondary">Maps <code>/project/secret-page</code> to <code>pages/projects/literally-any-name.php</code>.</small>
                    </div>
                    <i class="fa-solid fa-chevron-right text-body-secondary ms-3" aria-hidden="true"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-6">
            <h2 class="h5 fw-bold text-uppercase text-body-secondary mb-3">
                <i class="fa-solid fa-sliders me-2 text-primary" aria-hidden="true"></i>Media &amp; Module Demos
            </h2>
            <div class="list-group shadow-sm">
                <a href="/project/album-releases" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-3">
                    <div>
                        <h3 class="h6 mb-1 fw-bold">Album Release &amp; Tracklist Module</h3>
                        <small class="text-body-secondary">Showcases the persistent Sticky Audio Player, Markdown lyrics modal, and DSP store selector.</small>
                    </div>
                    <i class="fa-solid fa-chevron-right text-body-secondary ms-3" aria-hidden="true"></i>
                </a>
                <a href="/project/radio" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-3">
                    <div>
                        <h3 class="h6 mb-1 fw-bold">Global Radio Console</h3>
                        <small class="text-body-secondary">Demonstrates multi-catalog JSON playlist aggregation, ISRC deduplication, and smart shuffling.</small>
                    </div>
                    <i class="fa-solid fa-chevron-right text-body-secondary ms-3" aria-hidden="true"></i>
                </a>
            </div>
        </div>
    </div>
</div>