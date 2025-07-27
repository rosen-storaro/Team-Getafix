<?php
declare(strict_types=1);

namespace Services\Inventory;

require_once __DIR__ . '/Model.php';

/**
 * Inventory Controller
 * Handles inventory management operations
 */
class Controller {
    private Model $model;
    
    public function __construct() {
        $this->model = new Model();
    }
    
    /**
     * Get all items with filtering and pagination
     */
    public function getItems(): void {
        requireAuth();
        
        $filters = [];
        $limit = (int) ($_GET['limit'] ?? 50);
        $offset = (int) ($_GET['offset'] ?? 0);
        
        // Apply filters from query parameters
        if (!empty($_GET['search'])) {
            $filters['search'] = trim($_GET['search']);
        }
        
        if (!empty($_GET['category_id'])) {
            $filters['category_id'] = (int) $_GET['category_id'];
        }
        
        if (!empty($_GET['location_id'])) {
            $filters['location_id'] = (int) $_GET['location_id'];
        }
        
        if (!empty($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }
        
        if (!empty($_GET['sensitive_level'])) {
            $filters['sensitive_level'] = $_GET['sensitive_level'];
        }
        
        if (isset($_GET['low_stock']) && $_GET['low_stock'] === '1') {
            $filters['low_stock'] = true;
        }
        
        try {
            $items = $this->model->getAllItems($filters, $limit, $offset);
            
            // Get total count for pagination
            $totalCount = $this->model->count($filters);
            
            jsonResponse([
                'items' => $items,
                'pagination' => [
                    'total' => $totalCount,
                    'limit' => $limit,
                    'offset' => $offset,
                    'has_more' => ($offset + $limit) < $totalCount
                ]
            ]);
            
        } catch (\Exception $e) {
            error_log("Get items error: " . $e->getMessage());
            jsonResponse(['error' => 'Failed to fetch items'], 500);
        }
    }
    
    /**
     * Get single item by ID
     */
    public function getItem(int $id): void {
        requireAuth();
        
        try {
            $item = $this->model->getItemById($id);
            
            if (!$item) {
                jsonResponse(['error' => 'Item not found'], 404);
            }
            
            // Get item history for admins
            $user = getCurrentUser();
            if ($user && in_array($user['role_name'], ['Admin', 'Super-admin'])) {
                $item['history'] = $this->model->getItemHistory($id);
            }
            
            jsonResponse(['item' => $item]);
            
        } catch (\Exception $e) {
            error_log("Get item error: " . $e->getMessage());
            jsonResponse(['error' => 'Failed to fetch item'], 500);
        }
    }
    
    /**
     * Create new item (Admin only)
     */
    public function createItem(): void {
        requireRole('Admin');
        
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
            'name' => trim($input['name'] ?? ''),
            'category_id' => !empty($input['category_id']) ? (int) $input['category_id'] : null,
            'location_id' => !empty($input['location_id']) ? (int) $input['location_id'] : null,
            'serial_number' => trim($input['serial_number'] ?? ''),
            'quantity' => (int) ($input['quantity'] ?? 1),
            'low_stock_threshold' => (int) ($input['low_stock_threshold'] ?? 3),
            'status' => $input['status'] ?? 'Available',
            'condition_notes' => trim($input['condition_notes'] ?? ''),
            'sensitive_level' => $input['sensitive_level'] ?? 'No',
            'description' => trim($input['description'] ?? ''),
            'purchase_date' => !empty($input['purchase_date']) ? $input['purchase_date'] : null,
            'purchase_price' => !empty($input['purchase_price']) ? (float) $input['purchase_price'] : null,
            'warranty_expiry' => !empty($input['warranty_expiry']) ? $input['warranty_expiry'] : null,
            'created_by' => $_SESSION['user_id']
        ];
        
        // Validate input
        $errors = $this->model->validateItemData($data);
        if (!empty($errors)) {
            jsonResponse(['error' => 'Validation failed', 'errors' => $errors], 400);
        }
        
        try {
            $itemId = $this->model->createItem($data);
            
            jsonResponse([
                'success' => true,
                'message' => 'Item created successfully',
                'item_id' => $itemId
            ], 201);
            
        } catch (\Exception $e) {
            error_log("Create item error: " . $e->getMessage());
            jsonResponse(['error' => 'Failed to create item'], 500);
        }
    }
    
