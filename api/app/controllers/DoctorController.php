<?php

class DoctorController extends Controller
{
    
    public function handle(): void
    {
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
        $deptId = $this->getQueryParam('department_id');
        $own    = $this->getQueryParam('own');

        $doctorModel = new Doctor($this->db);

        if ($own === 'true') {
            $userId = $this->getUserId();
            if (!$userId) {
                Response::error("Unauthorized.", 401);
            }
            $doctorModel->setUserId((int)$userId);
            $avail = $doctorModel->getOwnAvailability();
            Response::json($avail);
        } elseif ($deptId) {
            $doctorModel->setDepartmentId((int)$deptId);
            $doctors = $doctorModel->fetchByDepartment();
            Response::json($doctors);
        } else {
            $doctors = $doctorModel->fetchAllPublic();
            Response::json($doctors);
        }
    }

    private function store(): void
    {
        $this->requireRole('admin', "Only administrators can create doctor records.");

        $data = $this->getInput();

        $name           = $data['name'] ?? '';
        $email          = $data['email'] ?? '';
        $password       = $data['password'] ?? '';
        $phone          = $data['phone'] ?? '';
        $specialization = $data['specialization'] ?? '';
        $departmentId   = $data['department_id'] ?? null;
        $availability   = $data['availability'] ?? 'Monday - Friday (9 AM - 5 PM)';

        if (empty($name) || empty($email) || empty($password) || empty($specialization) || !$departmentId) {
            Response::error("Name, email, password, specialization, and department are required.", 400);
        }

        try {
            $adminModel = new Admin($this->db);
            if ($adminModel->createDoctor($name, $email, $password, $phone, $specialization, $departmentId, $availability)) {
                Response::success("Doctor created successfully!");
            } else {
                Response::error("Failed to create doctor.", 500);
            }
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    private function update(): void
    {
        $this->requireAuth();

        $data     = $this->getInput();
        $userId   = $this->getUserId();
        $userRole = $this->getUserRole();

        if ($userRole === 'doctor') {
            $availability = $data['availability'] ?? '';
            if (empty($availability)) {
                Response::error("Availability details cannot be empty.", 400);
            }

            $doctorModel = new Doctor($this->db);
            $doctorModel->setUserId((int)$userId);
            $doctorModel->setAvailability($availability);
            if ($doctorModel->updateOwnAvailability()) {
                Response::success("Availability updated successfully!");
            } else {
                Response::error("Failed to update availability.", 500);
            }
        } elseif ($userRole === 'admin') {
            $doctorId       = $data['doctor_id'] ?? null;
            $name           = $data['name'] ?? '';
            $email          = $data['email'] ?? '';
            $phone          = $data['phone'] ?? '';
            $specialization = $data['specialization'] ?? '';
            $departmentId   = $data['department_id'] ?? null;
            $availability   = $data['availability'] ?? '';

            if (!$doctorId || empty($name) || empty($email) || empty($specialization) || !$departmentId) {
                Response::error("Missing required fields for update.", 400);
            }

            try {
                $adminModel = new Admin($this->db);
                if ($adminModel->updateDoctor($doctorId, $name, $email, $phone, $specialization, $departmentId, $availability)) {
                    Response::success("Doctor profile updated successfully!");
                } else {
                    Response::error("Failed to update doctor.", 500);
                }
            } catch (Exception $e) {
                Response::error($e->getMessage(), 400);
            }
        } else {
            Response::error("Unauthorized action.", 403);
        }
    }

    private function destroy(): void
    {
        $this->requireRole('admin', "Only administrators can delete doctors.");

        $doctorId = $this->getQueryParam('id');
        if (!$doctorId) {
            Response::error("Doctor ID is required.", 400);
        }

        try {
            $adminModel = new Admin($this->db);
            if ($adminModel->deleteDoctor($doctorId)) {
                Response::success("Doctor deleted successfully.");
            } else {
                Response::error("Failed to delete doctor.", 500);
            }
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }
}
