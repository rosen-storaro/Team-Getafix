<?php
$title = 'Admin Inventory Management - School Inventory Management System';
ob_start();
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Inventory Management</h1>
                    <p class="text-muted">Manage inventory items, categories, and locations</p>
                </div>
                <div>
                    <a href="/inventory/add" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Add Item
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h4 class="mb-0" id="totalItems">0</h4>
                    <p class="mb-0">Total Items</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h4 class="mb-0" id="availableItems">0</h4>
                    <p class="mb-0">Available</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h4 class="mb-0" id="lowStockItems">0</h4>
                    <p class="mb-0">Low Stock</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h4 class="mb-0" id="checkedOutItems">0</h4>
                    <p class="mb-0">Checked Out</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory Management Tabs -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="inventoryTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="items-tab" data-bs-toggle="tab" data-bs-target="#items" type="button" role="tab">
                                <i class="bi bi-box-seam me-2"></i>Items
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="categories-tab" data-bs-toggle="tab" data-bs-target="#categories" type="button" role="tab">
                                <i class="bi bi-tags me-2"></i>Categories
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="locations-tab" data-bs-toggle="tab" data-bs-target="#locations" type="button" role="tab">
                                <i class="bi bi-geo-alt me-2"></i>Locations
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="inventoryTabContent">
                        <!-- Items Tab -->
                        <div class="tab-pane fade show active" id="items" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5>All Items</h5>
                                <div>
                                    <button class="btn btn-outline-secondary me-2" onclick="exportItems()">
                                        <i class="bi bi-download me-2"></i>Export CSV
                                    </button>
                                    <a href="/inventory/add" class="btn btn-primary">
                                        <i class="bi bi-plus me-2"></i>Add Item
                                    </a>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped" id="itemsTable">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Category</th>
                                            <th>Location</th>
                                            <th>Quantity</th>
                                            <th>Status</th>
                                            <th>Condition</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="7" class="text-center">
                                                <div class="spinner-border text-primary" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                                <p class="mt-2 text-muted">Loading items...</p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Categories Tab -->
                        <div class="tab-pane fade" id="categories" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5>Categories</h5>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                                    <i class="bi bi-plus me-2"></i>Add Category
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped" id="categoriesTable">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Description</th>
                                            <th>Items Count</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="4" class="text-center">Loading categories...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Locations Tab -->
                        <div class="tab-pane fade" id="locations" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5>Locations</h5>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLocationModal">
                                    <i class="bi bi-plus me-2"></i>Add Location
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped" id="locationsTable">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Description</th>
                                            <th>Items Count</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="4" class="text-center">Loading locations...</td>
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
    loadInventoryStats();
    loadItems();
    loadCategories();
    loadLocations();
});

async function loadInventoryStats() {
    try {
        const response = await fetch("/api/reports/dashboard", {
            credentials: "same-origin"
        });
        if (response.ok) {
            const data = await response.json();
            const stats = data.stats || {};
            document.getElementById("totalItems").textContent = stats.total_items || 0;
            document.getElementById("availableItems").textContent = stats.available_items || 0;
            document.getElementById("lowStockItems").textContent = stats.low_stock_items || 0;
            document.getElementById("checkedOutItems").textContent = stats.checked_out_items || 0;
        }
    } catch (error) {
        console.error("Error loading stats:", error);
    }
}

async function loadItems() {
    try {
        const response = await fetch("/api/inventory/items", {
            credentials: "same-origin"
        });
        if (response.ok) {
            const data = await response.json();
            renderItemsTable(data.items || []);
        }
    } catch (error) {
        console.error("Error loading items:", error);
    }
}

