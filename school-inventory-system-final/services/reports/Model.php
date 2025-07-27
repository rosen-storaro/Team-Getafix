<?php
declare(strict_types=1);

namespace Services\Reports;

require_once __DIR__ . '/../../config/database.php';

/**
 * Reports Model
 * Handles analytics, reports, and CSV export functionality
 */
class Model extends \BaseModel {
    
    public function __construct() {
        parent::__construct('inventory');
    }
    
    /**
     * Get inventory usage report
     */
    public function getInventoryUsageReport(string $dateFrom = null, string $dateTo = null): array {
        $dateFrom = $dateFrom ?: date('Y-m-01'); // First day of current month
        $dateTo = $dateTo ?: date('Y-m-d'); // Today
        
        $sql = "
            SELECT 
                i.id,
                i.name,
                i.serial_number,
                c.name as category_name,
                l.name as location_name,
                COUNT(br.id) as total_requests,
                COUNT(CASE WHEN br.status = 'Approved' THEN 1 END) as approved_requests,
                COUNT(CASE WHEN br.status = 'Returned' THEN 1 END) as returned_requests,
                COUNT(CASE WHEN br.status = 'Pending' THEN 1 END) as pending_requests,
                SUM(CASE WHEN br.status IN ('Approved', 'Returned') THEN br.quantity ELSE 0 END) as total_quantity_borrowed,
                AVG(CASE WHEN br.status = 'Returned' THEN DATEDIFF(br.returned_at, br.date_from) END) as avg_borrow_days,
                COUNT(CASE WHEN br.status = 'Approved' AND br.date_to < NOW() THEN 1 END) as overdue_count
            FROM items i
            LEFT JOIN categories c ON i.category_id = c.id
            LEFT JOIN locations l ON i.location_id = l.id
            LEFT JOIN borrow_requests br ON i.id = br.item_id 
                AND br.created_at BETWEEN ? AND ?
            GROUP BY i.id, i.name, i.serial_number, c.name, l.name
            ORDER BY total_requests DESC, i.name ASC
        ";
        
        $stmt = $this->execute($sql, [$dateFrom, $dateTo]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get borrowing trends over time
     */
    public function getBorrowingTrends(string $period = 'month', int $limit = 12): array {
        $dateFormat = match($period) {
            'day' => '%Y-%m-%d',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            'year' => '%Y',
            default => '%Y-%m'
        };
        
        $sql = "
            SELECT 
                DATE_FORMAT(created_at, ?) as period,
                COUNT(*) as total_requests,
                COUNT(CASE WHEN status = 'Approved' THEN 1 END) as approved_requests,
                COUNT(CASE WHEN status = 'Declined' THEN 1 END) as declined_requests,
                COUNT(CASE WHEN status = 'Returned' THEN 1 END) as returned_requests,
                SUM(quantity) as total_quantity_requested
            FROM borrow_requests
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? {$period})
            GROUP BY DATE_FORMAT(created_at, ?)
            ORDER BY period DESC
            LIMIT ?
        ";
        
        $stmt = $this->execute($sql, [$dateFormat, $limit, $dateFormat, $limit]);
        $results = $stmt->fetchAll();
        
        // Reverse to get chronological order
        return array_reverse($results);
    }
    
    /**
     * Get user activity report
     */
    public function getUserActivityReport(string $dateFrom = null, string $dateTo = null): array {
        $dateFrom = $dateFrom ?: date('Y-m-01');
        $dateTo = $dateTo ?: date('Y-m-d');
        
        $sql = "
            SELECT 
                u.id,
                u.username,
                u.first_name,
                u.last_name,
                u.email,
                COUNT(br.id) as total_requests,
                COUNT(CASE WHEN br.status = 'Approved' THEN 1 END) as approved_requests,
                COUNT(CASE WHEN br.status = 'Returned' THEN 1 END) as returned_requests,
                COUNT(CASE WHEN br.status = 'Pending' THEN 1 END) as pending_requests,
                COUNT(CASE WHEN br.status = 'Declined' THEN 1 END) as declined_requests,
                SUM(CASE WHEN br.status IN ('Approved', 'Returned') THEN br.quantity ELSE 0 END) as total_items_borrowed,
                COUNT(CASE WHEN br.status = 'Approved' AND br.date_to < NOW() THEN 1 END) as overdue_count,
                MAX(br.created_at) as last_request_date
            FROM auth_db.users u
            LEFT JOIN borrow_requests br ON u.id = br.user_id 
                AND br.created_at BETWEEN ? AND ?
            WHERE u.role_id = 1  -- Only regular users
            GROUP BY u.id, u.username, u.first_name, u.last_name, u.email
            HAVING total_requests > 0
            ORDER BY total_requests DESC, u.last_name ASC
        ";
        
        $stmt = $this->execute($sql, [$dateFrom, $dateTo]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get category usage statistics
     */
    public function getCategoryUsageStats(string $dateFrom = null, string $dateTo = null): array {
        $dateFrom = $dateFrom ?: date('Y-m-01');
        $dateTo = $dateTo ?: date('Y-m-d');
        
        $sql = "
            SELECT 
                c.id,
                c.name as category_name,
                COUNT(i.id) as total_items,
                COUNT(br.id) as total_requests,
                COUNT(CASE WHEN br.status = 'Approved' THEN 1 END) as approved_requests,
                SUM(CASE WHEN br.status IN ('Approved', 'Returned') THEN br.quantity ELSE 0 END) as total_quantity_borrowed,
                ROUND(COUNT(br.id) / COUNT(i.id), 2) as requests_per_item
            FROM categories c
            LEFT JOIN items i ON c.id = i.category_id
            LEFT JOIN borrow_requests br ON i.id = br.item_id 
                AND br.created_at BETWEEN ? AND ?
            GROUP BY c.id, c.name
            ORDER BY total_requests DESC, c.name ASC
        ";
        
        $stmt = $this->execute($sql, [$dateFrom, $dateTo]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get location usage statistics
     */
    public function getLocationUsageStats(string $dateFrom = null, string $dateTo = null): array {
        $dateFrom = $dateFrom ?: date('Y-m-01');
        $dateTo = $dateTo ?: date('Y-m-d');
        
        $sql = "
            SELECT 
                l.id,
                l.name as location_name,
                COUNT(i.id) as total_items,
                COUNT(br.id) as total_requests,
                COUNT(CASE WHEN br.status = 'Approved' THEN 1 END) as approved_requests,
                SUM(CASE WHEN br.status IN ('Approved', 'Returned') THEN br.quantity ELSE 0 END) as total_quantity_borrowed,
                ROUND(COUNT(br.id) / COUNT(i.id), 2) as requests_per_item
            FROM locations l
            LEFT JOIN items i ON l.id = i.location_id
            LEFT JOIN borrow_requests br ON i.id = br.item_id 
                AND br.created_at BETWEEN ? AND ?
            GROUP BY l.id, l.name
            ORDER BY total_requests DESC, l.name ASC
        ";
        
        $stmt = $this->execute($sql, [$dateFrom, $dateTo]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get overdue items report
     */
    public function getOverdueItemsReport(): array {
        $sql = "
            SELECT 
                br.id as request_id,
                br.date_from,
                br.date_to,
                DATEDIFF(NOW(), br.date_to) as days_overdue,
                br.quantity,
                i.name as item_name,
                i.serial_number,
                c.name as category_name,
                l.name as location_name,
                u.username,
                u.first_name,
                u.last_name,
                u.email,
                u.phone
            FROM borrow_requests br
            JOIN items i ON br.item_id = i.id
            LEFT JOIN categories c ON i.category_id = c.id
            LEFT JOIN locations l ON i.location_id = l.id
            JOIN auth_db.users u ON br.user_id = u.id
            WHERE br.status = 'Approved' AND br.date_to < NOW()
            ORDER BY days_overdue DESC, br.date_to ASC
        ";
        
        $stmt = $this->execute($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Get low stock items report
     */
    public function getLowStockReport(): array {
        $sql = "
            SELECT 
                i.id,
                i.name,
                i.serial_number,
                i.quantity,
                i.low_stock_threshold,
                (i.low_stock_threshold - i.quantity) as shortage,
                c.name as category_name,
                l.name as location_name,
                i.status,
                i.purchase_price,
                i.created_at
            FROM items i
            LEFT JOIN categories c ON i.category_id = c.id
            LEFT JOIN locations l ON i.location_id = l.id
            WHERE i.quantity <= i.low_stock_threshold
            ORDER BY (i.low_stock_threshold - i.quantity) DESC, i.name ASC
        ";
        
        $stmt = $this->execute($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Get financial report (based on purchase prices)
     */
    public function getFinancialReport(string $dateFrom = null, string $dateTo = null): array {
        $dateFrom = $dateFrom ?: date('Y-01-01'); // Start of year
        $dateTo = $dateTo ?: date('Y-m-d');
        
        $sql = "
            SELECT 
                c.name as category_name,
                COUNT(i.id) as total_items,
                SUM(i.purchase_price) as total_value,
                AVG(i.purchase_price) as avg_item_value,
                MIN(i.purchase_price) as min_item_value,
                MAX(i.purchase_price) as max_item_value,
                COUNT(br.id) as total_requests,
                SUM(CASE WHEN br.status IN ('Approved', 'Returned') THEN br.quantity * i.purchase_price ELSE 0 END) as total_borrowed_value
            FROM categories c
            LEFT JOIN items i ON c.id = i.category_id AND i.purchase_price IS NOT NULL
            LEFT JOIN borrow_requests br ON i.id = br.item_id 
                AND br.created_at BETWEEN ? AND ?
            GROUP BY c.id, c.name
            HAVING total_items > 0
            ORDER BY total_value DESC, c.name ASC
        ";
        
        $stmt = $this->execute($sql, [$dateFrom, $dateTo]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get dashboard summary statistics
     */
    public function getDashboardStats(): array {
        $stats = [];
        
        // Basic inventory stats
        $stmt = $this->execute("SELECT COUNT(*) FROM items");
        $stats['total_items'] = (int) $stmt->fetchColumn();
        
        $stmt = $this->execute("SELECT COUNT(*) FROM items WHERE status = 'Available'");
        $stats['available_items'] = (int) $stmt->fetchColumn();
        
        $stmt = $this->execute("SELECT COUNT(*) FROM items WHERE quantity <= low_stock_threshold");
        $stats['low_stock_items'] = (int) $stmt->fetchColumn();
        
        // Request stats
        $stmt = $this->execute("SELECT COUNT(*) FROM borrow_requests WHERE status = 'Pending'");
        $stats['pending_requests'] = (int) $stmt->fetchColumn();
        
        $stmt = $this->execute("SELECT COUNT(*) FROM borrow_requests WHERE status = 'Approved'");
        $stats['active_borrows'] = (int) $stmt->fetchColumn();
        
        $stmt = $this->execute("SELECT COUNT(*) FROM borrow_requests WHERE status = 'Approved' AND date_to < NOW()");
        $stats['overdue_items'] = (int) $stmt->fetchColumn();
        
        // This month stats
        $stmt = $this->execute("
            SELECT COUNT(*) FROM borrow_requests 
            WHERE YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at) = MONTH(NOW())
        ");
        $stats['requests_this_month'] = (int) $stmt->fetchColumn();
        
        // Total value (if purchase prices available)
        $stmt = $this->execute("SELECT SUM(purchase_price * quantity) FROM items WHERE purchase_price IS NOT NULL");
        $stats['total_inventory_value'] = (float) ($stmt->fetchColumn() ?: 0);
        
        // Most active users this month
        $stmt = $this->execute("
            SELECT u.first_name, u.last_name, COUNT(br.id) as request_count
            FROM auth_db.users u
            JOIN borrow_requests br ON u.id = br.user_id
            WHERE YEAR(br.created_at) = YEAR(NOW()) AND MONTH(br.created_at) = MONTH(NOW())
            GROUP BY u.id, u.first_name, u.last_name
            ORDER BY request_count DESC
            LIMIT 5
        ");
        $stats['top_users_this_month'] = $stmt->fetchAll();
        
        // Most borrowed categories
        $stmt = $this->execute("
            SELECT c.name, COUNT(br.id) as request_count
            FROM categories c
            JOIN items i ON c.id = i.category_id
            JOIN borrow_requests br ON i.id = br.item_id
            WHERE br.status IN ('Approved', 'Returned')
            GROUP BY c.id, c.name
            ORDER BY request_count DESC
            LIMIT 5
        ");
        $stats['top_categories'] = $stmt->fetchAll();
        
        return $stats;
    }
    
    /**
     * Export data to CSV format
     */
    public function exportToCSV(string $reportType, array $data, string $filename = null): string {
        $filename = $filename ?: $reportType . '_' . date('Y-m-d_H-i-s') . '.csv';
        $filepath = __DIR__ . '/../../storage/exports/' . $filename;
        
        // Create exports directory if it doesn't exist
        $exportDir = dirname($filepath);
        if (!is_dir($exportDir)) {
            mkdir($exportDir, 0775, true);
        }
        
        $file = fopen($filepath, 'w');
        
        if (empty($data)) {
            fwrite($file, "No data available for export\n");
            fclose($file);
            return $filepath;
        }
        
        // Write headers
        $headers = array_keys($data[0]);
        fputcsv($file, $headers);
        
        // Write data rows
        foreach ($data as $row) {
            fputcsv($file, array_values($row));
        }
        
        // Add metadata footer
        fwrite($file, "\n");
        fwrite($file, "Report Type: {$reportType}\n");
        fwrite($file, "Generated: " . date('Y-m-d H:i:s') . "\n");
        fwrite($file, "Total Records: " . count($data) . "\n");
        
        fclose($file);
        
        return $filepath;
    }
    
    /**
     * Get chart data for borrowing trends
     */
    public function getChartData(string $chartType, array $params = []): array {
        switch ($chartType) {
            case 'borrowing_trends':
                return $this->getBorrowingTrendsChartData($params);
            case 'category_usage':
                return $this->getCategoryUsageChartData($params);
            case 'location_usage':
                return $this->getLocationUsageChartData($params);
            case 'user_activity':
                return $this->getUserActivityChartData($params);
            case 'status_distribution':
                return $this->getStatusDistributionChartData($params);
            default:
                throw new \InvalidArgumentException("Unknown chart type: {$chartType}");
        }
    }
    
    /**
     * Get borrowing trends chart data
     */
    private function getBorrowingTrendsChartData(array $params): array {
        $period = $params['period'] ?? 'month';
        $limit = $params['limit'] ?? 12;
        
        $trends = $this->getBorrowingTrends($period, $limit);
        
        return [
            'type' => 'line',
            'data' => [
                'labels' => array_column($trends, 'period'),
                'datasets' => [
                    [
                        'label' => 'Total Requests',
                        'data' => array_column($trends, 'total_requests'),
                        'borderColor' => 'rgb(75, 192, 192)',
                        'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                        'tension' => 0.1
                    ],
                    [
                        'label' => 'Approved Requests',
                        'data' => array_column($trends, 'approved_requests'),
                        'borderColor' => 'rgb(54, 162, 235)',
                        'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                        'tension' => 0.1
                    ]
                ]
            ],
            'options' => [
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Borrowing Trends Over Time'
                    ]
                ],
                'scales' => [
                    'y' => [
                        'beginAtZero' => true
                    ]
                ]
            ]
        ];
    }
    
    /**
     * Get category usage chart data
     */
    private function getCategoryUsageChartData(array $params): array {
        $dateFrom = $params['date_from'] ?? null;
        $dateTo = $params['date_to'] ?? null;
        
        $categories = $this->getCategoryUsageStats($dateFrom, $dateTo);
        
        return [
            'type' => 'doughnut',
            'data' => [
                'labels' => array_column($categories, 'category_name'),
                'datasets' => [
                    [
                        'label' => 'Requests by Category',
                        'data' => array_column($categories, 'total_requests'),
                        'backgroundColor' => [
                            'rgba(255, 99, 132, 0.8)',
                            'rgba(54, 162, 235, 0.8)',
                            'rgba(255, 205, 86, 0.8)',
                            'rgba(75, 192, 192, 0.8)',
                            'rgba(153, 102, 255, 0.8)',
                            'rgba(255, 159, 64, 0.8)',
                            'rgba(199, 199, 199, 0.8)',
                            'rgba(83, 102, 255, 0.8)'
                        ]
                    ]
                ]
            ],
            'options' => [
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Requests by Category'
                    ],
                    'legend' => [
                        'position' => 'bottom'
                    ]
                ]
            ]
        ];
    }
    
    /**
     * Get location usage chart data
     */
    private function getLocationUsageChartData(array $params): array {
        $dateFrom = $params['date_from'] ?? null;
        $dateTo = $params['date_to'] ?? null;
        
        $locations = $this->getLocationUsageStats($dateFrom, $dateTo);
        
        return [
            'type' => 'bar',
            'data' => [
                'labels' => array_column($locations, 'location_name'),
                'datasets' => [
                    [
                        'label' => 'Total Items',
                        'data' => array_column($locations, 'total_items'),
                        'backgroundColor' => 'rgba(54, 162, 235, 0.8)',
                        'borderColor' => 'rgba(54, 162, 235, 1)',
                        'borderWidth' => 1
                    ],
                    [
                        'label' => 'Total Requests',
                        'data' => array_column($locations, 'total_requests'),
                        'backgroundColor' => 'rgba(255, 99, 132, 0.8)',
                        'borderColor' => 'rgba(255, 99, 132, 1)',
                        'borderWidth' => 1
                    ]
                ]
            ],
            'options' => [
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Usage by Location'
                    ]
                ],
                'scales' => [
                    'y' => [
                        'beginAtZero' => true
                    ]
                ]
            ]
        ];
    }
    
    /**
     * Get user activity chart data
     */
    private function getUserActivityChartData(array $params): array {
        $dateFrom = $params['date_from'] ?? null;
        $dateTo = $params['date_to'] ?? null;
        $limit = $params['limit'] ?? 10;
        
        $users = array_slice($this->getUserActivityReport($dateFrom, $dateTo), 0, $limit);
        
        return [
            'type' => 'horizontalBar',
            'data' => [
                'labels' => array_map(function($user) {
                    return $user['first_name'] . ' ' . $user['last_name'];
                }, $users),
                'datasets' => [
                    [
                        'label' => 'Total Requests',
                        'data' => array_column($users, 'total_requests'),
                        'backgroundColor' => 'rgba(75, 192, 192, 0.8)',
                        'borderColor' => 'rgba(75, 192, 192, 1)',
                        'borderWidth' => 1
                    ]
                ]
            ],
            'options' => [
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Top Active Users'
                    ]
                ],
                'scales' => [
                    'x' => [
                        'beginAtZero' => true
                    ]
                ]
            ]
        ];
    }
    
    /**
     * Get status distribution chart data
     */
    private function getStatusDistributionChartData(array $params): array {
        $sql = "
            SELECT status, COUNT(*) as count
            FROM borrow_requests
            GROUP BY status
            ORDER BY count DESC
        ";
        
        $stmt = $this->execute($sql);
        $statusData = $stmt->fetchAll();
        
        return [
            'type' => 'pie',
            'data' => [
                'labels' => array_column($statusData, 'status'),
                'datasets' => [
                    [
                        'label' => 'Request Status Distribution',
                        'data' => array_column($statusData, 'count'),
                        'backgroundColor' => [
                            'rgba(255, 99, 132, 0.8)',   // Pending
                            'rgba(54, 162, 235, 0.8)',   // Approved
                            'rgba(255, 205, 86, 0.8)',   // Returned
                            'rgba(75, 192, 192, 0.8)',   // Declined
                            'rgba(153, 102, 255, 0.8)',  // Cancelled
                        ]
                    ]
                ]
            ],
            'options' => [
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Request Status Distribution'
                    ],
                    'legend' => [
                        'position' => 'bottom'
                    ]
                ]
            ]
        ];
    }
}

