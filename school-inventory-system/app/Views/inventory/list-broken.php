<?php
$title = 'Browse Inventory - School Inventory Management System';
ob_start();
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Browse Inventory</h1>
                    <p class="text-muted mb-0">Discover and request available items</p>
                </div>
                <?php if (in_array($_SESSION['role_name'] ?? '', ['Admin', 'Super-admin'])): ?>
                <div>
                    <a href="/inventory/add" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Add Item
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Search and Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form id="searchForm" class="row g-3">
                        <div class="col-md-4">
                            <label for="searchQuery" class="form-label">Search Items</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" class="form-control" id="searchQuery" name="search" 
                                       placeholder="Search by name, serial number, or description...">
                            </div>
                        </div>
                        
                        <div class="col-md-2">
                            <label for="categoryFilter" class="form-label">Category</label>
                            <select class="form-select" id="categoryFilter" name="category_id">
                                <option value="">All Categories</option>
                                <!-- Categories will be loaded dynamically -->
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label for="locationFilter" class="form-label">Location</label>
                            <select class="form-select" id="locationFilter" name="location_id">
                                <option value="">All Locations</option>
                                <!-- Locations will be loaded dynamically -->
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label for="statusFilter" class="form-label">Status</label>
                            <select class="form-select" id="statusFilter" name="status">
                                <option value="">All Status</option>
                                <option value="Available">Available</option>
                                <option value="Checked Out">Checked Out</option>
                                <option value="Reserved">Reserved</option>
                                <option value="Under Repair">Under Repair</option>
                                <option value="Lost/Stolen">Lost/Stolen</option>
                                <option value="Retired">Retired</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search me-1"></i>Search
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    <!-- Quick Filters -->
                    <div class="mt-3">
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="quickFilter('available')">
                                <i class="bi bi-check-circle me-1"></i>Available Only
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-warning" onclick="quickFilter('low_stock')">
                                <i class="bi bi-exclamation-triangle me-1"></i>Low Stock
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearFilters()">
                                <i class="bi bi-x-circle me-1"></i>Clear Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Results Summary -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div id="resultsInfo" class="text-muted">
                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                    Loading items...
                </div>
                <div class="btn-group btn-group-sm" role="group">
                    <input type="radio" class="btn-check" name="viewMode" id="gridView" value="grid" checked>
                    <label class="btn btn-outline-secondary" for="gridView">
                        <i class="bi bi-grid-3x3-gap"></i>
                    </label>
                    
                    <input type="radio" class="btn-check" name="viewMode" id="listView" value="list">
                    <label class="btn btn-outline-secondary" for="listView">
                        <i class="bi bi-list"></i>
                    </label>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Items Grid/List -->
    <div id="itemsContainer">
        <!-- Items will be loaded here -->
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Loading inventory items...</p>
        </div>
    </div>
    
    <!-- Pagination -->
    <div class="row mt-4">
        <div class="col-12">
            <nav id="paginationNav" style="display: none;">
                <ul class="pagination justify-content-center" id="paginationList">
                    <!-- Pagination will be generated here -->
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Item Detail Modal -->
<div class="modal fade" id="itemDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Item Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="itemDetailContent">
                <!-- Item details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="requestItemBtn" style="display: none;">
                    <i class="bi bi-plus-circle me-2"></i>Request Item
                </button>
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
let currentViewMode = "grid";
let itemsData = [];

document.addEventListener("DOMContentLoaded", function() {
    loadCategories();
    loadLocations();
    loadItems();
    
    // Search form handler
    document.getElementById("searchForm").addEventListener("submit", function(e) {
        e.preventDefault();
        currentPage = 1;
        loadItems();
    });
    
    // View mode change handlers
    document.querySelectorAll("input[name=\'viewMode\']").forEach(radio => {
        radio.addEventListener("change", function() {
            currentViewMode = this.value;
            renderItems(itemsData);
        });
    });
    
    // Real-time search
    let searchTimeout;
    document.getElementById("searchQuery").addEventListener("input", function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentPage = 1;
            loadItems();
        }, 500);
    });
});

