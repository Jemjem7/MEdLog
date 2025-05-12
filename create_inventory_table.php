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
    
    // Create medicines table
    $sql = "CREATE TABLE IF NOT EXISTS medicines (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        description TEXT DEFAULT '',
        quantity INT NOT NULL DEFAULT 0,
        price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        expiry_date DATE NOT NULL,
        supplier VARCHAR(100) DEFAULT '',
        category VARCHAR(50) DEFAULT '',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    echo "Medicines table created successfully<br>";
    
    // Update existing records to set default values for null fields
    $sql = "UPDATE medicines SET 
            description = COALESCE(description, ''),
            price = COALESCE(price, 0.00),
            supplier = COALESCE(supplier, ''),
            category = COALESCE(category, '')
            WHERE description IS NULL 
               OR price IS NULL 
               OR supplier IS NULL 
               OR category IS NULL";
    $conn->exec($sql);
    echo "Existing records updated successfully<br>";
    
    echo "Setup completed successfully!";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 