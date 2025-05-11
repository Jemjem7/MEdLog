<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $patient_name = $_POST['patientName'];
    $doctor_name = $_POST['doctorName'];
    $diagnosis = $_POST['diagnosis'];
    $date = $_POST['date'];
    $status = $_POST['status'];

    // Update the prescription
    $stmt = $conn->prepare("UPDATE prescriptions SET patient_name=?, doctor_name=?, diagnosis=?, date=?, status=? WHERE id=?");
    $stmt->bind_param("ssssss", $patient_name, $doctor_name, $diagnosis, $date, $status, $id);
    $stmt->execute();

    // Remove existing medicines and add updated ones
    $conn->query("DELETE FROM medicines WHERE prescription_id='$id'");

    $medicines = json_decode($_POST['medicines'], true);
    foreach ($medicines as $medicine) {
        $name = $medicine['name'];
        $dosage = $medicine['dosage'];
        $duration = $medicine['duration'];

        $stmt = $conn->prepare("INSERT INTO medicines (prescription_id, name, dosage, duration) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $id, $name, $dosage, $duration);
        $stmt->execute();
    }

    echo json_encode(["status" => "success"]);
}
?>
