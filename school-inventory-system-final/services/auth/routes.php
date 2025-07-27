<?php
declare(strict_types=1);

/**
 * Authentication Service Routes
 * Defines all authentication-related API endpoints
 */

require_once __DIR__ . '/Controller.php';

$authController = new \Services\Auth\Controller();

// Authentication endpoints
$router->post('/api/auth/register', function() use ($authController) {
    $authController->register();
});

$router->post('/api/auth/login', function() use ($authController) {
    $authController->login();
});

$router->get('/api/auth/logout', function() use ($authController) {
    $authController->logout();
});

$router->post('/api/auth/logout', function() use ($authController) {
    $authController->logout();
});

// User profile endpoints
$router->get('/api/auth/profile', function() use ($authController) {
    $authController->profile();
});

$router->put('/api/auth/profile', function() use ($authController) {
    $authController->updateProfile();
});

$router->post('/api/auth/change-password', function() use ($authController) {
    $authController->changePassword();
});

// User management endpoints (Admin/Super-admin only)
$router->get('/api/auth/users', function() use ($authController) {
    $authController->getUsers();
});

$router->put('/api/auth/users/{id}/status', function($id) use ($authController) {
    $authController->updateUserStatus((int) $id);
});

$router->put('/api/auth/users/{id}/role', function($id) use ($authController) {
    $authController->updateUserRole((int) $id);
});

$router->post('/api/auth/users/{id}/temp-password', function($id) use ($authController) {
    $authController->generateTempPassword((int) $id);
});

$router->post('/api/auth/change-password', function() use ($authController) {
    $authController->changePassword();
});

$router->get('/api/auth/roles', function() use ($authController) {
    $authController->getRoles();
});

// Google OAuth endpoints (optional)
$router->get('/api/auth/google/callback', function() use ($authController) {
    $authController->googleCallback();
});

// Frontend routes for authentication pages
$router->get('/change-password', function() {
    requireAuth();
    include __DIR__ . '/../../app/Views/auth/change-password.php';
});

