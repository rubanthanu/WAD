# Hospital Appointment Management System

A web-based **Hospital Appointment Management System** designed for Patients, Doctors, and Administrators. This application features a modern, responsive ReactJS SPA (Single Page Application) frontend communicating with an Object-Oriented PHP 8 PDO backend JSON API.

---

## Key Features

- **Patient Portal**:
  - Registration and authentication.
  - Multi-step booking wizard with double-booking slot checking.
  - Calendar overview of booked visits with status details.
  - Instant schedule cancellation.
- **Doctor Portal**:
  - Operational schedule queue.
  - Appointment confirmations, cancellations, and completion flags.
  - Dynamic consulting availability description.
- **Admin Portal**:
  - Real-time aggregation of clinic statistics.
  - CRUD panels to manage the Doctor roster, medical departments, and patient details.
  - Master CRUD table for scheduling oversight.
- **Security & Validation**:
  - Complete client-side input safety checks (JavaScript) and server-side safety guards (PHP).
  - Encrypted credential hashes (BCRYPT).
  - Database constraint validation (foreign key restricts).

---

## Directory Structure

```
├── api/                   # Backend PHP JSON API
│   ├── classes/           # Encapsulated OOP PHP Classes
│   │   ├── Database.php   # Handles PDO connection (Encapsulation)
│   │   ├── User.php       # Parent Class (Inheritance -> Patient, Doctor, Admin)
│   │   ├── Patient.php    # Patient-specific scheduling operations
│   │   ├── Doctor.php     # Doctor schedules and availability
│   │   ├── Admin.php      # Stats aggregation and complete CRUD operations
│   │   └── Appointment.php# Booking conflict resolution and record management
│   ├── auth.php           # Authentication routing
│   ├── appointments.php   # Scheduling CRUD routing
│   ├── doctors.php        # Doctor roster & availability routing
│   ├── departments.php    # Medical departments CRUD routing
│   ├── stats.php          # Admin statistics metrics routing
│   ├── config.php         # CORS, session, and Spl Autoloader configuration
│   └── bootstrap.php      # Automated database builder and demo data loader
├── src/                   # Frontend ReactJS SPA
│   ├── pages/             # Layout screen pages (Home, Login, Dashboard, etc.)
│   ├── App.jsx            # AuthContext, layout wrappers, and protected route shields
│   ├── index.css          # Custom glassmorphic styling system
│   └── main.jsx           # Application mounter
├── schema.sql             # Raw MySQL table definition structures
├── index.html             # Single entrypoint HTML with metadata tags
└── package.json           # React NodeJS dependencies
```

---

## Setup Instructions

### 1. Database Setup (XAMPP / MySQL)
1. Launch **XAMPP Control Panel** and start both **Apache** and **MySQL** modules.
2. Ensure MySQL is running on `localhost:3306`.
3. Open your browser and navigate to **[http://localhost/WAD/api/bootstrap.php](http://localhost/WAD/api/bootstrap.php)**, or execute in your terminal:
   ```bash
   php api/bootstrap.php
   ```
4. This script automatically:
   - Configures the MySQL database `hospital_db`.
   - Creates tables for `users`, `doctors`, `departments`, and `appointments`.
   - Populates departments, doctors, administrative users, and sample appointments.

### 2. Frontend Launch (ReactJS Dev Server)
1. Open a terminal inside the workspace directory (`c:\xampp\htdocs\WAD`).
2. Install NodeJS project dependencies (if not already done):
   ```bash
   npm install
   ```
3. Boot the local React web server:
   ```bash
   npm run dev
   ```
4. Open the displayed local address (usually **[http://localhost:5173](http://localhost:5173)**) in your browser.

---

## Demo Credentials

You can use the following preloaded credentials to test role-based access control (RBAC):

- **Administrator**:
  - Email: `admin@hospital.com`
  - Password: `admin123`
- **Doctor (Cardiologist)**:
  - Email: `sarah.j@hospital.com`
  - Password: `doctor123`
- **Patient**:
  - Email: `jane.doe@example.com`
  - Password: `patient123`
