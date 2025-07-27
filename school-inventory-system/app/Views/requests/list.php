<?php
$title = 'My Requests - School Inventory Management System';
ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">My Requests</h1>
                    <p class="text-muted mb-0">View and manage your borrow requests</p>
                </div>
                <a href="/requests/create" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>New Request
                </a>
            </div>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="statusFilter" class="form-label">Status</label>
                            <select class="form-select" id="statusFilter">
                                <option value="">All Statuses</option>
                                <option value="Pending">Pending</option>
                                <option value="Approved">Approved</option>
                                <option value="Declined">Declined</option>
                                <option value="Returned">Returned</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="dateFromFilter" class="form-label">From Date</label>
                            <input type="date" class="form-control" id="dateFromFilter">
                        </div>
                        <div class="col-md-3">
                            <label for="dateToFilter" class="form-label">To Date</label>
                            <input type="date" class="form-control" id="dateToFilter">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="button" class="btn btn-outline-primary me-2" onclick="applyFilters()">
                                <i class="bi bi-funnel me-2"></i>Filter
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="clearFilters()">
                                <i class="bi bi-x-circle me-2"></i>Clear
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Requests Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Requests</h5>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshRequests()">
                            <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                        </button>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-download me-1"></i>Export
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="exportRequests('csv')">CSV</a></li>
                                <li><a class="dropdown-item" href="#" onclick="exportRequests('pdf')">PDF</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="loadingSpinner" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading requests...</p>
                    </div>
                    
                    <div id="requestsContainer" style="display: none;">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Request ID</th>
                                        <th>Item</th>
                                        <th>Quantity</th>
                                        <th>Borrow Date</th>
                                        <th>Return Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="requestsTableBody">
                                    <!-- Requests will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <nav aria-label="Requests pagination">
                            <ul class="pagination justify-content-center" id="pagination">
                                <!-- Pagination will be generated here -->
                            </ul>
                        </nav>
                    </div>
                    
                    <div id="noRequestsMessage" class="text-center py-4" style="display: none;">
                        <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                        <h5 class="text-muted mt-3">No requests found</h5>
                        <p class="text-muted">You haven't made any requests yet.</p>
                        <a href="/requests/create" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Create Your First Request
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Request Details Modal -->
<div class="modal fade" id="requestModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Request Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="requestModalBody">
                <!-- Request details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <div id="requestModalActions">
                    <!-- Action buttons will be added here based on request status -->
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

$scripts = '
<script>
let currentPage = 1;
let currentFilters = {};

document.addEventListener("DOMContentLoaded", function() {
    loadRequests();
});

async function loadRequests(page = 1) {
    currentPage = page;
    
    try {
        showLoading();
        
        const params = new URLSearchParams({
            page: page,
            limit: 10,
            ...currentFilters
        });
        
        const response = await fetch(`/api/requests?${params}`);
        const data = await response.json();
        
        if (response.ok) {
            displayRequests(data.requests);
            displayPagination(data.pagination);
        } else {
            throw new Error(data.error || "Failed to load requests");
        }
        
    } catch (error) {
        console.error("Error loading requests:", error);
        showError("Failed to load requests: " + error.message);
    }
}

function showLoading() {
    document.getElementById("loadingSpinner").style.display = "block";
    document.getElementById("requestsContainer").style.display = "none";
    document.getElementById("noRequestsMessage").style.display = "none";
}

