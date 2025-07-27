<?php
$title = 'Admin Request Management - School Inventory Management System';
ob_start();
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0">Request Management</h1>
            <p class="text-muted">Manage borrowing requests and approvals</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h4 class="mb-0" id="pendingRequests">0</h4>
                    <p class="mb-0">Pending Approval</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h4 class="mb-0" id="approvedRequests">0</h4>
                    <p class="mb-0">Approved</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h4 class="mb-0" id="activeRequests">0</h4>
                    <p class="mb-0">Active Loans</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h4 class="mb-0" id="overdueRequests">0</h4>
                    <p class="mb-0">Overdue</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Request Management Tabs -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="requestTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">
                                <i class="bi bi-clock me-2"></i>Pending Approval
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="active-tab" data-bs-toggle="tab" data-bs-target="#active" type="button" role="tab">
                                <i class="bi bi-check-circle me-2"></i>Active Loans
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="overdue-tab" data-bs-toggle="tab" data-bs-target="#overdue" type="button" role="tab">
                                <i class="bi bi-exclamation-triangle me-2"></i>Overdue
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">
                                <i class="bi bi-list me-2"></i>All Requests
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="requestTabContent">
                        <!-- Pending Requests Tab -->
                        <div class="tab-pane fade show active" id="pending" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5>Pending Approval</h5>
                                <div>
                                    <button class="btn btn-success me-2" onclick="bulkApprove()">
                                        <i class="bi bi-check-all me-2"></i>Bulk Approve
                                    </button>
                                    <button class="btn btn-outline-secondary" onclick="refreshPending()">
                                        <i class="bi bi-arrow-clockwise me-2"></i>Refresh
                                    </button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped" id="pendingTable">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" id="selectAllPending"></th>
                                            <th>User</th>
                                            <th>Item</th>
                                            <th>Quantity</th>
                                            <th>Borrow Date</th>
                                            <th>Return Date</th>
                                            <th>Purpose</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="8" class="text-center">
                                                <div class="spinner-border text-primary" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                                <p class="mt-2 text-muted">Loading pending requests...</p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Active Loans Tab -->
                        <div class="tab-pane fade" id="active" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5>Active Loans</h5>
                                <button class="btn btn-outline-secondary" onclick="refreshActive()">
                                    <i class="bi bi-arrow-clockwise me-2"></i>Refresh
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped" id="activeTable">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Item</th>
                                            <th>Quantity</th>
                                            <th>Borrowed</th>
                                            <th>Due Date</th>
                                            <th>Days Left</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="7" class="text-center">Loading active loans...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Overdue Tab -->
                        <div class="tab-pane fade" id="overdue" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5>Overdue Items</h5>
                                <button class="btn btn-warning" onclick="sendOverdueReminders()">
                                    <i class="bi bi-envelope me-2"></i>Send Reminders
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped" id="overdueTable">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Item</th>
                                            <th>Quantity</th>
                                            <th>Due Date</th>
                                            <th>Days Overdue</th>
                                            <th>Contact</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="7" class="text-center">Loading overdue items...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- All Requests Tab -->
                        <div class="tab-pane fade" id="all" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5>All Requests</h5>
                                <div>
                                    <button class="btn btn-outline-secondary me-2" onclick="exportRequests()">
                                        <i class="bi bi-download me-2"></i>Export CSV
                                    </button>
                                    <button class="btn btn-outline-secondary" onclick="refreshAll()">
                                        <i class="bi bi-arrow-clockwise me-2"></i>Refresh
                                    </button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped" id="allTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>User</th>
                                            <th>Item</th>
                                            <th>Status</th>
                                            <th>Created</th>
                                            <th>Borrow Date</th>
                                            <th>Return Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="8" class="text-center">Loading all requests...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

