<?php
// pages/project/overview.php
// Mapped to '/project' via Elara auto-discovery
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
            <h1 class="fw-bold mb-4 border-bottom pb-2">Project Overview</h1>
            <p class="lead">
                This section demonstrates the inherited routing logic. By utilizing the <code>common</code> block in your JSON configuration, all pages within this directory automatically share the same sidebars and header logic.
            </p>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="list-group shadow-sm">
                <a href="/project/about" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-3">
                    <div>
                        <h5 class="mb-1 fw-bold">About the Project</h5>
                        <small class="text-body-secondary">Learn the history and architecture.</small>
                    </div>
                    <i class="fa-solid fa-chevron-right text-muted" aria-hidden="true"></i>
                </a>
                <a href="/project/portfolio" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-3">
                    <div>
                        <h5 class="mb-1 fw-bold">View Portfolio</h5>
                        <small class="text-body-secondary">Examine the alpha and beta releases.</small>
                    </div>
                    <i class="fa-solid fa-chevron-right text-muted" aria-hidden="true"></i>
                </a>
            </div>
        </div>
    </div>
</div>