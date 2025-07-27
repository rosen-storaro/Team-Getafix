<?php
$title = 'Inventory - School Inventory Management System';
ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Inventory</h1>
                    <p class="text-muted mb-0">Browse and manage inventory items</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="/inventory/add" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Add Item
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Search and Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="searchInput" class="form-label">Search</label>
                            <input type="text" class="form-control" id="searchInput" placeholder="Search items...">
                        </div>
                        <div class="col-md-2">
                            <label for="categoryFilter" class="form-label">Category</label>
                            <select class="form-select" id="categoryFilter">
                                <option value="">All Categories</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="statusFilter" class="form-label">Status</label>
                            <select class="form-select" id="statusFilter">
                                <option value="">All Statuses</option>
                                <option value="Available">Available</option>
                                <option value="Checked Out">Checked Out</option>
                                <option value="Reserved">Reserved</option>
                                <option value="Under Repair">Under Repair</option>
                                <option value="Lost/Stolen">Lost/Stolen</option>
                                <option value="Retired">Retired</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="conditionFilter" class="form-label">Condition</label>
                            <select class="form-select" id="conditionFilter">
                                <option value="">All Conditions</option>
                                <option value="New">New</option>
                                <option value="Good">Good</option>
                                <option value="Fair">Fair</option>
                                <option value="Poor">Poor</option>
                                <option value="Damaged">Damaged</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-outline-primary me-2" onclick="applyFilters()">
                                <i class="bi bi-funnel me-1"></i>Filter
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="clearFilters()">
                                <i class="bi bi-x-circle me-1"></i>Clear
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Items Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Items</h5>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshItems()">
                            <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="loadingSpinner" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading items...</p>
                    </div>
                    
                    <div id="itemsContainer" style="display: none;">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Category</th>
                                        <th>Location</th>
                                        <th>Quantity</th>
                                        <th>Status</th>
                                        <th>Condition</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsTableBody">
                                    <!-- Items will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <nav aria-label="Items pagination">
                            <ul class="pagination justify-content-center" id="pagination">
                                <!-- Pagination will be generated here -->
                            </ul>
                        </nav>
                    </div>
                    
                    <div id="noItemsMessage" class="text-center py-4" style="display: none;">
                        <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                        <h5 class="text-muted mt-3">No items found</h5>
                        <p class="text-muted">No items match your search criteria.</p>
                    </div>
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
    loadCategories();
    loadItems();
    
    document.getElementById("searchInput").addEventListener("input", debounce(applyFilters, 500));
});

async function loadCategories() {
    try {
        const response = await fetch("/api/inventory/categories");
        const data = await response.json();
        
        const select = document.getElementById("categoryFilter");
        data.categories.forEach(category => {
            const option = document.createElement("option");
            option.value = category.id;
            option.textContent = category.name;
            select.appendChild(option);
        });
    } catch (error) {
        console.error("Error loading categories:", error);
    }
}

async function loadItems(page = 1) {
    currentPage = page;
    
    try {
        showLoading();
        
        const params = new URLSearchParams({
            page: page,
            limit: 20,
            ...currentFilters
        });
        
        const response = await fetch("/api/inventory/items?" + params);
        const data = await response.json();
        
        if (response.ok) {
            displayItems(data.items);
            displayPagination(data.pagination);
        } else {
            throw new Error(data.error || "Failed to load items");
        }
        
    } catch (error) {
        console.error("Error loading items:", error);
        showError("Failed to load items: " + error.message);
    }
}

function showLoading() {
    document.getElementById("loadingSpinner").style.display = "block";
    document.getElementById("itemsContainer").style.display = "none";
    document.getElementById("noItemsMessage").style.display = "none";
}

function displayItems(items) {
    const tbody = document.getElementById("itemsTableBody");
    const container = document.getElementById("itemsContainer");
    const noItemsMsg = document.getElementById("noItemsMessage");
    const loading = document.getElementById("loadingSpinner");
    
    loading.style.display = "none";
    
    if (items.length === 0) {
        container.style.display = "none";
        noItemsMsg.style.display = "block";
        return;
    }
    
    tbody.innerHTML = items.map(item => {
        return "<tr>" +
            "<td>" +
                "<div class=\"d-flex align-items-center\">" +
                    (item.photo_url ? 
                        "<img src=\"" + item.photo_url + "\" alt=\"" + escapeHtml(item.name) + "\" class=\"rounded me-2\" style=\"width: 40px; height: 40px; object-fit: cover;\">" :
                        "<div class=\"bg-light rounded me-2 d-flex align-items-center justify-content-center\" style=\"width: 40px; height: 40px;\">" +
                            "<i class=\"bi bi-box text-muted\"></i>" +
                        "</div>"
                    ) +
                    "<div>" +
                        "<div class=\"fw-bold\">" + escapeHtml(item.name) + "</div>" +
                        (item.serial_number ? "<small class=\"text-muted\">" + escapeHtml(item.serial_number) + "</small>" : "") +
                    "</div>" +
                "</div>" +
            "</td>" +
            "<td>" + escapeHtml(item.category_name || "N/A") + "</td>" +
            "<td>" + escapeHtml(item.location_name || "N/A") + "</td>" +
            "<td>" +
                item.quantity +
                (item.quantity <= item.low_stock_threshold ? " <span class=\"badge bg-warning text-dark\">Low</span>" : "") +
            "</td>" +
            "<td>" + getStatusBadge(item.status) + "</td>" +
            "<td>" + getConditionBadge(item.condition) + "</td>" +
            "<td>" +
                "<div class=\"btn-group btn-group-sm\">" +
                    "<button type=\"button\" class=\"btn btn-outline-primary\" onclick=\"viewItem(" + item.id + ")\">" +
                        "<i class=\"bi bi-eye\"></i>" +
                    "</button>" +
                    (item.status === "Available" ? 
                        "<button type=\"button\" class=\"btn btn-outline-success\" onclick=\"requestItem(" + item.id + ")\">" +
                            "<i class=\"bi bi-plus-circle\"></i>" +
                        "</button>" : ""
                    ) +
                "</div>" +
            "</td>" +
        "</tr>";
    }).join("");
    
    container.style.display = "block";
    noItemsMsg.style.display = "none";
}

