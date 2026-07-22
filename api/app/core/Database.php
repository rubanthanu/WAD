<?php

class Database
{

    private string $host;

    private string $dbName;

    private string $username;

    private string $password;

    private ?PDO $conn = null;

    public function __construct()
    {
        $config = require __DIR__ . '/../config/database.php';

        $this->host     = $config['host'];
        $this->dbName   = $config['db_name'];
        $this->username = $config['username'];
        $this->password = $config['password'];
    }

    public function getConnection(): PDO
    {
        if ($this->conn === null) {
            try {
                $this->conn = new PDO(
                    "mysql:host={$this->host};dbname={$this->dbName};charset=utf8mb4",
                    $this->username,
                    $this->password
                );
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $exception) {
                // Fix 7: Log the real error server-side; never expose it to the client.
                // The log entry includes a timestamp and the full PDO message for debugging.
                error_log('[WAD ' . date('Y-m-d H:i:s') . '] Database connection failure: ' . $exception->getMessage());

                http_response_code(500);
                echo json_encode(["error" => "Service temporarily unavailable."]);
                exit();
            }
        }
        return $this->conn;
    }
}
