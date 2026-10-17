<?php
/**
 * ============================================================================
 * STARDUST ENGINE CMS - DECOUPLED ROUTING DEMO
 * ============================================================================
 * 
 * ARCHITECTURE OVERVIEW:
 * This view serves strictly as a demonstrative tool to explain the CMS's 
 * decoupled routing architecture. It proves that the physical file name 
 * (`literally-any-name.php`) has no forced correlation to the public-facing URL.
 * ============================================================================
 */
?>

<div class="container py-4">
    
    <!-- Semantic Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-4">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item"><a href="/project" class="text-decoration-none">Project</a></li>
        <li class="breadcrumb-item active" aria-current="page">Decoupled Route Mapping</li>
      </ol>
    </nav>

    <!-- Header Section -->
    <div class="row mb-5">
        <div class="col-12">
            <h1 class="display-5 fw-bold text-body-emphasis mb-3">Decoupled Route Architecture</h1>
            <p class="lead text-body-secondary">
                You are currently viewing a physical file named <code>pages/projects/literally-any-name.php</code>, while your browser URL remains clean and semantic at <code>/project/secret-page</code>.
            </p>
        </div>
    </div>

    <!-- Educational Grid Section -->
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card h-100 border-secondary-subtle shadow-sm bg-body-tertiary">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold mb-3">
                        <i class="fa-solid fa-link text-primary me-2" aria-hidden="true"></i>The Public URL
                    </h2>
                    <p>
                        In a traditional flat-file CMS, visiting <code>/project/secret-page</code> requires a folder named <code>project</code> containing a file named <code>secret-page.php</code>. 
                    </p>
                    <p class="mb-0">
                        The Stardust Engine front-controller completely decouples public URLs from physical filesystem paths.
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100 border-secondary-subtle shadow-sm bg-body-tertiary">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold mb-3">
                        <i class="fa-solid fa-file-code text-success me-2" aria-hidden="true"></i>The JSON Route Map
                    </h2>
                    <p>
                        Because the engine resolves routes via <code>data/routes/*.json</code>, you can organize your internal PHP view fragments however best suits your engineering workflow.
                    </p>
                    <p class="mb-0">
                        Simply map the public route key to any relative view path inside your JSON route file.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Code Snippet Display Section -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card shadow-sm border-secondary-subtle">
                <div class="card-header bg-body-tertiary fw-bold font-monospace">
                    <i class="fa-solid fa-code me-2 text-primary" aria-hidden="true"></i>data/routes/routes.json
                </div>
                <div class="card-body p-0">
<pre class="m-0 p-4 font-monospace bg-dark text-light overflow-auto" style="border-bottom-left-radius: var(--bs-card-inner-border-radius); border-bottom-right-radius: var(--bs-card-inner-border-radius);">
<code>"/project/secret-page": {
  "view": "pages/projects/literally-any-name",
  "title": "Decoupled Architecture | Stardust Engine CMS",
  "ogDescription": "Demonstrating how public URLs detach cleanly from physical file names."
}</code>
</pre>
                </div>
            </div>
        </div>
    </div>

</div>