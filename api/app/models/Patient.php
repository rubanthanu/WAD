<?php

class Patient extends User
{
    private ?string $dateOfBirth = null;
    private ?string $gender = null;

    public function __construct(PDO $db)
    {
        parent::__construct($db);
        $this->setRole('patient');
    }

    public function getDateOfBirth(): ?string
    {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth(?string $dateOfBirth): void
    {
        $this->dateOfBirth = $dateOfBirth;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): void
    {
        $this->gender = $gender;
    }

    public function registerPatient(): int
    {
        $checkQuery = "SELECT id FROM users WHERE email = :email LIMIT 1";
        $checkStmt = $this->db->prepare($checkQuery);
        $checkStmt->bindValue(':email', $this->getEmail());
        $checkStmt->execute();

        if ($checkStmt->rowCount() > 0) {
            throw new Exception("Email is already registered.");
        }

        $hashedPassword = password_hash($this->getPassword(), PASSWORD_BCRYPT);

        $this->db->beginTransaction();
        try {
            // 1. Insert into users table
            $userQuery = "INSERT INTO users (name, email, password, role, phone) 
                          VALUES (:name, :email, :password, 'patient', :phone)";
            $userStmt = $this->db->prepare($userQuery);
            $userStmt->bindValue(':name', $this->getName());
            $userStmt->bindValue(':email', $this->getEmail());
            $userStmt->bindValue(':password', $hashedPassword);
            $userStmt->bindValue(':phone', $this->getPhone());
            $userStmt->execute();

            $userId = (int)$this->db->lastInsertId();

            // 2. Insert into patients sub-table
            $patientQuery = "INSERT INTO patients (user_id, date_of_birth, gender) 
                             VALUES (:user_id, :dob, :gender)";
            $patientStmt = $this->db->prepare($patientQuery);
            $patientStmt->bindValue(':user_id', $userId);
            $patientStmt->bindValue(':dob', $this->getDateOfBirth());
            $patientStmt->bindValue(':gender', $this->getGender());
            $patientStmt->execute();

            $this->db->commit();
            return $userId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getBookedAppointments(): array
    {
        $query = "SELECT a.id, a.appointment_date, a.appointment_time, a.status, a.notes, 
                         u.name AS doctor_name, d.specialization, dept.name AS department_name
                  FROM appointments a
                  JOIN doctors d ON a.doctor_id = d.id
                  JOIN users u ON d.user_id = u.id
                  JOIN departments dept ON d.department_id = dept.id
                  WHERE a.patient_id = :patient_id
                  ORDER BY a.appointment_date DESC, a.appointment_time DESC";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':patient_id', $this->getId());
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function cancelAppointment(int $appointmentId): bool
    {
        
        $checkQuery = "SELECT id FROM appointments WHERE id = :id AND patient_id = :patient_id LIMIT 1";
        $checkStmt = $this->db->prepare($checkQuery);
        $checkStmt->bindParam(':id', $appointmentId);
        $checkStmt->bindValue(':patient_id', $this->getId());
        $checkStmt->execute();

        if ($checkStmt->rowCount() === 0) {
            throw new Exception("Appointment not found or access denied.");
        }

        $query = "UPDATE appointments SET status = 'cancelled' WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $appointmentId);
        return $stmt->execute();
    }
}
