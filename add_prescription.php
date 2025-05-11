<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $id = $_POST['id'];
    $patient_name = $_POST['patientName'];
    $doctor_name = $_POST['doctorName'];
    $diagnosis = $_POST['diagnosis'];
    $date = $_POST['date'];
    $status = $_POST['status'];

    // Insert the prescription into the database
    $stmt = $conn->prepare("INSERT INTO prescriptions (id, patient_name, doctor_name, diagnosis, date, status) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt === false) {
        echo json_encode(["status" => "error", "message" => "Error preparing statement for prescriptions"]);
        exit;
    }
    
    $stmt->bind_param("ssssss", $id, $patient_name, $doctor_name, $diagnosis, $date, $status);
    if (!$stmt->execute()) {
        echo json_encode(["status" => "error", "message" => "Error executing query for prescriptions"]);
        exit;
    }

    // Insert each medicine associated with the prescription
    $medicines = json_decode($_POST['medicines'], true);
    foreach ($medicines as $medicine) {
        $name = $medicine['name'];
        $dosage = $medicine['dosage'];
        $duration = $medicine['duration'];

        $stmt = $conn->prepare("INSERT INTO medicines (prescription_id, name, dosage, duration) VALUES (?, ?, ?, ?)");
        if ($stmt === false) {
            echo json_encode(["status" => "error", "message" => "Error preparing statement for medicines"]);
            exit;
        }

        $stmt->bind_param("ssss", $id, $name, $dosage, $duration);
        if (!$stmt->execute()) {
            echo json_encode(["status" => "error", "message" => "Error executing query for medicines"]);
            exit;
        }
    }

    echo json_encode(["status" => "success"]);
}
?>
