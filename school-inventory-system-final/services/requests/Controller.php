<?php
declare(strict_types=1);

namespace Services\Requests;

require_once __DIR__ . '/Model.php';

/**
 * Requests Controller
 * Handles borrowing workflow operations
 */
class Controller {
    private Model $model;
    
    public function __construct() {
        $this->model = new Model();
    }
    
    /**
     * Create new borrow request
     */
    public function createRequest(): void {
        requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['error' => 'Method not allowed'], 405);
        }
        
        // Handle both JSON and form data
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true);
        } else {
            $input = $_POST;
        }
        
        $data = [
            'item_id' => (int) ($input['item_id'] ?? 0),
            'user_id' => $_SESSION['user_id'],
            'quantity' => (int) ($input['quantity'] ?? 1),
            'date_from' => $input['date_from'] ?? '',
            'date_to' => $input['date_to'] ?? '',
            'purpose' => trim($input['purpose'] ?? '')
        ];
        
        // Validate input
        $errors = $this->model->validateRequestData($data);
        if (!empty($errors)) {
            jsonResponse(['error' => 'Validation failed', 'errors' => $errors], 400);
        }
        
        try {
            $requestId = $this->model->createRequest($data);
            
            jsonResponse([
                'success' => true,
                'message' => 'Borrow request submitted successfully',
                'request_id' => $requestId
            ], 201);
            
        } catch (\InvalidArgumentException $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            error_log("Create request error: " . $e->getMessage());
            jsonResponse(['error' => 'Failed to create request'], 500);
        }
    }
    
    /**
     * Get all requests (Admin) or user's requests (User)
     */
    public function getRequests(): void {
        requireAuth();
        
        $user = getCurrentUser();
        $filters = [];
        $limit = (int) ($_GET['limit'] ?? 50);
        $offset = (int) ($_GET['offset'] ?? 0);
        
        // Users can only see their own requests
        if ($user['role_name'] === 'User') {
            $filters['user_id'] = $user['id'];
        }
        
        // Apply additional filters from query parameters
        if (!empty($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }
        
        if (!empty($_GET['item_id'])) {
            $filters['item_id'] = (int) $_GET['item_id'];
        }
        
        if (!empty($_GET['user_id']) && in_array($user['role_name'], ['Admin', 'Super-admin'])) {
            $filters['user_id'] = (int) $_GET['user_id'];
        }
        
        if (!empty($_GET['date_from'])) {
            $filters['date_from'] = $_GET['date_from'];
        }
        
        if (!empty($_GET['date_to'])) {
            $filters['date_to'] = $_GET['date_to'];
        }
        
        if (isset($_GET['overdue']) && $_GET['overdue'] === '1') {
            $filters['overdue'] = true;
        }
        
        try {
            $requests = $this->model->getAllRequests($filters, $limit, $offset);
            
            // Get total count for pagination
            $totalCount = $this->model->count($filters);
            
            jsonResponse([
                'requests' => $requests,
                'pagination' => [
                    'total' => $totalCount,
                    'limit' => $limit,
                    'offset' => $offset,
                    'has_more' => ($offset + $limit) < $totalCount
                ]
            ]);
            
        } catch (\Exception $e) {
            error_log("Get requests error: " . $e->getMessage());
            jsonResponse(['error' => 'Failed to fetch requests'], 500);
        }
    }
    
    /**
     * Get single request by ID
     */
    public function getRequest(int $id): void {
        requireAuth();
        
        try {
            $request = $this->model->getRequestById($id);
            
            if (!$request) {
                jsonResponse(['error' => 'Request not found'], 404);
            }
            
            $user = getCurrentUser();
            
            // Users can only see their own requests
            if ($user['role_name'] === 'User' && $request['user_id'] !== $user['id']) {
                jsonResponse(['error' => 'Access denied'], 403);
            }
            
            jsonResponse(['request' => $request]);
            
        } catch (\Exception $e) {
            error_log("Get request error: " . $e->getMessage());
            jsonResponse(['error' => 'Failed to fetch request'], 500);
        }
    }
    
    /**
     * Get pending requests for approval (Admin only)
     */
    public function getPendingRequests(): void {
        requireRole('Admin');
        
        try {
            $requests = $this->model->getPendingRequests();
            jsonResponse(['requests' => $requests]);
            
        } catch (\Exception $e) {
            error_log("Get pending requests error: " . $e->getMessage());
            jsonResponse(['error' => 'Failed to fetch pending requests'], 500);
        }
    }
    
    /**
     * Get overdue requests (Admin only)
     */
    public function getOverdueRequests(): void {
        requireRole('Admin');
        
        try {
            $requests = $this->model->getOverdueRequests();
            jsonResponse(['requests' => $requests]);
            
        } catch (\Exception $e) {
            error_log("Get overdue requests error: " . $e->getMessage());
            jsonResponse(['error' => 'Failed to fetch overdue requests'], 500);
        }
    }
    
    /**
     * Approve request (Admin only)
     */
    public function approveRequest(int $id): void {
        requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            jsonResponse(['error' => 'Method not allowed'], 405);
        }
        
        $user = getCurrentUser();
        
        // Check if user can approve this request
        $request = $this->model->getRequestById($id);
        if (!$request) {
            jsonResponse(['error' => 'Request not found'], 404);
        }
        
        // For sensitive items, only super-admin can approve
        if ($request['sensitive_level'] !== 'No' && $user['role_name'] !== 'Super-admin') {
            jsonResponse(['error' => 'Super-admin approval required for sensitive items'], 403);
        }
        
        // Regular admin can approve non-sensitive items
        if (!in_array($user['role_name'], ['Admin', 'Super-admin'])) {
            jsonResponse(['error' => 'Admin privileges required'], 403);
        }
        
        try {
            $success = $this->model->approveRequest($id, $user['id']);
            
            if ($success) {
                jsonResponse(['success' => true, 'message' => 'Request approved successfully']);
            } else {
                jsonResponse(['error' => 'Request not found or already processed'], 404);
            }
            
        } catch (\Exception $e) {
            error_log("Approve request error: " . $e->getMessage());
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }
    
    /**
     * Decline request (Admin only)
     */
    public function declineRequest(int $id): void {
        requireRole('Admin');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            jsonResponse(['error' => 'Method not allowed'], 405);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $reason = trim($input['reason'] ?? '');
        
        try {
            $success = $this->model->declineRequest($id, $_SESSION['user_id'], $reason);
            
            if ($success) {
                jsonResponse(['success' => true, 'message' => 'Request declined successfully']);
            } else {
                jsonResponse(['error' => 'Request not found or already processed'], 404);
            }
            
        } catch (\Exception $e) {
            error_log("Decline request error: " . $e->getMessage());
            jsonResponse(['error' => 'Failed to decline request'], 500);
        }
    }
    
    /**
     * Return item (Admin only)
     */
    public function returnItem(int $id): void {
        requireRole('Admin');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            jsonResponse(['error' => 'Method not allowed'], 405);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $condition = trim($input['condition'] ?? '');
        $notes = trim($input['notes'] ?? '');
        
        try {
            $success = $this->model->returnItem($id, $_SESSION['user_id'], $condition, $notes);
            
            if ($success) {
                jsonResponse(['success' => true, 'message' => 'Item returned successfully']);
            } else {
                jsonResponse(['error' => 'Request not found or not approved'], 404);
            }
            
        } catch (\Exception $e) {
            error_log("Return item error: " . $e->getMessage());
            jsonResponse(['error' => 'Failed to return item'], 500);
        }
    }
    
    /**
     * Cancel request
     */
    public function cancelRequest(int $id): void {
        requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            jsonResponse(['error' => 'Method not allowed'], 405);
        }
        
        try {
            $success = $this->model->cancelRequest($id, $_SESSION['user_id']);
            
            if ($success) {
                jsonResponse(['success' => true, 'message' => 'Request cancelled successfully']);
            } else {
                jsonResponse(['error' => 'Request not found or cannot be cancelled'], 404);
            }
            
        } catch (\Exception $e) {
            error_log("Cancel request error: " . $e->getMessage());
            jsonResponse(['error' => 'Failed to cancel request'], 500);
        }
    }
    
    /**
     * Extend request (Admin only)
     */
    public function extendRequest(int $id): void {
        requireRole('Admin');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            jsonResponse(['error' => 'Method not allowed'], 405);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $newDateTo = $input['new_date_to'] ?? '';
        
        if (empty($newDateTo)) {
            jsonResponse(['error' => 'New return date is required'], 400);
        }
        
        try {
            $success = $this->model->extendRequest($id, $newDateTo, $_SESSION['user_id']);
            
            if ($success) {
                jsonResponse(['success' => true, 'message' => 'Request extended successfully']);
            } else {
                jsonResponse(['error' => 'Request not found or not approved'], 404);
            }
            
        } catch (\InvalidArgumentException $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            error_log("Extend request error: " . $e->getMessage());
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }
    
    /**
     * Get request statistics (Admin only)
     */
    public function getRequestStats(): void {
        requireRole('Admin');
        
        try {
            $stats = $this->model->getRequestStats();
            jsonResponse(['stats' => $stats]);
            
        } catch (\Exception $e) {
            error_log("Get request stats error: " . $e->getMessage());
            jsonResponse(['error' => 'Failed to fetch request statistics'], 500);
        }
    }
    
    /**
     * Get most borrowed items (Admin only)
     */
    public function getMostBorrowedItems(): void {
        requireRole('Admin');
        
        $limit = (int) ($_GET['limit'] ?? 10);
        
        try {
            $items = $this->model->getMostBorrowedItems($limit);
            jsonResponse(['items' => $items]);
            
        } catch (\Exception $e) {
            error_log("Get most borrowed items error: " . $e->getMessage());
            jsonResponse(['error' => 'Failed to fetch most borrowed items'], 500);
        }
    }
    
    /**
     * Get borrowing history for an item (Admin only)
     */
    public function getItemBorrowHistory(int $itemId): void {
        requireRole('Admin');
        
        try {
            $history = $this->model->getItemBorrowHistory($itemId);
            jsonResponse(['history' => $history]);
            
        } catch (\Exception $e) {
            error_log("Get item borrow history error: " . $e->getMessage());
            jsonResponse(['error' => 'Failed to fetch item borrow history'], 500);
        }
    }
    
    /**
     * Get user's borrowing history
     */
    public function getUserHistory(): void {
        requireAuth();
        
        $user = getCurrentUser();
        $userId = $user['id'];
        
        // Admin can view any user's history
        if (in_array($user['role_name'], ['Admin', 'Super-admin']) && !empty($_GET['user_id'])) {
            $userId = (int) $_GET['user_id'];
        }
        
        $status = $_GET['status'] ?? null;
        
        try {
            $requests = $this->model->getUserRequests($userId, $status);
            jsonResponse(['requests' => $requests]);
            
        } catch (\Exception $e) {
            error_log("Get user history error: " . $e->getMessage());
            jsonResponse(['error' => 'Failed to fetch user history'], 500);
        }
    }
    
    /**
     * Check item availability for borrowing
     */
    public function checkAvailability(): void {
        requireAuth();
        
        $itemId = (int) ($_GET['item_id'] ?? 0);
        $quantity = (int) ($_GET['quantity'] ?? 1);
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';
        
        if (!$itemId || !$dateFrom || !$dateTo) {
            jsonResponse(['error' => 'Item ID, date_from, and date_to are required'], 400);
        }
        
        try {
            // Use reflection to access private method for availability check
            $reflection = new \ReflectionClass($this->model);
            $method = $reflection->getMethod('isItemAvailable');
            $method->setAccessible(true);
            
            $isAvailable = $method->invoke($this->model, $itemId, $quantity, $dateFrom, $dateTo);
            
            jsonResponse([
                'available' => $isAvailable,
                'item_id' => $itemId,
                'quantity' => $quantity,
                'date_from' => $dateFrom,
                'date_to' => $dateTo
            ]);
            
        } catch (\Exception $e) {
            error_log("Check availability error: " . $e->getMessage());
            jsonResponse(['error' => 'Failed to check availability'], 500);
        }
    }
    
    /**
     * Bulk approve requests (Admin only)
     */
    public function bulkApproveRequests(): void {
        requireRole('Admin');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['error' => 'Method not allowed'], 405);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $requestIds = $input['request_ids'] ?? [];
        
        if (empty($requestIds) || !is_array($requestIds)) {
            jsonResponse(['error' => 'Request IDs are required'], 400);
        }
        
        $user = getCurrentUser();
        $results = [];
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($requestIds as $requestId) {
            try {
                $request = $this->model->getRequestById((int) $requestId);
                
                if (!$request) {
                    $results[] = ['id' => $requestId, 'status' => 'error', 'message' => 'Request not found'];
                    $errorCount++;
                    continue;
                }
                
                // Check sensitive item approval
                if ($request['sensitive_level'] !== 'No' && $user['role_name'] !== 'Super-admin') {
                    $results[] = ['id' => $requestId, 'status' => 'error', 'message' => 'Super-admin approval required'];
                    $errorCount++;
                    continue;
                }
                
                $success = $this->model->approveRequest((int) $requestId, $user['id']);
                
                if ($success) {
                    $results[] = ['id' => $requestId, 'status' => 'success', 'message' => 'Approved'];
                    $successCount++;
                } else {
                    $results[] = ['id' => $requestId, 'status' => 'error', 'message' => 'Already processed'];
                    $errorCount++;
                }
                
            } catch (\Exception $e) {
                $results[] = ['id' => $requestId, 'status' => 'error', 'message' => $e->getMessage()];
                $errorCount++;
            }
        }
        
        jsonResponse([
            'success' => true,
            'message' => "Processed {$successCount} approvals, {$errorCount} errors",
            'results' => $results,
            'summary' => [
                'total' => count($requestIds),
                'success' => $successCount,
                'errors' => $errorCount
            ]
        ]);
    }
}

