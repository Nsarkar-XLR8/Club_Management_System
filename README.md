# UIU Club Management System (CMS Pro) - Enterprise Edition

Welcome to the upgraded United International University (UIU) Club Management System! This project has been transformed from a legacy core PHP application into a modern, industry-grade **React (Vite) Single Page Application** with a **PHP REST API** backend and a fully normalized **MySQL** database.

## 🚀 Enterprise Features

1. **Executive Board Election & Digital Voting System:** Secure, role-restricted voting for verified club members.
2. **Campus Facility & Equipment Booking Engine:** Conflict-prevention system for reserving auditoriums and gear.
3. **Digital Student Club ID Card Pass:** Glassmorphism UI cards with QR codes and PDF export.
4. **DSA Reports & Event Analytics:** Dashboard for the Director of Student Affairs with chart metrics.
5. **Dynamic Pinned Announcements & Noticeboard:** Club-specific pinned notices and general announcements.
6. **Post-Event Feedback & Rating System:** Analytics and reviews for completed events.
7. **Alumni Mentorship Network & Directory:** Connection platform mapping alumni to current students.

---

## 🛠️ Tech Stack
- **Frontend:** React.js (Vite), Tailwind CSS, Lucide Icons
- **Backend:** PHP 8+ (Stateless REST API)
- **Database:** MySQL / MariaDB
- **Authentication:** JWT (JSON Web Tokens) with Role-Based Access Control (RBAC)

---

## 💻 How to Run the Project Locally

Follow these steps to set up and run the full-stack application on your local machine.

### Prerequisites
- **Node.js** (v18 or higher)
- **MySQL Server** (XAMPP / MAMP / Docker)
- **PHP** (v8.0 or higher)

### 1. Database Setup
1. Open your MySQL client (e.g., phpMyAdmin, DBeaver, or MySQL CLI).
2. Create a new database named `cms`.
3. Import the schema file to create the tables:
   ```bash
   mysql -u root -p cms < database/schema.sql
   ```
4. Import the seeders to populate the database with mock data (including the admin user):
   ```bash
   mysql -u root -p cms < database/seeders.sql
   ```
*(Note: If you are using XAMPP, you can use phpMyAdmin's "Import" tab to upload `schema.sql` and then `seeders.sql` to the `cms` database)*

### 2. Backend Setup (PHP REST API)
The backend requires a PHP server running on port 8000. It expects the MySQL database credentials to be `root` with no password (default XAMPP settings). If your database has a password, update it in `api/config.php`.

1. Open a terminal and navigate to the project root directory.
2. Start the built-in PHP development server on port 8000:
   ```bash
   php -S localhost:8000 -t ./
   ```
*This will serve the API endpoints at `http://localhost:8000/api/...`.*

### 3. Frontend Setup (React SPA)
The frontend is built with React and Vite. It will run on a separate development server and proxy requests to your PHP backend.

1. Open a **new** terminal and navigate to the `frontend` directory:
   ```bash
   cd frontend
   ```
2. Install the Node.js dependencies:
   ```bash
   npm install
   ```
3. Start the Vite development server:
   ```bash
   npm run dev
   ```
4. Open your browser and navigate to the URL provided by Vite (usually `http://localhost:5173`).

### 🔑 Test Credentials
You can log in using the mock data populated from the seeders:
- **Admin:** `admin@uiu.ac.bd` (Password: `password123`)
- **Student/Member:** `student1@uiu.ac.bd` (Password: `password123`)

---

## 🔒 Security Architecture
- **JWT Authentication:** Stateful sessions have been replaced with stateless JWT tokens stored securely.
- **RBAC Middleware:** Backend routes strictly enforce authorization (e.g., only verified members can vote; only admins can approve budgets).
- **Password Hashing:** Passwords are encrypted using `password_hash()`.