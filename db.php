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
$dbname = "medlog";  // database name

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['patientName'], $_POST['doctorName'], $_POST['diagnosis'], $_POST['prescriptionDate'], $_POST['status'])) {
    $patientName = $_POST['patientName'];
    $doctorName = $_POST['doctorName'];
    $diagnosis = $_POST['diagnosis'];
    $date = $_POST['prescriptionDate'];
    $status = $_POST['status'];

    // For simplicity, let's assume one medicine per prescription for now
    $medicine = $_POST['medicine'] ?? '';
    $med_dosage = $_POST['dosage'] ?? '';

    $stmt = $conn->prepare("INSERT INTO prescriptions (patient_name, doctor_name, diagnosis, medicine, med_dosage, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $patientName, $doctorName, $diagnosis, $medicine, $med_dosage, $status, $date);
    $stmt->execute();
    $stmt->close();

    // Optionally redirect or show a success message
    header("Location: prescription_management.php?success=1");
    exit();
}
?>