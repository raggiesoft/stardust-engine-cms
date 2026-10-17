<?php
/**
 * ============================================================================
 * COMPONENT: footer-default.php
 * CONTEXT: Default Visual Footer Component
 *
 * PURPOSE:
 *     Serves as the generic, system-wide fallback visual footer for the
 *     Stardust Engine CMS. Rendered inside `<footer id="elara-master-footer">`
 *     by `includes/footer.php`.
 * 
 * DESIGN DECISIONS & FUTURE MAINTENANCE:
 *     - Uses a semantic `<div>` wrapper rather than a nested `<footer>` tag so
 *       it complies strictly with HTML5 and WCAG 2.1 AA landmark specifications.
 *     - Links directly to all working sample routes included in `/pages/projects/`.
 * ============================================================================
 */
?>
<div class="bg-body-tertiary border-top py-5">
    <div class="container">
        <div class="row gy-4">
            
            <!-- Column 1: Brand & Description -->
            <div class="col-lg-4 col-md-6">
                <a href="/" class="d-flex align-items-center mb-3 link-body-emphasis text-decoration-none">
                    <i class="fa-solid fa-rocket me-2 fs-4 text-primary" aria-hidden="true"></i>
                    <span class="fs-5 fw-bold text-uppercase brand-font">Stardust Engine</span>
                </a>
                <p class="text-body-secondary small mb-3">
                    A blazing-fast, zero-database flat-file CMS powered by the Elara Vanilla JS SPA Router and PHP 8.5.
                </p>
                <div class="small text-body-secondary">
                    <i class="fa-solid fa-microchip me-1 text-primary" aria-hidden="true"></i> Built for speed, accessibility, and independence.
                </div>
            </div>

            <!-- Column 2: Example Routing Links -->
            <div class="col-lg-4 col-md-6">
                <h2 class="h6 fw-bold text-uppercase mb-3">Included Demo Routes</h2>
                <ul class="nav flex-column small gap-2">
                    <li class="nav-item"><a href="/project" class="nav-link p-0 text-body-secondary">Project Overview (DRY Inheritance)</a></li>
                    <li class="nav-item"><a href="/project/about" class="nav-link p-0 text-body-secondary">About Page (Layout Override)</a></li>
                    <li class="nav-item"><a href="/project/album-releases" class="nav-link p-0 text-body-secondary">Audio Player &amp; Tracklist Showcase</a></li>
                    <li class="nav-item"><a href="/project/radio" class="nav-link p-0 text-body-secondary">Radio Stream &amp; Markdown Parser</a></li>
                    <li class="nav-item"><a href="/project/dynamic-directory" class="nav-link p-0 text-body-secondary">Dynamic Directory Auto-Indexer</a></li>
                </ul>
            </div>

            <!-- Column 3: External Resources & Socials -->
            <div class="col-lg-4 col-md-12 text-lg-end">
                <h2 class="h6 fw-bold text-uppercase mb-3">Developer Resources</h2>
                <p class="small text-body-secondary mb-3">
                    Explore the route schema in <code>/data/routes/routes.json</code> or customize global settings in <code>/data/settings.json</code>.
                </p>
                <div class="d-flex justify-content-lg-end gap-2">
                    <a href="https://github.com/raggiesoft/stardust-engine-cms" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-secondary rounded-pill px-3" aria-label="View Stardust Engine CMS on GitHub">
                        <i class="fa-brands fa-github me-1" aria-hidden="true"></i>GitHub Repository
                    </a>
                </div>
            </div>
            
        </div>
    </div>
</div>