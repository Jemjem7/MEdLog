<?php
// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "medlog_db";

try {
    // Create connection
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create system_settings table
    $sql = "CREATE TABLE IF NOT EXISTS system_settings (
        id INT PRIMARY KEY AUTO_INCREMENT,
        currency VARCHAR(10) DEFAULT 'PHP',
        date_format VARCHAR(20) DEFAULT 'Y-m-d',
        timezone VARCHAR(50) DEFAULT 'Asia/Manila',
        low_stock_threshold INT DEFAULT 20,
        expiry_alert_days INT DEFAULT 90,
        location VARCHAR(100) DEFAULT 'Manolo Fortich, Bukidnon',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    echo "System settings table created successfully<br>";
    
    // Check if table is empty
    $stmt = $conn->query("SELECT COUNT(*) FROM system_settings");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        // Insert default settings for Philippines
        $sql = "INSERT INTO system_settings (currency, date_format, timezone, low_stock_threshold, expiry_alert_days, location) 
                VALUES ('PHP', 'Y-m-d', 'Asia/Manila', 20, 90, 'Manolo Fortich, Bukidnon')";
        $conn->exec($sql);
        echo "Default settings inserted successfully<br>";
    }
    
    echo "Setup completed successfully!";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 