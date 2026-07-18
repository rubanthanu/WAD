<?php

class Doctor extends Model
{
    
    private ?int $id = null;

    private ?int $userId = null;

    private string $specialization = '';

    private ?int $departmentId = null;

    private string $availability = '';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function getSpecialization(): string
    {
        return $this->specialization;
    }

    public function getDepartmentId(): ?int
    {
        return $this->departmentId;
    }

    public function getAvailability(): string
    {
        return $this->availability;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function setUserId(?int $userId): void
    {
        $this->userId = $userId;
    }

    public function setSpecialization(string $specialization): void
    {
        $this->specialization = $specialization;
    }

    public function setDepartmentId(?int $departmentId): void
    {
        $this->departmentId = $departmentId;
    }

    public function setAvailability(string $availability): void
    {
        $this->availability = $availability;
    }

    public function getDoctorIdByUserId(): ?int
    {
        $query = "SELECT id FROM doctors WHERE user_id = :user_id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':user_id', $this->getUserId());
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return (int)$stmt->fetch()['id'];
        }
        return null;
    }

    public function getSchedule(): array
    {
        $query = "SELECT a.id, a.appointment_date, a.appointment_time, a.status, a.notes, 
                         u.name AS patient_name, u.email AS patient_email, u.phone AS patient_phone
                  FROM appointments a
                  JOIN doctors d ON a.doctor_id = d.id
                  JOIN users u ON a.patient_id = u.id
                  WHERE d.user_id = :user_id
                  ORDER BY a.appointment_date DESC, a.appointment_time DESC";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':user_id', $this->getUserId());
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateAppointmentStatus(int $appointmentId, string $status): bool
    {
        
        $checkQuery = "SELECT a.id 
                       FROM appointments a 
                       JOIN doctors d ON a.doctor_id = d.id 
                       WHERE a.id = :appointment_id AND d.user_id = :user_id LIMIT 1";
        $checkStmt = $this->db->prepare($checkQuery);
        $checkStmt->bindParam(':appointment_id', $appointmentId);
        $checkStmt->bindValue(':user_id', $this->getUserId());
        $checkStmt->execute();

        if ($checkStmt->rowCount() === 0) {
            throw new Exception("Appointment not found or unauthorized.");
        }

        $query = "UPDATE appointments SET status = :status WHERE id = :appointment_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':appointment_id', $appointmentId);
        return $stmt->execute();
    }

    public function updateOwnAvailability(): bool
    {
        $query = "UPDATE doctors SET availability = :availability WHERE user_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':availability', $this->getAvailability());
        $stmt->bindValue(':user_id', $this->getUserId());
        return $stmt->execute();
    }

    public function getOwnAvailability(): array|false
    {
        $query = "SELECT availability, specialization, department_id FROM doctors WHERE user_id = :user_id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':user_id', $this->getUserId());
        $stmt->execute();
        return $stmt->fetch();
    }

    public function fetchAllPublic(): array
    {
        $query = "SELECT d.id, d.specialization, d.availability, u.name, u.email, u.phone, dept.name AS department_name 
                  FROM doctors d 
                  JOIN users u ON d.user_id = u.id 
                  JOIN departments dept ON d.department_id = dept.id
                  ORDER BY dept.name ASC, u.name ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function fetchByDepartment(): array
    {
        $query = "SELECT d.id, d.specialization, d.availability, u.name 
                  FROM doctors d 
                  JOIN users u ON d.user_id = u.id 
                  WHERE d.department_id = :department_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':department_id', $this->getDepartmentId());
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
