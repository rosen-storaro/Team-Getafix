<?php
$title = 'QR Code Management - School Inventory Management System';
ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">QR Code Management</h1>
                    <p class="text-muted mb-0">Generate and manage QR codes for inventory items</p>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-success" onclick="generateAllQRCodes()">
                        <i class="bi bi-qr-code me-2"></i>Generate All QR Codes
                    </button>
                    <button type="button" class="btn btn-primary" onclick="showQRScanner()">
                        <i class="bi bi-camera me-2"></i>Scan QR Code
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- QR Code Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-primary">Total Items</h5>
                    <h2 class="mb-0" id="totalItems">-</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-success">QR Codes Generated</h5>
                    <h2 class="mb-0" id="qrGenerated">-</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-warning">Pending Generation</h5>
                    <h2 class="mb-0" id="qrPending">-</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-info">Last Generated</h5>
                    <p class="mb-0" id="lastGenerated">-</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- QR Code Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="d-grid">
                                <button type="button" class="btn btn-outline-primary" onclick="showBulkGenerator()">
                                    <i class="bi bi-collection me-2"></i>Bulk Generate QR Codes
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-grid">
                                <button type="button" class="btn btn-outline-success" onclick="downloadAllQRCodes()">
                                    <i class="bi bi-download me-2"></i>Download All QR Codes
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-grid">
                                <button type="button" class="btn btn-outline-info" onclick="printQRCodes()">
                                    <i class="bi bi-printer me-2"></i>Print QR Code Labels
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Items with QR Codes -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Items & QR Codes</h5>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshItems()">
                            <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                        </button>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="showOnlyWithoutQR">
                            <label class="form-check-label" for="showOnlyWithoutQR">
                                Show only items without QR codes
                            </label>
                        </div>
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
                                        <th>Serial Number</th>
                                        <th>QR Code Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsTableBody">
                                    <!-- Items will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- QR Code Preview Modal -->
<div class="modal fade" id="qrPreviewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">QR Code Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center" id="qrPreviewBody">
                <!-- QR code will be displayed here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="downloadQRBtn">
                    <i class="bi bi-download me-2"></i>Download
                </button>
            </div>
        </div>
    </div>
</div>

<!-- QR Scanner Modal -->
<div class="modal fade" id="qrScannerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">QR Code Scanner</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div id="qrScannerContainer">
                        <p class="text-muted">QR Code scanner would be implemented here using a camera library like QuaggaJS or ZXing.</p>
                        <div class="border rounded p-4 mb-3" style="height: 300px; display: flex; align-items: center; justify-content: center; background-color: #f8f9fa;">
                            <div class="text-center">
                                <i class="bi bi-camera" style="font-size: 4rem; color: #6c757d;"></i>
                                <p class="mt-2 text-muted">Camera View</p>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="manualQRInput" class="form-label">Or enter QR code data manually:</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="manualQRInput" placeholder="Paste QR code data here...">
                                <button class="btn btn-primary" type="button" onclick="processManualQR()">
                                    <i class="bi bi-search me-1"></i>Process
                                </button>
                            </div>
                        </div>
                    </div>
                    <div id="qrScanResult" style="display: none;">
                        <!-- Scan result will be displayed here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Generation Modal -->
<div class="modal fade" id="bulkGeneratorModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk QR Code Generation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Select items to generate QR codes for:</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="selectAllItems">
                        <label class="form-check-label" for="selectAllItems">
                            <strong>Select All Items</strong>
                        </label>
                    </div>
                    <hr>
                    <div id="bulkItemsList" style="max-height: 300px; overflow-y: auto;">
                        <!-- Items list will be loaded here -->
                    </div>
                </div>
                <div id="bulkProgress" style="display: none;">
                    <div class="progress mb-3">
                        <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                    </div>
                    <p class="text-center mb-0">Generating QR codes... <span id="progressText">0/0</span></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="startBulkGeneration">
                    <i class="bi bi-qr-code me-2"></i>Generate Selected
                </button>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

$scripts = '
<script>
let currentItems = [];

document.addEventListener("DOMContentLoaded", function() {
    loadQRStats();
    loadItems();
    
    document.getElementById("showOnlyWithoutQR").addEventListener("change", filterItems);
    document.getElementById("selectAllItems").addEventListener("change", toggleSelectAll);
    document.getElementById("startBulkGeneration").addEventListener("click", startBulkGeneration);
});

async function loadQRStats() {
    try {
        const response = await fetch("/api/qrcode/stats");
        const data = await response.json();
        
        if (response.ok) {
            document.getElementById("totalItems").textContent = data.total_items;
            document.getElementById("qrGenerated").textContent = data.qr_generated;
            document.getElementById("qrPending").textContent = data.qr_pending;
            document.getElementById("lastGenerated").textContent = data.last_generated || "Never";
        }
    } catch (error) {
        console.error("Error loading QR stats:", error);
    }
}

