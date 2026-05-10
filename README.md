# Hotel Management System

A lightweight PHP and MySQL hotel management prototype for user registration, login, room booking, invoice creation, and simple session-protected dashboard access.

## Overview

This project demonstrates the core workflow of a small hotel booking system:

1. Guests, staff, or admins register with an account.
2. Users log in with a password verified against a stored password hash.
3. Authenticated users can reach a protected dashboard.
4. A room booking form submits booking details.
5. The booking transaction checks room availability, calculates the stay price, confirms the booking, marks the room as booked, and creates an invoice.

## Features

- **User registration** with `guest`, `staff`, and `admin` roles.
- **Secure password storage** using PHP `password_hash()` and login verification using `password_verify()`.
- **Session-based authentication** for dashboard access.
- **Room booking workflow** with check-in/check-out validation.
- **Database transaction support** for booking, room status updates, and invoice creation.
- **Row locking during booking** with `SELECT ... FOR UPDATE` to reduce double-booking risk.
- **Database connection smoke test** through `test_connection.php`.

## Project Structure

| File | Purpose |
| --- | --- |
| `register.php` | Registration form and user creation logic. |
| `login.php` | Login form, password verification, and session creation. |
| `dashboard.php` | Protected landing page for logged-in users. |
| `logout.php` | Ends the current session and redirects to login. |
| `booking.html` | Basic room booking form. |
| `book_room.php` | Transactional booking handler that updates rooms and invoices. |
| `test_connection.php` | Verifies that the database connection can be opened. |
| `db_connect.php` | Required database connection file expected by the PHP scripts. This file is not currently committed and must be created locally. |

## Requirements

- PHP 7.4 or newer.
- MySQL or MariaDB.
- A web server capable of running PHP, such as Apache, Nginx with PHP-FPM, XAMPP, WAMP, MAMP, or PHP's built-in development server.
- The PHP MySQLi extension enabled.

## Setup

### 1. Clone the repository

```bash
git clone <repository-url>
cd Hotel_management_system
```

### 2. Create the database

Create a MySQL database for the application:

```sql
CREATE DATABASE hotel_management;
USE hotel_management;
```

### 3. Create the expected tables

The repository does not currently include a schema file, but the PHP code expects the following tables and columns:

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('guest', 'staff', 'admin') NOT NULL DEFAULT 'guest'
);

CREATE TABLE rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_number VARCHAR(50) NOT NULL UNIQUE,
    room_type VARCHAR(100),
    price DECIMAL(10, 2) NOT NULL,
    status ENUM('available', 'booked') NOT NULL DEFAULT 'available'
);

CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    room_id INT NOT NULL,
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    status ENUM('confirmed', 'cancelled') NOT NULL DEFAULT 'confirmed',
    total_price DECIMAL(10, 2) NOT NULL,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (room_id) REFERENCES rooms(id)
);

CREATE TABLE invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    payment_date DATETIME NOT NULL,
    FOREIGN KEY (booking_id) REFERENCES bookings(id)
);
```

Add at least one available room before testing bookings:

```sql
INSERT INTO rooms (room_number, room_type, price, status)
VALUES ('101', 'Deluxe', 2500.00, 'available');
```

### 4. Create `db_connect.php`

Create a local `db_connect.php` file in the project root. The application scripts include this file to access the `$conn` MySQLi connection.

```php
<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'hotel_management';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}
?>
```

> Keep real credentials out of version control. The repository already ignores `db_connect.php`.

### 5. Run the application

Using PHP's built-in development server:

```bash
php -S localhost:8000
```

Then open these pages in your browser:

- `http://localhost:8000/register.php` to create a user.
- `http://localhost:8000/login.php` to log in.
- `http://localhost:8000/dashboard.php` to view the protected dashboard.
- `http://localhost:8000/booking.html` to book a room.
- `http://localhost:8000/test_connection.php` to verify database connectivity.

## Booking Flow Details

`book_room.php` performs the booking inside a database transaction:

1. Reads `user_id`, `room_id`, `check_in`, and `check_out` from the submitted form.
2. Locks the selected room row with `FOR UPDATE`.
3. Confirms that the room exists and is marked as `available`.
4. Calculates the number of nights and total price.
5. Inserts a confirmed booking.
6. Updates the room status to `booked`.
7. Inserts an invoice for the booking.
8. Commits the transaction, or rolls it back if any step fails.

## Current Limitations

- The UI is intentionally minimal and uses plain HTML forms.
- `db_connect.php` and a formal SQL schema file are not committed.
- Booking uses manually entered `user_id` and `room_id` values instead of authenticated user and room selection screens.
- Room availability is tracked with a single `status` field, so a booked room cannot be reserved for future non-overlapping date ranges without additional availability logic.
- There is no dedicated admin interface for adding rooms, viewing bookings, or managing users.

## Suggested Next Improvements

- Add a committed `schema.sql` or migration system.
- Use the logged-in session user for bookings instead of a manual `user_id` field.
- Add room listing and availability search by date range.
- Improve form validation and sanitize output before rendering.
- Add a shared layout and CSS for a more polished interface.
- Build role-specific dashboards for guests, staff, and admins.
- Add automated tests for registration, login, and booking transactions.

## License

No license file is currently included. Add a license before distributing or reusing this project publicly.
