<?php

class PatientController extends Controller
{
    public function handle(): void
    {
        $method = $this->getMethod();

        if ($method === 'POST') {
            $data   = $this->getInput();
            $action = $data['action'] ?? '';

            match ($action) {
                'register' => $this->register($data),
                default    => Response::error("Invalid action.", 400),
            };
        } elseif ($method === 'GET') {
            $this->profile();
        } elseif ($method === 'PUT') {
            $this->profile();
        } else {
            Response::error("Method not allowed.", 405);
        }
    }

    public function register(array $data): void
    {
        if (!Validator::required($data, ['name', 'email', 'password'])) {
            Response::error("Name, email and password are required.", 400);
        }

        $name     = trim($data['name']);
        $email    = trim($data['email']);
        $password = $data['password'];
        $phone    = trim($data['phone'] ?? '');
        $dob      = !empty($data['date_of_birth']) ? trim($data['date_of_birth']) : (!empty($data['dob']) ? trim($data['dob']) : null);
        $gender   = !empty($data['gender']) ? trim($data['gender']) : null;

        if (!Validator::email($email)) {
            Response::error("Please provide a valid email address.", 400);
        }

        if (strlen($password) < 6) {
            Response::error("Password must be at least 6 characters long.", 400);
        }

        if ($gender !== null && !Validator::validGender($gender)) {
            Response::error("Invalid gender selection. Allowed values are male, female, or other.", 400);
        }

        if ($dob !== null && !Validator::validDate($dob)) {
            Response::error("Invalid date of birth format. Must be YYYY-MM-DD.", 400);
        }

        try {
            $patientModel = new Patient($this->db);
            $patientModel->setName($name);
            $patientModel->setEmail($email);
            $patientModel->setPassword($password);
            $patientModel->setPhone($phone);
            $patientModel->setDateOfBirth($dob);
            $patientModel->setGender($gender);

            $userId = $patientModel->registerPatient();

            if ($userId) {
                Response::json([
                    "success" => true,
                    "message" => "Registration successful. You can now login.",
                    "user_id" => $userId,
                ]);
            } else {
                Response::error("Registration failed.", 500);
            }
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    private function profile(): void
    {
        // Placeholder for future endpoints (View profile / Update profile)
        Response::error("Endpoint not implemented yet.", 501);
    }
}
