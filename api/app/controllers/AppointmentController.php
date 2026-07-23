<?php

class AppointmentController extends Controller
{
    
    public function handle(): void
    {
        $this->requireAuth();

        $method = $this->getMethod();

        match ($method) {
            'GET'    => $this->index(),
            'POST'   => $this->store(),
            'PUT'    => $this->update(),
            'DELETE' => $this->destroy(),
            default  => Response::error("Method not allowed.", 405),
        };
    }

    private function index(): void
    {
        $userId   = $this->getUserId();
        $userRole = $this->getUserRole();

        if ($userRole === 'admin') {
            $search = $this->getQueryParam('search', '');
            $status = $this->getQueryParam('status', '');

            $appointmentModel = new Appointment($this->db);
            $appointments = $appointmentModel->fetchAll($search, $status);
            Response::json($appointments);
        } elseif ($userRole === 'doctor') {
            $doctorModel = new Doctor($this->db);
            $doctorModel->setUserId((int)$userId);
            $schedule = $doctorModel->getSchedule();
            Response::json($schedule);
        } elseif ($userRole === 'patient') {
            $patientModel = new Patient($this->db);
            $patientModel->setId((int)$userId);
            $appointments = $patientModel->getBookedAppointments();
            Response::json($appointments);
        }
    }

    private function store(): void
    {
        $userRole = $this->getUserRole();
        $userId   = $this->getUserId();

        if ($userRole !== 'patient' && $userRole !== 'admin') {
            Response::error("Only patients or admins can book appointments.", 403);
        }

        $data = $this->getInput();

        if (!Validator::required($data, ['doctor_id', 'appointment_date', 'appointment_time'])) {
            Response::error("Doctor, date, and time are required fields.", 400);
        }

        $patientId = $userRole === 'admin' ? ($data['patient_id'] ?? null) : $userId;
        if (!$patientId) {
            Response::error("Patient ID is required.", 400);
        }

        $doctorId  = (int)$data['doctor_id'];
        $date      = trim($data['appointment_date']);
        $time      = trim($data['appointment_time']);
        $notes     = trim($data['notes'] ?? '');

        try {
            $appointmentModel = new Appointment($this->db);
            $appointmentModel->setPatientId((int)$patientId);
            $appointmentModel->setDoctorId($doctorId);
            $appointmentModel->setAppointmentDate($date);
            $appointmentModel->setAppointmentTime($time);
            $appointmentModel->setNotes($notes);
            $apptId = $appointmentModel->book();

            if ($apptId) {
                Response::json([
                    "success"        => true,
                    "message"        => "Appointment booked successfully!",
                    "appointment_id" => $apptId,
                ]);
            } else {
                Response::error("Failed to book appointment.", 500);
            }
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    private function update(): void
    {
        $data = $this->getInput();

        if (!Validator::required($data, ['appointment_id', 'status'])) {
            Response::error("Appointment ID and status are required.", 400);
        }

        $appointmentId = (int)$data['appointment_id'];
        $status        = trim($data['status']);

        if (!Validator::validStatus($status)) {
            Response::error("Invalid appointment status.", 400);
        }

        $userId   = $this->getUserId();
        $userRole = $this->getUserRole();

        try {
            if ($userRole === 'doctor') {
                $doctorModel = new Doctor($this->db);
                $doctorModel->setUserId((int)$userId);
                $doctorModel->updateAppointmentStatus($appointmentId, $status);
                Response::success("Appointment status updated to '$status'.");
            } elseif ($userRole === 'patient') {
                if ($status !== 'cancelled') {
                    Response::error("Patients can only cancel appointments.", 403);
                }
                $patientModel = new Patient($this->db);
                $patientModel->setId((int)$userId);
                $patientModel->cancelAppointment($appointmentId);
                Response::success("Appointment successfully cancelled.");
            } elseif ($userRole === 'admin') {
                $appointmentModel = new Appointment($this->db);
                $appointmentModel->setId($appointmentId);
                $appointmentModel->setStatus($status);
                $appointmentModel->updateStatus();
                Response::success("Appointment status updated to '$status' by administrator.");
            }
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    private function destroy(): void
    {
        $this->requireRole('admin', "Only administrators can delete appointments.");

        $appointmentId = $this->getQueryParam('id');

        if (!$appointmentId) {
            Response::error("Appointment ID is required.", 400);
        }

        $appointmentModel = new Appointment($this->db);
        $appointmentModel->setId((int)$appointmentId);
        if ($appointmentModel->delete()) {
            Response::success("Appointment deleted successfully.");
        } else {
            Response::error("Failed to delete appointment.", 500);
        }
    }
}
