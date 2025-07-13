# LaundryHub

This project is the Final Project for the Human-Computer Interaction course  
Informatics Study Program, Faculty of CompSci, Universitas Esa Unggul

## Installation Guide

1. **Download or Clone this Repository**
    - Use Git:  
      `git clone https://github.com/yourusername/laundryhub.git`
    - Or download as ZIP and extract.

2. **Create a Database named `laundryhub` (MariaDB/MySQL)**
    - Using phpMyAdmin:
        - Open phpMyAdmin
        - Click "New" and create a database named `laundryhub`
    - Using terminal/command line:
      ```sql
      CREATE DATABASE laundryhub;
      ```

3. **Import the Database Structure**
    - Import `laundryhub.sql` into the `laundryhub` database via phpMyAdmin or command line.

4. **Configure Database Connection**
    - Edit `connect-db.php` and set your database username, password, and host if needed.

5. **Run the Project**
    - Place the project folder in your web server directory (e.g., `htdocs` for XAMPP).
    - Start Apache and MySQL from XAMPP.
    - Access the project via `http://localhost/laundryhub/` in your browser.

## Admin Login

- **Email:** `admin`
- **Password:** `admin`

## Troubleshooting

- If you get a database connection error, check your credentials in `connect-db.php`.
- Make sure MariaDB/MySQL service is running.

---