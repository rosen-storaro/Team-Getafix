<?php
$title = 'Item Details - School Inventory Management System';
ob_start();

// Get item ID from URL
$itemId = $_GET['id'] ?? null;
if (!$itemId) {
    header('Location: /inventory');
    exit;
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Item Details</h1>
                    <p class="text-muted mb-0">View detailed information about this inventory item</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="/inventory" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Inventory
                    </a>
                    <?php if ($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Super-admin'): ?>
                    <button type="button" class="btn btn-primary" onclick="editItem()">
                        <i class="bi bi-pencil me-2"></i>Edit Item
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div id="loadingSpinner" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2 text-muted">Loading item details...</p>
    </div>
    
    <div id="itemDetails" style="display: none;">
        <!-- Item details will be loaded here -->
    </div>
    
    <div id="errorMessage" style="display: none;" class="alert alert-danger">
        <h5>Error Loading Item</h5>
        <p id="errorText"></p>
        <a href="/inventory" class="btn btn-outline-danger">Return to Inventory</a>
    </div>
</div>

<?php
$content = ob_get_clean();

$scripts = '
<script>
const itemId = ' . json_encode($itemId) . ';

document.addEventListener("DOMContentLoaded", function() {
    loadItemDetails();
});

async function loadItemDetails() {
    try {
        const response = await fetch(`/api/inventory/items/${itemId}`);
        const data = await response.json();
        
        if (response.ok) {
            displayItemDetails(data);
        } else {
            throw new Error(data.error || "Failed to load item details");
        }
    } catch (error) {
        console.error("Error loading item details:", error);
        showError(error.message);
    }
}

function displayItemDetails(item) {
    const container = document.getElementById("itemDetails");
    const loading = document.getElementById("loadingSpinner");
    
    loading.style.display = "none";
    
    container.innerHTML = `
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">${escapeHtml(item.name)}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Basic Information</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Item ID:</strong></td>
                                        <td>${item.id}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Name:</strong></td>
                                        <td>${escapeHtml(item.name)}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Category:</strong></td>
                                        <td>${escapeHtml(item.category_name || "N/A")}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Location:</strong></td>
                                        <td>${escapeHtml(item.location_name || "N/A")}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Serial Number:</strong></td>
                                        <td>${escapeHtml(item.serial_number || "N/A")}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6>Status & Availability</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Status:</strong></td>
                                        <td><span class="badge bg-${getStatusColor(item.status)}">${item.status}</span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Condition:</strong></td>
                                        <td>${escapeHtml(item.condition_notes || "N/A")}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Quantity:</strong></td>
                                        <td>${item.quantity}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Low Stock Threshold:</strong></td>
                                        <td>${item.low_stock_threshold}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Sensitive Level:</strong></td>
                                        <td><span class="badge bg-${getSensitiveColor(item.sensitive_level)}">${item.sensitive_level}</span></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        ${item.description ? `
                        <div class="mt-3">
                            <h6>Description</h6>
                            <p>${escapeHtml(item.description)}</p>
                        </div>
                        ` : ""}
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <h6>Purchase Information</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Purchase Date:</strong></td>
                                        <td>${item.purchase_date || "N/A"}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Purchase Price:</strong></td>
                                        <td>${item.purchase_price ? "$" + parseFloat(item.purchase_price).toFixed(2) : "N/A"}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Warranty Expiry:</strong></td>
                                        <td>${item.warranty_expiry || "N/A"}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6>System Information</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Created:</strong></td>
                                        <td>${new Date(item.created_at).toLocaleString()}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Last Updated:</strong></td>
                                        <td>${new Date(item.updated_at).toLocaleString()}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Created By:</strong></td>
                                        <td>${escapeHtml(item.created_by_name || "N/A")}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Item Photo</h6>
                    </div>
                    <div class="card-body text-center">
                        ${item.photo_path ? 
                            `<img src="${item.photo_path}" alt="${escapeHtml(item.name)}" class="img-fluid rounded" style="max-height: 300px;">` :
                            `<div class="bg-light rounded p-4" style="height: 200px; display: flex; align-items: center; justify-content: center;">
                                <div class="text-center">
                                    <i class="bi bi-image" style="font-size: 3rem; color: #6c757d;"></i>
                                    <p class="mt-2 text-muted">No photo available</p>
                                </div>
                            </div>`
                        }
                    </div>
                </div>
                
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Quick Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            ${item.status === "Available" ? 
                                `<a href="/requests/create?item_id=${item.id}" class="btn btn-success">
                                    <i class="bi bi-plus-circle me-2"></i>Request Item
                                </a>` : ""
                            }
                            <button type="button" class="btn btn-outline-primary" onclick="generateQR()">
                                <i class="bi bi-qr-code me-2"></i>Generate QR Code
                            </button>
                            <button type="button" class="btn btn-outline-info" onclick="viewHistory()">
                                <i class="bi bi-clock-history me-2"></i>View History
                            </button>
                        </div>
                    </div>
                </div>
                
                ${item.quantity <= item.low_stock_threshold ? `
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Low Stock Alert!</strong><br>
                    This item is running low on stock.
                </div>
                ` : ""}
            </div>
        </div>
    `;
    
    container.style.display = "block";
}

function showError(message) {
    document.getElementById("loadingSpinner").style.display = "none";
    document.getElementById("errorText").textContent = message;
    document.getElementById("errorMessage").style.display = "block";
}

function getStatusColor(status) {
    const colors = {
        "Available": "success",
        "Checked Out": "warning",
        "Reserved": "info",
        "Under Repair": "secondary",
        "Lost/Stolen": "danger",
        "Retired": "dark"
    };
    return colors[status] || "secondary";
}

function getSensitiveColor(level) {
    const colors = {
        "No": "secondary",
        "Sensitive": "warning",
        "High Value": "danger"
    };
    return colors[level] || "secondary";
}

function editItem() {
    window.location.href = `/inventory/edit/${itemId}`;
}

function generateQR() {
    // Implement QR code generation
    alert("QR code generation would be implemented here");
}

function viewHistory() {
    // Implement history viewing
    alert("History viewing would be implemented here");
}

function escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
}
</script>
';

include __DIR__ . '/../../layout.php';
?>

