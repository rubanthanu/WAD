<?php

class Appointment {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function isTimeSlotAvailable($doctor_id, $date, $time) {
        // Check if there is an active appointment (not cancelled) at the exact same date and time for this doctor
        $query = "SELECT id FROM appointments 
                  WHERE doctor_id = :doctor_id 
                    AND appointment_date = :date 
                    AND appointment_time = :time 
                    AND status != 'cancelled' 
                  LIMIT 1";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':doctor_id', $doctor_id);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':time', $time);
        $stmt->execute();

        return $stmt->rowCount() === 0;
    }

    public function book($patient_id, $doctor_id, $date, $time, $notes) {
        // Validate date is not in the past
        $today = date("Y-m-d");
        if ($date < $today) {
            throw new Exception("Cannot book appointments in the past.");
        }

        // Validate time slot availability
        if (!$this->isTimeSlotAvailable($doctor_id, $date, $time)) {
            throw new Exception("The selected time slot is already booked for this doctor. Please choose a different time.");
        }

        $query = "INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, status, notes) 
                  VALUES (:patient_id, :doctor_id, :date, :time, 'pending', :notes)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':patient_id', $patient_id);
        $stmt->bindParam(':doctor_id', $doctor_id);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':time', $time);
        $stmt->bindParam(':notes', $notes);

        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function updateStatus($appointment_id, $status) {
        $validStatuses = ['pending', 'confirmed', 'cancelled', 'completed'];
        if (!in_array($status, $validStatuses)) {
            throw new Exception("Invalid appointment status.");
        }

        $query = "UPDATE appointments SET status = :status WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $appointment_id);
        return $stmt->execute();
    }

    public function cancel($appointment_id) {
        return $this->updateStatus($appointment_id, 'cancelled');
    }

    public function delete($appointment_id) {
        $query = "DELETE FROM appointments WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $appointment_id);
        return $stmt->execute();
    }

    public function fetchAll($search = '', $status = '') {
        $query = "SELECT a.id, a.appointment_date, a.appointment_time, a.status, a.notes,
                         p.name AS patient_name, p.phone AS patient_phone, p.email AS patient_email,
                         d_user.name AS doctor_name, d.specialization, dept.name AS department_name
                  FROM appointments a
                  JOIN users p ON a.patient_id = p.id
                  JOIN doctors d ON a.doctor_id = d.id
                  JOIN users d_user ON d.user_id = d_user.id
                  JOIN departments dept ON d.department_id = dept.id
                  WHERE 1=1";

        $params = [];

        if (!empty($status)) {
            $query .= " AND a.status = :status";
            $params[':status'] = $status;
        }

        if (!empty($search)) {
            $query .= " AND (p.name LIKE :search OR d_user.name LIKE :search OR dept.name LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        $query .= " ORDER BY a.appointment_date DESC, a.appointment_time DESC";

        $stmt = $this->db->prepare($query);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
