<?php

class Session
{
    /** Inactivity timeout: 30 minutes */
    private const TIMEOUT_SECONDS = 1800;

    // -------------------------------------------------------------------------
    // Core accessors (unchanged signatures — no breaking changes)
    // -------------------------------------------------------------------------

    public static function getUserId(): int|string|null
    {
        return $_SESSION['user_id'] ?? null;
    }

    public static function getUserRole(): ?string
    {
        return $_SESSION['role'] ?? null;
    }

    /**
     * Fix 5 — Improved authentication check.
     * Requires user_id to be set AND be a positive integer.
     * Prevents spoofing via user_id = 0 or user_id = null.
     */
    public static function isAuthenticated(): bool
    {
        $id = self::getUserId();
        return $id !== null && (int)$id > 0;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    // -------------------------------------------------------------------------
    // Fix 3 — Improved destroy(): clears server data AND browser cookie
    // -------------------------------------------------------------------------

    /**
     * Fully terminates the session:
     *  1. Clears $_SESSION superglobal in-memory.
     *  2. Expires the browser session cookie immediately.
     *  3. Destroys the server-side session file.
     */
    public static function destroy(): void
    {
        // 1. Clear all session variables from memory
        $_SESSION = [];
        session_unset();

        // 2. Expire the browser cookie so it is removed immediately
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,   // past timestamp forces deletion
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        // 3. Destroy the server-side session storage
        session_destroy();
    }

    // -------------------------------------------------------------------------
    // Fix 2 — Session regeneration (call after successful login)
    // -------------------------------------------------------------------------

    /**
     * Regenerates the session ID and deletes the old session file.
     * Prevents session fixation attacks.
     * Must be called BEFORE writing any session variables.
     */
    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    // -------------------------------------------------------------------------
    // Fix 4 — Session timeout (30-minute inactivity)
    // -------------------------------------------------------------------------

    /**
     * Checks whether the authenticated session has timed out.
     *
     * - Called on every request (from core/index.php after session_start).
     * - Unauthenticated sessions are skipped (no impact on login page).
     * - On timeout: destroys session, returns HTTP 401, exits.
     * - Active sessions: refreshes the last-activity timestamp.
     */
    public static function checkTimeout(): void
    {
        // Only enforce timeout for authenticated sessions
        if (!self::isAuthenticated()) {
            return;
        }

        if (isset($_SESSION['_last_activity'])) {
            $idle = time() - (int)$_SESSION['_last_activity'];
            if ($idle > self::TIMEOUT_SECONDS) {
                self::destroy();
                http_response_code(401);
                echo json_encode(['error' => 'Session expired. Please log in again.']);
                exit();
            }
        }

        // Refresh activity timestamp on every authenticated request
        $_SESSION['_last_activity'] = time();
    }

    // -------------------------------------------------------------------------
    // Fix 9 — CSRF token generation and validation
    // -------------------------------------------------------------------------

    /**
     * Returns the CSRF token for the current session, generating one if needed.
     * Token is a 64-character hex string derived from 32 cryptographic random bytes.
     */
    public static function generateCsrfToken(): string
    {
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }

    /**
     * Validates a submitted CSRF token against the one stored in session.
     * Uses hash_equals() to prevent timing-based side-channel attacks.
     */
    public static function validateCsrfToken(string $token): bool
    {
        $stored = $_SESSION['_csrf_token'] ?? '';
        if (empty($stored) || empty($token)) {
            return false;
        }
        return hash_equals($stored, $token);
    }
}
