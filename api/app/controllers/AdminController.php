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

    public function bootstrap(): void
    {
        $host     = 'localhost';
        $username = 'root';
        $password = '';

        try {
            
            $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $pdo->exec("CREATE DATABASE IF NOT EXISTS hospital_db");
            $pdo->exec("USE hospital_db");

            $pdo->exec("CREATE TABLE IF NOT EXISTS departments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            $pdo->exec("CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                role ENUM('patient', 'doctor', 'admin') NOT NULL,
                phone VARCHAR(20),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            $pdo->exec("CREATE TABLE IF NOT EXISTS doctors (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                specialization VARCHAR(100) NOT NULL,
                department_id INT NOT NULL,
                availability VARCHAR(255) DEFAULT 'Monday - Friday (9 AM - 5 PM)',
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE RESTRICT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            $pdo->exec("CREATE TABLE IF NOT EXISTS appointments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                patient_id INT NOT NULL,
                doctor_id INT NOT NULL,
                appointment_date DATE NOT NULL,
                appointment_time TIME NOT NULL,
                status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            $countDepts = $pdo->query("SELECT COUNT(*) FROM departments")->fetchColumn();

            if ($countDepts == 0) {
                
                $depts = [
                    ['Cardiology', 'Deals with disorders of the heart and blood vessels.'],
                    ['Pediatrics', 'Deals with the medical care of infants, children, and adolescents.'],
                    ['Neurology', 'Deals with disorders of the nervous system.'],
                    ['Dermatology', 'Deals with skin, nails, hair and its diseases.'],
                    ['General Medicine', 'Deals with the prevention, diagnosis, and treatment of internal diseases.']
                ];
                $insertDept = $pdo->prepare("INSERT INTO departments (name, description) VALUES (?, ?)");
                foreach ($depts as $dept) {
                    $insertDept->execute($dept);
                }

                $adminPassword   = password_hash('admin123', PASSWORD_BCRYPT);
                $doctorPassword  = password_hash('doctor123', PASSWORD_BCRYPT);
                $patientPassword = password_hash('patient123', PASSWORD_BCRYPT);

                $pdo->exec("INSERT INTO users (name, email, password, role, phone) VALUES 
                    ('System Administrator', 'admin@hospital.com', '$adminPassword', 'admin', '123-456-7890')");

                $insertUser   = $pdo->prepare("INSERT INTO users (name, email, password, role, phone) VALUES (?, ?, ?, ?, ?)");
                $insertDoctor = $pdo->prepare("INSERT INTO doctors (user_id, specialization, department_id, availability) VALUES (?, ?, ?, ?)");

                $insertUser->execute(['Dr. Sarah Jenkins', 'sarah.j@hospital.com', $doctorPassword, 'doctor', '555-0101']);
                $docId1 = $pdo->lastInsertId();
                $insertDoctor->execute([$docId1, 'Cardiologist', 1, 'Mon, Wed, Fri (9 AM - 1 PM)']);

                $insertUser->execute(['Dr. Robert Chen', 'robert.c@hospital.com', $doctorPassword, 'doctor', '555-0102']);
                $docId2 = $pdo->lastInsertId();
                $insertDoctor->execute([$docId2, 'Pediatrician', 2, 'Tue, Thu (10 AM - 4 PM)']);

                $insertUser->execute(['Dr. Alice Vance', 'alice.v@hospital.com', $doctorPassword, 'doctor', '555-0103']);
                $docId3 = $pdo->lastInsertId();
                $insertDoctor->execute([$docId3, 'Neurologist', 3, 'Mon - Thu (1 PM - 5 PM)']);

                $insertUser->execute(['Dr. Michael Green', 'michael.g@hospital.com', $doctorPassword, 'doctor', '555-0104']);
                $docId4 = $pdo->lastInsertId();
                $insertDoctor->execute([$docId4, 'General Physician', 5, 'Mon - Fri (8 AM - 12 PM)']);

                $insertUser->execute(['Jane Doe', 'jane.doe@example.com', $patientPassword, 'patient', '555-0201']);
                $patientId1 = $pdo->lastInsertId();
                $insertUser->execute(['John Smith', 'john.smith@example.com', $patientPassword, 'patient', '555-0202']);
                $patientId2 = $pdo->lastInsertId();

                $docRecord1 = $pdo->query("SELECT id FROM doctors WHERE user_id = $docId1")->fetchColumn();
                $docRecord2 = $pdo->query("SELECT id FROM doctors WHERE user_id = $docId2")->fetchColumn();

                $tomorrow = date('Y-m-d', strtotime('+1 day'));
                $dayAfter = date('Y-m-d', strtotime('+2 days'));

                $insertAppt = $pdo->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, status, notes) VALUES (?, ?, ?, ?, ?, ?)");
                $insertAppt->execute([$patientId1, $docRecord1, $tomorrow, '10:00:00', 'pending', 'Regular checkup for blood pressure concerns.']);
                $insertAppt->execute([$patientId2, $docRecord2, $dayAfter, '14:30:00', 'confirmed', 'Follow-up appointment for child vaccination.']);
            }

            Response::json([
                "success" => true,
                "message" => "Database initialized and populated with demo data successfully!"
            ]);

        } catch (PDOException $e) {
            Response::json([
                "success" => false,
                "error"   => "Database installation failed: " . $e->getMessage()
            ], 500);
        }
    }
}
