<?php
declare(strict_types=1);

namespace Services\Reports;

require_once __DIR__ . '/Model.php';

/**
 * Reports Controller
 * Handles analytics, reports, and export operations
 */
class Controller {
    private Model $model;
    
    public function __construct() {
        $this->model = new Model();
    }
    
    /**
     * Get dashboard statistics (Admin only)
     */
    public function getDashboardStats(): void {
        requireRole('Admin');
        
        try {
            $stats = $this->model->getDashboardStats();
            jsonResponse(['stats' => $stats]);
            
        } catch (\Exception $e) {
            error_log("Get dashboard stats error: " . $e->getMessage());
            jsonResponse(['error' => 'Failed to fetch dashboard statistics'], 500);
        }
    }
    
    /**
     * Get inventory usage report (Admin only)
     */
    public function getInventoryUsageReport(): void {
        requireRole('Admin');
        
        $dateFrom = $_GET['date_from'] ?? null;
        $dateTo = $_GET['date_to'] ?? null;
        
        try {
            $report = $this->model->getInventoryUsageReport($dateFrom, $dateTo);
            
            jsonResponse([
                'report' => $report,
                'period' => [
                    'from' => $dateFrom ?: date('Y-m-01'),
                    'to' => $dateTo ?: date('Y-m-d')
                ]
            ]);
            
        } catch (\Exception $e) {
            error_log("Get inventory usage report error: " . $e->getMessage());
            jsonResponse(['error' => 'Failed to generate inventory usage report'], 500);
        }
    }
    
    /**
     * Get borrowing trends (Admin only)
     */
    public function getBorrowingTrends(): void {
        requireRole('Admin');
        
        $period = $_GET['period'] ?? 'month';
        $limit = (int) ($_GET['limit'] ?? 12);
        
        // Validate period
        if (!in_array($period, ['day', 'week', 'month', 'year'])) {
            jsonResponse(['error' => 'Invalid period. Must be: day, week, month, or year'], 400);
        }
        
        try {
            $trends = $this->model->getBorrowingTrends($period, $limit);
            
            jsonResponse([
                'trends' => $trends,
                'period' => $period,
                'limit' => $limit
            ]);
            
        } catch (\Exception $e) {
            error_log("Get borrowing trends error: " . $e->getMessage());
            jsonResponse(['error' => 'Failed to fetch borrowing trends'], 500);
        }
    }
    
    /**
     * Get user activity report (Admin only)
     */
    public function getUserActivityReport(): void {
        requireRole('Admin');
        
        $dateFrom = $_GET['date_from'] ?? null;
        $dateTo = $_GET['date_to'] ?? null;
        
        try {
            $report = $this->model->getUserActivityReport($dateFrom, $dateTo);
            
            jsonResponse([
                'report' => $report,
                'period' => [
                    'from' => $dateFrom ?: date('Y-m-01'),
                    'to' => $dateTo ?: date('Y-m-d')
                ]
            ]);
            
        } catch (\Exception $e) {
            error_log("Get user activity report error: " . $e->getMessage());
            jsonResponse(['error' => 'Failed to generate user activity report'], 500);
        }
    }
    
    /**
     * Get category usage statistics (Admin only)
     */
    public function getCategoryUsageStats(): void {
        requireRole('Admin');
        
        $dateFrom = $_GET['date_from'] ?? null;
        $dateTo = $_GET['date_to'] ?? null;
        
        try {
            $stats = $this->model->getCategoryUsageStats($dateFrom, $dateTo);
            
            jsonResponse([
                'stats' => $stats,
                'period' => [
                    'from' => $dateFrom ?: date('Y-m-01'),
                    'to' => $dateTo ?: date('Y-m-d')
                ]
            ]);
            
        } catch (\Exception $e) {
            error_log("Get category usage stats error: " . $e->getMessage());
            jsonResponse(['error' => 'Failed to fetch category usage statistics'], 500);
        }
    }
    
    /**
     * Get location usage statistics (Admin only)
     */
    public function getLocationUsageStats(): void {
        requireRole('Admin');
        
        $dateFrom = $_GET['date_from'] ?? null;
        $dateTo = $_GET['date_to'] ?? null;
        
        try {
            $stats = $this->model->getLocationUsageStats($dateFrom, $dateTo);
            
            jsonResponse([
                'stats' => $stats,
                'period' => [
                    'from' => $dateFrom ?: date('Y-m-01'),
                    'to' => $dateTo ?: date('Y-m-d')
                ]
            ]);
            
        } catch (\Exception $e) {
            error_log("Get location usage stats error: " . $e->getMessage());
            jsonResponse(['error' => 'Failed to fetch location usage statistics'], 500);
        }
    }
    