function displayRequests(requests) {
    const tbody = document.getElementById("requestsTableBody");
    const container = document.getElementById("requestsContainer");
    const noRequestsMsg = document.getElementById("noRequestsMessage");
    const loading = document.getElementById("loadingSpinner");
    
    loading.style.display = "none";
    
    if (requests.length === 0) {
        container.style.display = "none";
        noRequestsMsg.style.display = "block";
        return;
    }
    
    tbody.innerHTML = requests.map(request => `
        <tr>
            <td>
                <span class="fw-bold">#${request.id}</span>
                <br>
                <small class="text-muted">${formatDate(request.created_at)}</small>
            </td>
            <td>
                <div class="d-flex align-items-center">
                    ${request.item_photo ? 
                        `<img src="${request.item_photo}" alt="${request.item_name}" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">` :
                        `<div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="bi bi-box text-muted"></i>
                        </div>`
                    }
                    <div>
                        <div class="fw-bold">${escapeHtml(request.item_name)}</div>
                        <small class="text-muted">${escapeHtml(request.category_name)}</small>
                    </div>
                </div>
            </td>
            <td>${request.quantity}</td>
            <td>${formatDate(request.borrow_date)}</td>
            <td>${formatDate(request.return_date)}</td>
            <td>
                <span class="badge bg-${getStatusColor(request.status)}">${request.status}</span>
                ${request.status === "Pending" && request.sensitive_level !== "No" ? 
                    `<br><small class="text-warning"><i class="bi bi-exclamation-triangle"></i> Requires super-admin approval</small>` : ""
                }
            </td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-primary" onclick="viewRequest(${request.id})">
                        <i class="bi bi-eye"></i>
                    </button>
                    ${request.status === "Pending" ? 
                        `<button type="button" class="btn btn-outline-danger" onclick="cancelRequest(${request.id})">
                            <i class="bi bi-x-circle"></i>
                        </button>` : ""
                    }
                </div>
            </td>
        </tr>
    `).join("");
    
    container.style.display = "block";
    noRequestsMsg.style.display = "none";
}

function displayPagination(pagination) {
    const paginationEl = document.getElementById("pagination");
    
    if (pagination.total_pages <= 1) {
        paginationEl.innerHTML = "";
        return;
    }
    
    let html = "";
    
    // Previous button
    html += `
        <li class="page-item ${pagination.current_page === 1 ? "disabled" : ""}">
            <a class="page-link" href="#" onclick="loadRequests(${pagination.current_page - 1})">Previous</a>
        </li>
    `;
    
    // Page numbers
    for (let i = 1; i <= pagination.total_pages; i++) {
        if (i === 1 || i === pagination.total_pages || (i >= pagination.current_page - 2 && i <= pagination.current_page + 2)) {
            html += `
                <li class="page-item ${i === pagination.current_page ? "active" : ""}">
                    <a class="page-link" href="#" onclick="loadRequests(${i})">${i}</a>
                </li>
            `;
        } else if (i === pagination.current_page - 3 || i === pagination.current_page + 3) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }
    
    // Next button
    html += `
        <li class="page-item ${pagination.current_page === pagination.total_pages ? "disabled" : ""}">
            <a class="page-link" href="#" onclick="loadRequests(${pagination.current_page + 1})">Next</a>
        </li>
    `;
    
    paginationEl.innerHTML = html;
}

async function viewRequest(requestId) {
    try {
        const response = await fetch(`/api/requests/${requestId}`);
        const data = await response.json();
        
        if (response.ok) {
            showRequestModal(data.request);
        } else {
            throw new Error(data.error || "Failed to load request details");
        }
    } catch (error) {
        console.error("Error loading request details:", error);
        alert("Failed to load request details: " + error.message);
    }
}

function showRequestModal(request) {
    const modalBody = document.getElementById("requestModalBody");
    const modalActions = document.getElementById("requestModalActions");
    
    modalBody.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <h6>Request Information</h6>
                <table class="table table-sm">
                    <tr><td><strong>Request ID:</strong></td><td>#${request.id}</td></tr>
                    <tr><td><strong>Status:</strong></td><td><span class="badge bg-${getStatusColor(request.status)}">${request.status}</span></td></tr>
                    <tr><td><strong>Created:</strong></td><td>${formatDateTime(request.created_at)}</td></tr>
                    <tr><td><strong>Quantity:</strong></td><td>${request.quantity}</td></tr>
                    <tr><td><strong>Borrow Date:</strong></td><td>${formatDate(request.borrow_date)}</td></tr>
                    <tr><td><strong>Return Date:</strong></td><td>${formatDate(request.return_date)}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6>Item Information</h6>
                <div class="d-flex align-items-center mb-3">
                    ${request.item_photo ? 
                        `<img src="${request.item_photo}" alt="${request.item_name}" class="rounded me-3" style="width: 80px; height: 80px; object-fit: cover;">` :
                        `<div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="bi bi-box text-muted" style="font-size: 2rem;"></i>
                        </div>`
                    }
                    <div>
                        <h6 class="mb-1">${escapeHtml(request.item_name)}</h6>
                        <p class="text-muted mb-1">${escapeHtml(request.category_name)}</p>
                        <small class="text-muted">${escapeHtml(request.location_name)}</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <h6>Purpose</h6>
                <p>${escapeHtml(request.purpose)}</p>
                
                ${request.notes ? `
                    <h6>Notes</h6>
                    <p>${escapeHtml(request.notes)}</p>
                ` : ""}
                
                ${request.admin_notes ? `
                    <h6>Admin Notes</h6>
                    <p class="text-muted">${escapeHtml(request.admin_notes)}</p>
                ` : ""}
            </div>
        </div>
    `;
    
    // Add action buttons based on status
    modalActions.innerHTML = "";
    if (request.status === "Pending") {
        modalActions.innerHTML = `
            <button type="button" class="btn btn-danger" onclick="cancelRequest(${request.id})">
                <i class="bi bi-x-circle me-2"></i>Cancel Request
            </button>
        `;
    }
    
    new bootstrap.Modal(document.getElementById("requestModal")).show();
}

