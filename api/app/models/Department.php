<?php

class Department extends Model
{
    
    private ?int $id = null;

    private string $name = '';

    private ?string $description = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function fetchAll(): array
    {
        $query = "SELECT * FROM departments ORDER BY name ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function create(): bool
    {
        $query = "INSERT INTO departments (name, description) VALUES (:name, :description)";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':name', $this->getName());
        $stmt->bindValue(':description', $this->getDescription());
        return $stmt->execute();
    }

    public function update(): bool
    {
        $query = "UPDATE departments SET name = :name, description = :description WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':name', $this->getName());
        $stmt->bindValue(':description', $this->getDescription());
        $stmt->bindValue(':id', $this->getId());
        return $stmt->execute();
    }

    public function delete(): bool
    {
        
        $checkQuery = "SELECT id FROM doctors WHERE department_id = :id LIMIT 1";
        $checkStmt = $this->db->prepare($checkQuery);
        $checkStmt->bindValue(':id', $this->getId());
        $checkStmt->execute();
        if ($checkStmt->rowCount() > 0) {
            throw new Exception("Cannot delete department. There are doctors assigned to it.");
        }

        $query = "DELETE FROM departments WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':id', $this->getId());
        return $stmt->execute();
    }
}
