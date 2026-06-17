<?php
require_once __DIR__ . '/config.php';

$dbObj = new Database();
$db = $dbObj->getConnection();

// Robust authentication extraction
$userId = $_SESSION['user_id'] ?? $_SERVER['HTTP_X_USER_ID'] ?? null;
$userRole = $_SESSION['role'] ?? $_SERVER['HTTP_X_USER_ROLE'] ?? null;

if (!$userId || !$userRole) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized access. Please login."]);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if ($userRole === 'admin') {
        $apptObj = new Appointment($db);
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $appointments = $apptObj->fetchAll($search, $status);
        echo json_encode($appointments);
    } 
    
    elseif ($userRole === 'doctor') {
        $doctorObj = new Doctor($db);
        $schedule = $doctorObj->getSchedule($userId);
        echo json_encode($schedule);
    } 
    
    elseif ($userRole === 'patient') {
        $patientObj = new Patient($db);
        $appointments = $patientObj->getBookedAppointments($userId);
        echo json_encode($appointments);
    }
    exit();
}

elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if ($userRole !== 'patient' && $userRole !== 'admin') {
        http_response_code(403);
        echo json_encode(["error" => "Only patients or admins can book appointments."]);
        exit();
    }

    // Book appointment
    $patient_id = $userRole === 'admin' ? ($data['patient_id'] ?? null) : $userId;
    $doctor_id = $data['doctor_id'] ?? null;
    $date = $data['appointment_date'] ?? null;
    $time = $data['appointment_time'] ?? null;
    $notes = $data['notes'] ?? '';

    if (!$patient_id || !$doctor_id || !$date || !$time) {
        http_response_code(400);
        echo json_encode(["error" => "Doctor, date, and time are required fields."]);
        exit();
    }

    try {
        $apptObj = new Appointment($db);
        $apptId = $apptObj->book($patient_id, $doctor_id, $date, $time, $notes);
        if ($apptId) {
            echo json_encode([
                "success" => true,
                "message" => "Appointment booked successfully!",
                "appointment_id" => $apptId
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Failed to book appointment."]);
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(["error" => $e->getMessage()]);
    }
    exit();
}

elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $appointment_id = $data['appointment_id'] ?? null;
    $status = $data['status'] ?? null;

    if (!$appointment_id || !$status) {
        http_response_code(400);
        echo json_encode(["error" => "Appointment ID and status are required."]);
        exit();
    }

    try {
        if ($userRole === 'doctor') {
            $doctorObj = new Doctor($db);
            $doctorObj->updateAppointmentStatus($appointment_id, $status, $userId);
            echo json_encode([
                "success" => true,
                "message" => "Appointment status updated to '$status'."
            ]);
        } 
        
        elseif ($userRole === 'patient') {
            if ($status !== 'cancelled') {
                http_response_code(403);
                echo json_encode(["error" => "Patients can only cancel appointments."]);
                exit();
            }
            $patientObj = new Patient($db);
            $patientObj->cancelAppointment($appointment_id, $userId);
            echo json_encode([
                "success" => true,
                "message" => "Appointment successfully cancelled."
            ]);
        } 
        
        elseif ($userRole === 'admin') {
            $apptObj = new Appointment($db);
            $apptObj->updateStatus($appointment_id, $status);
            echo json_encode([
                "success" => true,
                "message" => "Appointment status updated to '$status' by administrator."
            ]);
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(["error" => $e->getMessage()]);
    }
    exit();
}

elseif ($method === 'DELETE') {
    if ($userRole !== 'admin') {
        http_response_code(403);
        echo json_encode(["error" => "Only administrators can delete appointments."]);
        exit();
    }

    $appointment_id = $_GET['id'] ?? null;
    if (!$appointment_id) {
        http_response_code(400);
        echo json_encode(["error" => "Appointment ID is required."]);
        exit();
    }

    $apptObj = new Appointment($db);
    if ($apptObj->delete($appointment_id)) {
        echo json_encode([
            "success" => true,
            "message" => "Appointment deleted successfully."
        ]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Failed to delete appointment."]);
    }
    exit();
}

http_response_code(405);
echo json_encode(["error" => "Method not allowed."]);
