<?php
$title = '404 - Page Not Found';
ob_start();
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <div class="mb-4">
                <i class="bi bi-exclamation-triangle text-warning" style="font-size: 5rem;"></i>
            </div>
            <h1 class="display-4 fw-bold text-primary mb-3">404</h1>
            <h2 class="mb-3">Page Not Found</h2>
            <p class="lead text-muted mb-4">
                The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.
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

