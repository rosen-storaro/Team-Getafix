<?php
declare(strict_types=1);

namespace Services\Auth;

require_once __DIR__ . '/Model.php';

/**
 * Authentication Controller
 * Handles user authentication and management operations
 */
class Controller {
    private Model $model;
    
    public function __construct() {
        $this->model = new Model();
    }
    
    /**
     * User registration
     */
    public function register(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['error' => 'Method not allowed'], 405);
        }
        
        // Verify CSRF token
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!verifyCsrfToken($csrfToken)) {
            jsonResponse(['error' => 'Invalid CSRF token'], 400);
        }
        
        $data = [
            'username' => trim($_POST['username'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? '')
        ];
        
        // Validate input
        $errors = $this->model->validateUserData($data);
        if (!empty($errors)) {
            jsonResponse(['error' => 'Validation failed', 'errors' => $errors], 400);
        }
        
        try {
            $userId = $this->model->createUser($data);
            
            jsonResponse([
                'success' => true,
                'message' => 'Registration successful. Your account is pending approval.',
                'user_id' => $userId
            ], 201);
            
        } catch (\Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            jsonResponse(['error' => 'Registration failed'], 500);
        }
    }
    
    /**
     * User login
     */
    public function login(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['error' => 'Method not allowed'], 405);
        }
        
        // Verify CSRF token
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!verifyCsrfToken($csrfToken)) {
            jsonResponse(['error' => 'Invalid CSRF token'], 400);
        }
        
        $identifier = trim($_POST['identifier'] ?? ''); // username or email
        $password = $_POST['password'] ?? '';
        
        if (empty($identifier) || empty($password)) {
            jsonResponse(['error' => 'Username/email and password are required'], 400);
        }
        
        try {
            $user = $this->model->authenticate($identifier, $password);
            
            if (!$user) {
                jsonResponse(['error' => 'Invalid credentials'], 401);
            }
            
            if ($user['status'] !== 'Active') {
                $message = match($user['status']) {
                    'Pending' => 'Your account is pending approval',
                    'Disabled' => 'Your account has been disabled',
                    default => 'Account access denied'
                };
                jsonResponse(['error' => $message], 403);
            }
            
            // Set session data
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_data'] = $user;
            
            // Check if password change is required
            if ($user['must_change_password']) {
                jsonResponse([
                    'success' => true,
                    'must_change_password' => true,
                    'redirect' => '/change-password'
                ]);
            }
            
            // Determine redirect based on role
            $redirect = match($user['role_name']) {
                'User' => '/',
                'Admin', 'Super-admin' => '/admin',
                default => '/'
            };
            
            jsonResponse([
                'success' => true,
                'user' => $user,
                'redirect' => $redirect
            ]);
            
        } catch (\Exception $e) {
            error_log("Login error: " . $e->getMessage());
            jsonResponse(['error' => 'Login failed'], 500);
        }
    }
    
    /**
     * User logout
     */
    public function logout(): void {
        session_destroy();
        jsonResponse(['success' => true, 'redirect' => '/']);
    }
    
    /**
     * Get current user profile
     */
    public function profile(): void {
        requireAuth();
        
        $user = getCurrentUser();
        jsonResponse(['user' => $user]);
    }
    
    /**
     * Update user profile
     */
    public function updateProfile(): void {
        requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            jsonResponse(['error' => 'Method not allowed'], 405);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $userId = $_SESSION['user_id'];
        
        $allowedFields = ['first_name', 'last_name', 'phone', 'email'];
        $updateData = [];
        
        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                $updateData[$field] = trim($input[$field]);
            }
        }
        
        if (empty($updateData)) {
            jsonResponse(['error' => 'No valid fields to update'], 400);
        }
        
        // Validate email if being updated
        if (isset($updateData['email'])) {
            if (!filter_var($updateData['email'], FILTER_VALIDATE_EMAIL)) {
                jsonResponse(['error' => 'Invalid email format'], 400);
            }
            
            // Check if email already exists for another user
            $existingUser = $this->model->findByEmail($updateData['email']);
            if ($existingUser && $existingUser['id'] !== $userId) {
                jsonResponse(['error' => 'Email already exists'], 400);
            }
        }
        
        try {
            $success = $this->model->update($userId, $updateData);
            
            if ($success) {
                // Update session data
                $_SESSION['user_data'] = $this->model->findById($userId);
                
                jsonResponse(['success' => true, 'message' => 'Profile updated successfully']);
            } else {
                jsonResponse(['error' => 'Failed to update profile'], 500);
            }
            
        } catch (\Exception $e) {
            error_log("Profile update error: " . $e->getMessage());
            jsonResponse(['error' => 'Update failed'], 500);
        }
    }
    
    /**
     * Get all users (Admin only)
     */
    public function getUsers(): void {
        requireRole('Admin');
        
        $status = $_GET['status'] ?? null;
        
        try {
            $users = $this->model->getAllUsers($status);
            jsonResponse(['users' => $users]);
            
        } catch (\Exception $e) {
            error_log("Get users error: " . $e->getMessage());
            jsonResponse(['error' => 'Failed to fetch users'], 500);
        }
    }
    
    /**
     * Update user status (Super-admin only)
     */
    public function updateUserStatus(int $userId): void {
        requireRole('Super-admin');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            jsonResponse(['error' => 'Method not allowed'], 405);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $status = $input['status'] ?? '';
        
        $validStatuses = ['Pending', 'Active', 'Disabled'];
        if (!in_array($status, $validStatuses)) {
            jsonResponse(['error' => 'Invalid status'], 400);
        }
        
        try {
            $success = $this->model->updateStatus($userId, $status);
            
            if ($success) {
                jsonResponse(['success' => true, 'message' => 'User status updated successfully']);
            } else {
                jsonResponse(['error' => 'User not found'], 404);
            }
            
        } catch (\Exception $e) {
            error_log("Update user status error: " . $e->getMessage());
            jsonResponse(['error' => 'Failed to update user status'], 500);
        }
    }
    
    /**
     * Update user role (Super-admin only)
     */
    public function updateUserRole(int $userId): void {
        requireRole('Super-admin');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            jsonResponse(['error' => 'Method not allowed'], 405);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $roleId = (int) ($input['role_id'] ?? 0);
        
        if ($roleId <= 0) {
            jsonResponse(['error' => 'Invalid role ID'], 400);
        }
        
        try {
            $success = $this->model->updateRole($userId, $roleId);
            
            if ($success) {
                jsonResponse(['success' => true, 'message' => 'User role updated successfully']);
            } else {
                jsonResponse(['error' => 'User not found'], 404);
            }
            
        } catch (\Exception $e) {
            error_log("Update user role error: " . $e->getMessage());
            jsonResponse(['error' => 'Failed to update user role'], 500);
        }
    }
    
    /**
     * Generate temporary password (Super-admin only)
     */
    public function generateTempPassword(int $userId): void {
        requireRole('Super-admin');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['error' => 'Method not allowed'], 405);
        }
        
        try {
            $tempPassword = $this->model->generateTemporaryPassword($userId);
            
            jsonResponse([
                'success' => true,
                'temporary_password' => $tempPassword,
                'message' => 'Temporary password generated. User must change it on next login.'
            ]);
            
        } catch (\Exception $e) {
            error_log("Generate temp password error: " . $e->getMessage());
            jsonResponse(['error' => 'Failed to generate temporary password'], 500);
        }
    }
    
    /**
     * Get roles
     */
    public function getRoles(): void {
        requireRole('Super-admin');
        
        try {
            $roles = $this->model->getRoles();
            jsonResponse(['roles' => $roles]);
            
        } catch (\Exception $e) {
            error_log("Get roles error: " . $e->getMessage());
            jsonResponse(['error' => 'Failed to fetch roles'], 500);
        }
    }
    
    /**
     * Change user password (authenticated users only)
     */
    public function changePassword(): void {
        requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['error' => 'Method not allowed'], 405);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            jsonResponse(['error' => 'Invalid JSON input'], 400);
        }
        
        $currentPassword = $input['current_password'] ?? '';
        $newPassword = $input['new_password'] ?? '';
        $confirmPassword = $input['confirm_password'] ?? '';
        
        // Validate input
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            jsonResponse(['error' => 'All fields are required'], 400);
        }
        
        if ($newPassword !== $confirmPassword) {
            jsonResponse(['error' => 'New passwords do not match'], 400);
        }
        
        // Validate password strength
        if (!$this->validatePasswordStrength($newPassword)) {
            jsonResponse(['error' => 'Password must be at least 8 characters long and contain uppercase, lowercase, number, and special character'], 400);
        }
        
        try {
            $userId = $_SESSION['user_id'];
            $user = $this->model->getUserById($userId);
            
            if (!$user) {
                jsonResponse(['error' => 'User not found'], 404);
            }
            
            // Verify current password
            if (!password_verify($currentPassword, $user['password_hash'])) {
                jsonResponse(['error' => 'Current password is incorrect'], 400);
            }
            
            // Update password
            $success = $this->model->updatePassword($userId, $newPassword);
            
            if ($success) {
                // Clear force password change flag
                $this->model->clearForcePasswordChange($userId);
                
                jsonResponse([
                    'success' => true,
                    'message' => 'Password changed successfully'
                ]);
            } else {
                throw new \Exception('Failed to update password');
            }
            
        } catch (\Exception $e) {
            error_log("Change password error: " . $e->getMessage());
            jsonResponse(['error' => 'Failed to change password'], 500);
        }
    }
    
    /**
     * Validate password strength
     */
    private function validatePasswordStrength(string $password): bool {
        return strlen($password) >= 8 &&
               preg_match('/[A-Z]/', $password) &&
               preg_match('/[a-z]/', $password) &&
               preg_match('/\d/', $password) &&
               preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password);
    }

    /**
     * Google OAuth callback (optional feature)
     */
    public function googleCallback(): void {
        // This would handle Google OAuth callback
        // Implementation depends on Google OAuth setup
        jsonResponse(['error' => 'Google OAuth not implemented yet'], 501);
    }
}

