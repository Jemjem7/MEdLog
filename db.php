<?php
$host = 'localhost'; // Host name, usually 'localhost'
$dbname = 'medlog'; // Your database name
$username = 'root'; // Your MySQL username (default is 'root' for XAMPP)
$password = ''; // Your MySQL password (default is '' for XAMPP)

try {
    // Establish a PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Handle connection error
    echo "Connection failed: " . $e->getMessage();
}
?>


<?php
$host = "localhost";  // change if using a different host
$username = "root";   // your MySQL username
$password = "";       // your MySQL password
$dbname = "prescriptions_db";  // database name

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>