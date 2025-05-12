<?php
$servername = "localhost";
$username = "root";
$password = "";

try {
    // Create connection
    $conn = new PDO("mysql:host=$servername", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database
    $sql = "CREATE DATABASE IF NOT EXISTS medlog_db";
    $conn->exec($sql);
    echo "Database created successfully<br>";
    
    // Select the database
    $conn->exec("USE medlog_db");
    
    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        phone VARCHAR(20),
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'manager', 'staff') DEFAULT 'staff',
        last_login DATETIME,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    echo "Users table created successfully<br>";
    
    // Create user settings table
    $sql = "CREATE TABLE IF NOT EXISTS user_settings (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        email_notifications BOOLEAN DEFAULT TRUE,
        low_stock_alerts BOOLEAN DEFAULT TRUE,
        expiry_alerts BOOLEAN DEFAULT TRUE,
        currency VARCHAR(3) DEFAULT 'USD',
        date_format VARCHAR(20) DEFAULT 'MM/DD/YYYY',
        timezone VARCHAR(50) DEFAULT 'UTC',
        language VARCHAR(10) DEFAULT 'en',
        theme VARCHAR(20) DEFAULT 'light',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $conn->exec($sql);
    echo "User settings table created successfully<br>";
    
    // Create activity logs table
    $sql = "CREATE TABLE IF NOT EXISTS activity_logs (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT,
        action VARCHAR(255) NOT NULL,
        description TEXT,
        ip_address VARCHAR(45),
        user_agent TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )";
    $conn->exec($sql);
    echo "Activity logs table created successfully<br>";
    
    // Create security settings table
    $sql = "CREATE TABLE IF NOT EXISTS security_settings (
        id INT PRIMARY KEY AUTO_INCREMENT,
        setting_key VARCHAR(50) UNIQUE NOT NULL,
        setting_value TEXT,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    echo "Security settings table created successfully<br>";
    
    // Create remember tokens table
    $sql = "CREATE TABLE IF NOT EXISTS remember_tokens (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        token VARCHAR(64) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expires_at TIMESTAMP NOT NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_token (token)
    )";
    $conn->exec($sql);
    echo "Remember tokens table created successfully<br>";
    
    // Insert default security settings
    $sql = "INSERT IGNORE INTO security_settings (setting_key, setting_value, description) VALUES
        ('password_min_length', '8', 'Minimum password length'),
        ('password_require_special', '1', 'Require special characters in password'),
        ('password_require_numbers', '1', 'Require numbers in password'),
        ('password_require_uppercase', '1', 'Require uppercase letters in password'),
        ('max_login_attempts', '5', 'Maximum failed login attempts before lockout'),
        ('lockout_duration', '30', 'Account lockout duration in minutes'),
        ('session_timeout', '30', 'Session timeout in minutes'),
        ('require_2fa', '0', 'Require two-factor authentication')";
    $conn->exec($sql);
    echo "Default security settings inserted successfully<br>";
    
    // Create default admin user if not exists
    $sql = "INSERT IGNORE INTO users (name, email, password, role) VALUES 
        ('Admin User', 'admin@example.com', :password, 'admin')";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':password' => password_hash('admin123', PASSWORD_DEFAULT)]);
    echo "Default admin user created successfully<br>";
    
    // Get admin user ID
    $stmt = $conn->query("SELECT id FROM users WHERE email = 'admin@example.com'");
    $admin_id = $stmt->fetchColumn();
    
    // Create default settings for admin user
    $sql = "INSERT IGNORE INTO user_settings (user_id) VALUES (:user_id)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':user_id' => $admin_id]);
    echo "Default user settings created successfully<br>";
    
    echo "<br>Database setup completed successfully!<br>";
    echo "Default admin credentials:<br>";
    echo "Email: admin@example.com<br>";
    echo "Password: admin123<br>";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 