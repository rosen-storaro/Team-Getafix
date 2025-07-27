<?php
$title = 'Welcome - School Inventory Management System';
ob_start();
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Hero Section -->
            <div class="text-center mb-5">
                <div class="mb-4">
                    <i class="bi bi-box-seam text-primary" style="font-size: 4rem;"></i>
                </div>
                <h1 class="display-4 fw-bold text-primary mb-3">
                    School Inventory Management System
                </h1>
                <p class="lead text-muted mb-4">
                    Efficiently manage and track classroom equipment, supplies, and materials with our comprehensive digital solution.
                </p>
                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                    <a href="/login" class="btn btn-primary btn-lg px-4 me-md-2">
                        <i class="bi bi-box-arrow-in-right"></i> Login
                    </a>
                    <a href="/register" class="btn btn-outline-primary btn-lg px-4">
                        <i class="bi bi-person-plus"></i> Register
                    </a>
                </div>
            </div>
            
            <!-- Features Section -->
            <div class="row g-4 mb-5">
                <div class="col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <i class="bi bi-search text-primary mb-3" style="font-size: 2rem;"></i>
                            <h5 class="card-title">Easy Equipment Search</h5>
                            <p class="card-text text-muted">
                                Quickly find and filter equipment by category, location, or availability status.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <i class="bi bi-calendar-check text-primary mb-3" style="font-size: 2rem;"></i>
                            <h5 class="card-title">Request & Track</h5>
                            <p class="card-text text-muted">
                                Submit borrowing requests and track equipment usage with detailed history logs.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <i class="bi bi-qr-code text-primary mb-3" style="font-size: 2rem;"></i>
                            <h5 class="card-title">QR Code Integration</h5>
                            <p class="card-text text-muted">
                                Generate and scan QR codes for quick equipment identification and check-in/out.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <i class="bi bi-graph-up text-primary mb-3" style="font-size: 2rem;"></i>
                            <h5 class="card-title">Analytics & Reports</h5>
                            <p class="card-text text-muted">
                                View usage analytics and generate comprehensive reports for better inventory management.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <i class="bi bi-exclamation-triangle text-primary mb-3" style="font-size: 2rem;"></i>
                            <h5 class="card-title">Low Stock Alerts</h5>
                            <p class="card-text text-muted">
                                Automatic notifications when equipment quantities fall below threshold levels.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <i class="bi bi-file-earmark-text text-primary mb-3" style="font-size: 2rem;"></i>
                            <h5 class="card-title">Document Preview</h5>
                            <p class="card-text text-muted">
                                View PDF and document attachments directly in the browser with Google Docs integration.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- User Roles Section -->
            <div class="row g-4 mb-5">
                <div class="col-12">
                    <h3 class="text-center mb-4">User Roles & Permissions</h3>
                </div>
                
                <div class="col-md-4">
                    <div class="card border-primary">
                        <div class="card-header bg-primary text-white text-center">
                            <i class="bi bi-person"></i> Users
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled">
                                <li><i class="bi bi-check-circle text-success"></i> Browse equipment catalog</li>
                                <li><i class="bi bi-check-circle text-success"></i> Submit borrow requests</li>
                                <li><i class="bi bi-check-circle text-success"></i> View personal history</li>
                                <li><i class="bi bi-x-circle text-danger"></i> Cannot manage equipment</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card border-warning">
                        <div class="card-header bg-warning text-dark text-center">
                            <i class="bi bi-person-gear"></i> Administrators
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled">
                                <li><i class="bi bi-check-circle text-success"></i> All user permissions</li>
                                <li><i class="bi bi-check-circle text-success"></i> Approve/reject requests</li>
                                <li><i class="bi bi-check-circle text-success"></i> Update item status</li>
                                <li><i class="bi bi-check-circle text-success"></i> Export reports</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card border-danger">
                        <div class="card-header bg-danger text-white text-center">
                            <i class="bi bi-person-fill-gear"></i> Super Administrators
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled">
                                <li><i class="bi bi-check-circle text-success"></i> All admin permissions</li>
                                <li><i class="bi bi-check-circle text-success"></i> Manage users & roles</li>
                                <li><i class="bi bi-check-circle text-success"></i> Create/edit equipment</li>
                                <li><i class="bi bi-check-circle text-success"></i> System settings</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Call to Action -->
            <div class="text-center">
                <div class="card bg-light border-0">
                    <div class="card-body py-4">
                        <h4 class="card-title">Ready to Get Started?</h4>
                        <p class="card-text text-muted mb-4">
                            Join our inventory management system and streamline your equipment tracking today.
                        </p>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                            <a href="/register" class="btn btn-primary btn-lg">
                                <i class="bi bi-person-plus"></i> Create Account
                            </a>
                            <a href="/login" class="btn btn-outline-primary btn-lg">
                                <i class="bi bi-box-arrow-in-right"></i> Sign In
                            </a>
                        </div>
                        <small class="text-muted d-block mt-3">
                            New accounts require administrator approval before activation.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>

