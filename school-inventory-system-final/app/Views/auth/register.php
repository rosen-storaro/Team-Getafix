<?php
$title = 'Register - School Inventory Management System';
ob_start();
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow">
                <div class="card-body p-4">
                    <!-- Header -->
                    <div class="text-center mb-4">
                        <i class="bi bi-person-plus text-primary mb-3" style="font-size: 3rem;"></i>
                        <h3 class="card-title">Create Account</h3>
                        <p class="text-muted">Join the inventory management system</p>
                    </div>
                    
                    <!-- Registration Form -->
                    <form id="registerForm">
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name *</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name *</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">Username *</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-person"></i>
                                </span>
                                <input type="text" class="form-control" id="username" name="username" required 
                                       pattern="[a-zA-Z0-9_]+" title="Only letters, numbers, and underscores allowed">
                                <div class="invalid-feedback"></div>
                            </div>
                            <small class="text-muted">Only letters, numbers, and underscores. Minimum 3 characters.</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address *</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input type="email" class="form-control" id="email" name="email" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-telephone"></i>
                                </span>
                                <input type="tel" class="form-control" id="phone" name="phone">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password *</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword(\'password\')">
                                    <i class="bi bi-eye" id="password-toggle"></i>
                                </button>
                                <div class="invalid-feedback"></div>
                            </div>
                            <small class="text-muted">
                                Minimum 8 characters with uppercase, lowercase, number, and special character.
                            </small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password *</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-lock-fill"></i>
                                </span>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword(\'confirm_password\')">
                                    <i class="bi bi-eye" id="confirm_password-toggle"></i>
                                </button>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="terms" required>
                            <label class="form-check-label" for="terms">
                                I agree to the <a href="#" class="text-decoration-none">Terms of Service</a> 
                                and <a href="#" class="text-decoration-none">Privacy Policy</a> *
                            </label>
                        </div>
                        
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary" id="registerBtn">
                                <i class="bi bi-person-plus"></i> Create Account
                            </button>
                        </div>
                        
                        <div class="text-center">
                            <p class="mb-0">
                                Already have an account? 
                                <a href="/login" class="text-decoration-none">Sign in here</a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Important Notice -->
            <div class="alert alert-info mt-3" role="alert">
                <i class="bi bi-info-circle"></i>
                <strong>Account Approval Required:</strong> 
                New accounts must be approved by an administrator before you can access the system. 
                You will receive notification once your account is activated.
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

$scripts = '
<script>
document.getElementById("registerForm").addEventListener("submit", async function(e) {
    e.preventDefault();
    
    // Clear previous validation states
    clearValidationErrors();
    
    // Validate passwords match
    const password = document.getElementById("password").value;
    const confirmPassword = document.getElementById("confirm_password").value;
    
    if (password !== confirmPassword) {
        showFieldError("confirm_password", "Passwords do not match");
        return;
    }
    
    const btn = document.getElementById("registerBtn");
    const originalText = btn.innerHTML;
    
    try {
        btn.disabled = true;
        btn.innerHTML = \'<span class="spinner-border spinner-border-sm me-2"></span>Creating account...\';
        
        const formData = new FormData(this);
        
        const response = await fetch("/api/auth/register", {
            method: "POST",
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert("Account created successfully! Please wait for administrator approval.", "success");
            this.reset();
            setTimeout(() => window.location.href = "/login", 3000);
        } else {
            if (data.errors) {
                // Show field-specific errors
                for (const [field, error] of Object.entries(data.errors)) {
                    showFieldError(field, error);
                }
            } else {
                throw new Error(data.error || "Registration failed");
            }
        }
        
    } catch (error) {
        showAlert(error.message, "danger");
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
});

function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const toggle = document.getElementById(fieldId + "-toggle");
    
    if (field.type === "password") {
        field.type = "text";
        toggle.className = "bi bi-eye-slash";
    } else {
        field.type = "password";
        toggle.className = "bi bi-eye";
    }
}

function showFieldError(fieldName, message) {
    const field = document.getElementById(fieldName);
    const feedback = field.parentNode.querySelector(".invalid-feedback");
    
    field.classList.add("is-invalid");
    if (feedback) {
        feedback.textContent = message;
    }
}

function clearValidationErrors() {
    const invalidFields = document.querySelectorAll(".is-invalid");
    invalidFields.forEach(field => {
        field.classList.remove("is-invalid");
    });
}

// Real-time password validation
document.getElementById("password").addEventListener("input", function() {
    const password = this.value;
    const feedback = this.parentNode.querySelector(".invalid-feedback");
    
    if (password.length > 0 && password.length < 8) {
        showFieldError("password", "Password must be at least 8 characters");
    } else if (password.length >= 8 && !/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/.test(password)) {
        showFieldError("password", "Password must contain uppercase, lowercase, number, and special character");
    } else {
        this.classList.remove("is-invalid");
    }
});

// Real-time confirm password validation
document.getElementById("confirm_password").addEventListener("input", function() {
    const password = document.getElementById("password").value;
    const confirmPassword = this.value;
    
    if (confirmPassword.length > 0 && password !== confirmPassword) {
        showFieldError("confirm_password", "Passwords do not match");
    } else {
        this.classList.remove("is-invalid");
    }
});
</script>
';

include __DIR__ . '/../layout.php';
?>

