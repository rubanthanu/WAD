<?php

class Doctor extends User {
    // Get doctor record ID from user ID
    public function getDoctorIdByUserId($user_id) {
        $query = "SELECT id FROM doctors WHERE user_id = :user_id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch()['id'];
        }
        return null;
    }

    public function getSchedule($user_id) {
        $query = "SELECT a.id, a.appointment_date, a.appointment_time, a.status, a.notes, 
                         u.name AS patient_name, u.email AS patient_email, u.phone AS patient_phone
                  FROM appointments a
                  JOIN doctors d ON a.doctor_id = d.id
                  JOIN users u ON a.patient_id = u.id
                  WHERE d.user_id = :user_id
                  ORDER BY a.appointment_date DESC, a.appointment_time DESC";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateAppointmentStatus($appointment_id, $status, $user_id) {
        // Verify this appointment belongs to this doctor
        $checkQuery = "SELECT a.id 
                       FROM appointments a 
                       JOIN doctors d ON a.doctor_id = d.id 
                       WHERE a.id = :appointment_id AND d.user_id = :user_id LIMIT 1";
        $checkStmt = $this->db->prepare($checkQuery);
        $checkStmt->bindParam(':appointment_id', $appointment_id);
        $checkStmt->bindParam(':user_id', $user_id);
        $checkStmt->execute();

        if ($checkStmt->rowCount() === 0) {
            throw new Exception("Appointment not found or unauthorized.");
        }

        $query = "UPDATE appointments SET status = :status WHERE id = :appointment_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':appointment_id', $appointment_id);
        return $stmt->execute();
    }

    public function updateAvailability($user_id, $availability) {
        $query = "UPDATE doctors SET availability = :availability WHERE user_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':availability', $availability);
        $stmt->bindParam(':user_id', $user_id);
        return $stmt->execute();
    }

    public function getAvailability($user_id) {
        $query = "SELECT availability, specialization, department_id FROM doctors WHERE user_id = :user_id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function fetchAllPublic() {
        $query = "SELECT d.id, d.specialization, d.availability, u.name, u.email, u.phone, dept.name AS department_name 
                  FROM doctors d 
                  JOIN users u ON d.user_id = u.id 
                  JOIN departments dept ON d.department_id = dept.id
                  ORDER BY dept.name ASC, u.name ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function fetchByDepartment($department_id) {
        $query = "SELECT d.id, d.specialization, d.availability, u.name 
                  FROM doctors d 
                  JOIN users u ON d.user_id = u.id 
                  WHERE d.department_id = :department_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':department_id', $department_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
