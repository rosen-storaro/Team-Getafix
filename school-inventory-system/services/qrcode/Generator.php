<?php

namespace Services\QRCode;

class Generator {
    private $baseUrl;
    private $qrApiUrl;
    
    public function __construct() {
        $this->baseUrl = $_ENV['APP_URL'] ?? 'http://localhost:8000';
        $this->qrApiUrl = 'https://api.qrserver.com/v1/create-qr-code/';
    }
    
    /**
     * Generate QR code for an inventory item
     */
    public function generateItemQRCode(int $itemId, array $itemData = null): array {
        try {
            // Create the URL that the QR code will point to
            $itemUrl = $this->baseUrl . '/inventory/' . $itemId;
            
            // Generate QR code data
            $qrData = $this->createItemQRData($itemId, $itemData, $itemUrl);
            
            // Generate QR code image URL
            $qrImageUrl = $this->generateQRImageUrl($qrData);
            
            // Save QR code info to database (optional)
            $this->saveQRCodeInfo($itemId, $qrData, $qrImageUrl);
            
            return [
                'success' => true,
                'qr_data' => $qrData,
                'qr_image_url' => $qrImageUrl,
                'item_url' => $itemUrl,
                'download_url' => $this->generateDownloadUrl($qrImageUrl, $itemId)
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Generate QR code for a borrow request
     */
    public function generateRequestQRCode(int $requestId, array $requestData = null): array {
        try {
            $requestUrl = $this->baseUrl . '/requests/' . $requestId;
            $qrData = $this->createRequestQRData($requestId, $requestData, $requestUrl);
            $qrImageUrl = $this->generateQRImageUrl($qrData);
            
            return [
                'success' => true,
                'qr_data' => $qrData,
                'qr_image_url' => $qrImageUrl,
                'request_url' => $requestUrl
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Generate bulk QR codes for multiple items
     */
    public function generateBulkQRCodes(array $items): array {
        $results = [];
        $successful = 0;
        $failed = 0;
        
        foreach ($items as $item) {
            $result = $this->generateItemQRCode($item['id'], $item);
            $results[] = [
                'item_id' => $item['id'],
                'item_name' => $item['name'],
                'result' => $result
            ];
            
            if ($result['success']) {
                $successful++;
            } else {
                $failed++;
            }
        }
        
        return [
            'total' => count($items),
            'successful' => $successful,
            'failed' => $failed,
            'results' => $results
        ];
    }
    
    /**
     * Create QR data for an item
     */
    private function createItemQRData(int $itemId, ?array $itemData, string $itemUrl): string {
        // Create a JSON structure with item information
        $qrInfo = [
            'type' => 'inventory_item',
            'id' => $itemId,
            'url' => $itemUrl,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        // Add item details if provided
        if ($itemData) {
            $qrInfo['name'] = $itemData['name'] ?? '';
            $qrInfo['serial_number'] = $itemData['serial_number'] ?? '';
            $qrInfo['category'] = $itemData['category_name'] ?? '';
        }
        
        // For simple QR codes, just return the URL
        // For advanced QR codes, return JSON data
        return $itemUrl; // Simple approach
        // return json_encode($qrInfo); // Advanced approach
    }
    
    /**
     * Create QR data for a request
     */
    private function createRequestQRData(int $requestId, ?array $requestData, string $requestUrl): string {
        return $requestUrl;
    }
    
    /**
     * Generate QR code image URL using external service
     */
    private function generateQRImageUrl(string $data): string {
        $params = [
            'size' => '200x200',
            'data' => urlencode($data),
            'format' => 'png',
            'ecc' => 'M', // Error correction level
            'margin' => '10'
        ];
        
        $queryString = http_build_query($params);
        return $this->qrApiUrl . '?' . $queryString;
    }
    
    /**
     * Generate download URL for QR code
     */
    private function generateDownloadUrl(string $qrImageUrl, int $itemId): string {
        return $this->baseUrl . '/api/qrcode/download/' . $itemId;
    }
    
    /**
     * Save QR code information to database
     */
    private function saveQRCodeInfo(int $itemId, string $qrData, string $qrImageUrl): void {
        try {
            $db = \Config\Database::getInstance();
            
            // Check if QR code already exists for this item
            $checkSql = "SELECT id FROM item_qrcodes WHERE item_id = ?";
            $stmt = $db->execute($checkSql, [$itemId]);
            
            if ($stmt->rowCount() > 0) {
                // Update existing QR code
                $updateSql = "UPDATE item_qrcodes SET qr_data = ?, qr_image_url = ?, updated_at = NOW() WHERE item_id = ?";
                $db->execute($updateSql, [$qrData, $qrImageUrl, $itemId]);
            } else {
                // Insert new QR code
                $insertSql = "INSERT INTO item_qrcodes (item_id, qr_data, qr_image_url, created_at) VALUES (?, ?, ?, NOW())";
                $db->execute($insertSql, [$itemId, $qrData, $qrImageUrl]);
            }
            
        } catch (\Exception $e) {
            // Log error but don't fail the QR generation
            error_log("Failed to save QR code info: " . $e->getMessage());
        }
    }
    
    /**
     * Get QR code information for an item
     */
    public function getItemQRCode(int $itemId): ?array {
        try {
            $db = \Config\Database::getInstance();
            
            $sql = "SELECT * FROM item_qrcodes WHERE item_id = ?";
            $stmt = $db->execute($sql, [$itemId]);
            
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result ?: null;
            
        } catch (\Exception $e) {
            error_log("Failed to get QR code info: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Download QR code image
     */
    public function downloadQRCode(int $itemId): array {
        try {
            $qrInfo = $this->getItemQRCode($itemId);
            
            if (!$qrInfo) {
                throw new \Exception("QR code not found for item");
            }
            
            // Get the image content from the external service
            $imageContent = file_get_contents($qrInfo['qr_image_url']);
            
            if ($imageContent === false) {
                throw new \Exception("Failed to download QR code image");
            }
            
            return [
                'success' => true,
                'content' => $imageContent,
                'content_type' => 'image/png',
                'filename' => 'item_' . $itemId . '_qrcode.png'
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Generate QR codes for all items
     */
    public function generateAllItemQRCodes(): array {
        try {
            $db = \Config\Database::getInstance();
            
            $sql = "
                SELECT i.id, i.name, i.serial_number, c.name as category_name 
                FROM items i 
                LEFT JOIN categories c ON i.category_id = c.id 
                WHERE i.status != 'Retired'
                ORDER BY i.name
            ";
            
            $stmt = $db->execute($sql);
            $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            return $this->generateBulkQRCodes($items);
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Scan QR code and extract information
     */
    public function scanQRCode(string $qrData): array {
        try {
            // Check if it's a URL pointing to our system
            if (strpos($qrData, $this->baseUrl) === 0) {
                return $this->parseSystemQRCode($qrData);
            }
            
            // Try to parse as JSON
            $jsonData = json_decode($qrData, true);
            if ($jsonData && isset($jsonData['type'])) {
                return $this->parseJSONQRCode($jsonData);
            }
            
            // Unknown QR code format
            return [
                'success' => false,
                'error' => 'Unknown QR code format'
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Parse system URL QR code
     */
    private function parseSystemQRCode(string $url): array {
        $path = parse_url($url, PHP_URL_PATH);
        
        if (preg_match('/\/inventory\/(\d+)/', $path, $matches)) {
            return [
                'success' => true,
                'type' => 'inventory_item',
                'item_id' => (int)$matches[1],
                'url' => $url
            ];
        }
        
        if (preg_match('/\/requests\/(\d+)/', $path, $matches)) {
            return [
                'success' => true,
                'type' => 'borrow_request',
                'request_id' => (int)$matches[1],
                'url' => $url
            ];
        }
        
        return [
            'success' => false,
            'error' => 'Unrecognized system URL'
        ];
    }
    
    /**
     * Parse JSON QR code
     */
    private function parseJSONQRCode(array $data): array {
        return [
            'success' => true,
            'type' => $data['type'],
            'data' => $data
        ];
    }
}
?>

