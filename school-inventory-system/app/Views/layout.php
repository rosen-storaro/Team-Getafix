<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'School Inventory Management System' ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootswatch Cosmo Theme -->
    <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.0/dist/cosmo/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Chart.js -->
    <link href="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.min.css" rel="stylesheet">
    
    <style>
        .navbar-brand {
            font-weight: bold;
        }
        .low-stock-badge {
            background-color: #dc3545 !important;
            color: white;
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        .qr-code {
            max-width: 150px;
            height: auto;
        }
        .item-photo {
            max-width: 200px;
            height: auto;
            border-radius: 0.375rem;
        }
        .status-available { color: #198754; }
        .status-checked-out { color: #fd7e14; }
        .status-reserved { color: #0dcaf0; }
        .status-under-repair { color: #ffc107; }
        .status-lost-stolen { color: #dc3545; }
        .status-retired { color: #6c757d; }
        
        .sensitive-high-value { border-left: 4px solid #dc3545; }
        .sensitive-sensitive { border-left: 4px solid #ffc107; }
        
        .footer {
            margin-top: auto;
            padding: 1rem 0;
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }
        
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        main {
            flex: 1;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php if (isLoggedIn()): ?>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="bi bi-box-seam"></i>
                School Inventory
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/inventory">
                            <i class="bi bi-grid-3x3-gap"></i> Inventory
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/requests">
                            <i class="bi bi-clipboard-check"></i> My Requests
                        </a>
                    </li>
                    
                    <?php $user = getCurrentUser(); ?>
                    <?php if ($user && in_array($user['role_name'], ['Admin', 'Super-admin'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-gear"></i> Admin
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/admin">Dashboard</a></li>
                            <li><a class="dropdown-item" href="/admin/inventory">Manage Inventory</a></li>
                            <li><a class="dropdown-item" href="/admin/requests">Manage Requests</a></li>
                            <li><a class="dropdown-item" href="/admin/reports">Reports</a></li>
                            <?php if ($user['role_name'] === 'Super-admin'): ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/admin/users">Manage Users</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i>
                            <?= htmlspecialchars($user['first_name'] ?? $user['username']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#" onclick="showProfileModal()">
                                <i class="bi bi-person"></i> Profile
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="showChangePasswordModal()">
                                <i class="bi bi-key"></i> Change Password
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/logout">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <?php endif; ?>
    
    <!-- Main Content -->
    <main class="container-fluid py-4">
        <?= $content ?? '' ?>
    </main>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="container text-center">
            <span class="text-muted">
                &copy; <?= date('Y') ?> School Inventory Management System
            </span>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.min.js"></script>
    <!-- SheetJS -->
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    
    <!-- Common JavaScript -->
    <script>
        // CSRF Token
        const csrfToken = '<?= generateCsrfToken() ?>';
        
        // Helper function for API calls
        async function apiCall(url, options = {}) {
            const defaultOptions = {
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            };
            
            if (options.body && typeof options.body === 'object') {
                options.body = JSON.stringify(options.body);
            }
            
            const response = await fetch(url, { ...defaultOptions, ...options });
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.error || 'Request failed');
            }
            
            return data;
        }
        
        // Show alert messages
        function showAlert(message, type = 'info') {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            const container = document.querySelector('main .container, main .container-fluid');
            container.insertBefore(alertDiv, container.firstChild);
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }
        
        // Profile modal functions
        function showProfileModal() {
            // Implementation will be added when creating profile modal
            console.log('Profile modal not implemented yet');
        }
        
        function showChangePasswordModal() {
            // Implementation will be added when creating change password modal
            console.log('Change password modal not implemented yet');
        }
    </script>
    
    <?= $scripts ?? '' ?>
</body>
</html>

