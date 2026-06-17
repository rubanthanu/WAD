<?php
require_once __DIR__ . '/config.php';

$dbObj = new Database();
$db = $dbObj->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

// GET is public
if ($method === 'GET') {
    $query = "SELECT * FROM departments ORDER BY name ASC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $departments = $stmt->fetchAll();
    echo json_encode($departments);
    exit();
}

// POST/PUT/DELETE require Admin role
$userId = $_SESSION['user_id'] ?? $_SERVER['HTTP_X_USER_ID'] ?? null;
$userRole = $_SESSION['role'] ?? $_SERVER['HTTP_X_USER_ROLE'] ?? null;

if (!$userId || $userRole !== 'admin') {
    http_response_code(403);
    echo json_encode(["error" => "Access denied. Administrator privileges required."]);
    exit();
}

$adminObj = new Admin($db);

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $name = $data['name'] ?? '';
    $description = $data['description'] ?? '';

    if (empty($name)) {
        http_response_code(400);
        echo json_encode(["error" => "Department name is required."]);
        exit();
    }

    if ($adminObj->createDepartment($name, $description)) {
        echo json_encode([
            "success" => true,
            "message" => "Department created successfully!"
        ]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Failed to create department."]);
    }
    exit();
}

elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;
    $name = $data['name'] ?? '';
    $description = $data['description'] ?? '';

    if (!$id || empty($name)) {
        http_response_code(400);
        echo json_encode(["error" => "Department ID and name are required."]);
        exit();
    }

    if ($adminObj->updateDepartment($id, $name, $description)) {
        echo json_encode([
            "success" => true,
            "message" => "Department updated successfully!"
        ]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Failed to update department."]);
    }
    exit();
}

elseif ($method === 'DELETE') {
    $id = $_GET['id'] ?? null;
    if (!$id) {
        http_response_code(400);
        echo json_encode(["error" => "Department ID is required."]);
        exit();
    }

    try {
        if ($adminObj->deleteDepartment($id)) {
            echo json_encode([
                "success" => true,
                "message" => "Department deleted successfully."
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Failed to delete department."]);
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(["error" => $e->getMessage()]);
    }
    exit();
}

http_response_code(405);
echo json_encode(["error" => "Method not allowed."]);
