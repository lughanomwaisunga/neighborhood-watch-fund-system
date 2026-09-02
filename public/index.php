<?php
/**
 * Application Entry Point
 */

session_start();

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../src/helpers.php';

// Error handling
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
}

// Router
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base_path = str_replace('/public/index.php', '', $_SERVER['SCRIPT_NAME']);
$route = str_replace($base_path, '', $request_uri);
$route = trim($route, '/');

if (empty($route)) {
    $route = 'dashboard';
}

// Route mapping
$routes = [
    'dashboard' => ['controller' => 'DashboardController', 'action' => 'index'],
    'members' => ['controller' => 'MemberController', 'action' => 'index'],
    'members/create' => ['controller' => 'MemberController', 'action' => 'create'],
    'members/search' => ['controller' => 'MemberController', 'action' => 'search'],
    'payments' => ['controller' => 'PaymentController', 'action' => 'index'],
    'payments/initiate' => ['controller' => 'PaymentController', 'action' => 'initiatePayment'],
    'statements' => ['controller' => 'DashboardController', 'action' => 'getAllMembersStatements'],
];

// Parse route parameters
$route_parts = explode('/', $route);
$controller_name = isset($route_parts[0]) ? ucfirst($route_parts[0]) . 'Controller' : 'DashboardController';
$action = isset($route_parts[1]) ? $route_parts[1] : 'index';
$param1 = isset($route_parts[2]) ? intval($route_parts[2]) : null;

try {
    $controller_class = 'App\\Controllers\\' . $controller_name;
    
    if (class_exists($controller_class)) {
        $controller = new $controller_class();
        
        if (method_exists($controller, $action)) {
            $result = $param1 
                ? $controller->$action($param1) 
                : $controller->$action();
            
            // Return JSON or HTML response
            if (is_array($result)) {
                header('Content-Type: application/json');
                echo json_encode($result);
            } else {
                echo $result;
            }
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Action not found', 'status' => 404]);
        }
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Controller not found', 'status' => 404]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => APP_DEBUG ? $e->getMessage() : 'Internal server error',
        'status' => 500
    ]);
}
