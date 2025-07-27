<?php
$title = 'New Request - School Inventory Management System';
ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">New Borrow Request</h1>
                    <p class="text-muted mb-0">Request items from the inventory</p>
                </div>
                <a href="/requests" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to Requests
                </a>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Request Details</h5>
                </div>
                <div class="card-body">
                    <div id="alertContainer"></div>
                    
                    <form id="requestForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="item_id" class="form-label">Item *</label>
                                <select class="form-select" id="item_id" name="item_id" required>
                                    <option value="">Select Item</option>
                                </select>
                                <div class="form-text">Only available items are shown</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="quantity" class="form-label">Quantity *</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" min="1" value="1" required>
                                <div class="form-text">Available: <span id="availableQuantity">-</span></div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="borrow_date" class="form-label">Borrow Date *</label>
                                <input type="date" class="form-control" id="borrow_date" name="borrow_date" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="return_date" class="form-label">Expected Return Date *</label>
                                <input type="date" class="form-control" id="return_date" name="return_date" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="purpose" class="form-label">Purpose/Reason *</label>
                            <textarea class="form-control" id="purpose" name="purpose" rows="3" required placeholder="Describe why you need this item..."></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Additional Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Any additional information..."></textarea>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="bi bi-send me-2"></i>Submit Request
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
            <div class="card" id="itemDetailsCard" style="display: none;">
                <div class="card-header">
                    <h5 class="card-title mb-0">Item Details</h5>
                </div>
                <div class="card-body">
                    <div id="itemDetails">
                        <!-- Item details will be loaded here -->
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Request Guidelines</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="bi bi-info-circle text-info me-2"></i>
                            <small>Requests must be approved before borrowing</small>
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-info-circle text-info me-2"></i>
                            <small>Sensitive items require super-admin approval</small>
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-info-circle text-info me-2"></i>
                            <small>Return items in the same condition</small>
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-info-circle text-info me-2"></i>
                            <small>Late returns may affect future requests</small>
                        </li>
                        <li>
                            <i class="bi bi-info-circle text-info me-2"></i>
                            <small>Contact admin for urgent requests</small>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="/inventory" class="btn btn-outline-primary">
                            <i class="bi bi-search me-2"></i>Browse Items
                        </a>
                        <a href="/requests" class="btn btn-outline-secondary">
                            <i class="bi bi-list me-2"></i>My Requests
                        </a>
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
document.addEventListener("DOMContentLoaded", function() {
    loadAvailableItems();
    setMinDates();
    
    document.getElementById("item_id").addEventListener("change", handleItemChange);
    document.getElementById("quantity").addEventListener("input", validateQuantity);
    document.getElementById("borrow_date").addEventListener("change", validateDates);
    document.getElementById("return_date").addEventListener("change", validateDates);
    document.getElementById("requestForm").addEventListener("submit", handleSubmit);
});

function setMinDates() {
    const today = new Date().toISOString().split("T")[0];
    document.getElementById("borrow_date").min = today;
    document.getElementById("return_date").min = today;
}

async function loadAvailableItems() {
    try {
        const response = await fetch("/api/inventory/available");
        const data = await response.json();
        
        const select = document.getElementById("item_id");
        select.innerHTML = "<option value=\"\">Select Item</option>";
        
        data.items.forEach(item => {
            const option = document.createElement("option");
            option.value = item.id;
            option.textContent = `${item.name} (${item.category_name}) - Available: ${item.available_quantity}`;
            option.dataset.item = JSON.stringify(item);
            select.appendChild(option);
        });
    } catch (error) {
        console.error("Error loading items:", error);
        showAlert("Failed to load available items", "danger");
    }
}

function handleItemChange() {
    const select = document.getElementById("item_id");
    const selectedOption = select.options[select.selectedIndex];
    
    if (selectedOption.value) {
        const item = JSON.parse(selectedOption.dataset.item);
        showItemDetails(item);
        updateAvailableQuantity(item.available_quantity);
        validateQuantity();
    } else {
        hideItemDetails();
        updateAvailableQuantity(0);
    }
}

