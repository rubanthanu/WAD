<?php

class Admin extends User
{
    public function __construct(PDO $db)
    {
        parent::__construct($db);
        $this->setRole('admin');
    }

    public function createDoctor(
        string $name,
        string $email,
        string $password,
        string $phone,
        string $specialization,
        int $departmentId,
        string $availability
    ): bool {
        $this->db->beginTransaction();
        try {
            
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $userQuery = "INSERT INTO users (name, email, password, role, phone) VALUES (:name, :email, :password, 'doctor', :phone)";
            $userStmt = $this->db->prepare($userQuery);
            $userStmt->bindParam(':name', $name);
            $userStmt->bindParam(':email', $email);
            $userStmt->bindParam(':password', $hashedPassword);
            $userStmt->bindParam(':phone', $phone);
            $userStmt->execute();

            $userId = $this->db->lastInsertId();

            $docQuery = "INSERT INTO doctors (user_id, specialization, department_id, availability) 
                         VALUES (:user_id, :specialization, :department_id, :availability)";
            $docStmt = $this->db->prepare($docQuery);
            $docStmt->bindParam(':user_id', $userId);
            $docStmt->bindParam(':specialization', $specialization);
            $docStmt->bindParam(':department_id', $departmentId);
            $docStmt->bindParam(':availability', $availability);
            $docStmt->execute();

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updateDoctor(
        int $doctorId,
        string $name,
        string $email,
        string $phone,
        string $specialization,
        int $departmentId,
        string $availability
    ): bool {
        
        $findQuery = "SELECT user_id FROM doctors WHERE id = :id LIMIT 1";
        $findStmt = $this->db->prepare($findQuery);
        $findStmt->bindParam(':id', $doctorId);
        $findStmt->execute();

        if ($findStmt->rowCount() === 0) {
            throw new Exception("Doctor record not found.");
        }
        $userId = $findStmt->fetch()['user_id'];

        $emailQuery = "SELECT id FROM users WHERE email = :email AND id != :user_id LIMIT 1";
        $emailStmt = $this->db->prepare($emailQuery);
        $emailStmt->bindParam(':email', $email);
        $emailStmt->bindParam(':user_id', $userId);
        $emailStmt->execute();
        if ($emailStmt->rowCount() > 0) {
            throw new Exception("Email is already used by another user.");
        }

        $this->db->beginTransaction();
        try {
            
            $userQuery = "UPDATE users SET name = :name, email = :email, phone = :phone WHERE id = :user_id";
            $userStmt = $this->db->prepare($userQuery);
            $userStmt->bindParam(':name', $name);
            $userStmt->bindParam(':email', $email);
            $userStmt->bindParam(':phone', $phone);
            $userStmt->bindParam(':user_id', $userId);
            $userStmt->execute();

            $docQuery = "UPDATE doctors SET specialization = :specialization, department_id = :department_id, availability = :availability WHERE id = :doctor_id";
            $docStmt = $this->db->prepare($docQuery);
            $docStmt->bindParam(':specialization', $specialization);
            $docStmt->bindParam(':department_id', $departmentId);
            $docStmt->bindParam(':availability', $availability);
            $docStmt->bindParam(':doctor_id', $doctorId);
            $docStmt->execute();

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function deleteDoctor(int $doctorId): bool
    {
        
        $findQuery = "SELECT user_id FROM doctors WHERE id = :id LIMIT 1";
        $findStmt = $this->db->prepare($findQuery);
        $findStmt->bindParam(':id', $doctorId);
        $findStmt->execute();

        if ($findStmt->rowCount() === 0) {
            throw new Exception("Doctor record not found.");
        }
        $userId = $findStmt->fetch()['user_id'];

        $query = "DELETE FROM users WHERE id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        return $stmt->execute();
    }

    public function getSystemStats(): array
    {
        
        $patientsQuery = "SELECT COUNT(*) AS count FROM users WHERE role = 'patient'";
        $patientsStmt = $this->db->prepare($patientsQuery);
        $patientsStmt->execute();
        $patients = $patientsStmt->fetch()['count'];

        $doctorsQuery = "SELECT COUNT(*) AS count FROM doctors";
        $doctorsStmt = $this->db->prepare($doctorsQuery);
        $doctorsStmt->execute();
        $doctors = $doctorsStmt->fetch()['count'];

        $deptsQuery = "SELECT COUNT(*) AS count FROM departments";
        $deptsStmt = $this->db->prepare($deptsQuery);
        $deptsStmt->execute();
        $depts = $deptsStmt->fetch()['count'];

        $apptQuery = "SELECT COUNT(*) AS total, 
                             SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending,
                             SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) AS confirmed
                      FROM appointments";
        $apptStmt = $this->db->prepare($apptQuery);
        $apptStmt->execute();
        $appt = $apptStmt->fetch();

        return [
            "total_patients"          => (int)$patients,
            "total_doctors"           => (int)$doctors,
            "total_departments"       => (int)$depts,
            "total_appointments"      => (int)($appt['total'] ?? 0),
            "pending_appointments"    => (int)($appt['pending'] ?? 0),
            "confirmed_appointments"  => (int)($appt['confirmed'] ?? 0),
        ];
    }
}
