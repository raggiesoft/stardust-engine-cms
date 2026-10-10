<?php
/**
 * Architectural Block Comment:
 * File: sidebar-default.php
 * Purpose:
 *     Provides the default fallback sidebar navigation component for the Stardust Engine CMS.
 *     It typically renders alongside the main content area in multi-column layouts, offering 
 *     local contextual navigation and external resource links.
 * 
 * Design Decisions & Future Maintenance:
 *     - Visual Hierarchy: Separates internal "Local Navigation" from "External Links" into distinct card blocks 
 *       (`div.card`) to visually compartmentalize the routing hierarchy.
 *     - Bootstrap Components: Relies on Bootstrap 5 `nav`, `nav-link`, and `card` utilities. Custom helper classes 
 *       like `hover-bg-subtle` are assumed to be defined in the global stylesheet for hover interactions.
 *     - Accessibility: Employs `aria-hidden="true"` on decorative FontAwesome icons to prevent screen readers 
 *       from reading out redundant icon names. External links feature the `rel="noopener noreferrer"` attribute.
 */
?>

<!-- Local Navigation Block -->
<div class="card border-0 bg-transparent mb-4">
    <div class="card-body p-0 p-xl-2">
        <!-- Block Header -->
        <h6 class="text-uppercase fw-bold text-body-secondary mb-3 small border-bottom border-secondary-subtle pb-2">
            <i class="fa-solid fa-map-location-dot me-2" aria-hidden="true"></i>Local Navigation
        </h6>
        <ul class="nav flex-column small gap-1">
            <!-- Root Route -->
            <li class="nav-item">
                <a class="nav-link link-body-emphasis rounded hover-bg-subtle" href="/">
                    <i class="fa-solid fa-house fa-fw me-2 text-primary" aria-hidden="true"></i>Return Home
                </a>
            </li>
            <!-- Project Overview Route -->
            <li class="nav-item">
                <a class="nav-link link-body-emphasis rounded hover-bg-subtle" href="/project">
                    <i class="fa-solid fa-folder fa-fw me-2 text-warning" aria-hidden="true"></i>Project Root
                </a>
            </li>
            <!-- About Sub-route -->
            <li class="nav-item">
                <a class="nav-link link-body-emphasis rounded hover-bg-subtle" href="/project/about">
                    <i class="fa-solid fa-circle-info fa-fw me-2 text-info" aria-hidden="true"></i>About Page
                </a>
            </li>
        </ul>
    </div>
</div>

<!-- External Links Block -->
<div class="card border-0 bg-transparent">
    <div class="card-body p-0 p-xl-2">
        <!-- Block Header -->
        <h6 class="text-uppercase fw-bold text-body-secondary mb-3 small border-bottom border-secondary-subtle pb-2">
            <i class="fa-brands fa-github me-2" aria-hidden="true"></i>External Links
        </h6>
        <ul class="nav flex-column small gap-1">
            <!-- External Repo Link -->
            <li class="nav-item">
                <a class="nav-link link-secondary rounded hover-bg-subtle" href="https://github.com/raggiesoft/stardust-engine-cms" target="_blank" rel="noopener noreferrer">
                    <!-- Flex container used to push the 'external link' arrow to the far right -->
                    <span class="d-flex align-items-center justify-content-between">
                        <span><i class="fa-solid fa-book-open me-2" aria-hidden="true"></i>Documentation</span>
                        <i class="fa-solid fa-arrow-up-right-from-square small opacity-50" aria-hidden="true"></i>
                    </span>
                </a>
            </li>
        </ul>
    </div>
</div>