function showItemDetails(item) {
    const card = document.getElementById("itemDetailsCard");
    const details = document.getElementById("itemDetails");
    
    details.innerHTML = `
        <div class="text-center mb-3">
            ${item.photo_url ? 
                `<img src="${item.photo_url}" alt="${item.name}" class="img-fluid rounded" style="max-height: 150px;">` :
                `<div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 150px;">
                    <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                </div>`
            }
        </div>
        <h6 class="fw-bold">${escapeHtml(item.name)}</h6>
        <p class="text-muted small mb-2">${escapeHtml(item.description || "No description")}</p>
        <div class="row g-2 small">
            <div class="col-6"><strong>Category:</strong></div>
            <div class="col-6">${escapeHtml(item.category_name)}</div>
            <div class="col-6"><strong>Location:</strong></div>
            <div class="col-6">${escapeHtml(item.location_name)}</div>
            <div class="col-6"><strong>Condition:</strong></div>
            <div class="col-6">
                <span class="badge bg-${getConditionColor(item.condition)}">${escapeHtml(item.condition)}</span>
            </div>
            <div class="col-6"><strong>Available:</strong></div>
            <div class="col-6">${item.available_quantity}</div>
            ${item.sensitive_level !== "No" ? 
                `<div class="col-12 mt-2">
                    <span class="badge bg-warning text-dark">
                        <i class="bi bi-exclamation-triangle me-1"></i>${item.sensitive_level}
                    </span>
                </div>` : ""
            }
        </div>
    `;
    
    card.style.display = "block";
}

function hideItemDetails() {
    document.getElementById("itemDetailsCard").style.display = "none";
}

function updateAvailableQuantity(quantity) {
    document.getElementById("availableQuantity").textContent = quantity;
    document.getElementById("quantity").max = quantity;
}

function validateQuantity() {
    const quantityInput = document.getElementById("quantity");
    const maxQuantity = parseInt(quantityInput.max) || 0;
    const currentQuantity = parseInt(quantityInput.value) || 0;
    
    if (currentQuantity > maxQuantity) {
        quantityInput.setCustomValidity(`Maximum available quantity is ${maxQuantity}`);
        quantityInput.classList.add("is-invalid");
    } else {
        quantityInput.setCustomValidity("");
        quantityInput.classList.remove("is-invalid");
    }
}

function validateDates() {
    const borrowDate = document.getElementById("borrow_date").value;
    const returnDate = document.getElementById("return_date").value;
    const returnInput = document.getElementById("return_date");
    
    if (borrowDate && returnDate) {
        if (new Date(returnDate) <= new Date(borrowDate)) {
            returnInput.setCustomValidity("Return date must be after borrow date");
            returnInput.classList.add("is-invalid");
        } else {
            returnInput.setCustomValidity("");
            returnInput.classList.remove("is-invalid");
        }
    }
    
    // Update minimum return date
    if (borrowDate) {
        const minReturnDate = new Date(borrowDate);
        minReturnDate.setDate(minReturnDate.getDate() + 1);
        returnInput.min = minReturnDate.toISOString().split("T")[0];
    }
}

async function handleSubmit(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById("submitBtn");
    const originalText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Submitting Request...`;
    
    try {
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());
        
        const response = await fetch("/api/requests", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (response.ok) {
            showAlert("Request submitted successfully! You will be notified when it is reviewed.", "success");
            e.target.reset();
            hideItemDetails();
            updateAvailableQuantity(0);
            setTimeout(() => {
                window.location.href = "/requests";
            }, 2000);
        } else {
            throw new Error(result.error || "Failed to submit request");
        }
        
    } catch (error) {
        console.error("Submit request error:", error);
        showAlert(error.message, "danger");
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
}

function getConditionColor(condition) {
    const colors = {
        "New": "success",
        "Good": "primary", 
        "Fair": "warning",
        "Poor": "danger",
        "Damaged": "dark"
    };
    return colors[condition] || "secondary";
}

function escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
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

