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
 * 
 * CORE RESPONSIBILITIES:
 * 1. Educational Content: Explains the difference between traditional flat-file 
 *    CMS routing and Stardust's JSON-based mapping.
 * 2. Code Presentation: Utilizes Bootstrap UI components to display a code 
 *    snippet demonstrating the configuration block required to route a semantic 
 *    URL to an abstractly named file.
 * 
 * USAGE INSTRUCTIONS:
 * - This file can be safely deleted in production environments.
 * - Review this file to understand how to structure the `routes.json` file 
 *   located in the `data/routes/` directory.
 * ============================================================================
 */
// pages/project/literally-any-name.php
// This file demonstrates the complete decoupling of URLs and physical files.
?>

<div class="container py-4">
    
    <!-- Semantic Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-4">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item"><a href="/project" class="text-decoration-none">Project</a></li>
        <li class="breadcrumb-item active" aria-current="page">Custom Routing</li>
      </ol>
    </nav>

    <!-- Header Section -->
    <div class="row mb-5">
        <div class="col-12">
            <h1 class="display-5 fw-bold text-body-emphasis mb-3">Decoupled Architecture</h1>
            <p class="lead text-body-secondary">
                You are currently viewing a physical file named <code>literally-any-name.php</code>, but your browser URL is clean and semantic.
            </p>
        </div>
    </div>

    <!-- Educational Grid Section -->
    <div class="row g-4">
        
        <!-- Explanation: The URL limitation in traditional CMS -->
        <div class="col-md-6">
            <div class="card h-100 border-0 shadow-sm bg-body-tertiary">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold mb-3">
                        <i class="fa-solid fa-link text-primary me-2" aria-hidden="true"></i>The URL
                    </h2>
                    <p>
                        In a traditional flat-file CMS, visiting <code>/project/secret-page</code> means the server must have a folder named <code>project</code> containing a file named <code>secret-page.php</code>. 
                    </p>
                    <p class="mb-0">
                        The Stardust Engine router completely bypasses this limitation.
                    </p>
                </div>
            </div>
        </div>

        <!-- Explanation: The JSON Solution in Stardust -->
        <div class="col-md-6">
            <div class="card h-100 border-0 shadow-sm bg-body-tertiary">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold mb-3">
                        <i class="fa-solid fa-file-code text-success me-2" aria-hidden="true"></i>The JSON Map
                    </h2>
                    <p>
                        Because the engine relies on <code>routes.json</code>, you can name your internal PHP fragments whatever makes the most sense for your development workflow. 
                    </p>
                    <p class="mb-0">
                        You simply tell the configuration exactly which view to load for which route.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Code Snippet Display Section -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card shadow-sm border-secondary border-opacity-25">
                <div class="card-header bg-secondary bg-opacity-10 fw-bold font-monospace">
                    <i class="fa-brands fa-js me-2" aria-hidden="true"></i>data/routes/routes.example.json
                </div>
                <div class="card-body p-0">
<!-- Preformatted code block showcasing JSON configuration -->
<pre class="m-0 p-4 font-monospace bg-dark text-light overflow-auto" style="border-bottom-left-radius: var(--bs-card-inner-border-radius); border-bottom-right-radius: var(--bs-card-inner-border-radius);">
<code>"/project/secret-page": {
  "view": "pages/project/literally-any-name",
  "title": "Decoupled Architecture | Stardust Engine",
  "ogDescription": "Demonstrating how URLs detach from physical file names."
}</code>
</pre>
                </div>
            </div>
        </div>
    </div>

</div>