    /**
     * Get overdue items report (Admin only)
     */
    public function getOverdueItemsReport(): void {
        requireRole('Admin');
        
        try {
            $report = $this->model->getOverdueItemsReport();
            jsonResponse(['report' => $report]);
            
        } catch (\Exception $e) {
            error_log("Get overdue items report error: " . $e->getMessage());
            jsonResponse(['error' => 'Failed to generate overdue items report'], 500);
        }
    }
    
    /**
     * Get low stock report (Admin only)
     */
    public function getLowStockReport(): void {
        requireRole('Admin');
        
        try {
            $report = $this->model->getLowStockReport();
            jsonResponse(['report' => $report]);
            
        } catch (\Exception $e) {
            error_log("Get low stock report error: " . $e->getMessage());
            jsonResponse(['error' => 'Failed to generate low stock report'], 500);
        }
    }
    
    /**
     * Get financial report (Admin only)
     */
    public function getFinancialReport(): void {
        requireRole('Admin');
        
        $dateFrom = $_GET['date_from'] ?? null;
        $dateTo = $_GET['date_to'] ?? null;
        
        try {
            $report = $this->model->getFinancialReport($dateFrom, $dateTo);
            
            jsonResponse([
                'report' => $report,
                'period' => [
                    'from' => $dateFrom ?: date('Y-01-01'),
                    'to' => $dateTo ?: date('Y-m-d')
                ]
            ]);
            
        } catch (\Exception $e) {
            error_log("Get financial report error: " . $e->getMessage());
            jsonResponse(['error' => 'Failed to generate financial report'], 500);
        }
    }
    
    /**
     * Get chart data (Admin only)
     */
    public function getChartData(): void {
        requireRole('Admin');
        
        $chartType = $_GET['type'] ?? '';
        
        if (empty($chartType)) {
            jsonResponse(['error' => 'Chart type is required'], 400);
        }
        
        $params = [
            'period' => $_GET['period'] ?? 'month',
            'limit' => (int) ($_GET['limit'] ?? 12),
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null
        ];
        
        try {
            $chartData = $this->model->getChartData($chartType, $params);
            jsonResponse(['chart' => $chartData]);
            
        } catch (\InvalidArgumentException $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            error_log("Get chart data error: " . $e->getMessage());
            jsonResponse(['error' => 'Failed to generate chart data'], 500);
        }
    }
    
    /**
     * Export report to CSV (Admin only)
     */
    public function exportReport(): void {
        requireRole('Admin');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['error' => 'Method not allowed'], 405);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $reportType = $input['report_type'] ?? '';
        $dateFrom = $input['date_from'] ?? null;
        $dateTo = $input['date_to'] ?? null;
        $filename = $input['filename'] ?? null;
        
        if (empty($reportType)) {
            jsonResponse(['error' => 'Report type is required'], 400);
        }
        
