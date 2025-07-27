<?php
$title = 'Advanced Analytics - School Inventory Management System';
ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Advanced Analytics</h1>
                    <p class="text-muted mb-0">Comprehensive insights and data visualization</p>
                </div>
                <div class="d-flex gap-2">
                    <select class="form-select" id="timeRangeSelect" style="width: auto;">
                        <option value="7">Last 7 days</option>
                        <option value="30" selected>Last 30 days</option>
                        <option value="90">Last 90 days</option>
                        <option value="365">Last year</option>
                    </select>
                    <button type="button" class="btn btn-outline-primary" onclick="refreshAnalytics()">
                        <i class="bi bi-arrow-clockwise me-2"></i>Refresh
                    </button>
                    <button type="button" class="btn btn-success" onclick="exportAnalytics()">
                        <i class="bi bi-download me-2"></i>Export Report
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Key Performance Indicators -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-center mb-2">
                        <i class="bi bi-box-seam text-primary me-2" style="font-size: 1.5rem;"></i>
                        <h5 class="card-title mb-0">Total Items</h5>
                    </div>
                    <h2 class="text-primary mb-1" id="totalItems">-</h2>
                    <small class="text-muted">
                        <span id="itemsChange" class="badge bg-light text-dark">-</span>
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-center mb-2">
                        <i class="bi bi-check-circle text-success me-2" style="font-size: 1.5rem;"></i>
                        <h5 class="card-title mb-0">Available Items</h5>
                    </div>
                    <h2 class="text-success mb-1" id="availableItems">-</h2>
                    <small class="text-muted">
                        <span id="availabilityRate">-</span>% availability
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-center mb-2">
                        <i class="bi bi-arrow-repeat text-warning me-2" style="font-size: 1.5rem;"></i>
                        <h5 class="card-title mb-0">Active Requests</h5>
                    </div>
                    <h2 class="text-warning mb-1" id="activeRequests">-</h2>
                    <small class="text-muted">
                        <span id="requestsChange" class="badge bg-light text-dark">-</span>
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-center mb-2">
                        <i class="bi bi-currency-dollar text-info me-2" style="font-size: 1.5rem;"></i>
                        <h5 class="card-title mb-0">Total Value</h5>
                    </div>
                    <h2 class="text-info mb-1" id="totalValue">-</h2>
                    <small class="text-muted">
                        Inventory worth
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts Row 1 -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Request Trends</h5>
                    <div class="btn-group btn-group-sm" role="group">
                        <input type="radio" class="btn-check" name="trendType" id="trendDaily" value="daily" checked>
                        <label class="btn btn-outline-primary" for="trendDaily">Daily</label>
                        
                        <input type="radio" class="btn-check" name="trendType" id="trendWeekly" value="weekly">
                        <label class="btn btn-outline-primary" for="trendWeekly">Weekly</label>
                        
                        <input type="radio" class="btn-check" name="trendType" id="trendMonthly" value="monthly">
                        <label class="btn btn-outline-primary" for="trendMonthly">Monthly</label>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="requestTrendsChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Request Status Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="requestStatusChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts Row 2 -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Most Popular Categories</h5>
                </div>
                <div class="card-body">
                    <canvas id="categoryPopularityChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Item Utilization Rate</h5>
                </div>
                <div class="card-body">
                    <canvas id="utilizationChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Data Tables -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Top Borrowed Items</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Category</th>
                                    <th>Times Borrowed</th>
                                    <th>Utilization</th>
                                </tr>
                            </thead>
                            <tbody id="topItemsTable">
                                <!-- Data will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Most Active Users</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Role</th>
                                    <th>Total Requests</th>
                                    <th>Success Rate</th>
                                </tr>
                            </thead>
                            <tbody id="topUsersTable">
                                <!-- Data will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Advanced Metrics -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Advanced Metrics</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h4 class="text-primary mb-1" id="avgRequestTime">-</h4>
                                <small class="text-muted">Avg. Request Processing Time</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h4 class="text-success mb-1" id="approvalRate">-</h4>
                                <small class="text-muted">Request Approval Rate</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h4 class="text-warning mb-1" id="avgBorrowDuration">-</h4>
                                <small class="text-muted">Avg. Borrow Duration</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h4 class="text-danger mb-1" id="overdueRate">-</h4>
                                <small class="text-muted">Overdue Rate</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Alerts and Insights -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">System Insights</h5>
                </div>
                <div class="card-body">
                    <div id="insightsContainer">
                        <!-- Insights will be generated here -->
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Alerts & Recommendations</h5>
                </div>
                <div class="card-body">
                    <div id="alertsContainer">
                        <!-- Alerts will be generated here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

