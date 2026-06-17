<?php

class Admin extends User {
    // Doctors CRUD
    public function createDoctor($name, $email, $password, $phone, $specialization, $department_id, $availability) {
        $this->db->beginTransaction();
        try {
            // 1. Create a user record with role 'doctor'
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $userQuery = "INSERT INTO users (name, email, password, role, phone) VALUES (:name, :email, :password, 'doctor', :phone)";
            $userStmt = $this->db->prepare($userQuery);
            $userStmt->bindParam(':name', $name);
            $userStmt->bindParam(':email', $email);
            $userStmt->bindParam(':password', $hashed_password);
            $userStmt->bindParam(':phone', $phone);
            $userStmt->execute();

            $user_id = $this->db->lastInsertId();

            // 2. Create the doctor record linking to the user
            $docQuery = "INSERT INTO doctors (user_id, specialization, department_id, availability) 
                         VALUES (:user_id, :specialization, :department_id, :availability)";
            $docStmt = $this->db->prepare($docQuery);
            $docStmt->bindParam(':user_id', $user_id);
            $docStmt->bindParam(':specialization', $specialization);
            $docStmt->bindParam(':department_id', $department_id);
            $docStmt->bindParam(':availability', $availability);
            $docStmt->execute();

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updateDoctor($doctor_id, $name, $email, $phone, $specialization, $department_id, $availability) {
        // Fetch user_id first
        $findQuery = "SELECT user_id FROM doctors WHERE id = :id LIMIT 1";
        $findStmt = $this->db->prepare($findQuery);
        $findStmt->bindParam(':id', $doctor_id);
        $findStmt->execute();
        
        if ($findStmt->rowCount() === 0) {
            throw new Exception("Doctor record not found.");
        }
        $user_id = $findStmt->fetch()['user_id'];

        // Check if email belongs to someone else
        $emailQuery = "SELECT id FROM users WHERE email = :email AND id != :user_id LIMIT 1";
        $emailStmt = $this->db->prepare($emailQuery);
        $emailStmt->bindParam(':email', $email);
        $emailStmt->bindParam(':user_id', $user_id);
        $emailStmt->execute();
        if ($emailStmt->rowCount() > 0) {
            throw new Exception("Email is already used by another user.");
        }

        $this->db->beginTransaction();
        try {
            // 1. Update user
            $userQuery = "UPDATE users SET name = :name, email = :email, phone = :phone WHERE id = :user_id";
            $userStmt = $this->db->prepare($userQuery);
            $userStmt->bindParam(':name', $name);
            $userStmt->bindParam(':email', $email);
            $userStmt->bindParam(':phone', $phone);
            $userStmt->bindParam(':user_id', $user_id);
            $userStmt->execute();

            // 2. Update doctor details
            $docQuery = "UPDATE doctors SET specialization = :specialization, department_id = :department_id, availability = :availability WHERE id = :doctor_id";
            $docStmt = $this->db->prepare($docQuery);
            $docStmt->bindParam(':specialization', $specialization);
            $docStmt->bindParam(':department_id', $department_id);
            $docStmt->bindParam(':availability', $availability);
            $docStmt->bindParam(':doctor_id', $doctor_id);
            $docStmt->execute();

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function deleteDoctor($doctor_id) {
        // Find user_id to delete the user which cascades to doctor record
        $findQuery = "SELECT user_id FROM doctors WHERE id = :id LIMIT 1";
        $findStmt = $this->db->prepare($findQuery);
        $findStmt->bindParam(':id', $doctor_id);
        $findStmt->execute();

        if ($findStmt->rowCount() === 0) {
            throw new Exception("Doctor record not found.");
        }
        $user_id = $findStmt->fetch()['user_id'];

        // Deleting user automatically cascades to doctors table
        $query = "DELETE FROM users WHERE id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        return $stmt->execute();
    }

    // Departments CRUD
    public function createDepartment($name, $description) {
        $query = "INSERT INTO departments (name, description) VALUES (:name, :description)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        return $stmt->execute();
    }

    public function updateDepartment($id, $name, $description) {
        $query = "UPDATE departments SET name = :name, description = :description WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function deleteDepartment($id) {
        // Check if doctors are assigned to this department
        $checkQuery = "SELECT id FROM doctors WHERE department_id = :id LIMIT 1";
        $checkStmt = $this->db->prepare($checkQuery);
        $checkStmt->bindParam(':id', $id);
        $checkStmt->execute();
        if ($checkStmt->rowCount() > 0) {
            throw new Exception("Cannot delete department. There are doctors assigned to it.");
        }

        $query = "DELETE FROM departments WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Statistics
    public function getSystemStats() {
        // Total Patients
        $patientsQuery = "SELECT COUNT(*) AS count FROM users WHERE role = 'patient'";
        $patientsStmt = $this->db->prepare($patientsQuery);
        $patientsStmt->execute();
        $patients = $patientsStmt->fetch()['count'];

        // Total Doctors
        $doctorsQuery = "SELECT COUNT(*) AS count FROM doctors";
        $doctorsStmt = $this->db->prepare($doctorsQuery);
        $doctorsStmt->execute();
        $doctors = $doctorsStmt->fetch()['count'];

        // Total Departments
        $deptsQuery = "SELECT COUNT(*) AS count FROM departments";
        $deptsStmt = $this->db->prepare($deptsQuery);
        $deptsStmt->execute();
        $depts = $deptsStmt->fetch()['count'];

        // Appointments Breakdown
        $apptQuery = "SELECT COUNT(*) AS total, 
                             SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending,
                             SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) AS confirmed
                      FROM appointments";
        $apptStmt = $this->db->prepare($apptQuery);
        $apptStmt->execute();
        $appt = $apptStmt->fetch();

        return [
            "total_patients" => (int)$patients,
            "total_doctors" => (int)$doctors,
            "total_departments" => (int)$depts,
            "total_appointments" => (int)($appt['total'] ?? 0),
            "pending_appointments" => (int)($appt['pending'] ?? 0),
            "confirmed_appointments" => (int)($appt['confirmed'] ?? 0),
        ];
    }
}
