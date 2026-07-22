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
                'register' => $this->register($data),
                'logout'   => $this->logout(),
                default    => Response::error("Invalid action.", 400),
            };
        } elseif ($method === 'GET') {
            $this->checkSession();
        } else {
            Response::error("Method not allowed.", 405);
        }
    }

    // -------------------------------------------------------------------------
    // Fix 2 — session_regenerate_id(true) added after credential verification
    // -------------------------------------------------------------------------

    private function login(array $data): void
    {
        $email    = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($email) || empty($password)) {
            Response::error("Email and password are required.", 400);
        }

        $userModel = new User($this->db);
        $userModel->setEmail($email);
        $userModel->setPassword($password);
        $user = $userModel->login();

        if ($user) {
            // Fix 2: Regenerate session ID BEFORE writing any session variables.
            // Prevents session fixation — any attacker-supplied session ID is
            // invalidated and a fresh, server-generated ID is issued.
            Session::regenerate();

            Session::set('user_id', $user['id']);
            Session::set('role',    $user['role']);
            Session::set('name',    $user['name']);

            // Seed the activity timestamp so checkTimeout works from first request
            Session::set('_last_activity', time());

            Response::json([
                "success"    => true,
                "message"    => "Login successful",
                "user"       => $user,
                "csrf_token" => Session::generateCsrfToken(), // available for frontend use
            ]);
        } else {
            Response::error("Invalid email or password.", 401);
        }
    }

    private function register(array $data): void
    {
        $name     = $data['name'] ?? '';
        $email    = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        $phone    = $data['phone'] ?? '';
        $role     = 'patient';

        if (empty($name) || empty($email) || empty($password)) {
            Response::error("Name, email and password are required.", 400);
        }

        try {
            $userModel = new User($this->db);
            $userModel->setName($name);
            $userModel->setEmail($email);
            $userModel->setPassword($password);
            $userModel->setRole($role);
            $userModel->setPhone($phone);
            $userId = $userModel->register();

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

    private function logout(): void
    {
        // Fix 3: destroy() now also clears the browser cookie
        Session::destroy();
        Response::json([
            "success" => true,
            "message" => "Logged out successfully",
        ]);
    }

    // -------------------------------------------------------------------------
    // Fix 6 — Stale session cleanup
    // -------------------------------------------------------------------------

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
                    "csrf_token"    => Session::generateCsrfToken(), // available for frontend use
                ]);
            }

            // Fix 6: user_id is set but user no longer exists in DB.
            // Destroy the stale session so requireAuth() stops passing it.
            Session::destroy();
        }

        Response::json([
            "authenticated" => false,
            "user"          => null,
        ]);
    }
}
