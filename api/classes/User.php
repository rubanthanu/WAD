<?php

class User {
    protected $db;
    public $id;
    public $name;
    public $email;
    public $role;
    public $phone;

    public function __construct($db) {
        $this->db = $db;
    }

    public function login($email, $password) {
        $query = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            if (password_verify($password, $row['password'])) {
                $this->id = $row['id'];
                $this->name = $row['name'];
                $this->email = $row['email'];
                $this->role = $row['role'];
                $this->phone = $row['phone'];
                return [
                    "id" => $this->id,
                    "name" => $this->name,
                    "email" => $this->email,
                    "role" => $this->role,
                    "phone" => $this->phone
                ];
            }
        }
        return false;
    }

    public function register($name, $email, $password, $role, $phone) {
        // Check if email already exists
        $checkQuery = "SELECT id FROM users WHERE email = :email LIMIT 1";
        $checkStmt = $this->db->prepare($checkQuery);
        $checkStmt->bindParam(':email', $email);
        $checkStmt->execute();

        if ($checkStmt->rowCount() > 0) {
            throw new Exception("Email is already registered.");
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $query = "INSERT INTO users (name, email, password, role, phone) VALUES (:name, :email, :password, :role, :phone)";
        $stmt = $this->db->prepare($query);

        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':phone', $phone);

        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function getUserById($id) {
        $query = "SELECT id, name, email, role, phone FROM users WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $stmt->fetch();
        }
        return null;
    }
}
