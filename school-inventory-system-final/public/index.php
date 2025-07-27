<?php
declare(strict_types=1);

/**
 * School Inventory Management System
 * Main Front Controller
 * 
 * Handles all incoming requests and routes them to appropriate services
 */

// Start session
session_start();

// Load configuration
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/database.php';

// Load security configuration and initialize security measures
require_once __DIR__ . '/../config/security.php';
Config\Security::initialize();

// Load autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Error handling
set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function($exception) {
    error_log("Uncaught exception: " . $exception->getMessage());
    
    if (env('APP_DEBUG', false)) {
        echo "<pre>Error: " . $exception->getMessage() . "\n";
        echo "File: " . $exception->getFile() . ":" . $exception->getLine() . "\n";
        echo "Trace:\n" . $exception->getTraceAsString() . "</pre>";
    } else {
        http_response_code(500);
        include __DIR__ . '/../app/Views/errors/500.php';
    }
});

/**
 * Simple Router Class
 */
class Router {
    private array $routes = [];
    private string $basePath;
    
    public function __construct(string $basePath = '') {
        $this->basePath = rtrim($basePath, '/');
    }
    
    public function addRoute(string $method, string $pattern, callable $handler): void {
        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => $pattern,
            'handler' => $handler
        ];
    }
    
    public function get(string $pattern, callable $handler): void {
        $this->addRoute('GET', $pattern, $handler);
    }
    
    public function post(string $pattern, callable $handler): void {
        $this->addRoute('POST', $pattern, $handler);
    }
    
    public function put(string $pattern, callable $handler): void {
        $this->addRoute('PUT', $pattern, $handler);
    }
    
    public function delete(string $pattern, callable $handler): void {
        $this->addRoute('DELETE', $pattern, $handler);
    }
    
    public function dispatch(): void {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = str_replace($this->basePath, '', $uri);
        $uri = '/' . trim($uri, '/');
        
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            
            $pattern = '#^' . preg_replace('/\{([^}]+)\}/', '([^/]+)', $route['pattern']) . '$#';
            
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Remove full match
                call_user_func_array($route['handler'], $matches);
                return;
            }
        }
        
        // No route found
        http_response_code(404);
        include __DIR__ . '/../app/Views/errors/404.php';
    }
}

/**
 * Helper Functions
 */
function jsonResponse(array $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function redirectTo(string $url): void {
    header("Location: {$url}");
    exit;
}

function generateCsrfToken(): string {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function getCurrentUser(): ?array {
    if (!isLoggedIn()) {
        return null;
    }
    
    // Get user from session or database
    if (!isset($_SESSION['user_data'])) {
        require_once __DIR__ . '/../services/auth/Model.php';
        $authModel = new \Services\Auth\Model();
        $_SESSION['user_data'] = $authModel->findById($_SESSION['user_id']);
    }
    
    return $_SESSION['user_data'];
}

function hasRole(string $role): bool {
    $user = getCurrentUser();
    if (!$user) return false;
    
    $userRole = $user['role_name'];
    
    // Super-admin has access to everything
    if ($userRole === 'Super-admin') return true;
    
    // Admin has access to User functions
    if ($role === 'User' && $userRole === 'Admin') return true;
    
    // Exact role match
    return $userRole === $role;
}

function requireAuth(): void {
    if (!isLoggedIn()) {
        if (str_starts_with($_SERVER['REQUEST_URI'], '/api/')) {
            jsonResponse(['error' => 'Authentication required'], 401);
        } else {
            redirectTo('/login');
        }
    }
}

function requireRole(string $role): void {
    requireAuth();
    if (!hasRole($role)) {
        if (str_starts_with($_SERVER['REQUEST_URI'], '/api/')) {
            jsonResponse(['error' => 'Insufficient permissions'], 403);
        } else {
            http_response_code(403);
            include __DIR__ . '/../app/Views/errors/403.php';
            exit;
        }
    }
}

// Initialize router
$router = new Router();

// Load service routes
require_once __DIR__ . '/../services/auth/routes.php';
require_once __DIR__ . '/../services/inventory/routes.php';
require_once __DIR__ . '/../services/requests/routes.php';
require_once __DIR__ . '/../services/reports/routes.php';

// Frontend routes
$router->get('/', function() {
    if (isLoggedIn()) {
        include __DIR__ . '/../app/Views/dashboard.php';
    } else {
        include __DIR__ . '/../app/Views/landing.php';
    }
});

$router->get('/dashboard', function() {
    requireAuth();
    include __DIR__ . '/../app/Views/dashboard.php';
});

$router->get('/login', function() {
    if (isLoggedIn()) {
        redirectTo('/');
    }
    include __DIR__ . '/../app/Views/auth/login.php';
});

$router->get('/register', function() {
    if (isLoggedIn()) {
        redirectTo('/');
    }
    include __DIR__ . '/../app/Views/auth/register.php';
});

$router->get('/logout', function() {
    session_destroy();
    redirectTo('/');
});// Frontend routes
$router->get('/dashboard', function() {
    requireAuth();
    include __DIR__ . '/../app/Views/dashboard.php';
});

$router->get('/inventory/add', function() {
    requireRole('Admin');
    include __DIR__ . '/../app/Views/inventory/add.php';
});

$router->get('/inventory/browse', function() {
    requireAuth();
    include __DIR__ . '/../app/Views/inventory/list.php';
});

$router->get('/inventory/{id}', function($id) {
    requireAuth();
    include __DIR__ . '/../app/Views/inventory/detail.php';
});

$router->get('/inventory', function() {
    requireAuth();
    include __DIR__ . '/../app/Views/inventory/list.php';
});

$router->get('/requests/create', function() {
    requireAuth();
    include __DIR__ . '/../app/Views/requests/create.php';
});

$router->get('/requests', function() {
    requireAuth();
    include __DIR__ . '/../app/Views/requests/list.php';
});

$router->get('/admin', function() {
    requireRole('Admin');
    include __DIR__ . '/../app/Views/admin/dashboard.php';
});

$router->get('/admin/users', function() {
    requireRole('Super-admin');
    include __DIR__ . '/../app/Views/admin/users.php';
});

$router->get('/admin/inventory', function() {
    requireRole('Admin');
    include __DIR__ . '/../app/Views/admin/inventory.php';
});

$router->get('/admin/requests', function() {
    requireRole('Admin');
    include __DIR__ . '/../app/Views/admin/requests.php';
});

$router->get('/admin/reports', function() {
    requireRole('Admin');
    include __DIR__ . '/../app/Views/admin/reports.php';
});

// Handle static files (for development)
$requestUri = $_SERVER['REQUEST_URI'];
if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg)$/', $requestUri)) {
    $filePath = __DIR__ . $requestUri;
    if (file_exists($filePath)) {
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'ico' => 'image/x-icon',
            'svg' => 'image/svg+xml'
        ];
        
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
        
        header("Content-Type: {$mimeType}");
        readfile($filePath);
        exit;
    }
}

// Dispatch the request
$router->dispatch();

