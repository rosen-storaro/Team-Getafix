<?php
declare(strict_types=1);

namespace Services\Auth;

require_once __DIR__ . '/../../config/database.php';

/**
 * User Model
 * Handles user authentication and management
 */
class Model extends \BaseModel {
    protected string $table = 'users';
    
    public function __construct() {
        parent::__construct('auth');
    }
    
    /**
     * Create new user with hashed password
     */
    public function createUser(array $userData): int {
        // Hash password if provided
        if (isset($userData['password'])) {
            $userData['password_hash'] = password_hash($userData['password'], PASSWORD_BCRYPT);
            unset($userData['password']);
        }
        
        // Set default values
        $userData['status'] = $userData['status'] ?? 'Pending';
        $userData['role_id'] = $userData['role_id'] ?? 1; // Default to User role
        $userData['created_at'] = date('Y-m-d H:i:s');
        
        return $this->create($userData);
    }
    
    /**
     * Authenticate user with username/email and password
     */
    public function authenticate(string $identifier, string $password): ?array {
        $sql = "
            SELECT u.*, r.name as role_name 
            FROM users u 
            JOIN roles r ON u.role_id = r.id 
            WHERE (u.username = ? OR u.email = ?) AND u.status = 'Active'
        ";
        
        $stmt = $this->execute($sql, [$identifier, $identifier]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Update last login
            $this->updateLastLogin($user['id']);
            
            // Remove sensitive data
            unset($user['password_hash']);
            return $user;
        }
        
        return null;
    }
    
    /**
     * Find user by username
     */
    public function findByUsername(string $username): ?array {
        $sql = "
            SELECT u.*, r.name as role_name 
            FROM users u 
            JOIN roles r ON u.role_id = r.id 
            WHERE u.username = ?
        ";
        
        $stmt = $this->execute($sql, [$username]);
        $result = $stmt->fetch();
        
        if ($result) {
            unset($result['password_hash']);
            return $result;
        }
        
        return null;
    }
    
    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?array {
        $sql = "
            SELECT u.*, r.name as role_name 
            FROM users u 
            JOIN roles r ON u.role_id = r.id 
            WHERE u.email = ?
        ";
        
        $stmt = $this->execute($sql, [$email]);
        $result = $stmt->fetch();
        
        if ($result) {
            unset($result['password_hash']);
            return $result;
        }
        
        return null;
    }
    
    /**
     * Find user by Google ID
     */
    public function findByGoogleId(string $googleId): ?array {
        $sql = "
            SELECT u.*, r.name as role_name 
            FROM users u 
            JOIN roles r ON u.role_id = r.id 
            WHERE u.google_id = ?
        ";
        
        $stmt = $this->execute($sql, [$googleId]);
        $result = $stmt->fetch();
        
        if ($result) {
            unset($result['password_hash']);
            return $result;
        }
        
        return null;
    }
    
    /**
     * Get user with role information
     */
    public function findById(int $id): ?array {
        $sql = "
            SELECT u.*, r.name as role_name 
            FROM users u 
            JOIN roles r ON u.role_id = r.id 
            WHERE u.id = ?
        ";
        
        $stmt = $this->execute($sql, [$id]);
        $result = $stmt->fetch();
        
        if ($result) {
            unset($result['password_hash']);
            return $result;
        }
        
        return null;
    }
    
    /**
     * Get user by ID including password hash (for authentication)
     */
    public function getUserById(int $id): ?array {
        $sql = "
            SELECT u.*, r.name as role_name 
            FROM users u 
            JOIN roles r ON u.role_id = r.id 
            WHERE u.id = ?
        ";
        
        $stmt = $this->execute($sql, [$id]);
        $result = $stmt->fetch();
        
        return $result ?: null;
    }
    
