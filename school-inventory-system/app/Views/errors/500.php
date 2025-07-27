<?php
$title = '500 - Internal Server Error';
ob_start();
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <div class="mb-4">
                <i class="bi bi-exclamation-octagon text-danger" style="font-size: 5rem;"></i>
            </div>
            <h1 class="display-4 fw-bold text-danger mb-3">500</h1>
            <h2 class="mb-3">Internal Server Error</h2>
            <p class="lead text-muted mb-4">
                Something went wrong on our end. We're working to fix the issue. Please try again later.
            </p>
            <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                <a href="/" class="btn btn-primary">
                    <i class="bi bi-house"></i> Go Home
                </a>
                <button onclick="location.reload()" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise"></i> Try Again
                </button>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>

