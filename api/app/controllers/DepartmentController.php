<?php

class DepartmentController extends Controller
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
        $departmentModel = new Department($this->db);
        $departments = $departmentModel->fetchAll();
        Response::json($departments);
    }

    private function store(): void
    {
        $this->requireRole('admin', "Access denied. Administrator privileges required.");

        $data        = $this->getInput();
        $name        = $data['name'] ?? '';
        $description = $data['description'] ?? '';

        if (empty($name)) {
            Response::error("Department name is required.", 400);
        }

        $departmentModel = new Department($this->db);
        $departmentModel->setName($name);
        $departmentModel->setDescription($description);
        if ($departmentModel->create()) {
            Response::success("Department created successfully!");
        } else {
            Response::error("Failed to create department.", 500);
        }
    }

    private function update(): void
    {
        $this->requireRole('admin', "Access denied. Administrator privileges required.");

        $data        = $this->getInput();
        $id          = $data['id'] ?? null;
        $name        = $data['name'] ?? '';
        $description = $data['description'] ?? '';

        if (!$id || empty($name)) {
            Response::error("Department ID and name are required.", 400);
        }

        $departmentModel = new Department($this->db);
        $departmentModel->setId((int)$id);
        $departmentModel->setName($name);
        $departmentModel->setDescription($description);
        if ($departmentModel->update()) {
            Response::success("Department updated successfully!");
        } else {
            Response::error("Failed to update department.", 500);
        }
    }

    private function destroy(): void
    {
        $this->requireRole('admin', "Access denied. Administrator privileges required.");

        $id = $this->getQueryParam('id');
        if (!$id) {
            Response::error("Department ID is required.", 400);
        }

        try {
            $departmentModel = new Department($this->db);
            $departmentModel->setId((int)$id);
            if ($departmentModel->delete()) {
                Response::success("Department deleted successfully.");
            } else {
                Response::error("Failed to delete department.", 500);
            }
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }
}
