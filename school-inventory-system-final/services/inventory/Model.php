<?php
declare(strict_types=1);

namespace Services\Inventory;

require_once __DIR__ . '/../../config/database.php';

/**
 * Inventory Model
 * Handles inventory items, categories, and locations
 */
class Model extends \BaseModel {
    protected string $table = 'items';
    
    public function __construct() {
        parent::__construct('inventory');
    }
    
    /**
     * Get all items with category and location information
     */
    public function getAllItems(array $filters = [], int $limit = 0, int $offset = 0): array {
        $sql = "
            SELECT i.*, 
                   c.name as category_name,
                   l.name as location_name,
                   CASE 
                       WHEN i.quantity <= i.low_stock_threshold THEN 1 
                       ELSE 0 
                   END as is_low_stock
            FROM items i
            LEFT JOIN categories c ON i.category_id = c.id
            LEFT JOIN locations l ON i.location_id = l.id
        ";
        
        $params = [];
        $whereConditions = [];
        
        // Apply filters
        if (!empty($filters['search'])) {
            $whereConditions[] = "(i.name LIKE ? OR i.serial_number LIKE ? OR i.description LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($filters['category_id'])) {
            $whereConditions[] = "i.category_id = ?";
            $params[] = $filters['category_id'];
        }
        
        if (!empty($filters['location_id'])) {
            $whereConditions[] = "i.location_id = ?";
            $params[] = $filters['location_id'];
        }
        
        if (!empty($filters['status'])) {
            $whereConditions[] = "i.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['sensitive_level'])) {
            $whereConditions[] = "i.sensitive_level = ?";
            $params[] = $filters['sensitive_level'];
        }
        
        if (isset($filters['low_stock']) && $filters['low_stock']) {
            $whereConditions[] = "i.quantity <= i.low_stock_threshold";
        }
        
        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(' AND ', $whereConditions);
        }
        
        $sql .= " ORDER BY i.created_at DESC";
        
        if ($limit > 0) {
            $sql .= " LIMIT {$limit}";
            if ($offset > 0) {
                $sql .= " OFFSET {$offset}";
            }
        }
        