async function loadCategories() {
    try {
        const response = await fetch("/api/inventory/categories");
        if (response.ok) {
            const data = await response.json();
            const select = document.getElementById("categoryFilter");
            
            data.categories.forEach(category => {
                const option = document.createElement("option");
                option.value = category.id;
                option.textContent = category.name;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error("Error loading categories:", error);
    }
}

async function loadLocations() {
    try {
        const response = await fetch("/api/inventory/locations");
        if (response.ok) {
            const data = await response.json();
            const select = document.getElementById("locationFilter");
            
            data.locations.forEach(location => {
                const option = document.createElement("option");
                option.value = location.id;
                option.textContent = location.name;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error("Error loading locations:", error);
    }
}

async function loadItems() {
    try {
        // Get form data
        const formData = new FormData(document.getElementById("searchForm"));
        const params = new URLSearchParams();
        
        // Add form filters
        for (const [key, value] of formData.entries()) {
            if (value.trim()) {
                params.append(key, value);
            }
        }
        
        // Add pagination
        params.append("limit", "12");
        params.append("offset", ((currentPage - 1) * 12).toString());
        
        // Add special filters
        Object.keys(currentFilters).forEach(key => {
            params.append(key, currentFilters[key]);
        });
        
        const response = await fetch(`/api/inventory/items?${params.toString()}`);
        if (response.ok) {
            const data = await response.json();
            itemsData = data.items || [];
            
            renderItems(itemsData);
            renderPagination(data.pagination);
            updateResultsInfo(data.pagination);
        } else {
            throw new Error("Failed to load items");
        }
    } catch (error) {
        console.error("Error loading items:", error);
        document.getElementById("itemsContainer").innerHTML = `
            <div class="text-center py-5">
                <i class="bi bi-exclamation-circle text-danger fs-1"></i>
                <h4 class="mt-3">Error Loading Items</h4>
                <p class="text-muted">Please try again later.</p>
                <button class="btn btn-primary" onclick="loadItems()">Retry</button>
            </div>
        `;
    }
}

function renderItems(items) {
    const container = document.getElementById("itemsContainer");
    
    if (items.length === 0) {
        container.innerHTML = `
            <div class="text-center py-5">
                <i class="bi bi-inbox fs-1 text-muted"></i>
                <h4 class="mt-3">No Items Found</h4>
                <p class="text-muted">Try adjusting your search criteria.</p>
            </div>
        `;
        return;
    }
    
    if (currentViewMode === "grid") {
        renderGridView(items, container);
    } else {
        renderListView(items, container);
    }
}

function renderGridView(items, container) {
    container.innerHTML = `
        <div class="row">
            ${items.map(item => `
                <div class="col-md-6 col-lg-4 col-xl-3 mb-4">
                    <div class="card h-100 shadow-sm item-card" data-item-id="${item.id}">
                        <div class="position-relative">
                            ${item.photo_path ? 
                                `<img src="/${item.photo_path}" class="card-img-top" style="height: 200px; object-fit: cover;" alt="${escapeHtml(item.name)}">` :
                                `<div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="bi bi-box-seam text-muted" style="font-size: 3rem;"></i>
                                </div>`
                            }
                            <div class="position-absolute top-0 end-0 m-2">
                                ${getStatusBadge(item.status)}
                            </div>
                            ${item.is_low_stock ? 
                                `<div class="position-absolute top-0 start-0 m-2">
                                    <span class="badge bg-warning">Low Stock</span>
                                </div>` : ''
                            }
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title">${escapeHtml(item.name)}</h6>
                            <p class="card-text text-muted small flex-grow-1">
                                ${item.category_name ? `<i class="bi bi-tag me-1"></i>${escapeHtml(item.category_name)}<br>` : ''}
                                ${item.location_name ? `<i class="bi bi-geo-alt me-1"></i>${escapeHtml(item.location_name)}<br>` : ''}
                                ${item.serial_number ? `<i class="bi bi-hash me-1"></i>${escapeHtml(item.serial_number)}` : ''}
                            </p>
                            <div class="mt-auto">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">Qty: ${item.quantity}</small>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick="viewItemDetails(${item.id})">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        ${item.status === 'Available' ? 
                                            `<button class="btn btn-success" onclick="requestItem(${item.id})">
                                                <i class="bi bi-plus-circle"></i>
                                            </button>` : ''
                                        }
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('')}
        </div>
    `;
}

function renderListView(items, container) {
    container.innerHTML = `
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Item</th>
                            <th>Category</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Quantity</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${items.map(item => `
                            <tr class="item-row" data-item-id="${item.id}">
                                <td>
                                    <div class="d-flex align-items-center">
                                        ${item.photo_path ? 
                                            `<img src="/${item.photo_path}" class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;" alt="${escapeHtml(item.name)}">` :
                                            `<div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                <i class="bi bi-box-seam text-muted"></i>
                                            </div>`
                                        }
                                        <div>
                                            <div class="fw-medium">${escapeHtml(item.name)}</div>
                                            ${item.serial_number ? `<small class="text-muted">${escapeHtml(item.serial_number)}</small>` : ''}
                                            ${item.is_low_stock ? `<br><span class="badge bg-warning badge-sm">Low Stock</span>` : ''}
                                        </div>
                                    </div>
                                </td>
                                <td>${item.category_name ? escapeHtml(item.category_name) : '-'}</td>
                                <td>${item.location_name ? escapeHtml(item.location_name) : '-'}</td>
                                <td>${getStatusBadge(item.status)}</td>
                                <td>${item.quantity}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick="viewItemDetails(${item.id})" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        ${item.status === 'Available' ? 
                                            `<button class="btn btn-success" onclick="requestItem(${item.id})" title="Request Item">
                                                <i class="bi bi-plus-circle"></i>
                                            </button>` : ''
                                        }
                                    </div>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        </div>
    `;
}

function renderPagination(pagination) {
    const nav = document.getElementById("paginationNav");
    const list = document.getElementById("paginationList");
    
    if (pagination.total <= pagination.limit) {
        nav.style.display = "none";
        return;
    }
    
    nav.style.display = "block";
    
    const totalPages = Math.ceil(pagination.total / pagination.limit);
    const currentPageNum = Math.floor(pagination.offset / pagination.limit) + 1;
    
    let paginationHTML = "";
    
    // Previous button
    paginationHTML += `
        <li class="page-item ${currentPageNum === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${currentPageNum - 1}); return false;">
                <i class="bi bi-chevron-left"></i>
            </a>
        </li>
    `;
    
    // Page numbers
    const startPage = Math.max(1, currentPageNum - 2);
    const endPage = Math.min(totalPages, currentPageNum + 2);
    
    if (startPage > 1) {
        paginationHTML += `<li class="page-item"><a class="page-link" href="#" onclick="changePage(1); return false;">1</a></li>`;
        if (startPage > 2) {
            paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }
    
    for (let i = startPage; i <= endPage; i++) {
        paginationHTML += `
            <li class="page-item ${i === currentPageNum ? 'active' : ''}">
                <a class="page-link" href="#" onclick="changePage(${i}); return false;">${i}</a>
            </li>
        `;
    }
    
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
        paginationHTML += `<li class="page-item"><a class="page-link" href="#" onclick="changePage(${totalPages}); return false;">${totalPages}</a></li>`;
    }
    
    // Next button
    paginationHTML += `
        <li class="page-item ${currentPageNum === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${currentPageNum + 1}); return false;">
                <i class="bi bi-chevron-right"></i>
            </a>
        </li>
    `;
    
    list.innerHTML = paginationHTML;
}

function updateResultsInfo(pagination) {
    const info = document.getElementById("resultsInfo");
    const start = pagination.offset + 1;
    const end = Math.min(pagination.offset + pagination.limit, pagination.total);
    
    info.innerHTML = `Showing ${start}-${end} of ${pagination.total} items`;
}

function changePage(page) {
    currentPage = page;
    loadItems();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function quickFilter(type) {
    currentFilters = {};
    currentPage = 1;
    
    if (type === 'available') {
        document.getElementById("statusFilter").value = "Available";
    } else if (type === 'low_stock') {
        currentFilters.low_stock = "1";
    }
    
    loadItems();
}

function clearFilters() {
    document.getElementById("searchForm").reset();
    currentFilters = {};
    currentPage = 1;
    loadItems();
}

async function viewItemDetails(itemId) {
    try {
        const response = await fetch(`/api/inventory/items/${itemId}`);
        if (response.ok) {
            const data = await response.json();
            renderItemDetails(data.item);
            
            const modal = new bootstrap.Modal(document.getElementById("itemDetailModal"));
            modal.show();
        } else {
            throw new Error("Failed to load item details");
        }
    } catch (error) {
        console.error("Error loading item details:", error);
        showAlert("Failed to load item details", "danger");
    }
}

function renderItemDetails(item) {
    const content = document.getElementById("itemDetailContent");
    const requestBtn = document.getElementById("requestItemBtn");
    
    content.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                ${item.photo_path ? 
                    `<img src="/${item.photo_path}" class="img-fluid rounded" alt="${escapeHtml(item.name)}">` :
                    `<div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 300px;">
                        <i class="bi bi-box-seam text-muted" style="font-size: 4rem;"></i>
                    </div>`
                }
            </div>
            <div class="col-md-6">
                <h4>${escapeHtml(item.name)}</h4>
                ${item.description ? `<p class="text-muted">${escapeHtml(item.description)}</p>` : ''}
                
                <table class="table table-sm">
                    <tr>
                        <td><strong>Status:</strong></td>
                        <td>${getStatusBadge(item.status)}</td>
                    </tr>
                    <tr>
                        <td><strong>Quantity:</strong></td>
                        <td>${item.quantity} ${item.is_low_stock ? '<span class="badge bg-warning ms-2">Low Stock</span>' : ''}</td>
                    </tr>
                    ${item.category_name ? `
                    <tr>
                        <td><strong>Category:</strong></td>
                        <td>${escapeHtml(item.category_name)}</td>
                    </tr>
                    ` : ''}
                    ${item.location_name ? `
                    <tr>
                        <td><strong>Location:</strong></td>
                        <td>${escapeHtml(item.location_name)}</td>
                    </tr>
                    ` : ''}
                    ${item.serial_number ? `
                    <tr>
                        <td><strong>Serial Number:</strong></td>
                        <td>${escapeHtml(item.serial_number)}</td>
                    </tr>
                    ` : ''}
                    ${item.purchase_date ? `
                    <tr>
                        <td><strong>Purchase Date:</strong></td>
                        <td>${formatDate(item.purchase_date)}</td>
                    </tr>
                    ` : ''}
                    ${item.warranty_expiry ? `
                    <tr>
                        <td><strong>Warranty Expires:</strong></td>
                        <td>${formatDate(item.warranty_expiry)}</td>
                    </tr>
                    ` : ''}
                    ${item.condition_notes ? `
                    <tr>
                        <td><strong>Condition:</strong></td>
                        <td>${escapeHtml(item.condition_notes)}</td>
                    </tr>
                    ` : ''}
                </table>
            </div>
        </div>
    `;
    
    // Show/hide request button
    if (item.status === 'Available') {
        requestBtn.style.display = "inline-block";
        requestBtn.onclick = () => requestItem(item.id);
    } else {
        requestBtn.style.display = "none";
    }
}

function requestItem(itemId) {
    // Close modal if open
    const modal = bootstrap.Modal.getInstance(document.getElementById("itemDetailModal"));
    if (modal) {
        modal.hide();
    }
    
    // Redirect to request creation page with item pre-selected
    window.location.href = `/requests/create?item_id=${itemId}`;
}

function getStatusBadge(status) {
    const statusColors = {
        "Available": "success",
        "Checked Out": "primary",
        "Reserved": "warning",
        "Under Repair": "info",
        "Lost/Stolen": "danger",
        "Retired": "secondary"
    };
    
    const color = statusColors[status] || "secondary";
    return `<span class="badge bg-${color}">${status}</span>`;
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString();
}

function escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
}
</script>
';

include __DIR__ . '/../layout.php';
?>

