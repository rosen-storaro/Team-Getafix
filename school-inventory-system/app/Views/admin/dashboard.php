<?php
$title = 'Admin Dashboard - School Inventory Management System';
ob_start();
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0">Admin Dashboard</h1>
            <p class="text-muted">Administrative tools and system management</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-people me-2"></i>User Management
                    </h5>
                    <p class="card-text">Manage user accounts, roles, and permissions</p>
                    <a href="/admin/users" class="btn btn-primary">Manage Users</a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-box-seam me-2"></i>Inventory Management
                    </h5>
                    <p class="card-text">Manage inventory items, categories, and locations</p>
                    <a href="/admin/inventory" class="btn btn-primary">Manage Inventory</a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-graph-up me-2"></i>Reports & Analytics
                    </h5>
                    <p class="card-text">View system reports and analytics</p>
                    <a href="/admin/analytics" class="btn btn-primary">View Reports</a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-qr-code me-2"></i>QR Code Management
                    </h5>
                    <p class="card-text">Generate and manage QR codes for items</p>
                    <a href="/admin/qrcode" class="btn btn-primary">Manage QR Codes</a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-gear me-2"></i>System Settings
                    </h5>
                    <p class="card-text">Configure system settings and preferences</p>
                    <a href="/admin/settings" class="btn btn-primary">Settings</a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-shield-check me-2"></i>Security Logs
                    </h5>
                    <p class="card-text">View security logs and system activity</p>
                    <a href="/admin/logs" class="btn btn-primary">View Logs</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>