$scripts = '
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let charts = {};
let analyticsData = {};

document.addEventListener("DOMContentLoaded", function() {
    loadAnalytics();
    
    document.getElementById("timeRangeSelect").addEventListener("change", loadAnalytics);
    document.querySelectorAll("input[name=trendType]").forEach(radio => {
        radio.addEventListener("change", updateRequestTrendsChart);
    });
});

async function loadAnalytics() {
    try {
        const timeRange = document.getElementById("timeRangeSelect").value;
        
        showLoading();
        
        const response = await fetch(`/api/reports/advanced-analytics?days=${timeRange}`);
        const data = await response.json();
        
        if (response.ok) {
            analyticsData = data;
            updateKPIs(data.kpis);
            updateCharts(data.charts);
            updateTables(data.tables);
            updateMetrics(data.metrics);
            updateInsights(data.insights);
            updateAlerts(data.alerts);
        } else {
            throw new Error(data.error || "Failed to load analytics");
        }
        
    } catch (error) {
        console.error("Error loading analytics:", error);
        showError("Failed to load analytics: " + error.message);
    }
}

function showLoading() {
    // Show loading indicators
    document.querySelectorAll("[id$=Items], [id$=Requests], [id$=Value]").forEach(el => {
        el.textContent = "Loading...";
    });
}

function updateKPIs(kpis) {
    document.getElementById("totalItems").textContent = kpis.total_items || 0;
    document.getElementById("availableItems").textContent = kpis.available_items || 0;
    document.getElementById("activeRequests").textContent = kpis.active_requests || 0;
    document.getElementById("totalValue").textContent = "$" + (kpis.total_value || 0).toLocaleString();
    
    // Update change indicators
    document.getElementById("itemsChange").textContent = (kpis.items_change || 0) + "%";
    document.getElementById("itemsChange").className = `badge ${kpis.items_change >= 0 ? "bg-success" : "bg-danger"}`;
    
    document.getElementById("requestsChange").textContent = (kpis.requests_change || 0) + "%";
    document.getElementById("requestsChange").className = `badge ${kpis.requests_change >= 0 ? "bg-success" : "bg-danger"}`;
    
    document.getElementById("availabilityRate").textContent = Math.round((kpis.available_items / kpis.total_items) * 100) || 0;
}

