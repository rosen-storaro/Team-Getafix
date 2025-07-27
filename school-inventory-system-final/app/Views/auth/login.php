<?php
$title = 'Login - School Inventory Management System';
ob_start();
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow">
                <div class="card-body p-4">
                    <!-- Header -->
                    <div class="text-center mb-4">
                        <i class="bi bi-box-seam text-primary mb-3" style="font-size: 3rem;"></i>
                        <h3 class="card-title">Sign In</h3>
                        <p class="text-muted">Access your inventory account</p>
                    </div>
                    
                    <!-- Login Form -->
                    <form id="loginForm">
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                        
                        <div class="mb-3">
                            <label for="identifier" class="form-label">Username or Email</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-person"></i>
                                </span>
                                <input type="text" class="form-control" id="identifier" name="identifier" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                    <i class="bi bi-eye" id="password-toggle"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember">
                            <label class="form-check-label" for="remember">
                                Remember me
                            </label>
                        </div>
                        
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary" id="loginBtn">
                                <i class="bi bi-box-arrow-in-right"></i> Sign In
                            </button>
                        </div>
                        
                        <!-- Google OAuth (if enabled) -->
                        <?php if (env('GOOGLE_CLIENT_ID')): ?>
                        <div class="d-grid mb-3">
                            <button type="button" class="btn btn-outline-danger" onclick="signInWithGoogle()">
                                <i class="bi bi-google"></i> Sign in with Google
                            </button>
                        </div>
                        <hr>
                        <?php endif; ?>
                        
                        <div class="text-center">
                            <p class="mb-0">
                                Don't have an account? 
                                <a href="/register" class="text-decoration-none">Register here</a>
                            </p>
                            <small class="text-muted">
                                Forgot your password? Contact an administrator for assistance.
                            </small>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Demo Credentials (for development) -->
            <?php if (env('APP_DEBUG', false)): ?>
            <div class="card mt-3 border-warning">
                <div class="card-header bg-warning text-dark">
                    <i class="bi bi-info-circle"></i> Demo Credentials
                </div>
                <div class="card-body">
                    <small>
                        <strong>Super Admin:</strong> superadmin / pass123!@#<br>
                        <em>Note: You must change the password on first login</em>
                    </small>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

$scripts = '
<script>
document.getElementById("loginForm").addEventListener("submit", async function(e) {
    e.preventDefault();
    
    const btn = document.getElementById("loginBtn");
    const originalText = btn.innerHTML;
    
    try {
        btn.disabled = true;
        btn.innerHTML = \'<span class="spinner-border spinner-border-sm me-2"></span>Signing in...\';
        
        const formData = new FormData(this);
        
        const response = await fetch("/api/auth/login", {
            method: "POST",
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            if (data.must_change_password) {
                showAlert("Password change required. Redirecting...", "warning");
                setTimeout(() => window.location.href = data.redirect, 1500);
            } else {
                showAlert("Login successful! Redirecting...", "success");
                setTimeout(() => window.location.href = data.redirect, 1500);
            }
        } else {
            throw new Error(data.error || "Login failed");
        }
        
    } catch (error) {
        showAlert(error.message, "danger");
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

function signInWithGoogle() {
    // Google OAuth implementation would go here
    showAlert("Google OAuth not implemented yet", "info");
}
</script>
';

include __DIR__ . '/../layout.php';
?>

