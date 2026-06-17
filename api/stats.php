<?php
require_once __DIR__ . '/config.php';

$dbObj = new Database();
$db = $dbObj->getConnection();

$userId = $_SESSION['user_id'] ?? $_SERVER['HTTP_X_USER_ID'] ?? null;
$userRole = $_SESSION['role'] ?? $_SERVER['HTTP_X_USER_ROLE'] ?? null;

if (!$userId || $userRole !== 'admin') {
    http_response_code(403);
    echo json_encode(["error" => "Access denied. Administrator privileges required."]);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $adminObj = new Admin($db);
    try {
        $stats = $adminObj->getSystemStats();
        echo json_encode($stats);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => "Failed to aggregate statistics: " . $e->getMessage()]);
    }
    exit();
}

http_response_code(405);
echo json_encode(["error" => "Method not allowed."]);
