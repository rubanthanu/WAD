<?php
require_once __DIR__ . '/config.php';

$dbObj = new Database();
$db = $dbObj->getConnection();
$userObj = new User($db);

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';

    if ($action === 'login') {
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(["error" => "Email and password are required."]);
            exit();
        }

        $user = $userObj->login($email, $password);
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            
            echo json_encode([
                "success" => true,
                "message" => "Login successful",
                "user" => $user
            ]);
        } else {
            http_response_code(401);
            echo json_encode(["error" => "Invalid email or password."]);
        }
        exit();
    } 
    
    elseif ($action === 'register') {
        $name = $data['name'] ?? '';
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        $phone = $data['phone'] ?? '';
        $role = 'patient'; // Public registration is always for patients

        if (empty($name) || empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(["error" => "Name, email and password are required."]);
            exit();
        }

        try {
            $user_id = $userObj->register($name, $email, $password, $role, $phone);
            if ($user_id) {
                echo json_encode([
                    "success" => true,
                    "message" => "Registration successful. You can now login.",
                    "user_id" => $user_id
                ]);
            } else {
                http_response_code(500);
                echo json_encode(["error" => "Registration failed."]);
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
        exit();
    }

    elseif ($action === 'logout') {
        session_unset();
        session_destroy();
        echo json_encode([
            "success" => true,
            "message" => "Logged out successfully"
        ]);
        exit();
    }
} 

elseif ($method === 'GET') {
    // Check session status
    if (isset($_SESSION['user_id'])) {
        $user = $userObj->getUserById($_SESSION['user_id']);
        if ($user) {
            echo json_encode([
                "authenticated" => true,
                "user" => $user
            ]);
            exit();
        }
    }
    echo json_encode([
        "authenticated" => false,
        "user" => null
    ]);
    exit();
}

http_response_code(405);
echo json_encode(["error" => "Method not allowed."]);