function updateCharts(chartData) {
    // Request Trends Chart
    updateRequestTrendsChart();
    
    // Request Status Chart
    if (charts.requestStatus) {
        charts.requestStatus.destroy();
    }
    
    const statusCtx = document.getElementById("requestStatusChart").getContext("2d");
    charts.requestStatus = new Chart(statusCtx, {
        type: "doughnut",
        data: {
            labels: chartData.request_status.labels || ["Pending", "Approved", "Declined", "Returned"],
            datasets: [{
                data: chartData.request_status.data || [0, 0, 0, 0],
                backgroundColor: ["#ffc107", "#28a745", "#dc3545", "#17a2b8"],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: "bottom"
                }
            }
        }
    });
    
    // Category Popularity Chart
    if (charts.categoryPopularity) {
        charts.categoryPopularity.destroy();
    }
    
    const categoryCtx = document.getElementById("categoryPopularityChart").getContext("2d");
    charts.categoryPopularity = new Chart(categoryCtx, {
        type: "bar",
        data: {
            labels: chartData.category_popularity.labels || [],
            datasets: [{
                label: "Requests",
                data: chartData.category_popularity.data || [],
                backgroundColor: "#007bff",
                borderColor: "#0056b3",
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    
    // Utilization Chart
    if (charts.utilization) {
        charts.utilization.destroy();
    }
    
    const utilizationCtx = document.getElementById("utilizationChart").getContext("2d");
    charts.utilization = new Chart(utilizationCtx, {
        type: "line",
        data: {
            labels: chartData.utilization.labels || [],
            datasets: [{
                label: "Utilization Rate (%)",
                data: chartData.utilization.data || [],
                borderColor: "#28a745",
                backgroundColor: "rgba(40, 167, 69, 0.1)",
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });
}

function updateRequestTrendsChart() {
    const trendType = document.querySelector("input[name=trendType]:checked").value;
    const chartData = analyticsData.charts?.request_trends?.[trendType] || { labels: [], data: [] };
    
    if (charts.requestTrends) {
        charts.requestTrends.destroy();
    }
    
    const trendsCtx = document.getElementById("requestTrendsChart").getContext("2d");
    charts.requestTrends = new Chart(trendsCtx, {
        type: "line",
        data: {
            labels: chartData.labels,
            datasets: [{
                label: "Requests",
                data: chartData.data,
                borderColor: "#007bff",
                backgroundColor: "rgba(0, 123, 255, 0.1)",
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function updateTables(tableData) {
    // Top Items Table
    const topItemsTable = document.getElementById("topItemsTable");
    topItemsTable.innerHTML = (tableData.top_items || []).map(item => `
        <tr>
            <td>${escapeHtml(item.name)}</td>
            <td>${escapeHtml(item.category)}</td>
            <td>${item.borrow_count}</td>
            <td>
                <div class="progress" style="height: 20px;">
                    <div class="progress-bar" role="progressbar" style="width: ${item.utilization}%">
                        ${item.utilization}%
                    </div>
                </div>
            </td>
        </tr>
    `).join("");
    
    // Top Users Table
    const topUsersTable = document.getElementById("topUsersTable");
    topUsersTable.innerHTML = (tableData.top_users || []).map(user => `
        <tr>
            <td>${escapeHtml(user.name)}</td>
            <td><span class="badge bg-secondary">${escapeHtml(user.role)}</span></td>
            <td>${user.request_count}</td>
            <td>
                <span class="badge ${user.success_rate >= 80 ? "bg-success" : user.success_rate >= 60 ? "bg-warning" : "bg-danger"}">
                    ${user.success_rate}%
                </span>
            </td>
        </tr>
    `).join("");
}

function updateMetrics(metrics) {
    document.getElementById("avgRequestTime").textContent = (metrics.avg_request_time || 0) + " hrs";
    document.getElementById("approvalRate").textContent = (metrics.approval_rate || 0) + "%";
    document.getElementById("avgBorrowDuration").textContent = (metrics.avg_borrow_duration || 0) + " days";
    document.getElementById("overdueRate").textContent = (metrics.overdue_rate || 0) + "%";
}

function updateInsights(insights) {
    const container = document.getElementById("insightsContainer");
    
    if (!insights || insights.length === 0) {
        container.innerHTML = "<p class=\"text-muted\">No insights available for the selected time period.</p>";
        return;
    }
    
    container.innerHTML = insights.map(insight => `
        <div class="alert alert-${insight.type} d-flex align-items-center" role="alert">
            <i class="bi bi-${insight.icon} me-2"></i>
            <div>
                <strong>${insight.title}</strong><br>
                ${insight.description}
            </div>
        </div>
    `).join("");
}

function updateAlerts(alerts) {
    const container = document.getElementById("alertsContainer");
    
    if (!alerts || alerts.length === 0) {
        container.innerHTML = "<p class=\"text-muted\">No alerts at this time.</p>";
        return;
    }
    
    container.innerHTML = alerts.map(alert => `
        <div class="alert alert-${alert.severity} alert-dismissible fade show" role="alert">
            <i class="bi bi-${alert.icon} me-2"></i>
            <strong>${alert.title}</strong><br>
            <small>${alert.message}</small>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `).join("");
}

function refreshAnalytics() {
    loadAnalytics();
}

async function exportAnalytics() {
    try {
        const timeRange = document.getElementById("timeRangeSelect").value;
        
        const response = await fetch(`/api/reports/export-analytics?days=${timeRange}&format=pdf`);
        
        if (response.ok) {
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement("a");
            a.href = url;
            a.download = `analytics_report_${timeRange}days.pdf`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        } else {
            throw new Error("Export failed");
        }
    } catch (error) {
        console.error("Export error:", error);
        showAlert("Failed to export analytics report", "danger");
    }
}

function escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
}

function showError(message) {
    console.error(message);
    // Implement error display
}

function showAlert(message, type) {
    console.log(`${type.toUpperCase()}: ${message}`);
    // Implement alert display
}
</script>
';

include __DIR__ . '/../../layout.php';
?>

