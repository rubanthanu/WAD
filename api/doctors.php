<?php
require_once __DIR__ . '/config.php';

$dbObj = new Database();
$db = $dbObj->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

// Public access allowed for GET (e.g. public directory), but POST/PUT/DELETE require auth
if ($method === 'GET') {
    $deptId = $_GET['department_id'] ?? null;
    $own = $_GET['own'] ?? null;

    $doctorObj = new Doctor($db);

    if ($own === 'true') {
        $userId = $_SESSION['user_id'] ?? $_SERVER['HTTP_X_USER_ID'] ?? null;
        if (!$userId) {
            http_response_code(401);
            echo json_encode(["error" => "Unauthorized."]);
            exit();
        }
        $avail = $doctorObj->getAvailability($userId);
        echo json_encode($avail);
    } 
    
    elseif ($deptId) {
        $doctors = $doctorObj->fetchByDepartment($deptId);
        echo json_encode($doctors);
    } 
    
    else {
        $doctors = $doctorObj->fetchAllPublic();
        echo json_encode($doctors);
    }
    exit();
}

// Write operations (POST, PUT, DELETE) require authentication
$userId = $_SESSION['user_id'] ?? $_SERVER['HTTP_X_USER_ID'] ?? null;
$userRole = $_SESSION['role'] ?? $_SERVER['HTTP_X_USER_ROLE'] ?? null;

if (!$userId || !$userRole) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized access."]);
    exit();
}

if ($method === 'POST') {
    if ($userRole !== 'admin') {
        http_response_code(403);
        echo json_encode(["error" => "Only administrators can create doctor records."]);
        exit();
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $name = $data['name'] ?? '';
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    $phone = $data['phone'] ?? '';
    $specialization = $data['specialization'] ?? '';
    $department_id = $data['department_id'] ?? null;
    $availability = $data['availability'] ?? 'Monday - Friday (9 AM - 5 PM)';

    if (empty($name) || empty($email) || empty($password) || empty($specialization) || !$department_id) {
        http_response_code(400);
        echo json_encode(["error" => "Name, email, password, specialization, and department are required."]);
        exit();
    }

    try {
        $adminObj = new Admin($db);
        if ($adminObj->createDoctor($name, $email, $password, $phone, $specialization, $department_id, $availability)) {
            echo json_encode([
                "success" => true,
                "message" => "Doctor created successfully!"
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Failed to create doctor."]);
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(["error" => $e->getMessage()]);
    }
    exit();
}

elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);

    if ($userRole === 'doctor') {
        // Doctor updating own availability
        $availability = $data['availability'] ?? '';
        if (empty($availability)) {
            http_response_code(400);
            echo json_encode(["error" => "Availability details cannot be empty."]);
            exit();
        }

        $doctorObj = new Doctor($db);
        if ($doctorObj->updateAvailability($userId, $availability)) {
            echo json_encode([
                "success" => true,
                "message" => "Availability updated successfully!"
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Failed to update availability."]);
        }
    } 
    
    elseif ($userRole === 'admin') {
        // Admin updating doctor details
        $doctor_id = $data['doctor_id'] ?? null;
        $name = $data['name'] ?? '';
        $email = $data['email'] ?? '';
        $phone = $data['phone'] ?? '';
        $specialization = $data['specialization'] ?? '';
        $department_id = $data['department_id'] ?? null;
        $availability = $data['availability'] ?? '';

        if (!$doctor_id || empty($name) || empty($email) || empty($specialization) || !$department_id) {
            http_response_code(400);
            echo json_encode(["error" => "Missing required fields for update."]);
            exit();
        }

        try {
            $adminObj = new Admin($db);
            if ($adminObj->updateDoctor($doctor_id, $name, $email, $phone, $specialization, $department_id, $availability)) {
                echo json_encode([
                    "success" => true,
                    "message" => "Doctor profile updated successfully!"
                ]);
            } else {
                http_response_code(500);
                echo json_encode(["error" => "Failed to update doctor."]);
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
    } 
    
    else {
        http_response_code(403);
        echo json_encode(["error" => "Unauthorized action."]);
    }
    exit();
}

elseif ($method === 'DELETE') {
    if ($userRole !== 'admin') {
        http_response_code(403);
        echo json_encode(["error" => "Only administrators can delete doctors."]);
        exit();
    }

    $doctor_id = $_GET['id'] ?? null;
    if (!$doctor_id) {
        http_response_code(400);
        echo json_encode(["error" => "Doctor ID is required."]);
        exit();
    }

    try {
        $adminObj = new Admin($db);
        if ($adminObj->deleteDoctor($doctor_id)) {
            echo json_encode([
                "success" => true,
                "message" => "Doctor deleted successfully."
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Failed to delete doctor."]);
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(["error" => $e->getMessage()]);
    }
    exit();
}

http_response_code(405);
echo json_encode(["error" => "Method not allowed."]);