function renderItemsTable(items) {
    const tbody = document.querySelector("#itemsTable tbody");
    if (items.length === 0) {
        tbody.innerHTML = "<tr><td colspan=\"7\" class=\"text-center text-muted\">No items found</td></tr>";
        return;
    }
    
    tbody.innerHTML = items.map(item => 
        "<tr>" +
            "<td>" + escapeHtml(item.name) + "</td>" +
            "<td>" + escapeHtml(item.category_name || item.category) + "</td>" +
            "<td>" + escapeHtml(item.location_name || item.location) + "</td>" +
            "<td>" + item.quantity + "</td>" +
            "<td><span class=\"badge bg-" + getStatusColor(item.status) + "\">" + item.status + "</span></td>" +
            "<td><span class=\"badge bg-secondary\">" + (item.condition || "undefined") + "</span></td>" +
            "<td>" +
                "<a href=\"/inventory/" + item.id + "\" class=\"btn btn-sm btn-outline-primary me-1\">View</a>" +
                "<button class=\"btn btn-sm btn-outline-warning me-1\" onclick=\"editItem(" + item.id + ")\">Edit</button>" +
                "<button class=\"btn btn-sm btn-outline-danger\" onclick=\"deleteItem(" + item.id + ")\">Delete</button>" +
            "</td>" +
        "</tr>"
    ).join("");
}

function getStatusColor(status) {
    switch(status) {
        case "Available": return "success";
        case "Checked Out": return "warning";
        case "Reserved": return "info";
        case "Under Repair": return "secondary";
        case "Lost/Stolen": return "danger";
        case "Retired": return "dark";
        default: return "secondary";
    }
}

async function loadCategories() {
    try {
        const response = await fetch("/api/inventory/categories", {
            credentials: "same-origin"
        });
        if (response.ok) {
            const data = await response.json();
            renderCategoriesTable(data.categories || []);
        }
    } catch (error) {
        console.error("Error loading categories:", error);
    }
}

function renderCategoriesTable(categories) {
    const tbody = document.querySelector("#categoriesTable tbody");
    if (categories.length === 0) {
        tbody.innerHTML = "<tr><td colspan=\"4\" class=\"text-center text-muted\">No categories found</td></tr>";
        return;
    }
    
    tbody.innerHTML = categories.map(category => 
        "<tr>" +
            "<td>" + escapeHtml(category.name) + "</td>" +
            "<td>" + escapeHtml(category.description || "") + "</td>" +
            "<td>" + (category.items_count || 0) + "</td>" +
            "<td>" +
                "<button class=\"btn btn-sm btn-outline-warning me-1\" onclick=\"editCategory(" + category.id + ")\">Edit</button>" +
                "<button class=\"btn btn-sm btn-outline-danger\" onclick=\"deleteCategory(" + category.id + ")\">Delete</button>" +
            "</td>" +
        "</tr>"
    ).join("");
}

async function loadLocations() {
    try {
        const response = await fetch("/api/inventory/locations", {
            credentials: "same-origin"
        });
        if (response.ok) {
            const data = await response.json();
            renderLocationsTable(data.locations || []);
        }
    } catch (error) {
        console.error("Error loading locations:", error);
    }
}

function renderLocationsTable(locations) {
    const tbody = document.querySelector("#locationsTable tbody");
    if (locations.length === 0) {
        tbody.innerHTML = "<tr><td colspan=\"4\" class=\"text-center text-muted\">No locations found</td></tr>";
        return;
    }
    
    tbody.innerHTML = locations.map(location => 
        "<tr>" +
            "<td>" + escapeHtml(location.name) + "</td>" +
            "<td>" + escapeHtml(location.description || "") + "</td>" +
            "<td>" + (location.items_count || 0) + "</td>" +
            "<td>" +
                "<button class=\"btn btn-sm btn-outline-warning me-1\" onclick=\"editLocation(" + location.id + ")\">Edit</button>" +
                "<button class=\"btn btn-sm btn-outline-danger\" onclick=\"deleteLocation(" + location.id + ")\">Delete</button>" +
            "</td>" +
        "</tr>"
    ).join("");
}

function escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
}

function exportItems() {
    window.open("/api/reports/export?type=inventory", "_blank");
}
</script>';

include __DIR__ . '/../layout.php';
?>

