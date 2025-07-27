<?php
$title = 'Dashboard - School Inventory Management System';
ob_start();
?>

<div class="container-fluid">
    <!-- Dashboard Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Dashboard</h1>
                    <p class="text-muted mb-0">Welcome back, <?= htmlspecialchars($_SESSION['first_name'] ?? 'User') ?>!</p>
                </div>
                <div class="text-end">
                    <small class="text-muted">Last updated: <span id="lastUpdated"><?= date('M j, Y g:i A') ?></span></small>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="row">
                <!-- Total Items -->
                <div class="col-md-4 mb-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0" id="totalItems">0</h4>
                                    <p class="mb-0">Total Items</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-box-seam fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Available Items -->
                <div class="col-md-4 mb-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0" id="availableItems">0</h4>
                                    <p class="mb-0">Available</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-check-circle fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Checked Out Items -->
                <div class="col-md-4 mb-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0" id="checkedOutItems">0</h4>
                                    <p class="mb-0">Checked Out</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-arrow-right-circle fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (in_array($_SESSION['role_name'] ?? '', ['Admin', 'Super-admin'])): ?>
                <!-- Low Stock Items -->
                <div class="col-md-4 mb-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0" id="lowStockItems">0</h4>
                                    <p class="mb-0">Low Stock</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-exclamation-triangle fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Requests -->
                <div class="col-md-4 mb-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0" id="pendingRequests">0</h4>
                                    <p class="mb-0">Pending Requests</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-clock fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Overdue Items -->
                <div class="col-md-4 mb-3">
                    <div class="card bg-dark text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0" id="overdueItems">0</h4>
                                    <p class="mb-0">Overdue</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-calendar-x fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-lightning me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="/inventory/browse" class="btn btn-primary">
                            <i class="bi bi-search me-2"></i>Browse Items
                        </a>
                        <a href="/requests/create" class="btn btn-success">
                            <i class="bi bi-plus-circle me-2"></i>New Request
                        </a>
                        <?php if (in_array($_SESSION['role_name'] ?? '', ['Admin', 'Super-admin'])): ?>
                        <a href="/inventory/add" class="btn btn-info">
                            <i class="bi bi-plus-square me-2"></i>Add Item
                        </a>
                        <a href="/admin" class="btn btn-secondary">
                            <i class="bi bi-gear me-2"></i>Admin Panel
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Message -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Loading dashboard statistics...</p>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-clock-history me-2"></i>Recent Activity
                    </h5>
                    <a href="/requests" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body" id="recentActivity">
                    <div class="text-center text-muted">
                        <i class="bi bi-inbox"></i><br>
                        No recent activity
                    </div>
                </div>
            </div>
        </div>

        <?php if (in_array($_SESSION['role_name'] ?? '', ['Admin', 'Super-admin'])): ?>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-graph-up me-2"></i>Borrowing Trends
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="borrowingChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (($_SESSION['role_name'] ?? '') === 'User'): ?>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-person-check me-2"></i>My Requests
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="/requests" class="btn btn-outline-primary">
                            <i class="bi bi-list me-2"></i>View My Requests
                        </a>
                        <a href="/requests/create" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>New Request
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();

$scripts = '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let borrowingChart = null;

document.addEventListener("DOMContentLoaded", function() {
    loadDashboardData();
    
    // Auto-refresh every 5 minutes
    setInterval(loadDashboardData, 5 * 60 * 1000);
});

async function loadDashboardData() {
    try {
        // Load dashboard stats
        const statsResponse = await fetch("/api/reports/dashboard", {
            credentials: "same-origin"
        });
        if (statsResponse.ok) {
            const statsData = await statsResponse.json();
            renderStatsCards(statsData.stats);
        } else {
            console.error("Failed to load dashboard stats:", statsResponse.status);
        }
        
        // Load recent activity
        loadRecentActivity();
        
    } catch (error) {
        console.error("Error loading dashboard data:", error);
        showAlert("Failed to load dashboard data", "danger");
    }
}

function renderStatsCards(stats) {
    // Update stats cards
    document.getElementById("totalItems").textContent = stats.total_items || 0;
    document.getElementById("availableItems").textContent = stats.available_items || 0;
    document.getElementById("checkedOutItems").textContent = stats.checked_out_items || 0;
    document.getElementById("lowStockItems").textContent = stats.low_stock_items || 0;
    document.getElementById("pendingRequests").textContent = stats.pending_requests || 0;
    document.getElementById("overdueItems").textContent = stats.overdue_items || 0;
    
    // Update last updated time
    document.getElementById("lastUpdated").textContent = new Date().toLocaleString();
    
    // Hide loading message
    const loadingElement = document.querySelector(".text-center");
    if (loadingElement) {
        loadingElement.style.display = "none";
    }
}

async function loadRecentActivity() {
    try {
        const response = await fetch("/api/requests?limit=5", {
            credentials: "same-origin"
        });
        if (response.ok) {
            const data = await response.json();
            renderRecentActivity(data.requests || []);
        }
    } catch (error) {
        console.error("Error loading recent activity:", error);
    }
}

function renderRecentActivity(activities) {
    const container = document.getElementById("recentActivity");
    if (!container) return;
    
    if (activities.length === 0) {
        container.innerHTML = "<div class=\'text-center text-muted\'><i class=\'bi bi-inbox\'></i><br>No recent activity</div>";
        return;
    }
    
    const html = activities.map(activity => 
        "<div class=\'d-flex justify-content-between align-items-center border-bottom py-2\'>" +
            "<div>" +
                "<strong>" + escapeHtml(activity.item_name || "Unknown Item") + "</strong><br>" +
                "<small class=\'text-muted\'>" + escapeHtml(activity.user_name || "Unknown User") + " - " + activity.status + "</small>" +
            "</div>" +
            "<small class=\'text-muted\'>" + formatDate(activity.created_at) + "</small>" +
        "</div>"
    ).join("");
    
    container.innerHTML = html;
}

function showAlert(message, type) {
    const alertDiv = document.createElement("div");
    alertDiv.className = "alert alert-" + type + " alert-dismissible fade show";
    alertDiv.innerHTML = message + 
        "<button type=\'button\' class=\'btn-close\' data-bs-dismiss=\'alert\'></button>";
    
    const container = document.querySelector(".container-fluid");
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

function escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString() + " " + date.toLocaleTimeString([], {hour: "2-digit", minute:"2-digit"});
}
</script>';

include __DIR__ . '/layout.php';
?>

