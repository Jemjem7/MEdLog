<?php
// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";

try {
    // Create connection without database
    $conn = new PDO("mysql:host=$servername", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS medlog_db";
    $conn->exec($sql);
    echo "Database created successfully<br>";
    
    // Select the database
    $conn->exec("USE medlog_db");
    
    // Create system_settings table
    $sql = "CREATE TABLE IF NOT EXISTS system_settings (
        id INT PRIMARY KEY AUTO_INCREMENT,
        currency VARCHAR(10) DEFAULT 'USD',
        date_format VARCHAR(20) DEFAULT 'Y-m-d',
        timezone VARCHAR(50) DEFAULT 'UTC',
        low_stock_threshold INT DEFAULT 10,
        expiry_alert_days INT DEFAULT 30,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    echo "System settings table created successfully<br>";
    
    // Check if default settings exist
    $stmt = $conn->query("SELECT COUNT(*) FROM system_settings");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        // Insert default settings
        $sql = "INSERT INTO system_settings (currency, date_format, timezone, low_stock_threshold, expiry_alert_days) 
                VALUES ('USD', 'Y-m-d', 'UTC', 10, 30)";
        $conn->exec($sql);
        echo "Default settings inserted successfully<br>";
    }
    
    echo "Database setup completed successfully!";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 