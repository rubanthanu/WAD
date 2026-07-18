<?php

require_once __DIR__ . '/app/core/index.php';

$route = $_GET['route'] ?? '';

switch ($route) {
    case 'auth':
        App::dispatch('AuthController', 'handle');
        break;
    case 'appointments':
        App::dispatch('AppointmentController', 'handle');
        break;
    case 'doctors':
        App::dispatch('DoctorController', 'handle');
        break;
    case 'departments':
        App::dispatch('DepartmentController', 'handle');
        break;
    case 'stats':
        App::dispatch('AdminController', 'stats');
        break;
    
    default:
        Response::error("Endpoint not found", 404);
}
