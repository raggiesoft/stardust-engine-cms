<?php
// pages/project/about/overview.php
// Mapped to '/project/about'
?>

<div class="container py-4">
    
    <nav aria-label="breadcrumb" class="mb-4">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item"><a href="/project" class="text-decoration-none">Project</a></li>
        <li class="breadcrumb-item active" aria-current="page">About</li>
      </ol>
    </nav>

    <article>
        <header class="mb-5">
            <h1 class="fw-bold text-body-emphasis">About This Project</h1>
            <p class="text-muted">Last Updated: <?php echo date("F Y"); ?></p>
        </header>

        <section class="mb-5">
            <h2 class="h4 fw-bold mb-3">The Architecture</h2>
            <p>
                The Stardust Engine CMS is built to respect the server infrastructure and the user's cognitive load. 
                By relying on native PHP arrays rather than complex SQL queries, time-to-first-byte (TTFB) is drastically reduced.
            </p>
            <div class="alert alert-info d-flex align-items-center" role="alert">
                <i class="fa-solid fa-circle-info fa-2x me-3" aria-hidden="true"></i>
                <div>
                    <strong>Pro Tip:</strong> You can inject custom SEO schemas directly into this page by modifying the <code>schemaType</code> key in your JSON routing file.
                </div>
            </div>
        </section>

        <section class="mb-5">
            <h2 class="h4 fw-bold mb-3">Accessibility Standards</h2>
            <p>
                Every template provided out of the box is audited against WCAG 2.1 AA standards. Ensure that any images added to this content block include descriptive <code>alt</code> attributes.
            </p>
        </section>
        
        <footer class="border-top pt-4">
            <a href="/project/about/history" class="btn btn-outline-primary">
                Read the History <i class="fa-solid fa-arrow-right ms-2" aria-hidden="true"></i>
            </a>
        </footer>
    </article>

</div>