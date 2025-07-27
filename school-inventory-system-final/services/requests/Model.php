<?php
declare(strict_types=1);

namespace Services\Requests;

require_once __DIR__ . '/../../config/database.php';

/**
 * Requests Model
 * Handles borrow requests and borrowing workflow
 */
class Model extends \BaseModel {
    protected string $table = 'borrow_requests';
    
    public function __construct() {
        parent::__construct('inventory');
    }
    
    /**
     * Create new borrow request
     */
    public function createRequest(array $data): int {
        // Set default values
        $data['status'] = 'Pending';
        $data['created_at'] = date('Y-m-d H:i:s');
        
        // Validate dates
        if (strtotime($data['date_from']) >= strtotime($data['date_to'])) {
            throw new \InvalidArgumentException("Return date must be after borrow date");
        }
        
        if (strtotime($data['date_from']) < time()) {
            throw new \InvalidArgumentException("Borrow date cannot be in the past");
        }
        
        // Check item availability
        if (!$this->isItemAvailable($data['item_id'], $data['quantity'], $data['date_from'], $data['date_to'])) {
            throw new \InvalidArgumentException("Item is not available for the requested period");
        }
        
        return $this->create($data);
    }
    
    /**
     * Get all requests with item and user information
     */
    public function getAllRequests(array $filters = [], int $limit = 0, int $offset = 0): array {
        $sql = "
            SELECT br.*, 
                   i.name as item_name,
                   i.serial_number,
                   i.sensitive_level,
                   c.name as category_name,
                   l.name as location_name,
                   u.username,
                   u.first_name,
                   u.last_name,
                   u.email,
                   approver.username as approved_by_username,
                   approver.first_name as approved_by_first_name,
                   approver.last_name as approved_by_last_name
            FROM borrow_requests br
            JOIN items i ON br.item_id = i.id
            LEFT JOIN categories c ON i.category_id = c.id
            LEFT JOIN locations l ON i.location_id = l.id
            LEFT JOIN auth_db.users u ON br.user_id = u.id
            LEFT JOIN auth_db.users approver ON br.approved_by = approver.id
        ";
        
        $params = [];
        $whereConditions = [];
        
        // Apply filters
        if (!empty($filters['user_id'])) {
            $whereConditions[] = "br.user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (!empty($filters['item_id'])) {
            $whereConditions[] = "br.item_id = ?";
            $params[] = $filters['item_id'];
        }
        
        if (!empty($filters['status'])) {
            $whereConditions[] = "br.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['date_from'])) {
            $whereConditions[] = "br.date_from >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereConditions[] = "br.date_to <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['overdue'])) {
            $whereConditions[] = "br.status = 'Approved' AND br.date_to < NOW()";
        }
        
        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(' AND ', $whereConditions);
        }
        
        $sql .= " ORDER BY br.created_at DESC";
        
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
     * Get request by ID with related information
     */
    public function getRequestById(int $id): ?array {
        $sql = "
            SELECT br.*, 
                   i.name as item_name,
                   i.serial_number,
                   i.sensitive_level,
                   i.photo_path,
                   c.name as category_name,
                   l.name as location_name,
                   u.username,
                   u.first_name,
                   u.last_name,
                   u.email,
                   u.phone,
                   approver.username as approved_by_username,
                   approver.first_name as approved_by_first_name,
                   approver.last_name as approved_by_last_name
            FROM borrow_requests br
            JOIN items i ON br.item_id = i.id
            LEFT JOIN categories c ON i.category_id = c.id
            LEFT JOIN locations l ON i.location_id = l.id
            LEFT JOIN auth_db.users u ON br.user_id = u.id
            LEFT JOIN auth_db.users approver ON br.approved_by = approver.id
            WHERE br.id = ?
        ";
        
        $stmt = $this->execute($sql, [$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Get user's requests
     */
    public function getUserRequests(int $userId, string $status = null): array {
        $filters = ['user_id' => $userId];
        if ($status) {
            $filters['status'] = $status;
        }
        
        return $this->getAllRequests($filters);
    }
    
    /**
     * Get pending requests for approval
     */
    public function getPendingRequests(): array {
        return $this->getAllRequests(['status' => 'Pending']);
    }
    
    /**
     * Get overdue requests
     */
    public function getOverdueRequests(): array {
        return $this->getAllRequests(['overdue' => true]);
    }
    
    /**
     * Approve request
     */
    public function approveRequest(int $requestId, int $approverId): bool {
        $request = $this->getRequestById($requestId);
        if (!$request || $request['status'] !== 'Pending') {
            return false;
        }
        
        // Check if item is still available
        if (!$this->isItemAvailable($request['item_id'], $request['quantity'], $request['date_from'], $request['date_to'], $requestId)) {
            throw new \Exception("Item is no longer available for the requested period");
        }
        
        // Start transaction
        $this->db->beginTransaction();
        
        try {
            // Update request status
            $updateData = [
                'status' => 'Approved',
                'approved_by' => $approverId,
                'approved_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $success = $this->update($requestId, $updateData);
            
            if ($success) {
                // Reserve the item if the borrow period has started
                if (strtotime($request['date_from']) <= time()) {
                    $this->reserveItemForRequest($request['item_id'], $request['quantity'], $approverId);
                }
                
                $this->db->commit();
                return true;
            } else {
                $this->db->rollback();
                return false;
            }
            
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Decline request
     */
    public function declineRequest(int $requestId, int $approverId, string $reason = ''): bool {
        $request = $this->getRequestById($requestId);
        if (!$request || $request['status'] !== 'Pending') {
            return false;
        }
        
        $updateData = [
            'status' => 'Declined',
            'approved_by' => $approverId,
            'approved_at' => date('Y-m-d H:i:s'),
            'declined_reason' => $reason,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->update($requestId, $updateData);
    }
    
    /**
     * Return item
     */
    public function returnItem(int $requestId, int $returnedBy, string $condition = '', string $notes = ''): bool {
        $request = $this->getRequestById($requestId);
        if (!$request || $request['status'] !== 'Approved') {
            return false;
        }
        
        // Start transaction
        $this->db->beginTransaction();
        
        try {
            // Update request status
            $updateData = [
                'status' => 'Returned',
                'returned_at' => date('Y-m-d H:i:s'),
                'return_condition' => $condition,
                'notes' => $notes,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $success = $this->update($requestId, $updateData);
            
            if ($success) {
                // Return the item to inventory
                $this->returnItemToInventory($request['item_id'], $request['quantity'], $returnedBy, $condition);
                
                $this->db->commit();
                return true;
            } else {
                $this->db->rollback();
                return false;
            }
            
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Check if item is available for borrowing
     */
    private function isItemAvailable(int $itemId, int $quantity, string $dateFrom, string $dateTo, int $excludeRequestId = null): bool {
        // Get item details
        $itemSql = "SELECT quantity, status FROM items WHERE id = ?";
        $stmt = $this->execute($itemSql, [$itemId]);
        $item = $stmt->fetch();
        
        if (!$item || $item['status'] !== 'Available') {
            return false;
        }
        
        // Check for overlapping approved requests
        $sql = "
            SELECT SUM(quantity) as reserved_quantity
            FROM borrow_requests 
            WHERE item_id = ? 
              AND status = 'Approved'
              AND (
                  (date_from <= ? AND date_to >= ?) OR
                  (date_from <= ? AND date_to >= ?) OR
                  (date_from >= ? AND date_to <= ?)
              )
        ";
        
        $params = [$itemId, $dateFrom, $dateFrom, $dateTo, $dateTo, $dateFrom, $dateTo];
        
        if ($excludeRequestId) {
            $sql .= " AND id != ?";
            $params[] = $excludeRequestId;
        }
        
        $stmt = $this->execute($sql, $params);
        $result = $stmt->fetch();
        $reservedQuantity = (int) ($result['reserved_quantity'] ?? 0);
        
        $availableQuantity = $item['quantity'] - $reservedQuantity;
        
        return $availableQuantity >= $quantity;
    }
    
    /**
     * Reserve item for approved request
     */
    private function reserveItemForRequest(int $itemId, int $quantity, int $userId): void {
        // Get current item
        $itemSql = "SELECT quantity, status FROM items WHERE id = ?";
        $stmt = $this->execute($itemSql, [$itemId]);
        $item = $stmt->fetch();
        
        if (!$item) {
            throw new \Exception("Item not found");
        }
        
        $newQuantity = $item['quantity'] - $quantity;
        $newStatus = $newQuantity > 0 ? 'Available' : 'Checked Out';
        
        // Update item
        $updateSql = "UPDATE items SET quantity = ?, status = ?, updated_at = NOW() WHERE id = ?";
        $this->execute($updateSql, [$newQuantity, $newStatus, $itemId]);
        
        // Log history
        $historySql = "
            INSERT INTO item_history (item_id, user_id, action, old_status, new_status, notes, created_at)
            VALUES (?, ?, 'Borrowed', ?, ?, ?, NOW())
        ";
        $this->execute($historySql, [
            $itemId, 
            $userId, 
            $item['status'], 
            $newStatus, 
            "Borrowed {$quantity} item(s)"
        ]);
    }
    
    /**
     * Return item to inventory
     */
    private function returnItemToInventory(int $itemId, int $quantity, int $userId, string $condition): void {
        // Get current item
        $itemSql = "SELECT quantity, status, condition_notes FROM items WHERE id = ?";
        $stmt = $this->execute($itemSql, [$itemId]);
        $item = $stmt->fetch();
        
        if (!$item) {
            throw new \Exception("Item not found");
        }
        
        $newQuantity = $item['quantity'] + $quantity;
        $newStatus = 'Available';
        $newCondition = $condition ?: $item['condition_notes'];
        
        // Update item
        $updateSql = "UPDATE items SET quantity = ?, status = ?, condition_notes = ?, updated_at = NOW() WHERE id = ?";
        $this->execute($updateSql, [$newQuantity, $newStatus, $newCondition, $itemId]);
        
        // Log history
        $historySql = "
            INSERT INTO item_history (item_id, user_id, action, old_status, new_status, notes, created_at)
            VALUES (?, ?, 'Returned', ?, ?, ?, NOW())
        ";
        $notes = "Returned {$quantity} item(s)";
        if ($condition) {
            $notes .= " - Condition: {$condition}";
        }
        
        $this->execute($historySql, [
            $itemId, 
            $userId, 
            $item['status'], 
            $newStatus, 
            $notes
        ]);
    }
    
    /**
     * Get request statistics
     */
    public function getRequestStats(): array {
        $stats = [];
        
        // Total requests
        $stmt = $this->execute("SELECT COUNT(*) FROM borrow_requests");
        $stats['total_requests'] = (int) $stmt->fetchColumn();
        
        // Pending requests
        $stmt = $this->execute("SELECT COUNT(*) FROM borrow_requests WHERE status = 'Pending'");
        $stats['pending_requests'] = (int) $stmt->fetchColumn();
        
        // Approved requests
        $stmt = $this->execute("SELECT COUNT(*) FROM borrow_requests WHERE status = 'Approved'");
        $stats['approved_requests'] = (int) $stmt->fetchColumn();
        
        // Overdue requests
        $stmt = $this->execute("SELECT COUNT(*) FROM borrow_requests WHERE status = 'Approved' AND date_to < NOW()");
        $stats['overdue_requests'] = (int) $stmt->fetchColumn();
        
        // Requests by status
        $stmt = $this->execute("SELECT status, COUNT(*) as count FROM borrow_requests GROUP BY status");
        $stats['by_status'] = [];
        while ($row = $stmt->fetch()) {
            $stats['by_status'][$row['status']] = (int) $row['count'];
        }
        
        // Requests this month
        $stmt = $this->execute("
            SELECT COUNT(*) 
            FROM borrow_requests 
            WHERE YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at) = MONTH(NOW())
        ");
        $stats['requests_this_month'] = (int) $stmt->fetchColumn();
        
        return $stats;
    }
    
    /**
     * Get most borrowed items
     */
    public function getMostBorrowedItems(int $limit = 10): array {
        $sql = "
            SELECT i.id, i.name, i.serial_number, c.name as category_name,
                   COUNT(br.id) as borrow_count,
                   SUM(br.quantity) as total_quantity_borrowed
            FROM items i
            LEFT JOIN borrow_requests br ON i.id = br.item_id AND br.status IN ('Approved', 'Returned')
            LEFT JOIN categories c ON i.category_id = c.id
            GROUP BY i.id, i.name, i.serial_number, c.name
            HAVING borrow_count > 0
            ORDER BY borrow_count DESC, total_quantity_borrowed DESC
            LIMIT ?
        ";
        
        $stmt = $this->execute($sql, [$limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get borrowing history for an item
     */
    public function getItemBorrowHistory(int $itemId): array {
        $sql = "
            SELECT br.*, 
                   u.username,
                   u.first_name,
                   u.last_name,
                   approver.username as approved_by_username,
                   approver.first_name as approved_by_first_name,
                   approver.last_name as approved_by_last_name
            FROM borrow_requests br
            LEFT JOIN auth_db.users u ON br.user_id = u.id
            LEFT JOIN auth_db.users approver ON br.approved_by = approver.id
            WHERE br.item_id = ?
            ORDER BY br.created_at DESC
        ";
        
        $stmt = $this->execute($sql, [$itemId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Cancel request (only if pending)
     */
    public function cancelRequest(int $requestId, int $userId): bool {
        $request = $this->getRequestById($requestId);
        
        if (!$request || $request['status'] !== 'Pending') {
            return false;
        }
        
        // Only allow user to cancel their own request or admin to cancel any
        if ($request['user_id'] !== $userId) {
            // Check if user is admin
            $userSql = "SELECT role_id FROM auth_db.users WHERE id = ?";
            $stmt = $this->execute($userSql, [$userId]);
            $user = $stmt->fetch();
            
            if (!$user || !in_array($user['role_id'], [2, 3])) { // Admin or Super-admin
                return false;
            }
        }
        
        $updateData = [
            'status' => 'Cancelled',
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->update($requestId, $updateData);
    }
    
    /**
     * Extend request (change return date)
     */
    public function extendRequest(int $requestId, string $newDateTo, int $approverId): bool {
        $request = $this->getRequestById($requestId);
        
        if (!$request || $request['status'] !== 'Approved') {
            return false;
        }
        
        // Validate new date
        if (strtotime($newDateTo) <= strtotime($request['date_to'])) {
            throw new \InvalidArgumentException("New return date must be after current return date");
        }
        
        // Check availability for extended period
        if (!$this->isItemAvailable($request['item_id'], $request['quantity'], $request['date_to'], $newDateTo, $requestId)) {
            throw new \Exception("Item is not available for the extended period");
        }
        
        $updateData = [
            'date_to' => $newDateTo,
            'notes' => ($request['notes'] ?? '') . "\nExtended until {$newDateTo} by admin",
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->update($requestId, $updateData);
    }
    
    /**
     * Validate request data
     */
    public function validateRequestData(array $data): array {
        $errors = [];
        
        // Item validation
        if (empty($data['item_id'])) {
            $errors['item_id'] = 'Item is required';
        } else {
            $itemSql = "SELECT id, status FROM items WHERE id = ?";
            $stmt = $this->execute($itemSql, [$data['item_id']]);
            $item = $stmt->fetch();
            
            if (!$item) {
                $errors['item_id'] = 'Invalid item selected';
            } elseif ($item['status'] !== 'Available') {
                $errors['item_id'] = 'Item is not available for borrowing';
            }
        }
        
        // Quantity validation
        if (empty($data['quantity']) || !is_numeric($data['quantity']) || (int) $data['quantity'] <= 0) {
            $errors['quantity'] = 'Quantity must be a positive number';
        }
        
        // Date validation
        if (empty($data['date_from'])) {
            $errors['date_from'] = 'Borrow date is required';
        } elseif (strtotime($data['date_from']) < strtotime('today')) {
            $errors['date_from'] = 'Borrow date cannot be in the past';
        }
        
        if (empty($data['date_to'])) {
            $errors['date_to'] = 'Return date is required';
        } elseif (!empty($data['date_from']) && strtotime($data['date_to']) <= strtotime($data['date_from'])) {
            $errors['date_to'] = 'Return date must be after borrow date';
        }
        
        // Purpose validation
        if (empty($data['purpose'])) {
            $errors['purpose'] = 'Purpose is required';
        } elseif (strlen($data['purpose']) > 255) {
            $errors['purpose'] = 'Purpose cannot exceed 255 characters';
        }
        
        return $errors;
    }
}