async function loadItems() {
    try {
        showLoading();
        
        const response = await fetch("/api/inventory/items?include_qr=true");
        const data = await response.json();
        
        if (response.ok) {
            currentItems = data.items;
            displayItems(currentItems);
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
}

function displayItems(items) {
    const tbody = document.getElementById("itemsTableBody");
    const container = document.getElementById("itemsContainer");
    const loading = document.getElementById("loadingSpinner");
    
    loading.style.display = "none";
    
    tbody.innerHTML = items.map(item => {
        const hasQR = item.qr_code_url ? true : false;
        
        return `
            <tr>
                <td>
                    <div class="d-flex align-items-center">
                        ${item.photo_url ? 
                            `<img src="${item.photo_url}" alt="${escapeHtml(item.name)}" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">` :
                            `<div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="bi bi-box text-muted"></i>
                            </div>`
                        }
                        <div>
                            <div class="fw-bold">${escapeHtml(item.name)}</div>
                            <small class="text-muted">ID: ${item.id}</small>
                        </div>
                    </div>
                </td>
                <td>${escapeHtml(item.category_name || "N/A")}</td>
                <td>${escapeHtml(item.serial_number || "N/A")}</td>
                <td>
                    ${hasQR ? 
                        `<span class="badge bg-success">Generated</span>` :
                        `<span class="badge bg-warning">Not Generated</span>`
                    }
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        ${hasQR ? 
                            `<button type="button" class="btn btn-outline-primary" onclick="previewQR(${item.id})">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button type="button" class="btn btn-outline-success" onclick="downloadQR(${item.id})">
                                <i class="bi bi-download"></i>
                            </button>` :
                            `<button type="button" class="btn btn-outline-primary" onclick="generateQR(${item.id})">
                                <i class="bi bi-qr-code"></i> Generate
                            </button>`
                        }
                        <button type="button" class="btn btn-outline-secondary" onclick="regenerateQR(${item.id})">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join("");
    
    container.style.display = "block";
}

function filterItems() {
    const showOnlyWithoutQR = document.getElementById("showOnlyWithoutQR").checked;
    
    if (showOnlyWithoutQR) {
        const filteredItems = currentItems.filter(item => !item.qr_code_url);
        displayItems(filteredItems);
    } else {
        displayItems(currentItems);
    }
}

async function generateQR(itemId) {
    try {
        const response = await fetch(`/api/qrcode/generate/${itemId}`, {
            method: "POST"
        });
        
        const result = await response.json();
        
        if (response.ok) {
            showAlert("QR code generated successfully!", "success");
            loadItems();
            loadQRStats();
        } else {
            throw new Error(result.error || "Failed to generate QR code");
        }
    } catch (error) {
        console.error("Generate QR error:", error);
        showAlert(error.message, "danger");
    }
}

async function previewQR(itemId) {
    try {
        const response = await fetch(`/api/qrcode/${itemId}`);
        const data = await response.json();
        
        if (response.ok) {
            const modalBody = document.getElementById("qrPreviewBody");
            modalBody.innerHTML = `
                <div class="mb-3">
                    <img src="${data.qr_image_url}" alt="QR Code" class="img-fluid" style="max-width: 300px;">
                </div>
                <h6>${escapeHtml(data.item_name)}</h6>
                <p class="text-muted">Item ID: ${data.item_id}</p>
                <p class="small">Scan this QR code to view item details</p>
            `;
            
            document.getElementById("downloadQRBtn").onclick = () => downloadQR(itemId);
            
            new bootstrap.Modal(document.getElementById("qrPreviewModal")).show();
        } else {
            throw new Error(data.error || "Failed to load QR code");
        }
    } catch (error) {
        console.error("Preview QR error:", error);
        showAlert(error.message, "danger");
    }
}

async function downloadQR(itemId) {
    try {
        const response = await fetch(`/api/qrcode/download/${itemId}`);
        
        if (response.ok) {
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement("a");
            a.href = url;
            a.download = `item_${itemId}_qrcode.png`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        } else {
            throw new Error("Download failed");
        }
    } catch (error) {
        console.error("Download QR error:", error);
        showAlert("Failed to download QR code", "danger");
    }
}

async function regenerateQR(itemId) {
    if (!confirm("Are you sure you want to regenerate the QR code for this item?")) {
        return;
    }
    
    await generateQR(itemId);
}

async function generateAllQRCodes() {
    if (!confirm("This will generate QR codes for all items. This may take a while. Continue?")) {
        return;
    }
    
    try {
        const response = await fetch("/api/qrcode/generate-all", {
            method: "POST"
        });
        
        const result = await response.json();
        
        if (response.ok) {
            showAlert(`QR codes generated! Successful: ${result.successful}, Failed: ${result.failed}`, "success");
            loadItems();
            loadQRStats();
        } else {
            throw new Error(result.error || "Failed to generate QR codes");
        }
    } catch (error) {
        console.error("Generate all QR error:", error);
        showAlert(error.message, "danger");
    }
}

function showQRScanner() {
    new bootstrap.Modal(document.getElementById("qrScannerModal")).show();
}

async function processManualQR() {
    const qrData = document.getElementById("manualQRInput").value.trim();
    
    if (!qrData) {
        showAlert("Please enter QR code data", "warning");
        return;
    }
    
    try {
        const response = await fetch("/api/qrcode/scan", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ qr_data: qrData })
        });
        
        const result = await response.json();
        
        if (response.ok) {
            displayScanResult(result);
        } else {
            throw new Error(result.error || "Failed to process QR code");
        }
    } catch (error) {
        console.error("Process QR error:", error);
        showAlert(error.message, "danger");
    }
}

