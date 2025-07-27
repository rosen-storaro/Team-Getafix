<?php
$title = 'Change Password - School Inventory Management System';
ob_start();
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-box-seam text-primary" style="font-size: 3rem;"></i>
                        <h3 class="mt-3">Change Password</h3>
                        <p class="text-muted">You must change your password before continuing</p>
                    </div>
                    
                    <div id="alertContainer"></div>
                    
                    <form id="changePasswordForm">
                        <div class="mb-3">
                            <label for="currentPassword" class="form-label">Current Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <input type="password" class="form-control" id="currentPassword" name="current_password" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('currentPassword')">
                                    <i class="bi bi-eye" id="currentPasswordIcon"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="newPassword" class="form-label">New Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-lock-fill"></i>
                                </span>
                                <input type="password" class="form-control" id="newPassword" name="new_password" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('newPassword')">
                                    <i class="bi bi-eye" id="newPasswordIcon"></i>
                                </button>
                            </div>
                            <div class="form-text">
                                Password must be at least 8 characters long and contain uppercase, lowercase, number, and special character.
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="confirmPassword" class="form-label">Confirm New Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-lock-fill"></i>
                                </span>
                                <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirmPassword')">
                                    <i class="bi bi-eye" id="confirmPasswordIcon"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="bi bi-check-circle me-2"></i>Change Password
                            </button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-3">
                        <small class="text-muted">
                            For security reasons, you cannot skip this step.
                        </small>
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
    const form = document.getElementById("changePasswordForm");
    const newPassword = document.getElementById("newPassword");
    const confirmPassword = document.getElementById("confirmPassword");
    
    // Real-time password validation
    newPassword.addEventListener("input", validatePassword);
    confirmPassword.addEventListener("input", validatePasswordMatch);
    
    form.addEventListener("submit", handleSubmit);
});

function validatePassword() {
    const password = document.getElementById("newPassword").value;
    const requirements = {
        length: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /\d/.test(password),
        special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
    };
    
    const isValid = Object.values(requirements).every(req => req);
    
    const input = document.getElementById("newPassword");
    if (password.length > 0) {
        input.classList.toggle("is-valid", isValid);
        input.classList.toggle("is-invalid", !isValid);
    } else {
        input.classList.remove("is-valid", "is-invalid");
    }
    
    validatePasswordMatch();
    return isValid;
}

function validatePasswordMatch() {
    const newPassword = document.getElementById("newPassword").value;
    const confirmPassword = document.getElementById("confirmPassword").value;
    const confirmInput = document.getElementById("confirmPassword");
    
    if (confirmPassword.length > 0) {
        const matches = newPassword === confirmPassword;
        confirmInput.classList.toggle("is-valid", matches);
        confirmInput.classList.toggle("is-invalid", !matches);
        return matches;
    } else {
        confirmInput.classList.remove("is-valid", "is-invalid");
        return false;
    }
}

async function handleSubmit(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById("submitBtn");
    const originalText = submitBtn.innerHTML;
    
    // Validate passwords
    if (!validatePassword()) {
        showAlert("New password does not meet requirements", "danger");
        return;
    }
    
    if (!validatePasswordMatch()) {
        showAlert("Passwords do not match", "danger");
        return;
    }
    
    // Show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status"></span>Changing Password...`;
    
    try {
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());
        
        const response = await fetch("/api/auth/change-password", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (response.ok) {
            showAlert("Password changed successfully! Redirecting...", "success");
            setTimeout(() => {
                window.location.href = "/dashboard";
            }, 2000);
        } else {
            throw new Error(result.error || "Failed to change password");
        }
        
    } catch (error) {
        console.error("Change password error:", error);
        showAlert(error.message, "danger");
        
        // Reset button
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
}

function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + "Icon");
    
    if (field.type === "password") {
        field.type = "text";
        icon.className = "bi bi-eye-slash";
    } else {
        field.type = "password";
        icon.className = "bi bi-eye";
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
    
    // Auto-dismiss success alerts
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

