# The Coding Science - Platform

A custom PHP-based web application for managing online courses, enrollments, and student dashboards.

## 🚀 Local Setup with XAMPP

### Prerequisites
-   **XAMPP** installed (Apache & MySQL).
-   **PHP** (should be included in XAMPP path or accessible).

### Installation Steps

1.  **Clone/Download** the project to your chosen directory.
    -   If using XAMPP's default `htdocs`, place the folder in `C:\xampp\htdocs\thecodingscience-live`.

2.  **Configuration**
    -   The `config.php` file is already pre-configured for default XAMPP settings:
        ```php
        define('DB_HOST', 'localhost');
        define('DB_NAME', 'thecodin_db');
        define('DB_USER', 'root');
        define('DB_PASS', '');
        ```
    -   If you have a password for your root user, update `config.php`.

3.  **Database Setup** (Automated)
    -   Open a terminal in the project directory.
    -   Run the setup script:
        ```bash
        php setup_db.php
        ```
    -   This wil create the database `thecodin_db` and import tables from `schema.sql` and data from `data_import.sql`.

4.  **Running the App**
    -   Start **Apache** and **MySQL** in XAMPP Control Panel.
    -   Open your browser.
    -   If viewing from `htdocs`: http://localhost/thecodingscience-live
    -   **OR** use the built-in PHP server (easiest):
        ```bash
        php -S localhost:8000
        ```
        Then visit http://localhost:8000

## 📁 Project Structure

-   `index.php`: Main entry point and router.
-   `config.php`: Database & Site configuration.
-   `includes/`: Helper functions and DB connection.
-   `views/`: Frontend page templates (Home, Courses, Dashboard, etc.).
-   `admin/`: Admin panel logic and views.
-   `assets/`: CSS, JS, and Images.

## 🔐 Admin Access

Check the database `users` table for admin credentials. Default might be:
-   **Email**: `admin@thecodingscience.com` (Check database to confirm)
-   **Password**: *Encrypted in DB* (You may need to create a new user manually or check `data_import.sql` for default users).