async function cancelRequest(requestId) {
    if (!confirm("Are you sure you want to cancel this request?")) {
        return;
    }
    
    try {
        const response = await fetch(`/api/requests/${requestId}/cancel`, {
            method: "POST"
        });
        
        const result = await response.json();
        
        if (response.ok) {
            alert("Request cancelled successfully");
            loadRequests(currentPage);
            bootstrap.Modal.getInstance(document.getElementById("requestModal"))?.hide();
        } else {
            throw new Error(result.error || "Failed to cancel request");
        }
    } catch (error) {
        console.error("Error cancelling request:", error);
        alert("Failed to cancel request: " + error.message);
    }
}

function applyFilters() {
    currentFilters = {
        status: document.getElementById("statusFilter").value,
        date_from: document.getElementById("dateFromFilter").value,
        date_to: document.getElementById("dateToFilter").value
    };
    
    // Remove empty filters
    Object.keys(currentFilters).forEach(key => {
        if (!currentFilters[key]) {
            delete currentFilters[key];
        }
    });
    
    loadRequests(1);
}

function clearFilters() {
    document.getElementById("statusFilter").value = "";
    document.getElementById("dateFromFilter").value = "";
    document.getElementById("dateToFilter").value = "";
    currentFilters = {};
    loadRequests(1);
}

function refreshRequests() {
    loadRequests(currentPage);
}

async function exportRequests(format) {
    try {
        const params = new URLSearchParams({
            format: format,
            ...currentFilters
        });
        
        const response = await fetch(`/api/requests/export?${params}`);
        
        if (response.ok) {
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement("a");
            a.href = url;
            a.download = `requests.${format}`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        } else {
            throw new Error("Export failed");
        }
    } catch (error) {
        console.error("Export error:", error);
        alert("Failed to export requests: " + error.message);
    }
}

function getStatusColor(status) {
    const colors = {
        "Pending": "warning",
        "Approved": "success",
        "Declined": "danger",
        "Returned": "info",
        "Cancelled": "secondary"
    };
    return colors[status] || "secondary";
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString();
}

function formatDateTime(dateString) {
    return new Date(dateString).toLocaleString();
}

function escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
}

function showError(message) {
    document.getElementById("loadingSpinner").style.display = "none";
    document.getElementById("requestsContainer").style.display = "none";
    document.getElementById("noRequestsMessage").innerHTML = `
        <i class="bi bi-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
        <h5 class="text-danger mt-3">Error Loading Requests</h5>
        <p class="text-muted">${message}</p>
        <button class="btn btn-primary" onclick="loadRequests()">
            <i class="bi bi-arrow-clockwise me-2"></i>Try Again
        </button>
    `;
    document.getElementById("noRequestsMessage").style.display = "block";
}
</script>
';

include __DIR__ . '/../layout.php';
?>

