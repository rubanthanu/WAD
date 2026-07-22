<?php

abstract class Controller
{

    protected PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // -------------------------------------------------------------------------
    // Existing request helpers (unchanged)
    // -------------------------------------------------------------------------

    protected function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    protected function getInput(): array
    {
        $data = json_decode(file_get_contents('php://input'), true);
        return is_array($data) ? $data : [];
    }

    protected function getQueryParam(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    protected function getUserId(): int|string|null
    {
        return Session::getUserId();
    }

    protected function getUserRole(): ?string
    {
        return Session::getUserRole();
    }

    // -------------------------------------------------------------------------
    // Existing auth guards (unchanged)
    // -------------------------------------------------------------------------

    protected function requireAuth(): void
    {
        if (!Session::isAuthenticated()) {
            Response::error("Unauthorized access. Please login.", 401);
        }
    }

    protected function requireRole(string|array $roles, string $message = "Access denied."): void
    {
        $this->requireAuth();

        $allowed = is_array($roles) ? $roles : [$roles];
        if (!in_array($this->getUserRole(), $allowed, true)) {
            Response::error($message, 403);
        }
    }

    // -------------------------------------------------------------------------
    // Fix 8 — Role re-validation from database
    // -------------------------------------------------------------------------

    /**
     * Re-fetches the authenticated user's role from the database and updates
     * the session. If the user no longer exists, the session is destroyed.
     *
     * Usage: call $this->refreshSessionRole() at the top of any admin-only or
     * doctor-only action where stale privilege escalation is a concern.
     * Not called on every request to avoid unnecessary DB queries.
     */
    protected function refreshSessionRole(): void
    {
        $userId = $this->getUserId();
        if (!$userId) {
            return;
        }

        $userModel = new User($this->db);
        $userModel->setId((int)$userId);
        $user = $userModel->getUserById();

        if (!$user) {
            // User was deleted — destroy session and reject request
            Session::destroy();
            Response::error("Your session is no longer valid. Please log in again.", 401);
        }

        // If the role changed in the database, update the session immediately
        if ($user['role'] !== $this->getUserRole()) {
            Session::set('role', $user['role']);
        }
    }

    // -------------------------------------------------------------------------
    // Fix 9 — CSRF token verification
    // -------------------------------------------------------------------------

    /**
     * Validates the CSRF token for state-changing requests (POST, PUT, DELETE).
     *
     * The token must be sent by the frontend in the X-CSRF-Token request header
     * or as a '_csrf' field in the JSON body.
     *
     * The CSRF token is included in login and checkSession responses so the
     * frontend can read and store it for subsequent requests.
     *
     * NOTE: SameSite=Strict on the session cookie already provides primary CSRF
     * protection. This method provides defence-in-depth. Call it from any
     * controller action that modifies data once the frontend sends the header:
     *
     *   $this->verifyCsrf();
     *
     * It is intentionally not auto-called from every POST/PUT/DELETE to avoid
     * breaking the existing frontend before it is updated to send the token.
     */
    protected function verifyCsrf(): void
    {
        // Accept token from custom header (preferred) or request body
        $token = $_SERVER['HTTP_X_CSRF_TOKEN']
              ?? $this->getInput()['_csrf']
              ?? '';

        if (!Session::validateCsrfToken($token)) {
            Response::error("Invalid or missing security token. Please refresh and try again.", 403);
        }
    }
}
