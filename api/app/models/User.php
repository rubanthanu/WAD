<?php

class User extends Model
{
    
    private ?int $id = null;

    private string $name = '';

    private string $email = '';

    private string $role = '';

    private ?string $phone = null;

    private ?string $password = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    public function login(): array|false
    {
        // Fix 10: Explicit columns — password fetched only for password_verify(); not returned to caller
        $query = "SELECT id, name, email, role, phone, password FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':email', $this->getEmail());
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            if (password_verify($this->getPassword(), $row['password'])) {
                $this->setId((int)$row['id']);
                $this->setName($row['name']);
                $this->setEmail($row['email']);
                $this->setRole($row['role']);
                $this->setPhone($row['phone']);

                return [
                    "id"    => $this->getId(),
                    "name"  => $this->getName(),
                    "email" => $this->getEmail(),
                    "role"  => $this->getRole(),
                    "phone" => $this->getPhone(),
                ];
            }
        }
        return false;
    }

    public function register(): int|false
    {
        
        $checkQuery = "SELECT id FROM users WHERE email = :email LIMIT 1";
        $checkStmt = $this->db->prepare($checkQuery);
        $checkStmt->bindValue(':email', $this->getEmail());
        $checkStmt->execute();

        if ($checkStmt->rowCount() > 0) {
            throw new Exception("Email is already registered.");
        }

        $hashedPassword = password_hash($this->getPassword(), PASSWORD_BCRYPT);

        $query = "INSERT INTO users (name, email, password, role, phone) VALUES (:name, :email, :password, :role, :phone)";
        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':name', $this->getName());
        $stmt->bindValue(':email', $this->getEmail());
        $stmt->bindValue(':password', $hashedPassword);
        $stmt->bindValue(':role', $this->getRole());
        $stmt->bindValue(':phone', $this->getPhone());

        if ($stmt->execute()) {
            return (int)$this->db->lastInsertId();
        }
        return false;
    }

    public function getUserById(): ?array
    {
        $query = "SELECT id, name, email, role, phone FROM users WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':id', $this->getId());
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $stmt->fetch();
        }
        return null;
    }
}
