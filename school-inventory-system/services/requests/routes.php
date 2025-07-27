<?php
declare(strict_types=1);

/**
 * Requests Service Routes
 * Defines all request and borrowing workflow endpoints
 */

require_once __DIR__ . '/Controller.php';

$requestsController = new \Services\Requests\Controller();

// Request management endpoints
$router->post('/api/requests', function() use ($requestsController) {
    $requestsController->createRequest();
});

$router->get('/api/requests', function() use ($requestsController) {
    $requestsController->getRequests();
});

$router->get('/api/requests/{id}', function($id) use ($requestsController) {
    $requestsController->getRequest((int) $id);
});

// Request workflow endpoints
$router->put('/api/requests/{id}/approve', function($id) use ($requestsController) {
    $requestsController->approveRequest((int) $id);
});

$router->put('/api/requests/{id}/decline', function($id) use ($requestsController) {
    $requestsController->declineRequest((int) $id);
});

$router->put('/api/requests/{id}/return', function($id) use ($requestsController) {
    $requestsController->returnItem((int) $id);
});

$router->put('/api/requests/{id}/cancel', function($id) use ($requestsController) {
    $requestsController->cancelRequest((int) $id);
});

$router->put('/api/requests/{id}/extend', function($id) use ($requestsController) {
    $requestsController->extendRequest((int) $id);
});

// Admin management endpoints
$router->get('/api/requests/pending', function() use ($requestsController) {
    $requestsController->getPendingRequests();
});

$router->get('/api/requests/overdue', function() use ($requestsController) {
    $requestsController->getOverdueRequests();
});

$router->post('/api/requests/bulk-approve', function() use ($requestsController) {
    $requestsController->bulkApproveRequests();
});

// Statistics and history endpoints
$router->get('/api/requests/stats', function() use ($requestsController) {
    $requestsController->getRequestStats();
});

$router->get('/api/requests/most-borrowed', function() use ($requestsController) {
    $requestsController->getMostBorrowedItems();
});

$router->get('/api/requests/user-history', function() use ($requestsController) {
    $requestsController->getUserHistory();
});

$router->get('/api/requests/item-history/{itemId}', function($itemId) use ($requestsController) {
    $requestsController->getItemBorrowHistory((int) $itemId);
});

// Utility endpoints
$router->get('/api/requests/check-availability', function() use ($requestsController) {
    $requestsController->checkAvailability();
});

