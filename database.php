<?php
// database.php
$servername = "localhost";
$username = "root";  // Default username for XAMPP
$password = "";      // Default password for XAMPP (usually empty)
$dbname = "user_db";  // Make sure this matches your created database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

CREATE DATABASE IF NOT EXISTS medlog;
USE medlog;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);
?>
