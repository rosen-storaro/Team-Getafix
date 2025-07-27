<?php
$title = 'Add Item - School Inventory Management System';
ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Add New Item</h1>
                    <p class="text-muted mb-0">Add a new item to the inventory</p>
                </div>
                <a href="/inventory" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to Inventory
                </a>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Item Information</h5>
                </div>
                <div class="card-body">
                    <div id="alertContainer"></div>
                    
                    <form id="addItemForm" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Item Name *</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="serial_number" class="form-label">Serial Number</label>
                                <input type="text" class="form-control" id="serial_number" name="serial_number">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="category_id" class="form-label">Category *</label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">Select Category</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="location_id" class="form-label">Location *</label>
                                <select class="form-select" id="location_id" name="location_id" required>
                                    <option value="">Select Location</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="quantity" class="form-label">Quantity *</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" min="1" value="1" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="low_stock_threshold" class="form-label">Low Stock Threshold</label>
                                <input type="number" class="form-control" id="low_stock_threshold" name="low_stock_threshold" min="0" value="5">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="purchase_price" class="form-label">Purchase Price</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="purchase_price" name="purchase_price" step="0.01" min="0">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="condition" class="form-label">Condition *</label>
                                <select class="form-select" id="condition" name="condition" required>
                                    <option value="">Select Condition</option>
                                    <option value="New">New</option>
                                    <option value="Good">Good</option>
                                    <option value="Fair">Fair</option>
                                    <option value="Poor">Poor</option>
                                    <option value="Damaged">Damaged</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="sensitive_level" class="form-label">Sensitive Level *</label>
                                <select class="form-select" id="sensitive_level" name="sensitive_level" required>
                                    <option value="">Select Level</option>
                                    <option value="No">No</option>
                                    <option value="Sensitive">Sensitive</option>
                                    <option value="High Value">High Value</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="purchase_date" class="form-label">Purchase Date</label>
                                <input type="date" class="form-control" id="purchase_date" name="purchase_date">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="warranty_expiry" class="form-label">Warranty Expiry</label>
                                <input type="date" class="form-control" id="warranty_expiry" name="warranty_expiry">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="photo" class="form-label">Item Photo</label>
                            <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                            <div class="form-text">Upload a photo of the item (optional). Max size: 5MB</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Additional Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="bi bi-plus-circle me-2"></i>Add Item
                            </button>
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise me-2"></i>Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-primary" onclick="showCategoryModal()">
                            <i class="bi bi-plus me-2"></i>Add New Category
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="showLocationModal()">
                            <i class="bi bi-plus me-2"></i>Add New Location
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Tips</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="bi bi-lightbulb text-warning me-2"></i>
                            <small>Use descriptive names for easy searching</small>
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-lightbulb text-warning me-2"></i>
                            <small>Serial numbers help track individual items</small>
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-lightbulb text-warning me-2"></i>
                            <small>Set appropriate low stock thresholds</small>
                        </li>
                        <li>
                            <i class="bi bi-lightbulb text-warning me-2"></i>
                            <small>Photos help with identification</small>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="categoryForm">
                    <div class="mb-3">
                        <label for="categoryName" class="form-label">Category Name *</label>
                        <input type="text" class="form-control" id="categoryName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="categoryDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="categoryDescription" name="description" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveCategory()">Save Category</button>
            </div>
        </div>
    </div>
</div>

<!-- Location Modal -->
<div class="modal fade" id="locationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Location</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="locationForm">
                    <div class="mb-3">
                        <label for="locationName" class="form-label">Location Name *</label>
                        <input type="text" class="form-control" id="locationName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="locationDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="locationDescription" name="description" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveLocation()">Save Location</button>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

$scripts = '
<script>
document.addEventListener("DOMContentLoaded", function() {
    loadCategories();
    loadLocations();
    
    document.getElementById("addItemForm").addEventListener("submit", handleSubmit);
});

async function loadCategories() {
    try {
        const response = await fetch("/api/inventory/categories");
        const data = await response.json();
        
        const select = document.getElementById("category_id");
        select.innerHTML = "<option value=\"\">Select Category</option>";
        
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

async function loadLocations() {
    try {
        const response = await fetch("/api/inventory/locations");
        const data = await response.json();
        
        const select = document.getElementById("location_id");
        select.innerHTML = "<option value=\"\">Select Location</option>";
        
        data.locations.forEach(location => {
            const option = document.createElement("option");
            option.value = location.id;
            option.textContent = location.name;
            select.appendChild(option);
        });
    } catch (error) {
        console.error("Error loading locations:", error);
    }
}

async function handleSubmit(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById("submitBtn");
    const originalText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Adding Item...`;
    
    try {
        const formData = new FormData(e.target);
        
        const response = await fetch("/api/inventory/items", {
            method: "POST",
            body: formData
        });
        
        const result = await response.json();
        
        if (response.ok) {
            showAlert("Item added successfully!", "success");
            e.target.reset();
            setTimeout(() => {
                window.location.href = "/inventory";
            }, 2000);
        } else {
            throw new Error(result.error || "Failed to add item");
        }
        
    } catch (error) {
        console.error("Add item error:", error);
        showAlert(error.message, "danger");
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
}

function showCategoryModal() {
    new bootstrap.Modal(document.getElementById("categoryModal")).show();
}

function showLocationModal() {
    new bootstrap.Modal(document.getElementById("locationModal")).show();
}

async function saveCategory() {
    const form = document.getElementById("categoryForm");
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    
    try {
        const response = await fetch("/api/inventory/categories", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (response.ok) {
            bootstrap.Modal.getInstance(document.getElementById("categoryModal")).hide();
            form.reset();
            loadCategories();
            showAlert("Category added successfully!", "success");
        } else {
            throw new Error(result.error || "Failed to add category");
        }
    } catch (error) {
        console.error("Add category error:", error);
        showAlert(error.message, "danger");
    }
}

async function saveLocation() {
    const form = document.getElementById("locationForm");
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    
    try {
        const response = await fetch("/api/inventory/locations", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (response.ok) {
            bootstrap.Modal.getInstance(document.getElementById("locationModal")).hide();
            form.reset();
            loadLocations();
            showAlert("Location added successfully!", "success");
        } else {
            throw new Error(result.error || "Failed to add location");
        }
    } catch (error) {
        console.error("Add location error:", error);
        showAlert(error.message, "danger");
    }
}

function showAlert(message, type) {
    const container = document.getElementById("alertContainer");
    const alertId = "alert-" + Date.now();
    
    container.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" id="${alertId}" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    if (type === "success") {
        setTimeout(() => {
            const alert = document.getElementById(alertId);
            if (alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    }
}
</script>
';

include __DIR__ . '/../layout.php';
?>