function displayScanResult(result) {
    const resultDiv = document.getElementById("qrScanResult");
    
    resultDiv.innerHTML = `
        <div class="alert alert-success">
            <h6>QR Code Processed Successfully!</h6>
            <p><strong>Type:</strong> ${result.type}</p>
            ${result.item_id ? `<p><strong>Item ID:</strong> ${result.item_id}</p>` : ""}
            ${result.request_id ? `<p><strong>Request ID:</strong> ${result.request_id}</p>` : ""}
            ${result.url ? `<p><strong>URL:</strong> <a href="${result.url}" target="_blank">${result.url}</a></p>` : ""}
        </div>
    `;
    
    resultDiv.style.display = "block";
}

function showBulkGenerator() {
    loadBulkItems();
    new bootstrap.Modal(document.getElementById("bulkGeneratorModal")).show();
}

function loadBulkItems() {
    const container = document.getElementById("bulkItemsList");
    
    container.innerHTML = currentItems.map(item => `
        <div class="form-check">
            <input class="form-check-input bulk-item-checkbox" type="checkbox" value="${item.id}" id="bulk_${item.id}">
            <label class="form-check-label" for="bulk_${item.id}">
                ${escapeHtml(item.name)} 
                <small class="text-muted">(${escapeHtml(item.category_name || "N/A")})</small>
                ${item.qr_code_url ? `<span class="badge bg-success ms-2">Has QR</span>` : `<span class="badge bg-warning ms-2">No QR</span>`}
            </label>
        </div>
    `).join("");
}

function toggleSelectAll() {
    const selectAll = document.getElementById("selectAllItems").checked;
    const checkboxes = document.querySelectorAll(".bulk-item-checkbox");
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll;
    });
}

async function startBulkGeneration() {
    const selectedItems = Array.from(document.querySelectorAll(".bulk-item-checkbox:checked")).map(cb => cb.value);
    
    if (selectedItems.length === 0) {
        showAlert("Please select at least one item", "warning");
        return;
    }
    
    const progressDiv = document.getElementById("bulkProgress");
    const progressBar = progressDiv.querySelector(".progress-bar");
    const progressText = document.getElementById("progressText");
    const startBtn = document.getElementById("startBulkGeneration");
    
    progressDiv.style.display = "block";
    startBtn.disabled = true;
    
    let completed = 0;
    let successful = 0;
    
    for (const itemId of selectedItems) {
        try {
            const response = await fetch(`/api/qrcode/generate/${itemId}`, {
                method: "POST"
            });
            
            if (response.ok) {
                successful++;
            }
        } catch (error) {
            console.error(`Failed to generate QR for item ${itemId}:`, error);
        }
        
        completed++;
        const progress = (completed / selectedItems.length) * 100;
        progressBar.style.width = progress + "%";
        progressText.textContent = `${completed}/${selectedItems.length}`;
    }
    
    showAlert(`Bulk generation completed! Generated: ${successful}/${selectedItems.length}`, "success");
    
    setTimeout(() => {
        bootstrap.Modal.getInstance(document.getElementById("bulkGeneratorModal")).hide();
        loadItems();
        loadQRStats();
    }, 2000);
}

function refreshItems() {
    loadItems();
    loadQRStats();
}

function downloadAllQRCodes() {
    showAlert("Bulk download feature would be implemented here", "info");
}

function printQRCodes() {
    showAlert("Print labels feature would be implemented here", "info");
}

function escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
}

function showAlert(message, type) {
    // Create and show alert (implementation depends on your alert system)
    console.log(`${type.toUpperCase()}: ${message}`);
}

function showError(message) {
    document.getElementById("loadingSpinner").style.display = "none";
    document.getElementById("itemsContainer").style.display = "none";
    showAlert(message, "danger");
}
</script>
';

include __DIR__ . '/../../layout.php';
?>

