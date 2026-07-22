<?php

// ============================================================
// Fix 1 — SESSION SECURITY HARDENING
// The autoloader MUST be registered before session_start() so
// that Session::checkTimeout() is callable immediately after.
// ============================================================

spl_autoload_register(function (string $className): void {

    $directories = [
        __DIR__ . '/',                // core/ (Controller, Model, Database)
        __DIR__ . '/../helpers/',     // Session, Response, Validator
        __DIR__ . '/../models/',      // User, Doctor, Patient, …
        __DIR__ . '/../controllers/', // AuthController, …
    ];

    foreach ($directories as $directory) {
        $file = $directory . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// --- Harden session INI settings (must come before session_start) -----------
ini_set('session.use_strict_mode',  '1'); // reject externally supplied session IDs
ini_set('session.use_only_cookies', '1'); // block ?PHPSESSID= URL injection
ini_set('session.cookie_httponly',  '1'); // deny JavaScript access to the cookie
ini_set('session.cookie_samesite',  'Strict'); // CSRF mitigation

// Rename cookie from the default 'PHPSESSID' to obscure the PHP stack
session_name('wad_sid');

// Detect HTTPS (covers direct TLS and common reverse-proxy headers)
$isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

session_set_cookie_params([
    'lifetime' => 0,            // session-only (deleted when browser closes)
    'path'     => '/',          // broad enough for XAMPP sub-directory installs
    'domain'   => '',           // current host only
    'secure'   => $isSecure,    // HTTPS-only in production; HTTP works in dev
    'httponly' => true,         // no JS access
    'samesite' => 'Strict',     // no cross-site cookie sending
]);

// --- CORS headers (unchanged from original) ---------------------------------
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
// X-CSRF-Token added so the frontend can send CSRF tokens in future
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-User-Id, X-User-Role, X-CSRF-Token");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// --- Start session (guarded, same as original) ------------------------------
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fix 4 — Session timeout check on every authenticated request
Session::checkTimeout();

// ============================================================
// App dispatcher (unchanged)
// ============================================================

class App
{
    public static function dispatch(string $controllerClass, string $method): void
    {
        $database = new Database();
        $db = $database->getConnection();

        $controller = new $controllerClass($db);
        $controller->$method();
    }
}
