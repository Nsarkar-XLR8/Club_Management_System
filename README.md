# Club Management System (CMS)

A comprehensive web-based application designed to manage university club activities, member registrations, and administrative tasks. This system features dynamic role-based access control, ensuring a tailored experience for Guests, Members, and Administrators.

## 🚀 Features

### 🔐 Role-Based Access Control (RBAC)
The system dynamically adjusts the interface and access rights based on user roles:
* **Admin:** Full control with access to the Admin Dashboard, User Management, and oversight of all club activities.
* **Member:** Access to specific club pages (Computer Club, Social Service, Robotics), the Forum, and their profile.
* **Guest:** Public access to Home, About, and Contact pages.

### 🏛️ Club Modules
dedicated management pages for various university bodies:
* 💻 **UIU Computer Club:** Tech events and workshops.
* 🤝 **Social Service Club:** Community engagement and charity.
* 🤖 **Robotic Club:** Innovation and engineering projects.
* 💬 **Forum:** A community discussion board (`UIUAppForum`).

### 💻 Tech Stack
* **Frontend:** HTML5, CSS3 (Modern Flexbox, CSS Variables, Responsive Design).
* **Backend:** Core PHP (Session Management, Authentication).
* **Database:** MySQL (via XAMPP).
* **Environment:** Apache Web Server.

## 🛠️ Installation & Setup

1.  **Clone the repository** into your XAMPP `htdocs` folder:
    ```bash
    cd C:\xampp\htdocs
    git clone [https://github.com/Nsarkar-XLR8/Club_Management_System.git](https://github.com/Nsarkar-XLR8/Club_Management_System.git) cms
    ```

2.  **Database Configuration:**
    * Open **phpMyAdmin** (`http://localhost/phpmyadmin`).
    * Create a new database named `club_management` (or your specific DB name).
    * Import the `.sql` file provided in the root directory (if available).
    * *Note: Ensure your `db_connect.php` matches your local MySQL credentials.*

3.  **Run the Application:**
    * Start **Apache** and **MySQL** in XAMPP Control Panel.
    * Open your browser and navigate to:
        `http://localhost/cms/pages/index.php`

## 📂 Project Structure

cms/ ├── css/ # Stylesheets ├── images/ # Assets (Logos, Profile placeholders) ├── pages/ # Core Application Logic │ ├── Admin_Dashboard.php │ ├── UIUComputerClub.php │ ├── navbar.php # Dynamic Navigation Bar │ ├── manage_users.php │ └── ... ├── index.php # Entry point └── README.md # Project Documentation


## 🔒 Security
* **Session Handling:** Secure login sessions with automatic timeouts.
* **Input Sanitization:** `htmlspecialchars` used to prevent XSS attacks on output.
* **Access Control:** Pages check `$_SESSION['role']` before rendering sensitive content.

## 👤 Author
**Nsarkar-XLR8**
* GitHub: [@Nsarkar-XLR8](https://github.com/Nsarkar-XLR8)

---
*This project is developed for educational and organizational management purposes.*