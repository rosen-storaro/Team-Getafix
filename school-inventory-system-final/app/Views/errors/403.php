<?php
$title = '403 - Access Forbidden';
ob_start();
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <div class="mb-4">
                <i class="bi bi-shield-exclamation text-danger" style="font-size: 5rem;"></i>
            </div>
            <h1 class="display-4 fw-bold text-danger mb-3">403</h1>
            <h2 class="mb-3">Access Forbidden</h2>
            <p class="lead text-muted mb-4">
                You don't have permission to access this resource. Please contact an administrator if you believe this is an error.
            </p>
            <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                <a href="/" class="btn btn-primary">
                    <i class="bi bi-house"></i> Go Home
                </a>
                <button onclick="history.back()" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Go Back
                </button>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>

