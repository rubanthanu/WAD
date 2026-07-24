<?php

require_once __DIR__ . '/app/core/App.php';

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
    case 'contact':
        App::dispatch('ContactController', 'handle');
        break;
    case 'stats':
        App::dispatch('AdminController', 'stats');
        break;
    case 'patients':
        App::dispatch('PatientController', 'handle');
        break;
    
    default:
        Response::error("Endpoint not found", 404);
}
