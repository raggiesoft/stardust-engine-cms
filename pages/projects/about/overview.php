<?php
/**
 * ============================================================================
 * STARDUST ENGINE CMS - GENERIC OVERVIEW TEMPLATE
 * ============================================================================
 * 
 * ARCHITECTURE OVERVIEW:
 * This view serves as a standard, text-heavy content page template. It is 
 * designed to demonstrate best practices for structuring semantic HTML5 documents
 * within the Stardust layout wrapper, while overriding the parent directory's
 * sidebar configuration (`"sidebar": false` in `routes.json`).
 * ============================================================================
 */
?>

<div class="container py-4">
    
    <!-- Semantic Breadcrumbs for Navigation -->
    <nav aria-label="breadcrumb" class="mb-4">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item"><a href="/project" class="text-decoration-none">Project</a></li>
        <li class="breadcrumb-item active" aria-current="page">About</li>
      </ol>
    </nav>

    <!-- Main Content Article -->
    <article>
        <header class="mb-5">
            <h1 class="fw-bold text-body-emphasis">About the Stardust Engine Architecture</h1>
            <p class="text-body-secondary">Last Updated: <?php echo date("F Y"); ?></p>
        </header>

        <!-- Section 1: Architecture -->
        <section class="mb-5">
            <h2 class="h4 fw-bold mb-3">Zero-Database Flat-File Design</h2>
            <p>
                The Stardust Engine CMS is built to respect both server infrastructure and the user's cognitive load. 
                By relying on native PHP 8.5 arrays and flat-file JSON configurations rather than relational SQL queries, time-to-first-byte (TTFB) is reduced to near-static speeds while retaining full server-side rendering (SSR) for SEO and OpenGraph crawlers.
            </p>
            <div class="alert alert-info d-flex align-items-center" role="alert">
                <i class="fa-solid fa-circle-info fa-2x me-3" aria-hidden="true"></i>
                <div>
                    <strong>Pro Tip:</strong> Notice how this page renders across the full container width! In <code>data/routes/routes.json</code>, the <code>/project/about</code> block sets <code>"sidebar": false</code>, overriding the parent <code>/project</code> common sidebar.
                </div>
            </div>
        </section>

        <!-- Section 2: Accessibility -->
        <section class="mb-5">
            <h2 class="h4 fw-bold mb-3">WCAG 2.1 AA Accessibility Standards</h2>
            <p>
                Every template provided out of the box is audited against WCAG 2.1 AA and Section 508 standards. When navigating between pages, the Elara SPA router automatically resets scroll position and transfers focus to <code>#main-content</code> so screen readers announce page transitions seamlessly.
            </p>
        </section>
        
        <!-- Article Footer with Call to Action -->
        <footer class="border-top pt-4 d-flex gap-3 flex-wrap">
            <a href="/project/secret-page" class="btn btn-primary rounded-pill px-4">
                Explore Decoupled Routing <i class="fa-solid fa-arrow-right ms-2" aria-hidden="true"></i>
            </a>
            <a href="/project" class="btn btn-outline-secondary rounded-pill px-4">
                Back to Project Overview
            </a>
        </footer>
    </article>

</div>