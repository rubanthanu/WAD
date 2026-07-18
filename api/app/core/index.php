<?php

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-User-Id, X-User-Role");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

spl_autoload_register(function (string $className): void {
    
    $directories = [
        __DIR__ . '/',                  
        __DIR__ . '/../helpers/',       
        __DIR__ . '/../models/',        
        __DIR__ . '/../controllers/',   
    ];

    foreach ($directories as $directory) {
        $file = $directory . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

class App
{
    
    public static function dispatch(string $controllerClass, string $method): void
    {
        $database = new Database();
        $db = $database->getConnection();

        $controller = new $controllerClass($db);
        $controller->$method();
    }
}
