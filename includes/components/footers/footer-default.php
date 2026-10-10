<?php
/**
 * Architectural Block Comment:
 * File: footer-default.php
 * Purpose:
 *     Serves as the generic, system-wide fallback footer for the Stardust Engine CMS.
 *     It provides standard baseline links (Home, GitHub, documentation) when a specific 
 *     theme or layout does not supply a bespoke footer.
 * 
 * Design Decisions & Future Maintenance:
 *     - Layout: Utilizes Bootstrap 5 grid classes (`row`, `col-lg-4`, `col-md-6`) to achieve 
 *       a responsive layout that stacks cleanly on mobile and expands into three columns on large screens.
 *     - Theming: Uses Bootstrap contextual CSS variables (`bg-body-tertiary`, `link-body-emphasis`) 
 *       to ensure it inherits the active light/dark color mode dynamically.
 *     - Accessibility: Icons are hidden from screen readers using `aria-hidden="true"`, and links 
 *       that open externally feature `target="_blank" rel="noopener noreferrer"` alongside explicit `aria-label`s.
 */
?>
<!-- Footer wrapper with sticky auto-margin and top border -->
<footer class="mt-auto bg-body-tertiary border-top py-5">
    <div class="container">
        <div class="row gy-4">
            
            <!-- Column 1: Brand & Description -->
            <div class="col-lg-4 col-md-6">
                <!-- Branding link returning to the homepage -->
                <a href="/" class="d-flex align-items-center mb-3 link-body-emphasis text-decoration-none">
                    <i class="fa-solid fa-rocket me-2 fs-4 text-primary" aria-hidden="true"></i>
                    <span class="fs-5 fw-bold text-uppercase brand-font">Stardust Engine</span>
                </a>
                <!-- Short elevator pitch for the CMS -->
                <p class="text-body-secondary small">
                    A blazing-fast, zero-database flat-file CMS powered by Vanilla JS routing and PHP 8.5.
                </p>
                <!-- Slogan/sub-branding -->
                <div class="small text-muted mt-3">
                    <i class="fa-solid fa-microchip me-1" aria-hidden="true"></i> Built for independence.
                </div>
            </div>

            <!-- Column 2: Example Routing Links -->
            <div class="col-lg-4 col-md-6">
                <h6 class="fw-bold mb-3">Example Routing</h6>
                <ul class="nav flex-column small">
                    <!-- Internal routes demonstrating the flat-file structure -->
                    <li class="nav-item mb-2"><a href="/project" class="nav-link p-0 text-body-secondary">Project Overview</a></li>
                    <li class="nav-item mb-2"><a href="/project/about" class="nav-link p-0 text-body-secondary">About the Architecture</a></li>
                    <li class="nav-item mb-2"><a href="/project/portfolio" class="nav-link p-0 text-body-secondary">Portfolio Examples</a></li>
                </ul>
            </div>

            <!-- Column 3: External Resources & Socials -->
            <div class="col-lg-4 col-md-6 text-md-end">
                <h6 class="fw-bold mb-3">Resources & Connect</h6>
                <div class="d-flex justify-content-md-end gap-3">
                    <!-- GitHub Repository Link -->
                    <a href="https://github.com/raggiesoft/stardust-engine-cms" target="_blank" rel="noopener noreferrer" class="link-secondary fs-5" aria-label="GitHub Repository">
                        <i class="fa-brands fa-github"></i>
                    </a>
                </div>
            </div>
            
        </div>
    </div>
</footer>