$scripts = '<script>
document.addEventListener("DOMContentLoaded", function() {
    loadRequestStats();
    loadPendingRequests();
    
    // Tab change handlers
    document.querySelectorAll("#requestTabs button").forEach(tab => {
        tab.addEventListener("shown.bs.tab", function(e) {
            const target = e.target.getAttribute("data-bs-target");
            switch(target) {
                case "#pending":
                    loadPendingRequests();
                    break;
                case "#active":
                    loadActiveRequests();
                    break;
                case "#overdue":
                    loadOverdueRequests();
                    break;
                case "#all":
                    loadAllRequests();
                    break;
            }
        });
    });
});

async function loadRequestStats() {
    try {
        const response = await fetch("/api/requests/stats", {
            credentials: "same-origin"
        });
        if (response.ok) {
            const data = await response.json();
            const stats = data.stats || {};
            document.getElementById("pendingRequests").textContent = stats.pending || 0;
            document.getElementById("approvedRequests").textContent = stats.approved || 0;
            document.getElementById("activeRequests").textContent = stats.active || 0;
            document.getElementById("overdueRequests").textContent = stats.overdue || 0;
        }
    } catch (error) {
        console.error("Error loading request stats:", error);
    }
}

async function loadPendingRequests() {
    try {
        const response = await fetch("/api/requests/pending", {
            credentials: "same-origin"
        });
        if (response.ok) {
            const data = await response.json();
            renderPendingTable(data.requests || []);
        }
    } catch (error) {
        console.error("Error loading pending requests:", error);
    }
}

function renderPendingTable(requests) {
    const tbody = document.querySelector("#pendingTable tbody");
    if (requests.length === 0) {
        tbody.innerHTML = "<tr><td colspan=\"8\" class=\"text-center text-muted\">No pending requests</td></tr>";
        return;
    }
    
    tbody.innerHTML = requests.map(request => 
        "<tr>" +
            "<td><input type=\"checkbox\" class=\"request-checkbox\" value=\"" + request.id + "\"></td>" +
            "<td>" + escapeHtml(request.user_name || "Unknown") + "</td>" +
            "<td>" + escapeHtml(request.item_name || "Unknown") + "</td>" +
            "<td>" + request.quantity + "</td>" +
            "<td>" + formatDate(request.borrow_date) + "</td>" +
            "<td>" + formatDate(request.return_date) + "</td>" +
            "<td>" + escapeHtml(request.purpose || "") + "</td>" +
            "<td>" +
                "<button class=\"btn btn-sm btn-success me-1\" onclick=\"approveRequest(" + request.id + ")\">Approve</button>" +
                "<button class=\"btn btn-sm btn-danger\" onclick=\"declineRequest(" + request.id + ")\">Decline</button>" +
            "</td>" +
        "</tr>"
    ).join("");
}

async function approveRequest(id) {
    try {
        const response = await fetch("/api/requests/" + id + "/approve", {
            method: "PUT",
            credentials: "same-origin"
        });
        if (response.ok) {
            showAlert("Request approved successfully", "success");
            loadPendingRequests();
            loadRequestStats();
        } else {
            const error = await response.json();
            showAlert(error.error || "Failed to approve request", "danger");
        }
    } catch (error) {
        console.error("Error approving request:", error);
        showAlert("Failed to approve request", "danger");
    }
}

async function declineRequest(id) {
    const reason = prompt("Please provide a reason for declining this request:");
    if (!reason) return;
    
    try {
        const response = await fetch("/api/requests/" + id + "/decline", {
            method: "PUT",
            headers: {
                "Content-Type": "application/json"
            },
            credentials: "same-origin",
            body: JSON.stringify({ reason: reason })
        });
        if (response.ok) {
            showAlert("Request declined", "info");
            loadPendingRequests();
            loadRequestStats();
        } else {
            const error = await response.json();
            showAlert(error.error || "Failed to decline request", "danger");
        }
    } catch (error) {
        console.error("Error declining request:", error);
        showAlert("Failed to decline request", "danger");
    }
}

function escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString();
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

function exportRequests() {
    window.open("/api/reports/export?type=requests", "_blank");
}
</script>';

include __DIR__ . '/../layout.php';
?>