        try {
            // Get report data based on type
            $data = match($reportType) {
                'inventory_usage' => $this->model->getInventoryUsageReport($dateFrom, $dateTo),
                'user_activity' => $this->model->getUserActivityReport($dateFrom, $dateTo),
                'category_usage' => $this->model->getCategoryUsageStats($dateFrom, $dateTo),
                'location_usage' => $this->model->getLocationUsageStats($dateFrom, $dateTo),
                'overdue_items' => $this->model->getOverdueItemsReport(),
                'low_stock' => $this->model->getLowStockReport(),
                'financial' => $this->model->getFinancialReport($dateFrom, $dateTo),
                'borrowing_trends' => $this->model->getBorrowingTrends($_GET['period'] ?? 'month', (int) ($_GET['limit'] ?? 12)),
                default => throw new \InvalidArgumentException("Unknown report type: {$reportType}")
            };
            
            if (empty($data)) {
                jsonResponse(['error' => 'No data available for export'], 400);
            }
            
            // Export to CSV
            $filepath = $this->model->exportToCSV($reportType, $data, $filename);
            
            // Get relative path for download
            $relativePath = str_replace(__DIR__ . '/../../', '', $filepath);
            
            jsonResponse([
                'success' => true,
                'message' => 'Report exported successfully',
                'download_url' => '/' . $relativePath,
                'filename' => basename($filepath),
                'records_count' => count($data)
            ]);
            
        } catch (\InvalidArgumentException $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            error_log("Export report error: " . $e->getMessage());
            jsonResponse(['error' => 'Failed to export report'], 500);
        }
    }
    
    /**
     * Download exported file
     */
    public function downloadExport(string $filename): void {
        requireRole('Admin');
        
        $filepath = __DIR__ . '/../../storage/exports/' . basename($filename);
        
        if (!file_exists($filepath)) {
            http_response_code(404);
            echo "File not found";
            return;
        }
        
        // Security check - only allow CSV files
        if (pathinfo($filename, PATHINFO_EXTENSION) !== 'csv') {
            http_response_code(403);
            echo "Invalid file type";
            return;
        }
        
        // Set headers for file download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        header('Content-Length: ' . filesize($filepath));
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: 0');
        
        // Output file
        readfile($filepath);
        
        // Optionally delete file after download (uncomment if desired)
        // unlink($filepath);
    }
    
    /**
     * Get available report types (Admin only)
     */
    public function getReportTypes(): void {
        requireRole('Admin');
        
        $reportTypes = [
            [
                'id' => 'inventory_usage',
                'name' => 'Inventory Usage Report',
                'description' => 'Detailed usage statistics for all inventory items',
                'supports_date_range' => true
            ],
            [
                'id' => 'user_activity',
                'name' => 'User Activity Report',
                'description' => 'User borrowing activity and statistics',
                'supports_date_range' => true
            ],
            [
                'id' => 'category_usage',
                'name' => 'Category Usage Statistics',
                'description' => 'Usage statistics grouped by item categories',
                'supports_date_range' => true
            ],
            [
                'id' => 'location_usage',
                'name' => 'Location Usage Statistics',
                'description' => 'Usage statistics grouped by item locations',
                'supports_date_range' => true
            ],
            [
                'id' => 'overdue_items',
                'name' => 'Overdue Items Report',
                'description' => 'Currently overdue borrowed items',
                'supports_date_range' => false
            ],
            [
                'id' => 'low_stock',
                'name' => 'Low Stock Report',
                'description' => 'Items with low stock levels',
                'supports_date_range' => false
            ],
            [
                'id' => 'financial',
                'name' => 'Financial Report',
                'description' => 'Financial overview based on item values',
                'supports_date_range' => true
            ],
            [
                'id' => 'borrowing_trends',
                'name' => 'Borrowing Trends',
                'description' => 'Borrowing trends over time',
                'supports_date_range' => false
            ]
        ];
        
        jsonResponse(['report_types' => $reportTypes]);
    }
    
    /**
     * Get available chart types (Admin only)
     */
    public function getChartTypes(): void {
        requireRole('Admin');
        
        $chartTypes = [
            [
                'id' => 'borrowing_trends',
                'name' => 'Borrowing Trends',
                'description' => 'Line chart showing borrowing trends over time',
                'type' => 'line',
                'supports_period' => true
            ],
            [
                'id' => 'category_usage',
                'name' => 'Category Usage',
                'description' => 'Doughnut chart showing requests by category',
                'type' => 'doughnut',
                'supports_period' => false
            ],
            [
                'id' => 'location_usage',
                'name' => 'Location Usage',
                'description' => 'Bar chart showing usage by location',
                'type' => 'bar',
                'supports_period' => false
            ],
            [
                'id' => 'user_activity',
                'name' => 'User Activity',
                'description' => 'Horizontal bar chart showing top active users',
                'type' => 'horizontalBar',
                'supports_period' => false
            ],
            [
                'id' => 'status_distribution',
                'name' => 'Status Distribution',
                'description' => 'Pie chart showing request status distribution',
                'type' => 'pie',
                'supports_period' => false
            ]
        ];
        
        jsonResponse(['chart_types' => $chartTypes]);
    }
    
    /**
     * Generate comprehensive analytics report (Admin only)
     */
    public function generateAnalyticsReport(): void {
        requireRole('Admin');
        
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        
        try {
            $analytics = [
                'period' => [
                    'from' => $dateFrom,
                    'to' => $dateTo
                ],
                'dashboard_stats' => $this->model->getDashboardStats(),
                'inventory_usage' => $this->model->getInventoryUsageReport($dateFrom, $dateTo),
                'borrowing_trends' => $this->model->getBorrowingTrends('month', 6),
                'category_stats' => $this->model->getCategoryUsageStats($dateFrom, $dateTo),
                'location_stats' => $this->model->getLocationUsageStats($dateFrom, $dateTo),
                'user_activity' => array_slice($this->model->getUserActivityReport($dateFrom, $dateTo), 0, 10),
                'overdue_items' => $this->model->getOverdueItemsReport(),
                'low_stock_items' => $this->model->getLowStockReport(),
                'financial_overview' => $this->model->getFinancialReport($dateFrom, $dateTo)
            ];
            
            jsonResponse(['analytics' => $analytics]);
            
        } catch (\Exception $e) {
            error_log("Generate analytics report error: " . $e->getMessage());
            jsonResponse(['error' => 'Failed to generate analytics report'], 500);
        }
    }
}

