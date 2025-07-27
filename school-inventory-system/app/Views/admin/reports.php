<?php
$title = 'Admin Reports - School Inventory Management System';
ob_start();
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0">Reports & Analytics</h1>
            <p class="text-muted">System reports and data analytics</p>
        </div>
    </div>

    <!-- Report Cards -->
    <div class="row mb-4">
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-box-seam fs-1 text-primary mb-3"></i>
                    <h5>Inventory Report</h5>
                    <p class="text-muted">Complete inventory listing</p>
                    <button class="btn btn-primary" onclick="exportReport('inventory')">
                        <i class="bi bi-download me-2"></i>Export CSV
                    </button>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-people fs-1 text-success mb-3"></i>
                    <h5>User Activity</h5>
                    <p class="text-muted">User borrowing activity</p>
                    <button class="btn btn-success" onclick="exportReport('user-activity')">
                        <i class="bi bi-download me-2"></i>Export CSV
                    </button>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-graph-up fs-1 text-info mb-3"></i>
                    <h5>Usage Analytics</h5>
                    <p class="text-muted">Item usage statistics</p>
                    <button class="btn btn-info" onclick="exportReport('usage-analytics')">
                        <i class="bi bi-download me-2"></i>Export CSV
                    </button>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-currency-dollar fs-1 text-warning mb-3"></i>
                    <h5>Financial Report</h5>
                    <p class="text-muted">Inventory value analysis</p>
                    <button class="btn btn-warning" onclick="exportReport('financial')">
                        <i class="bi bi-download me-2"></i>Export CSV
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Dashboard -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-graph-up me-2"></i>Borrowing Trends
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="trendPeriod" id="week" value="week" checked>
                            <label class="btn btn-outline-primary" for="week">Week</label>
                            
                            <input type="radio" class="btn-check" name="trendPeriod" id="month" value="month">
                            <label class="btn btn-outline-primary" for="month">Month</label>
                            
                            <input type="radio" class="btn-check" name="trendPeriod" id="year" value="year">
                            <label class="btn btn-outline-primary" for="year">Year</label>
                        </div>
                    </div>
                    <canvas id="trendsChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-pie-chart me-2"></i>Category Usage
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="categoryChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Items and Users -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-star me-2"></i>Most Borrowed Items
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm" id="topItemsTable">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Category</th>
                                    <th>Times Borrowed</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="3" class="text-center">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-person-check me-2"></i>Top Users
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm" id="topUsersTable">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Total Requests</th>
                                    <th>Active Loans</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="3" class="text-center">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

$scripts = '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let trendsChart = null;
let categoryChart = null;

document.addEventListener("DOMContentLoaded", function() {
    loadAnalytics();
    loadTopItems();
    loadTopUsers();
    
    // Trend period change handlers
    document.querySelectorAll("input[name=\'trendPeriod\']").forEach(radio => {
        radio.addEventListener("change", function() {
            if (this.checked) {
                loadTrends(this.value);
            }
        });
    });
    
    // Load initial trends
    loadTrends("week");
});

async function loadAnalytics() {
    try {
        const response = await fetch("/api/reports/analytics", {
            credentials: "same-origin"
        });
        if (response.ok) {
            const data = await response.json();
            renderCategoryChart(data.category_usage || []);
        }
    } catch (error) {
        console.error("Error loading analytics:", error);
    }
}

async function loadTrends(period) {
    try {
        const response = await fetch("/api/reports/borrowing-trends?period=" + period, {
            credentials: "same-origin"
        });
        if (response.ok) {
            const data = await response.json();
            renderTrendsChart(data.trends || []);
        }
    } catch (error) {
        console.error("Error loading trends:", error);
    }
}

function renderTrendsChart(trends) {
    const ctx = document.getElementById("trendsChart");
    if (!ctx) return;
    
    if (trendsChart) {
        trendsChart.destroy();
    }
    
    const labels = trends.map(t => t.date);
    const data = trends.map(t => t.count);
    
    trendsChart = new Chart(ctx, {
        type: "line",
        data: {
            labels: labels,
            datasets: [{
                label: "Requests",
                data: data,
                borderColor: "rgb(75, 192, 192)",
                backgroundColor: "rgba(75, 192, 192, 0.2)",
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
}

function renderCategoryChart(categories) {
    const ctx = document.getElementById("categoryChart");
    if (!ctx) return;
    
    if (categoryChart) {
        categoryChart.destroy();
    }
    
    const labels = categories.map(c => c.category);
    const data = categories.map(c => c.count);
    const colors = [
        "#FF6384", "#36A2EB", "#FFCE56", "#4BC0C0",
        "#9966FF", "#FF9F40", "#FF6384", "#C9CBCF"
    ];
    
    categoryChart = new Chart(ctx, {
        type: "doughnut",
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: colors.slice(0, data.length)
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

async function loadTopItems() {
    try {
        const response = await fetch("/api/reports/top-items", {
            credentials: "same-origin"
        });
        if (response.ok) {
            const data = await response.json();
            renderTopItemsTable(data.items || []);
        }
    } catch (error) {
        console.error("Error loading top items:", error);
    }
}

function renderTopItemsTable(items) {
    const tbody = document.querySelector("#topItemsTable tbody");
    if (items.length === 0) {
        tbody.innerHTML = "<tr><td colspan=\"3\" class=\"text-center text-muted\">No data available</td></tr>";
        return;
    }
    
    tbody.innerHTML = items.map(item => 
        "<tr>" +
            "<td>" + escapeHtml(item.name) + "</td>" +
            "<td>" + escapeHtml(item.category) + "</td>" +
            "<td><span class=\"badge bg-primary\">" + item.borrow_count + "</span></td>" +
        "</tr>"
    ).join("");
}

async function loadTopUsers() {
    try {
        const response = await fetch("/api/reports/top-users", {
            credentials: "same-origin"
        });
        if (response.ok) {
            const data = await response.json();
            renderTopUsersTable(data.users || []);
        }
    } catch (error) {
        console.error("Error loading top users:", error);
    }
}

function renderTopUsersTable(users) {
    const tbody = document.querySelector("#topUsersTable tbody");
    if (users.length === 0) {
        tbody.innerHTML = "<tr><td colspan=\"3\" class=\"text-center text-muted\">No data available</td></tr>";
        return;
    }
    
    tbody.innerHTML = users.map(user => 
        "<tr>" +
            "<td>" + escapeHtml(user.name) + "</td>" +
            "<td><span class=\"badge bg-info\">" + user.total_requests + "</span></td>" +
            "<td><span class=\"badge bg-warning\">" + user.active_loans + "</span></td>" +
        "</tr>"
    ).join("");
}

async function exportReport(type) {
    try {
        const response = await fetch("/api/reports/export?type=" + type, {
            credentials: "same-origin"
        });
        if (response.ok) {
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement("a");
            a.href = url;
            a.download = type + "-report-" + new Date().toISOString().split("T")[0] + ".csv";
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            showAlert("Report exported successfully", "success");
        } else {
            showAlert("Failed to export report", "danger");
        }
    } catch (error) {
        console.error("Error exporting report:", error);
        showAlert("Failed to export report", "danger");
    }
}

function escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
}

function showAlert(message, type) {
    const alertDiv = document.createElement("div");
    alertDiv.className = "alert alert-" + type + " alert-dismissible fade show";
    alertDiv.innerHTML = message + 
        "<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button>";
    
    const container = document.querySelector(".container-fluid");
    container.insertBefore(alertDiv, container.firstChild);
    
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}
</script>';

include __DIR__ . '/../layout.php';
?>

