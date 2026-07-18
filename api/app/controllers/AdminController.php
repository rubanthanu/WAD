<?php

class AdminController extends Controller
{
    
    public function stats(): void
    {
        $method = $this->getMethod();

        if ($method !== 'GET') {
            Response::error("Method not allowed.", 405);
        }

        $this->requireRole('admin', "Access denied. Administrator privileges required.");

        try {
            $adminModel = new Admin($this->db);
            $stats = $adminModel->getSystemStats();
            Response::json($stats);
        } catch (Exception $e) {
            Response::error("Failed to aggregate statistics: " . $e->getMessage(), 500);
        }
    }

   
    
}
