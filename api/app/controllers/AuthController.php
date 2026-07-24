<?php

class AuthController extends Controller
{

    public function handle(): void
    {
        $method = $this->getMethod();

        if ($method === 'POST') {
            $data   = $this->getInput();
            $action = $data['action'] ?? '';

            match ($action) {
                'login'    => $this->login($data),
                'register' => (new PatientController($this->db))->register($data),
                'logout'   => $this->logout(),
                default    => Response::error("Invalid action.", 400),
            };
        } elseif ($method === 'GET') {
            $this->checkSession();
        } else {
            Response::error("Method not allowed.", 405);
        }
    }

    private function login(array $data): void
    {
        if (!Validator::required($data, ['email', 'password'])) {
            Response::error("Email and password are required.", 400);
        }

        $email    = trim($data['email']);
        $password = $data['password'];

        if (!Validator::email($email)) {
            Response::error("Please provide a valid email address.", 400);
        }

        $userModel = new User($this->db);
        $userModel->setEmail($email);
        $userModel->setPassword($password);
        $user = $userModel->login();

        if ($user) {
            // Regenerate session ID BEFORE writing session variables
            Session::regenerate();

            Session::set('user_id', $user['id']);
            Session::set('role',    $user['role']);
            Session::set('name',    $user['name']);

            // Seed activity timestamp for session timeout
            Session::set('_last_activity', time());

            Response::json([
                "success"    => true,
                "message"    => "Login successful",
                "user"       => $user,
                "csrf_token" => Session::generateCsrfToken(),
            ]);
        } else {
            Response::error("Invalid email or password.", 401);
        }
    }

    private function logout(): void
    {
        Session::destroy();
        Response::json([
            "success" => true,
            "message" => "Logged out successfully",
        ]);
    }

    private function checkSession(): void
    {
        $userId = Session::get('user_id');
        if ($userId) {
            $userModel = new User($this->db);
            $userModel->setId((int)$userId);
            $user = $userModel->getUserById();

            if ($user) {
                Response::json([
                    "authenticated" => true,
                    "user"          => $user,
                    "csrf_token"    => Session::generateCsrfToken(),
                ]);
            }

            Session::destroy();
        }

        Response::json([
            "authenticated" => false,
            "user"          => null,
        ]);
    }
}
