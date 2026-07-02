<?php
// pages/project/dynamic-directory.php
// Demonstrates reading $_GET values for dynamic view rendering within the SPA

// 1. Simulate a data source (In production, replace with json_decode(file_get_contents(...)))
$catalog = [
    'alpha' => ['name' => 'Project Alpha', 'status' => 'Completed', 'lead' => 'Engineering', 'date' => '2025-01-15'],
    'beta'  => ['name' => 'Project Beta',  'status' => 'Active', 'lead' => 'Design', 'date' => '2026-06-01'],
    'gamma' => ['name' => 'Project Gamma', 'status' => 'Planning', 'lead' => 'Operations', 'date' => 'TBD']
];

// 2. Determine Request State
// Always sanitize input from the URL string
$requested_id = isset($_GET['id']) ? htmlspecialchars(strip_tags($_GET['id'])) : '';
$is_detail_view = !empty($requested_id);

// 3. Validation & State Management
$item_data = null;
$not_found = false;

if ($is_detail_view) {
    if (array_key_exists($requested_id, $catalog)) {
        $item_data = $catalog[$requested_id];
    } else {
        $not_found = true;
    }
}
?>

<div class="container py-5">
    
    <div class="d-flex justify-content-between align-items-end border-bottom border-primary pb-3 mb-4">
        <div>
            <h1 class="h3 fw-bold text-uppercase mb-1">Dynamic Directory</h1>
            <h2 class="h6 text-body-secondary mb-0">Testing <code>$_GET</code> Parameter Routing</h2>
        </div>
        <div class="text-end font-monospace small text-body-secondary">
            <strong>System Date:</strong> <?php echo date('Y-m-d'); ?>
        </div>
    </div>

    <?php if ($not_found): ?>
        <div class="alert alert-danger border-danger border-2 p-4 shadow-sm">
            <h4 class="alert-heading fw-bold"><i class="fa-solid fa-triangle-exclamation me-2" aria-hidden="true"></i>Record Not Found</h4>
            <p class="mb-0">No data found for the requested identifier: <code><?php echo $requested_id; ?></code></p>
        </div>
        <a href="?id=" class="btn btn-outline-secondary mt-3">
            <i class="fa-solid fa-arrow-left me-2" aria-hidden="true"></i>Return to Directory
        </a>

    <?php elseif ($is_detail_view): ?>
        <div class="card border-primary shadow-sm mb-4">
            <div class="card-header bg-primary bg-opacity-10 border-primary d-flex justify-content-between align-items-center">
                <h3 class="h5 fw-bold text-primary mb-0">Record: <?php echo htmlspecialchars($item_data['name']); ?></h3>
                <span class="badge bg-primary"><?php echo htmlspecialchars($item_data['status']); ?></span>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item bg-transparent px-0">
                                <strong>System ID:</strong> <code class="ms-2"><?php echo $requested_id; ?></code>
                            </li>
                            <li class="list-group-item bg-transparent px-0">
                                <strong>Department Lead:</strong> <span class="ms-2"><?php echo htmlspecialchars($item_data['lead']); ?></span>
                            </li>
                            <li class="list-group-item bg-transparent px-0">
                                <strong>Launch Date:</strong> <span class="ms-2"><?php echo htmlspecialchars($item_data['date']); ?></span>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-body-tertiary border rounded h-100">
                            <h4 class="h6 fw-bold text-uppercase border-bottom pb-2">Technical Note</h4>
                            <p class="small text-body-secondary mb-0">
                                Because Elara intercepts clicks and fetches the HTML payload dynamically, appending <code>?id=<?php echo $requested_id; ?></code> to the URL triggers a seamless state change without a hard browser refresh.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-primary p-3">
                <a href="?id=" class="btn btn-sm btn-outline-primary font-monospace">
                    <i class="fa-solid fa-arrow-left me-2" aria-hidden="true"></i>Back to Directory
                </a>
            </div>
        </div>

    <?php else: ?>
        <div class="mb-4">
            <p class="text-body-secondary mb-4">Select a record below to pass its identifier through the <code>$_GET</code> superglobal.</p>
            
            <div class="row g-4">
                <?php foreach ($catalog as $slug => $data): ?>
                    <div class="col-md-4">
                        <a href="?id=<?php echo urlencode($slug); ?>" class="text-decoration-none">
                            <div class="card h-100 border-secondary shadow-sm hover-lift transition-all">
                                <div class="card-body text-center p-4">
                                    <i class="fa-solid fa-folder-open fa-2x text-warning mb-3" aria-hidden="true"></i>
                                    <h3 class="h5 fw-bold text-body-emphasis mb-1"><?php echo htmlspecialchars($data['name']); ?></h3>
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis">ID: <?php echo $slug; ?></span>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
                
                <div class="col-md-4">
                    <a href="?id=omega-protocol" class="text-decoration-none">
                        <div class="card h-100 border-danger border-opacity-50 shadow-sm hover-lift transition-all bg-danger bg-opacity-10">
                            <div class="card-body text-center p-4">
                                <i class="fa-solid fa-link-slash fa-2x text-danger mb-3" aria-hidden="true"></i>
                                <h3 class="h5 fw-bold text-danger mb-1">Test 404 State</h3>
                                <span class="badge bg-danger">ID: omega-protocol</span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>

<style>
    .hover-lift { transition: transform 0.2s ease, box-shadow 0.2s ease; }
    .hover-lift:hover { transform: translateY(-4px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
    .transition-all { transition: all 0.3s ease; }
</style>