    /**
     * Get all users with role information
     */
    public function getAllUsers(string $status = null): array {
        $sql = "
            SELECT u.*, r.name as role_name 
            FROM users u 
            JOIN roles r ON u.role_id = r.id
        ";
        
        $params = [];
        if ($status) {
            $sql .= " WHERE u.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY u.created_at DESC";
        
        $stmt = $this->execute($sql, $params);
        $users = $stmt->fetchAll();
        
        // Remove password hashes
        foreach ($users as &$user) {
            unset($user['password_hash']);
        }
        
        return $users;
    }
    
    /**
     * Update user status
     */
    public function updateStatus(int $userId, string $status): bool {
        $validStatuses = ['Pending', 'Active', 'Disabled'];
        if (!in_array($status, $validStatuses)) {
            throw new \InvalidArgumentException("Invalid status: {$status}");
        }
        
        $sql = "UPDATE users SET status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->execute($sql, [$status, $userId]);
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Update user role
     */
    public function updateRole(int $userId, int $roleId): bool {
        $sql = "UPDATE users SET role_id = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->execute($sql, [$roleId, $userId]);
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Update last login timestamp
     */
    private function updateLastLogin(int $userId): void {
        $sql = "UPDATE users SET last_login = NOW() WHERE id = ?";
        $this->execute($sql, [$userId]);
    }
    
    /**
     * Check if username exists
     */
    public function usernameExists(string $username): bool {
        $stmt = $this->execute("SELECT COUNT(*) FROM users WHERE username = ?", [$username]);
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Check if email exists
     */
    public function emailExists(string $email): bool {
        $stmt = $this->execute("SELECT COUNT(*) FROM users WHERE email = ?", [$email]);
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Update user password
     */
    public function updatePassword(int $userId, string $newPassword): bool {
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $sql = "UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->execute($sql, [$passwordHash, $userId]);
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Clear force password change flag
     */
    public function clearForcePasswordChange(int $userId): bool {
        $sql = "UPDATE users SET force_password_change = 0, updated_at = NOW() WHERE id = ?";
        $stmt = $this->execute($sql, [$userId]);
        
        return $stmt->rowCount() > 0;
    }

    /**
     * Get all roles
     */
    public function getRoles(): array {
        $stmt = $this->execute("SELECT * FROM roles ORDER BY id");
        return $stmt->fetchAll();
    }
    
    /**
     * Get role by name
     */
    public function getRoleByName(string $name): ?array {
        $stmt = $this->execute("SELECT * FROM roles WHERE name = ?", [$name]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Generate temporary password
     */
    public function generateTemporaryPassword(int $userId): string {
        $tempPassword = bin2hex(random_bytes(8)); // 16 character password
        $hashedPassword = password_hash($tempPassword, PASSWORD_BCRYPT);
        
        $sql = "UPDATE users SET password_hash = ?, must_change_password = TRUE, updated_at = NOW() WHERE id = ?";
        $this->execute($sql, [$hashedPassword, $userId]);
        
        return $tempPassword;
    }
    
    /**
     * Link Google account to user
     */
    public function linkGoogleAccount(int $userId, string $googleId): bool {
        $sql = "UPDATE users SET google_id = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->execute($sql, [$googleId, $userId]);
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Get pending users count
     */
    public function getPendingUsersCount(): int {
        $stmt = $this->execute("SELECT COUNT(*) FROM users WHERE status = 'Pending'");
        return (int) $stmt->fetchColumn();
    }
    
    /**
     * Validate user data
     */
    public function validateUserData(array $data, bool $isUpdate = false): array {
        $errors = [];
        
        // Username validation
        if (!$isUpdate || isset($data['username'])) {
            if (empty($data['username'])) {
                $errors['username'] = 'Username is required';
            } elseif (strlen($data['username']) < 3) {
                $errors['username'] = 'Username must be at least 3 characters';
            } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $data['username'])) {
                $errors['username'] = 'Username can only contain letters, numbers, and underscores';
            } elseif ($this->usernameExists($data['username'])) {
                $errors['username'] = 'Username already exists';
            }
        }
        
        // Email validation
        if (!$isUpdate || isset($data['email'])) {
            if (empty($data['email'])) {
                $errors['email'] = 'Email is required';
            } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Invalid email format';
            } elseif ($this->emailExists($data['email'])) {
                $errors['email'] = 'Email already exists';
            }
        }
        
        // Password validation (only for new users or password changes)
        if (!$isUpdate && isset($data['password'])) {
            if (empty($data['password'])) {
                $errors['password'] = 'Password is required';
            } elseif (strlen($data['password']) < 8) {
                $errors['password'] = 'Password must be at least 8 characters';
            } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/', $data['password'])) {
                $errors['password'] = 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character';
            }
        }
        
        // Name validation
        if (isset($data['first_name']) && empty($data['first_name'])) {
            $errors['first_name'] = 'First name is required';
        }
        
        if (isset($data['last_name']) && empty($data['last_name'])) {
            $errors['last_name'] = 'Last name is required';
        }
        
        return $errors;
    }
}

