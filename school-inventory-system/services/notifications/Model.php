<?php

namespace Services\Notifications;

use Config\Database;

class Model {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Send email notification
     */
    public function sendEmail(string $to, string $subject, string $body, string $type = 'general'): bool {
        try {
            // Log the notification attempt
            $this->logNotification($to, $subject, $type, 'email');
            
            // In a production environment, you would integrate with a real email service
            // For now, we'll simulate email sending and log it
            $this->simulateEmailSending($to, $subject, $body);
            
            return true;
        } catch (\Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send request approval notification
     */
    public function sendRequestApprovalNotification(int $requestId, string $userEmail, string $userName, string $itemName, string $status): bool {
        $subject = "Request " . ucfirst($status) . " - " . $itemName;
        
        $body = $this->generateRequestNotificationBody($userName, $itemName, $status, $requestId);
        
        return $this->sendEmail($userEmail, $subject, $body, 'request_' . $status);
    }
    
    /**
     * Send low stock alert notification
     */
    public function sendLowStockAlert(array $lowStockItems, array $adminEmails): bool {
        $subject = "Low Stock Alert - " . count($lowStockItems) . " items need attention";
        
        $body = $this->generateLowStockAlertBody($lowStockItems);
        
        $success = true;
        foreach ($adminEmails as $email) {
            if (!$this->sendEmail($email, $subject, $body, 'low_stock_alert')) {
                $success = false;
            }
        }
        
        return $success;
    }
    
    /**
     * Send overdue item notification
     */
    public function sendOverdueNotification(array $overdueItems, array $userEmails): bool {
        $success = true;
        
        foreach ($userEmails as $userId => $email) {
            $userOverdueItems = array_filter($overdueItems, function($item) use ($userId) {
                return $item['user_id'] == $userId;
            });
            
            if (!empty($userOverdueItems)) {
                $subject = "Overdue Items - Please Return " . count($userOverdueItems) . " item(s)";
                $body = $this->generateOverdueNotificationBody($userOverdueItems);
                
                if (!$this->sendEmail($email, $subject, $body, 'overdue_reminder')) {
                    $success = false;
                }
            }
        }
        
        return $success;
    }
    
    /**
     * Log notification attempt
     */
    private function logNotification(string $recipient, string $subject, string $type, string $method): void {
        $sql = "INSERT INTO notification_logs (recipient, subject, type, method, sent_at) VALUES (?, ?, ?, ?, NOW())";
        $this->db->execute($sql, [$recipient, $subject, $type, $method]);
    }
    
    /**
     * Simulate email sending (replace with real email service in production)
     */
    private function simulateEmailSending(string $to, string $subject, string $body): void {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'to' => $to,
            'subject' => $subject,
            'body_preview' => substr(strip_tags($body), 0, 100) . '...'
        ];
        
        // Log to file for debugging
        $logFile = __DIR__ . '/../../storage/email_log.txt';
        $logLine = json_encode($logEntry) . "\n";
        file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
        
        // In production, replace this with actual email sending:
        // - PHPMailer
        // - SendGrid API
        // - AWS SES
        // - Mailgun API
        // etc.
    }
    
    /**
     * Generate request notification email body
     */
    private function generateRequestNotificationBody(string $userName, string $itemName, string $status, int $requestId): string {
        $statusMessage = match($status) {
            'approved' => 'has been approved! You can now pick up the item.',
            'declined' => 'has been declined. Please contact an administrator for more information.',
            'returned' => 'has been marked as returned. Thank you for returning the item on time.',
            default => 'status has been updated.'
        };
        
        return "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #2c5aa0;'>School Inventory Management System</h2>
                
                <p>Dear {$userName},</p>
                
                <p>Your request for <strong>{$itemName}</strong> (Request #{$requestId}) {$statusMessage}</p>
                
                " . ($status === 'approved' ? "
                <div style='background-color: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                    <h4 style='color: #155724; margin-top: 0;'>Next Steps:</h4>
                    <ul style='color: #155724;'>
                        <li>Contact the administrator to arrange pickup</li>
                        <li>Bring a valid ID when picking up the item</li>
                        <li>Remember to return the item by the agreed date</li>
                        <li>Report any issues immediately</li>
                    </ul>
                </div>
                " : "") . "
                
                <p>You can view your request details by logging into the system.</p>
                
                <p>Best regards,<br>
                School Inventory Management Team</p>
                
                <hr style='margin-top: 30px; border: none; border-top: 1px solid #eee;'>
                <p style='font-size: 12px; color: #666;'>
                    This is an automated message. Please do not reply to this email.
                </p>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Generate low stock alert email body
     */
    private function generateLowStockAlertBody(array $lowStockItems): string {
        $itemsList = '';
        foreach ($lowStockItems as $item) {
            $itemsList .= "
                <tr>
                    <td style='padding: 8px; border-bottom: 1px solid #ddd;'>{$item['name']}</td>
                    <td style='padding: 8px; border-bottom: 1px solid #ddd;'>{$item['category_name']}</td>
                    <td style='padding: 8px; border-bottom: 1px solid #ddd; text-align: center;'>{$item['quantity']}</td>
                    <td style='padding: 8px; border-bottom: 1px solid #ddd; text-align: center;'>{$item['low_stock_threshold']}</td>
                </tr>
            ";
        }
        
        return "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #dc3545;'>Low Stock Alert</h2>
                
                <p>The following items are running low on stock and need attention:</p>
                
                <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                    <thead>
                        <tr style='background-color: #f8f9fa;'>
                            <th style='padding: 12px; border-bottom: 2px solid #ddd; text-align: left;'>Item Name</th>
                            <th style='padding: 12px; border-bottom: 2px solid #ddd; text-align: left;'>Category</th>
                            <th style='padding: 12px; border-bottom: 2px solid #ddd; text-align: center;'>Current Stock</th>
                            <th style='padding: 12px; border-bottom: 2px solid #ddd; text-align: center;'>Threshold</th>
                        </tr>
                    </thead>
                    <tbody>
                        {$itemsList}
                    </tbody>
                </table>
                
                <p>Please consider restocking these items to ensure availability for users.</p>
                
                <p>Best regards,<br>
                School Inventory Management System</p>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Generate overdue notification email body
     */
    private function generateOverdueNotificationBody(array $overdueItems): string {
        $itemsList = '';
        foreach ($overdueItems as $item) {
            $daysOverdue = ceil((time() - strtotime($item['return_date'])) / (60 * 60 * 24));
            $itemsList .= "
                <tr>
                    <td style='padding: 8px; border-bottom: 1px solid #ddd;'>{$item['item_name']}</td>
                    <td style='padding: 8px; border-bottom: 1px solid #ddd;'>{$item['return_date']}</td>
                    <td style='padding: 8px; border-bottom: 1px solid #ddd; text-align: center; color: #dc3545;'>{$daysOverdue} days</td>
                </tr>
            ";
        }
        
        return "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #dc3545;'>Overdue Items - Action Required</h2>
                
                <p>You have the following overdue items that need to be returned immediately:</p>
                
                <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                    <thead>
                        <tr style='background-color: #f8f9fa;'>
                            <th style='padding: 12px; border-bottom: 2px solid #ddd; text-align: left;'>Item Name</th>
                            <th style='padding: 12px; border-bottom: 2px solid #ddd; text-align: left;'>Due Date</th>
                            <th style='padding: 12px; border-bottom: 2px solid #ddd; text-align: center;'>Days Overdue</th>
                        </tr>
                    </thead>
                    <tbody>
                        {$itemsList}
                    </tbody>
                </table>
                
                <div style='background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                    <h4 style='color: #721c24; margin-top: 0;'>Important:</h4>
                    <ul style='color: #721c24;'>
                        <li>Please return these items as soon as possible</li>
                        <li>Late returns may affect your future borrowing privileges</li>
                        <li>Contact an administrator if you need an extension</li>
                        <li>Report any damage or issues immediately</li>
                    </ul>
                </div>
                
                <p>Thank you for your prompt attention to this matter.</p>
                
                <p>Best regards,<br>
                School Inventory Management Team</p>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Get notification statistics
     */
    public function getNotificationStats(int $days = 30): array {
        $sql = "
            SELECT 
                type,
                method,
                COUNT(*) as count,
                DATE(sent_at) as date
            FROM notification_logs 
            WHERE sent_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY type, method, DATE(sent_at)
            ORDER BY sent_at DESC
        ";
        
        $stmt = $this->db->execute($sql, [$days]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
?>