    /**
     * Update item (Admin only)
     */
    public function updateItem(int $id): void {
        requireRole('Admin');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            jsonResponse(['error' => 'Method not allowed'], 405);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Only allow updating specific fields
        $allowedFields = [
            'name', 'category_id', 'location_id', 'serial_number', 'quantity',
            'low_stock_threshold', 'status', 'condition_notes', 'sensitive_level',
            'description', 'purchase_date', 'purchase_price', 'warranty_expiry'
        ];
        
        $data = [];
        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                $data[$field] = $input[$field];
            }
        }
        
        if (empty($data)) {
            jsonResponse(['error' => 'No valid fields to update'], 400);
        }
        
        // Validate input
        $errors = $this->model->validateItemData($data, true);
        if (!empty($errors)) {
            jsonResponse(['error' => 'Validation failed', 'errors' => $errors], 400);
        }
        
        try {
            $success = $this->model->updateItem($id, $data, $_SESSION['user_id']);
            
            if ($success) {
                jsonResponse(['success' => true, 'message' => 'Item updated successfully']);
            } else {
                jsonResponse(['error' => 'Item not found'], 404);
            }
            
        } catch (\Exception $e) {
            error_log("Update item error: " . $e->getMessage());
            jsonResponse(['error' => 'Failed to update item'], 500);
        }
    }
    
    /**
     * Update item status (Admin only)
     */
    public function updateItemStatus(int $id): void {
        requireRole('Admin');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            jsonResponse(['error' => 'Method not allowed'], 405);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $status = $input['status'] ?? '';
        $notes = $input['notes'] ?? '';
        
        if (empty($status)) {
            jsonResponse(['error' => 'Status is required'], 400);
        }
        
        try {
            $success = $this->model->updateItemStatus($id, $status, $_SESSION['user_id'], $notes);
            
            if ($success) {
                jsonResponse(['success' => true, 'message' => 'Item status updated successfully']);
            } else {
                jsonResponse(['error' => 'Item not found'], 404);
            }
            
        } catch (\InvalidArgumentException $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            error_log("Update item status error: " . $e->getMessage());
            jsonResponse(['error' => 'Failed to update item status'], 500);
        }
    }
    
    /**
     * Delete item (Super-admin only)
     */
    public function deleteItem(int $id): void {
        requireRole('Super-admin');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            jsonResponse(['error' => 'Method not allowed'], 405);
        }
        
        try {
            // Check if item has active borrow requests
            // This would be implemented when we create the requests service
            
            $success = $this->model->delete($id);
            
            if ($success) {
                jsonResponse(['success' => true, 'message' => 'Item deleted successfully']);
            } else {
                jsonResponse(['error' => 'Item not found'], 404);
            }
            
        } catch (\Exception $e) {
            error_log("Delete item error: " . $e->getMessage());
            jsonResponse(['error' => 'Failed to delete item'], 500);
        }
    }
    
    /**
     * Search items
     */
    public function searchItems(): void {
        requireAuth();
        
        $query = trim($_GET['q'] ?? '');
        $limit = (int) ($_GET['limit'] ?? 20);
        
        if (empty($query)) {
            jsonResponse(['error' => 'Search query is required'], 400);
        }
        
        try {
            $items = $this->model->searchItems($query, $limit);
            jsonResponse(['items' => $items]);
            
        } catch (\Exception $e) {
            error_log("Search items error: " . $e->getMessage());
            jsonResponse(['error' => 'Search failed'], 500);
        }
    }
    
    /**
     * Get available items for borrowing
     */
    public function getAvailableItems(): void {
        requireAuth();
        
        $filters = [];
        
        // Apply filters from query parameters
        if (!empty($_GET['category_id'])) {
            $filters['category_id'] = (int) $_GET['category_id'];
        }
        
        if (!empty($_GET['location_id'])) {
            $filters['location_id'] = (int) $_GET['location_id'];
        }
        
        try {
            $items = $this->model->getAvailableItems($filters);
            jsonResponse(['items' => $items]);
            
        } catch (\Exception $e) {
            error_log("Get available items error: " . $e->getMessage());
            jsonResponse(['error' => 'Failed to fetch available items'], 500);
        }
    }
    
    /**
     * Get low stock items (Admin only)
     */
    public function getLowStockItems(): void {
        requireRole('Admin');
        
        try {
            $items = $this->model->getLowStockItems();
            jsonResponse(['items' => $items]);
            
        } catch (\Exception $e) {
            error_log("Get low stock items error: " . $e->getMessage());
            jsonResponse(['error' => 'Failed to fetch low stock items'], 500);
        }
    }
    
    /**
     * Get inventory statistics (Admin only)
     */
    public function getInventoryStats(): void {
        requireRole('Admin');
        
        try {
            $stats = $this->model->getInventoryStats();
            jsonResponse(['stats' => $stats]);
            
        } catch (\Exception $e) {
            error_log("Get inventory stats error: " . $e->getMessage());
            jsonResponse(['error' => 'Failed to fetch inventory statistics'], 500);
        }
    }
    
    /**
     * Get categories
     */
    public function getCategories(): void {
        requireAuth();
        
        try {
            $categories = $this->model->getCategories();
            jsonResponse(['categories' => $categories]);
            
        } catch (\Exception $e) {
            error_log("Get categories error: " . $e->getMessage());
            jsonResponse(['error' => 'Failed to fetch categories'], 500);
        }
    }
    
    /**
     * Create category (Super-admin only)
     */
    public function createCategory(): void {
        requireRole('Super-admin');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['error' => 'Method not allowed'], 405);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        $data = [
            'name' => trim($input['name'] ?? ''),
            'description' => trim($input['description'] ?? '')
        ];
        
        if (empty($data['name'])) {
            jsonResponse(['error' => 'Category name is required'], 400);
        }
        
        try {
            $categoryId = $this->model->createCategory($data);
            
            jsonResponse([
                'success' => true,
                'message' => 'Category created successfully',
                'category_id' => $categoryId
            ], 201);
            
        } catch (\Exception $e) {
            error_log("Create category error: " . $e->getMessage());
            jsonResponse(['error' => 'Failed to create category'], 500);
        }
    }
    
    /**
     * Update category (Super-admin only)
     */
    public function updateCategory(int $id): void {
        requireRole('Super-admin');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            jsonResponse(['error' => 'Method not allowed'], 405);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        $data = [
            'name' => trim($input['name'] ?? ''),
            'description' => trim($input['description'] ?? '')
        ];
        
        if (empty($data['name'])) {
            jsonResponse(['error' => 'Category name is required'], 400);
        }
        
        try {
            $success = $this->model->updateCategory($id, $data);
            
            if ($success) {
                jsonResponse(['success' => true, 'message' => 'Category updated successfully']);
            } else {
                jsonResponse(['error' => 'Category not found'], 404);
            }
            
        } catch (\Exception $e) {
            error_log("Update category error: " . $e->getMessage());
            jsonResponse(['error' => 'Failed to update category'], 500);
        }
    }
    
    /**
     * Delete category (Super-admin only)
     */
    public function deleteCategory(int $id): void {
        requireRole('Super-admin');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            jsonResponse(['error' => 'Method not allowed'], 405);
        }
        
        try {
            $success = $this->model->deleteCategory($id);
            
            if ($success) {
                jsonResponse(['success' => true, 'message' => 'Category deleted successfully']);
            } else {
                jsonResponse(['error' => 'Category not found'], 404);
            }
            
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'Cannot delete category') !== false) {
                jsonResponse(['error' => $e->getMessage()], 400);
            } else {
                error_log("Delete category error: " . $e->getMessage());
                jsonResponse(['error' => 'Failed to delete category'], 500);
            }
        }
    }
    
    /**
     * Get locations
     */
    public function getLocations(): void {
        requireAuth();
        
        try {
            $locations = $this->model->getLocations();
            jsonResponse(['locations' => $locations]);
            
        } catch (\Exception $e) {
            error_log("Get locations error: " . $e->getMessage());
            jsonResponse(['error' => 'Failed to fetch locations'], 500);
        }
    }
    
    /**
     * Create location (Super-admin only)
     */
    public function createLocation(): void {
        requireRole('Super-admin');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['error' => 'Method not allowed'], 405);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        $data = [
            'name' => trim($input['name'] ?? ''),
            'description' => trim($input['description'] ?? '')
        ];
        
        if (empty($data['name'])) {
            jsonResponse(['error' => 'Location name is required'], 400);
        }
        
        try {
            $locationId = $this->model->createLocation($data);
            
            jsonResponse([
                'success' => true,
                'message' => 'Location created successfully',
                'location_id' => $locationId
            ], 201);
            
        } catch (\Exception $e) {
            error_log("Create location error: " . $e->getMessage());
            jsonResponse(['error' => 'Failed to create location'], 500);
        }
    }
    
    /**
     * Update location (Super-admin only)
     */
    public function updateLocation(int $id): void {
        requireRole('Super-admin');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            jsonResponse(['error' => 'Method not allowed'], 405);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        $data = [
            'name' => trim($input['name'] ?? ''),
            'description' => trim($input['description'] ?? '')
        ];
        
        if (empty($data['name'])) {
            jsonResponse(['error' => 'Location name is required'], 400);
        }
        
        try {
            $success = $this->model->updateLocation($id, $data);
            
            if ($success) {
                jsonResponse(['success' => true, 'message' => 'Location updated successfully']);
            } else {
                jsonResponse(['error' => 'Location not found'], 404);
            }
            
        } catch (\Exception $e) {
            error_log("Update location error: " . $e->getMessage());
            jsonResponse(['error' => 'Failed to update location'], 500);
        }
    }
    
    /**
     * Delete location (Super-admin only)
     */
    public function deleteLocation(int $id): void {
        requireRole('Super-admin');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            jsonResponse(['error' => 'Method not allowed'], 405);
        }
        
        try {
            $success = $this->model->deleteLocation($id);
            
            if ($success) {
                jsonResponse(['success' => true, 'message' => 'Location deleted successfully']);
            } else {
                jsonResponse(['error' => 'Location not found'], 404);
            }
            
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'Cannot delete location') !== false) {
                jsonResponse(['error' => $e->getMessage()], 400);
            } else {
                error_log("Delete location error: " . $e->getMessage());
                jsonResponse(['error' => 'Failed to delete location'], 500);
            }
        }
    }
    
    /**
     * Upload item photo (Admin only)
     */
    public function uploadItemPhoto(int $id): void {
        requireRole('Admin');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['error' => 'Method not allowed'], 405);
        }
        
        if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            jsonResponse(['error' => 'No valid photo uploaded'], 400);
        }
        
        $file = $_FILES['photo'];
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowedTypes)) {
            jsonResponse(['error' => 'Invalid file type. Only JPG, PNG, and GIF are allowed'], 400);
        }
        
        // Validate file size (5MB max)
        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $maxSize) {
            jsonResponse(['error' => 'File size too large. Maximum 5MB allowed'], 400);
        }
        
        try {
            // Create upload directory if it doesn't exist
            $uploadDir = __DIR__ . '/../../storage/uploads/items';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }
            
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'item_' . $id . '_' . time() . '.' . $extension;
            $filepath = $uploadDir . '/' . $filename;
            
            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                throw new \Exception('Failed to save uploaded file');
            }
            
            // Resize image to max 800px width, 70% quality
            $this->resizeImage($filepath, 800, 70);
            
            // Update item with photo path
            $photoPath = 'storage/uploads/items/' . $filename;
            $success = $this->model->updateItem($id, ['photo_path' => $photoPath], $_SESSION['user_id']);
            
            if ($success) {
                jsonResponse([
                    'success' => true,
                    'message' => 'Photo uploaded successfully',
                    'photo_path' => $photoPath
                ]);
            } else {
                // Clean up uploaded file if database update failed
                unlink($filepath);
                jsonResponse(['error' => 'Item not found'], 404);
            }
            
        } catch (\Exception $e) {
            error_log("Upload photo error: " . $e->getMessage());
            jsonResponse(['error' => 'Failed to upload photo'], 500);
        }
    }
    
    /**
     * Resize image to specified width and quality
     */
    private function resizeImage(string $filepath, int $maxWidth, int $quality): void {
        $imageInfo = getimagesize($filepath);
        if (!$imageInfo) {
            return;
        }
        
        [$width, $height, $type] = $imageInfo;
        
        // Skip if image is already smaller than max width
        if ($width <= $maxWidth) {
            return;
        }
        
        // Calculate new dimensions
        $newWidth = $maxWidth;
        $newHeight = (int) ($height * ($maxWidth / $width));
        
        // Create image resource based on type
        switch ($type) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($filepath);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($filepath);
                break;
            case IMAGETYPE_GIF:
                $source = imagecreatefromgif($filepath);
                break;
            default:
                return;
        }
        
        if (!$source) {
            return;
        }
        
        // Create new image
        $resized = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preserve transparency for PNG and GIF
        if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_GIF) {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
            imagefilledrectangle($resized, 0, 0, $newWidth, $newHeight, $transparent);
        }
        
        // Resize image
        imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        
        // Save resized image
        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($resized, $filepath, $quality);
                break;
            case IMAGETYPE_PNG:
                imagepng($resized, $filepath, (int) (9 - ($quality / 10)));
                break;
            case IMAGETYPE_GIF:
                imagegif($resized, $filepath);
                break;
        }
        
        // Clean up
        imagedestroy($source);
        imagedestroy($resized);
    }
}

