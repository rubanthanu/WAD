<?php

class Patient extends Model
{
    
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
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