        $stmt = $this->execute($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get item by ID with related information
     */
    public function getItemById(int $id): ?array {
        $sql = "
            SELECT i.*, 
                   c.name as category_name,
                   l.name as location_name,
                   CASE 
                       WHEN i.quantity <= i.low_stock_threshold THEN 1 
                       ELSE 0 
                   END as is_low_stock
            FROM items i
            LEFT JOIN categories c ON i.category_id = c.id
            LEFT JOIN locations l ON i.location_id = l.id
            WHERE i.id = ?
        ";
        
        $stmt = $this->execute($sql, [$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Create new item
     */
    public function createItem(array $data): int {
        // Set default values
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['status'] = $data['status'] ?? 'Available';
        $data['quantity'] = $data['quantity'] ?? 1;
        $data['low_stock_threshold'] = $data['low_stock_threshold'] ?? 3;
        $data['sensitive_level'] = $data['sensitive_level'] ?? 'No';
        
        $itemId = $this->create($data);
        
        // Log item creation
        $this->logItemHistory($itemId, $data['created_by'] ?? null, 'Created', '', 'Available', 'Item created');
        
        return $itemId;
    }
    
    /**
     * Update item
     */
    public function updateItem(int $id, array $data, int $userId = null): bool {
        $oldItem = $this->getItemById($id);
        if (!$oldItem) {
            return false;
        }
        
        $data['updated_at'] = date('Y-m-d H:i:s');
        $success = $this->update($id, $data);
        
        if ($success && $userId) {
            // Log status change if status was updated
            if (isset($data['status']) && $data['status'] !== $oldItem['status']) {
                $this->logItemHistory($id, $userId, 'Updated', $oldItem['status'], $data['status'], 'Status updated');
            } else {
                $this->logItemHistory($id, $userId, 'Updated', '', '', 'Item updated');
            }
        }
        
        return $success;
    }
    
    /**
     * Update item status
     */
    public function updateItemStatus(int $id, string $status, int $userId, string $notes = ''): bool {
        $validStatuses = ['Available', 'Checked Out', 'Reserved', 'Under Repair', 'Lost/Stolen', 'Retired'];
        if (!in_array($status, $validStatuses)) {
            throw new \InvalidArgumentException("Invalid status: {$status}");
        }
        
        $oldItem = $this->getItemById($id);
        if (!$oldItem) {
            return false;
        }
        
        $data = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $success = $this->update($id, $data);
        
        if ($success) {
            $this->logItemHistory($id, $userId, 'Updated', $oldItem['status'], $status, $notes ?: 'Status updated');
        }
        
        return $success;
    }
    
    /**
     * Update item quantity
     */
    public function updateItemQuantity(int $id, int $quantity, int $userId, string $notes = ''): bool {
        $oldItem = $this->getItemById($id);
        if (!$oldItem) {
            return false;
        }
        
        $data = [
            'quantity' => $quantity,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $success = $this->update($id, $data);
        
        if ($success) {
            $action = $quantity > $oldItem['quantity'] ? 'Restocked' : 'Quantity Updated';
            $this->logItemHistory($id, $userId, $action, (string)$oldItem['quantity'], (string)$quantity, $notes ?: 'Quantity updated');
        }
        
        return $success;
    }
    
    /**
     * Get item history
     */
    public function getItemHistory(int $itemId): array {
        $sql = "
            SELECT h.*, u.username, u.first_name, u.last_name
            FROM item_history h
            LEFT JOIN auth_db.users u ON h.user_id = u.id
            WHERE h.item_id = ?
            ORDER BY h.created_at DESC
        ";
        
        $stmt = $this->execute($sql, [$itemId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Log item history
     */
    private function logItemHistory(int $itemId, ?int $userId, string $action, string $oldStatus, string $newStatus, string $notes): void {
        $sql = "
            INSERT INTO item_history (item_id, user_id, action, old_status, new_status, notes, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ";
        
        $this->execute($sql, [$itemId, $userId, $action, $oldStatus, $newStatus, $notes]);
    }
    
    /**
     * Get low stock items
     */
    public function getLowStockItems(): array {
        $sql = "
            SELECT i.*, 
                   c.name as category_name,
                   l.name as location_name
            FROM items i
            LEFT JOIN categories c ON i.category_id = c.id
            LEFT JOIN locations l ON i.location_id = l.id
            WHERE i.quantity <= i.low_stock_threshold
            ORDER BY (i.quantity - i.low_stock_threshold) ASC
        ";
        
        $stmt = $this->execute($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Get low stock count
     */
    public function getLowStockCount(): int {
        $stmt = $this->execute("SELECT COUNT(*) FROM items WHERE quantity <= low_stock_threshold");
        return (int) $stmt->fetchColumn();
    }
    
    /**
     * Search items
     */
    public function searchItems(string $query, int $limit = 20): array {
        $searchTerm = '%' . $query . '%';
        
        $sql = "
            SELECT i.*, 
                   c.name as category_name,
                   l.name as location_name,
                   CASE 
                       WHEN i.quantity <= i.low_stock_threshold THEN 1 
                       ELSE 0 
                   END as is_low_stock
            FROM items i
            LEFT JOIN categories c ON i.category_id = c.id
            LEFT JOIN locations l ON i.location_id = l.id
            WHERE i.name LIKE ? 
               OR i.serial_number LIKE ? 
               OR i.description LIKE ?
               OR c.name LIKE ?
               OR l.name LIKE ?
            ORDER BY 
                CASE 
                    WHEN i.name LIKE ? THEN 1
                    WHEN i.serial_number LIKE ? THEN 2
                    ELSE 3
                END,
                i.name ASC
            LIMIT ?
        ";
        
        $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit];
        $stmt = $this->execute($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get available items for borrowing
     */
    public function getAvailableItems(array $filters = []): array {
        $filters['status'] = 'Available';
        return $this->getAllItems($filters);
    }
    
    /**
     * Check if item is available for borrowing
     */
    public function isItemAvailable(int $itemId, int $requestedQuantity = 1): bool {
        $item = $this->getItemById($itemId);
        
        if (!$item || $item['status'] !== 'Available') {
            return false;
        }
        
        return $item['quantity'] >= $requestedQuantity;
    }
    
    /**
     * Reserve item quantity
     */
    public function reserveItem(int $itemId, int $quantity, int $userId): bool {
        if (!$this->isItemAvailable($itemId, $quantity)) {
            return false;
        }
        
        $item = $this->getItemById($itemId);
        $newQuantity = $item['quantity'] - $quantity;
        
        // If all items are reserved, change status
        $newStatus = $newQuantity > 0 ? 'Available' : 'Reserved';
        
        $data = [
            'quantity' => $newQuantity,
            'status' => $newStatus,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $success = $this->update($itemId, $data);
        
        if ($success) {
            $this->logItemHistory($itemId, $userId, 'Reserved', $item['status'], $newStatus, "Reserved {$quantity} item(s)");
        }
        
        return $success;
    }
    
    /**
     * Return item quantity
     */
    public function returnItem(int $itemId, int $quantity, int $userId, string $condition = ''): bool {
        $item = $this->getItemById($itemId);
        if (!$item) {
            return false;
        }
        
        $newQuantity = $item['quantity'] + $quantity;
        $newStatus = 'Available';
        
        $data = [
            'quantity' => $newQuantity,
            'status' => $newStatus,
            'condition_notes' => $condition ?: $item['condition_notes'],
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $success = $this->update($itemId, $data);
        
        if ($success) {
            $notes = "Returned {$quantity} item(s)";
            if ($condition) {
                $notes .= " - Condition: {$condition}";
            }
            $this->logItemHistory($itemId, $userId, 'Returned', $item['status'], $newStatus, $notes);
        }
        
        return $success;
    }
    
    /**
     * Get categories
     */
    public function getCategories(): array {
        $stmt = $this->execute("SELECT * FROM categories ORDER BY name");
        return $stmt->fetchAll();
    }
    
    /**
     * Get category by ID
     */
    public function getCategoryById(int $id): ?array {
        $stmt = $this->execute("SELECT * FROM categories WHERE id = ?", [$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Create category
     */
    public function createCategory(array $data): int {
        $data['created_at'] = date('Y-m-d H:i:s');
        
        $sql = "INSERT INTO categories (name, description, created_at) VALUES (?, ?, ?)";
        $this->execute($sql, [$data['name'], $data['description'] ?? '', $data['created_at']]);
        
        return (int) $this->db->lastInsertId();
    }
    
    /**
     * Update category
     */
    public function updateCategory(int $id, array $data): bool {
        $sql = "UPDATE categories SET name = ?, description = ? WHERE id = ?";
        $stmt = $this->execute($sql, [$data['name'], $data['description'] ?? '', $id]);
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Delete category
     */
    public function deleteCategory(int $id): bool {
        // Check if category has items
        $stmt = $this->execute("SELECT COUNT(*) FROM items WHERE category_id = ?", [$id]);
        if ($stmt->fetchColumn() > 0) {
            throw new \Exception("Cannot delete category with existing items");
        }
        
        $stmt = $this->execute("DELETE FROM categories WHERE id = ?", [$id]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Get locations
     */
    public function getLocations(): array {
        $stmt = $this->execute("SELECT * FROM locations ORDER BY name");
        return $stmt->fetchAll();
    }
    
    /**
     * Get location by ID
     */
    public function getLocationById(int $id): ?array {
        $stmt = $this->execute("SELECT * FROM locations WHERE id = ?", [$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Create location
     */
    public function createLocation(array $data): int {
        $data['created_at'] = date('Y-m-d H:i:s');
        
        $sql = "INSERT INTO locations (name, description, created_at) VALUES (?, ?, ?)";
        $this->execute($sql, [$data['name'], $data['description'] ?? '', $data['created_at']]);
        
        return (int) $this->db->lastInsertId();
    }
    
    /**
     * Update location
     */
    public function updateLocation(int $id, array $data): bool {
        $sql = "UPDATE locations SET name = ?, description = ? WHERE id = ?";
        $stmt = $this->execute($sql, [$data['name'], $data['description'] ?? '', $id]);
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Delete location
     */
    public function deleteLocation(int $id): bool {
        // Check if location has items
        $stmt = $this->execute("SELECT COUNT(*) FROM items WHERE location_id = ?", [$id]);
        if ($stmt->fetchColumn() > 0) {
            throw new \Exception("Cannot delete location with existing items");
        }
        
        $stmt = $this->execute("DELETE FROM locations WHERE id = ?", [$id]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Get inventory statistics
     */
    public function getInventoryStats(): array {
        $stats = [];
        
        // Total items
        $stmt = $this->execute("SELECT COUNT(*) FROM items");
        $stats['total_items'] = (int) $stmt->fetchColumn();
        
        // Available items
        $stmt = $this->execute("SELECT COUNT(*) FROM items WHERE status = 'Available'");
        $stats['available_items'] = (int) $stmt->fetchColumn();
        
        // Checked out items
        $stmt = $this->execute("SELECT COUNT(*) FROM items WHERE status = 'Checked Out'");
        $stats['checked_out_items'] = (int) $stmt->fetchColumn();
        
        // Low stock items
        $stats['low_stock_items'] = $this->getLowStockCount();
        
        // Items by status
        $stmt = $this->execute("SELECT status, COUNT(*) as count FROM items GROUP BY status");
        $stats['by_status'] = [];
        while ($row = $stmt->fetch()) {
            $stats['by_status'][$row['status']] = (int) $row['count'];
        }
        
        // Items by category
        $stmt = $this->execute("
            SELECT c.name, COUNT(i.id) as count 
            FROM categories c 
            LEFT JOIN items i ON c.id = i.category_id 
            GROUP BY c.id, c.name
        ");
        $stats['by_category'] = [];
        while ($row = $stmt->fetch()) {
            $stats['by_category'][$row['name']] = (int) $row['count'];
        }
        
        return $stats;
    }
    
    /**
     * Validate item data
     */
    public function validateItemData(array $data, bool $isUpdate = false): array {
        $errors = [];
        
        // Name validation
        if (!$isUpdate || isset($data['name'])) {
            if (empty($data['name'])) {
                $errors['name'] = 'Item name is required';
            } elseif (strlen($data['name']) > 120) {
                $errors['name'] = 'Item name cannot exceed 120 characters';
            }
        }
        
        // Category validation
        if (isset($data['category_id']) && !empty($data['category_id'])) {
            if (!$this->getCategoryById((int) $data['category_id'])) {
                $errors['category_id'] = 'Invalid category selected';
            }
        }
        
        // Location validation
        if (isset($data['location_id']) && !empty($data['location_id'])) {
            if (!$this->getLocationById((int) $data['location_id'])) {
                $errors['location_id'] = 'Invalid location selected';
            }
        }
        
        // Quantity validation
        if (isset($data['quantity'])) {
            if (!is_numeric($data['quantity']) || (int) $data['quantity'] < 0) {
                $errors['quantity'] = 'Quantity must be a non-negative number';
            }
        }
        
        // Low stock threshold validation
        if (isset($data['low_stock_threshold'])) {
            if (!is_numeric($data['low_stock_threshold']) || (int) $data['low_stock_threshold'] < 0) {
                $errors['low_stock_threshold'] = 'Low stock threshold must be a non-negative number';
            }
        }
        
        // Status validation
        if (isset($data['status'])) {
            $validStatuses = ['Available', 'Checked Out', 'Reserved', 'Under Repair', 'Lost/Stolen', 'Retired'];
            if (!in_array($data['status'], $validStatuses)) {
                $errors['status'] = 'Invalid status selected';
            }
        }
        
        // Sensitive level validation
        if (isset($data['sensitive_level'])) {
            $validLevels = ['No', 'Sensitive', 'High Value'];
            if (!in_array($data['sensitive_level'], $validLevels)) {
                $errors['sensitive_level'] = 'Invalid sensitive level selected';
            }
        }
        
        return $errors;
    }
}