function displayPagination(pagination) {
    const paginationEl = document.getElementById("pagination");
    
    if (pagination.total_pages <= 1) {
        paginationEl.innerHTML = "";
        return;
    }
    
    let html = "";
    
    // Previous button
    html += "<li class=\"page-item " + (pagination.current_page === 1 ? "disabled" : "") + "\">" +
        "<a class=\"page-link\" href=\"#\" onclick=\"loadItems(" + (pagination.current_page - 1) + ")\">Previous</a>" +
    "</li>";
    
    // Page numbers
    for (let i = 1; i <= pagination.total_pages; i++) {
        if (i === 1 || i === pagination.total_pages || (i >= pagination.current_page - 2 && i <= pagination.current_page + 2)) {
            html += "<li class=\"page-item " + (i === pagination.current_page ? "active" : "") + "\">" +
                "<a class=\"page-link\" href=\"#\" onclick=\"loadItems(" + i + ")\">" + i + "</a>" +
            "</li>";
        } else if (i === pagination.current_page - 3 || i === pagination.current_page + 3) {
            html += "<li class=\"page-item disabled\"><span class=\"page-link\">...</span></li>";
        }
    }
    
    // Next button
    html += "<li class=\"page-item " + (pagination.current_page === pagination.total_pages ? "disabled" : "") + "\">" +
        "<a class=\"page-link\" href=\"#\" onclick=\"loadItems(" + (pagination.current_page + 1) + ")\">Next</a>" +
    "</li>";
    
    paginationEl.innerHTML = html;
}

function applyFilters() {
    currentFilters = {
        search: document.getElementById("searchInput").value,
        category_id: document.getElementById("categoryFilter").value,
        status: document.getElementById("statusFilter").value,
        condition: document.getElementById("conditionFilter").value
    };
    
    // Remove empty filters
    Object.keys(currentFilters).forEach(key => {
        if (!currentFilters[key]) {
            delete currentFilters[key];
        }
    });
    
    loadItems(1);
}

function clearFilters() {
    document.getElementById("searchInput").value = "";
    document.getElementById("categoryFilter").value = "";
    document.getElementById("statusFilter").value = "";
    document.getElementById("conditionFilter").value = "";
    currentFilters = {};
    loadItems(1);
}

function refreshItems() {
    loadItems(currentPage);
}

function viewItem(itemId) {
    window.location.href = "/inventory/" + itemId;
}

function requestItem(itemId) {
    window.location.href = "/requests/create?item_id=" + itemId;
}

function getStatusBadge(status) {
    const colors = {
        "Available": "success",
        "Checked Out": "warning",
        "Reserved": "info",
        "Under Repair": "secondary",
        "Lost/Stolen": "danger",
        "Retired": "dark"
    };
    const color = colors[status] || "secondary";
    return "<span class=\"badge bg-" + color + "\">" + status + "</span>";
}

function getConditionBadge(condition) {
    const colors = {
        "New": "success",
        "Good": "primary",
        "Fair": "warning",
        "Poor": "danger",
        "Damaged": "dark"
    };
    const color = colors[condition] || "secondary";
    return "<span class=\"badge bg-" + color + "\">" + condition + "</span>";
}

function escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
}

function showError(message) {
    document.getElementById("loadingSpinner").style.display = "none";
    document.getElementById("itemsContainer").style.display = "none";
    document.getElementById("noItemsMessage").innerHTML = 
        "<i class=\"bi bi-exclamation-triangle text-danger\" style=\"font-size: 3rem;\"></i>" +
        "<h5 class=\"text-danger mt-3\">Error Loading Items</h5>" +
        "<p class=\"text-muted\">" + message + "</p>" +
        "<button class=\"btn btn-primary\" onclick=\"loadItems()\">" +
            "<i class=\"bi bi-arrow-clockwise me-2\"></i>Try Again" +
        "</button>";
    document.getElementById("noItemsMessage").style.display = "block";
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
</script>
';

include __DIR__ . '/../layout.php';
?>

