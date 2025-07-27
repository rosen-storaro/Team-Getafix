<?php
declare(strict_types=1);

/**
 * Inventory Service Routes
 * Defines all inventory-related API endpoints
 */

require_once __DIR__ . '/Controller.php';

$inventoryController = new \Services\Inventory\Controller();

// Item management endpoints
$router->get('/api/inventory/items', function() use ($inventoryController) {
    $inventoryController->getItems();
});

$router->get('/api/inventory/items/{id}', function($id) use ($inventoryController) {
    $inventoryController->getItem((int) $id);
});

$router->post('/api/inventory/items', function() use ($inventoryController) {
    $inventoryController->createItem();
});

$router->put('/api/inventory/items/{id}', function($id) use ($inventoryController) {
    $inventoryController->updateItem((int) $id);
});

$router->put('/api/inventory/items/{id}/status', function($id) use ($inventoryController) {
    $inventoryController->updateItemStatus((int) $id);
});

$router->delete('/api/inventory/items/{id}', function($id) use ($inventoryController) {
    $inventoryController->deleteItem((int) $id);
});

$router->post('/api/inventory/items/{id}/photo', function($id) use ($inventoryController) {
    $inventoryController->uploadItemPhoto((int) $id);
});

// Search and filtering endpoints
$router->get('/api/inventory/search', function() use ($inventoryController) {
    $inventoryController->searchItems();
});

$router->get('/api/inventory/available', function() use ($inventoryController) {
    $inventoryController->getAvailableItems();
});

$router->get('/api/inventory/low-stock', function() use ($inventoryController) {
    $inventoryController->getLowStockItems();
});

$router->get('/api/inventory/stats', function() use ($inventoryController) {
    $inventoryController->getInventoryStats();
});

// Category management endpoints
$router->get('/api/inventory/categories', function() use ($inventoryController) {
    $inventoryController->getCategories();
});

$router->post('/api/inventory/categories', function() use ($inventoryController) {
    $inventoryController->createCategory();
});

$router->put('/api/inventory/categories/{id}', function($id) use ($inventoryController) {
    $inventoryController->updateCategory((int) $id);
});

$router->delete('/api/inventory/categories/{id}', function($id) use ($inventoryController) {
    $inventoryController->deleteCategory((int) $id);
});

// Location management endpoints
$router->get('/api/inventory/locations', function() use ($inventoryController) {
    $inventoryController->getLocations();
});

$router->post('/api/inventory/locations', function() use ($inventoryController) {
    $inventoryController->createLocation();
});

$router->put('/api/inventory/locations/{id}', function($id) use ($inventoryController) {
    $inventoryController->updateLocation((int) $id);
});

$router->delete('/api/inventory/locations/{id}', function($id) use ($inventoryController) {
    $inventoryController->deleteLocation((int) $id);
});

