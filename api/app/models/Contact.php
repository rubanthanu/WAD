<?php

class Contact extends Model
{
    private string $name = '';
    private string $email = '';
    private string $message = '';

    public function setName(string $name): void
    {
        $this->name = trim($name);
    }

    public function setEmail(string $email): void
    {
        $this->email = trim($email);
    }

    public function setMessage(string $message): void
    {
        $this->message = trim($message);
    }

    public function save(): bool
    {
        $query = "INSERT INTO contact_messages (name, email, message) VALUES (:name, :email, :message)";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':name', $this->name);
        $stmt->bindValue(':email', $this->email);
        $stmt->bindValue(':message', $this->message);
        return $stmt->execute();
    }
}
