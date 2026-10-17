<?php
/**
 * ============================================================================
 * COMPONENT: home.php
 * CONTEXT: Global Root Document (`/`)
 *
 * PURPOSE:
 * Serves as the primary landing page for the Stardust Engine CMS, introducing
 * developers to the core capabilities and providing interactive links to all
 * included architecture demonstrations.
 * ============================================================================
 */
?>

<div class="container-fluid py-5 bg-body-tertiary border-bottom">
    <div class="row justify-content-center">
        <div class="col-lg-8 text-center">
            <span class="badge bg-primary-subtle text-primary-emphasis rounded-pill px-3 py-2 mb-3 font-monospace">
                PHP 8.5 + ELARA VANILLA JS SPA ROUTER
            </span>
            <h1 class="display-4 fw-bold text-body-emphasis mb-3">
                Welcome to <?php echo htmlspecialchars($siteName ?? 'Stardust Engine CMS'); ?>
            </h1>
            <p class="lead text-body-secondary mb-4">
                A blazing-fast, zero-database flat-file Content Management System prioritizing sub-millisecond routing, persistent media state, and strict WCAG 2.1 AA accessibility.
            </p>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="/project" class="btn btn-primary btn-lg px-4 rounded-pill shadow-sm">
                    <i class="fa-solid fa-compass me-2" aria-hidden="true"></i>Explore Live Demos
                </a>
                <a href="/project/album-releases" class="btn btn-outline-primary btn-lg px-4 rounded-pill shadow-sm">
                    <i class="fa-solid fa-compact-disc me-2" aria-hidden="true"></i>Test Audio Engine
                </a>
                <a href="https://github.com/raggiesoft/stardust-engine-cms" target="_blank" rel="noopener noreferrer" class="btn btn-outline-secondary btn-lg px-4 rounded-pill shadow-sm">
                    <i class="fa-brands fa-github me-2" aria-hidden="true"></i>GitHub Repository
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <!-- Core Pillars -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card h-100 border-secondary-subtle shadow-sm hover-lift">
                <div class="card-body p-4 text-center">
                    <div class="text-primary mb-3">
                        <i class="bi bi-rocket-takeoff fs-1" aria-hidden="true"></i>
                    </div>
                    <h2 class="h5 fw-bold">Elara SPA Router</h2>
                    <p class="small text-body-secondary mb-0">
                        Intercepts standard <code>&lt;a&gt;</code> navigation and swaps layout zones via <code>DOMParser</code> while keeping persistent modules (like audio players) alive.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-secondary-subtle shadow-sm hover-lift">
                <div class="card-body p-4 text-center">
                    <div class="text-success mb-3">
                        <i class="bi bi-universal-access-circle fs-1" aria-hidden="true"></i>
                    </div>
                    <h2 class="h5 fw-bold">WCAG 2.1 AA Compliant</h2>
                    <p class="small text-body-secondary mb-0">
                        Semantic HTML5 landmarks, automatic focus management on SPA navigation, high-contrast Light/Dark mode tokens, and ARIA live regions.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-secondary-subtle shadow-sm hover-lift">
                <div class="card-body p-4 text-center">
                    <div class="text-info mb-3">
                        <i class="bi bi-braces-asterisk fs-1" aria-hidden="true"></i>
                    </div>
                    <h2 class="h5 fw-bold">Hierarchical JSON Routing</h2>
                    <p class="small text-body-secondary mb-0">
                        Define directory-wide <code>common</code> blocks in <code>data/routes/routes.json</code> so child routes inherit headers, sidebars, and themes automatically.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Interactive Architecture Showcase Grid -->
    <div class="border-top pt-5">
        <div class="d-flex justify-content-between align-items-end flex-wrap mb-4">
            <div>
                <h2 class="h3 fw-bold mb-1">Interactive Architecture Demos</h2>
                <p class="text-body-secondary mb-0">Click any card below to test soft SPA routing, layout overrides, and persistent audio playback.</p>
            </div>
            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 mt-2 mt-sm-0" data-stardust-theme-toggle>
                <i class="fa-solid fa-circle-half-stroke me-1" aria-hidden="true"></i>Toggle Light / Dark Theme
            </button>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <a href="/project" class="text-decoration-none">
                    <div class="card h-100 border-secondary-subtle shadow-sm hover-lift">
                        <div class="card-body p-4">
                            <span class="badge bg-primary-subtle text-primary-emphasis mb-2 font-monospace">/project</span>
                            <h3 class="h5 fw-bold text-body-emphasis">1. DRY Route Inheritance</h3>
                            <p class="small text-body-secondary mb-0">
                                Demonstrates how the <code>/project</code> <code>common</code> block automatically injects a contextual sidebar and brand header.
                            </p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-6 col-lg-4">
                <a href="/project/about" class="text-decoration-none">
                    <div class="card h-100 border-secondary-subtle shadow-sm hover-lift">
                        <div class="card-body p-4">
                            <span class="badge bg-info-subtle text-info-emphasis mb-2 font-monospace">/project/about</span>
                            <h3 class="h5 fw-bold text-body-emphasis">2. Sub-Directory Overrides</h3>
                            <p class="small text-body-secondary mb-0">
                                Shows how a child route can override its parent's <code>common</code> block (setting <code>"sidebar": false</code> for a full-width layout).
                            </p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-6 col-lg-4">
                <a href="/project/secret-page" class="text-decoration-none">
                    <div class="card h-100 border-secondary-subtle shadow-sm hover-lift">
                        <div class="card-body p-4">
                            <span class="badge bg-warning-subtle text-warning-emphasis mb-2 font-monospace">/project/secret-page</span>
                            <h3 class="h5 fw-bold text-body-emphasis">3. Decoupled View Mapping</h3>
                            <p class="small text-body-secondary mb-0">
                                Maps a clean semantic URL to <code>pages/projects/literally-any-name.php</code> without exposing internal filenames.
                            </p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-6 col-lg-4">
                <a href="/project/dynamic-directory" class="text-decoration-none">
                    <div class="card h-100 border-secondary-subtle shadow-sm hover-lift">
                        <div class="card-body p-4">
                            <span class="badge bg-success-subtle text-success-emphasis mb-2 font-monospace">/project/dynamic-directory</span>
                            <h3 class="h5 fw-bold text-body-emphasis">4. Query Parameter Routing</h3>
                            <p class="small text-body-secondary mb-0">
                                Tests <code>$_GET</code> query string state transitions (<code>?id=alpha</code>) inside the Elara SPA lifecycle.
                            </p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-6 col-lg-4">
                <a href="/project/album-releases" class="text-decoration-none">
                    <div class="card h-100 border-secondary-subtle shadow-sm hover-lift">
                        <div class="card-body p-4">
                            <span class="badge bg-primary-subtle text-primary-emphasis mb-2 font-monospace">/project/album-releases</span>
                            <h3 class="h5 fw-bold text-body-emphasis">5. Audio Player &amp; Tracklist</h3>
                            <p class="small text-body-secondary mb-0">
                                Full demonstration of the persistent Sticky Audio Player, Markdown lyrics modal, DSP store selector, and Schema.org JSON-LD.
                            </p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-6 col-lg-4">
                <a href="/project/radio" class="text-decoration-none">
                    <div class="card h-100 border-secondary-subtle shadow-sm hover-lift">
                        <div class="card-body p-4">
                            <span class="badge bg-danger-subtle text-danger-emphasis mb-2 font-monospace">/project/radio</span>
                            <h3 class="h5 fw-bold text-body-emphasis">6. Global Radio Aggregator</h3>
                            <p class="small text-body-secondary mb-0">
                                Demonstrates multi-catalog JSON aggregation, ISRC deduplication, smart album shuffling, and continuous queue playback.
                            </p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>