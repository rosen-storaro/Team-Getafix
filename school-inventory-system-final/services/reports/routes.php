<?php
declare(strict_types=1);

/**
 * Reports Service Routes
 * Defines all reports and analytics endpoints
 */

require_once __DIR__ . '/Controller.php';

$reportsController = new \Services\Reports\Controller();

// Dashboard and overview endpoints
$router->get('/api/reports/dashboard', function() use ($reportsController) {
    $reportsController->getDashboardStats();
});

$router->get('/api/reports/analytics', function() use ($reportsController) {
    $reportsController->generateAnalyticsReport();
});

// Specific report endpoints
$router->get('/api/reports/inventory-usage', function() use ($reportsController) {
    $reportsController->getInventoryUsageReport();
});

$router->get('/api/reports/borrowing-trends', function() use ($reportsController) {
    $reportsController->getBorrowingTrends();
});

$router->get('/api/reports/user-activity', function() use ($reportsController) {
    $reportsController->getUserActivityReport();
});

$router->get('/api/reports/category-usage', function() use ($reportsController) {
    $reportsController->getCategoryUsageStats();
});

$router->get('/api/reports/location-usage', function() use ($reportsController) {
    $reportsController->getLocationUsageStats();
});

$router->get('/api/reports/overdue-items', function() use ($reportsController) {
    $reportsController->getOverdueItemsReport();
});

$router->get('/api/reports/low-stock', function() use ($reportsController) {
    $reportsController->getLowStockReport();
});

$router->get('/api/reports/financial', function() use ($reportsController) {
    $reportsController->getFinancialReport();
});

// Chart data endpoints
$router->get('/api/reports/chart-data', function() use ($reportsController) {
    $reportsController->getChartData();
});

$router->get('/api/reports/chart-types', function() use ($reportsController) {
    $reportsController->getChartTypes();
});

// Export endpoints
$router->post('/api/reports/export', function() use ($reportsController) {
    $reportsController->exportReport();
});

$router->get('/api/reports/report-types', function() use ($reportsController) {
    $reportsController->getReportTypes();
});

$router->get('/storage/exports/{filename}', function($filename) use ($reportsController) {
    $reportsController->downloadExport($filename);
});

