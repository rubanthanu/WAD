<?php

class Appointment extends Model
{
    
    private ?int $id = null;

    private ?int $patientId = null;

    private ?int $doctorId = null;

    private ?string $appointmentDate = null;

    private ?string $appointmentTime = null;

    private string $status = 'pending';

    private ?string $notes = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPatientId(): ?int
    {
        return $this->patientId;
    }

    public function getDoctorId(): ?int
    {
        return $this->doctorId;
    }

    public function getAppointmentDate(): ?string
    {
        return $this->appointmentDate;
    }

    public function getAppointmentTime(): ?string
    {
        return $this->appointmentTime;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function setPatientId(?int $patientId): void
    {
        $this->patientId = $patientId;
    }

    public function setDoctorId(?int $doctorId): void
    {
        $this->doctorId = $doctorId;
    }

    public function setAppointmentDate(?string $appointmentDate): void
    {
        $this->appointmentDate = $appointmentDate;
    }

    public function setAppointmentTime(?string $appointmentTime): void
    {
        $this->appointmentTime = $appointmentTime;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function setNotes(?string $notes): void
    {
        $this->notes = $notes;
    }

    public function isTimeSlotAvailable(): bool
    {
        $query = "SELECT id FROM appointments 
                  WHERE doctor_id = :doctor_id 
                    AND appointment_date = :date 
                    AND appointment_time = :time 
                    AND status != 'cancelled' LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':doctor_id', $this->getDoctorId());
        $stmt->bindValue(':date', $this->getAppointmentDate());
        $stmt->bindValue(':time', $this->getAppointmentTime());
        $stmt->execute();

        return $stmt->rowCount() === 0;
    }

    public function book(): int|false
    {
        
        $today = date("Y-m-d");
        if ($this->getAppointmentDate() < $today) {
            throw new Exception("Cannot book appointments in the past.");
        }

        if (!$this->isTimeSlotAvailable()) {
            throw new Exception("The selected time slot is already booked for this doctor. Please choose a different time.");
        }

        $query = "INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, notes, status) 
                  VALUES (:patient_id, :doctor_id, :date, :time, :notes, 'pending')";
        $stmt = $this->db->prepare($query);
        
        $stmt->bindValue(':patient_id', $this->getPatientId());
        $stmt->bindValue(':doctor_id', $this->getDoctorId());
        $stmt->bindValue(':date', $this->getAppointmentDate());
        $stmt->bindValue(':time', $this->getAppointmentTime());
        $stmt->bindValue(':notes', $this->getNotes());

        if ($stmt->execute()) {
            return (int)$this->db->lastInsertId();
        }
        return false;
    }

    public function updateStatus(): bool
    {
        $validStatuses = ['pending', 'confirmed', 'cancelled', 'completed'];
        if (!in_array($this->getStatus(), $validStatuses)) {
            throw new Exception("Invalid status value.");
        }

        $query = "UPDATE appointments SET status = :status WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':status', $this->getStatus());
        $stmt->bindValue(':id', $this->getId());
        return $stmt->execute();
    }

    public function cancel(): bool
    {
        $this->setStatus('cancelled');
        return $this->updateStatus();
    }

    public function delete(): bool
    {
        $query = "DELETE FROM appointments WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':id', $this->getId());
        return $stmt->execute();
    }

    public function fetchAll(string $search = '', string $status = ''): array
    {
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
