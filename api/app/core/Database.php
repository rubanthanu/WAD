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
                http_response_code(500);
                echo json_encode(["error" => "Database connection failure: " . $exception->getMessage()]);
                exit();
            }
        }
        return $this->conn;
    }
}
