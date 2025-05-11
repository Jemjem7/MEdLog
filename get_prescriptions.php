<?php
include 'db.php';

$query = "SELECT * FROM prescriptions";
$result = $conn->query($query);
$prescriptions = [];

while ($row = $result->fetch_assoc()) {
    $prescription_id = $row['id'];
    $medicines_query = "SELECT * FROM medicines WHERE prescription_id = '$prescription_id'";
    $medicines_result = $conn->query($medicines_query);
    $medicines = [];

    while ($medicine = $medicines_result->fetch_assoc()) {
        $medicines[] = $medicine;
    }

    $row['medicines'] = $medicines;
    $prescriptions[] = $row;
}

echo json_encode($prescriptions);
?>
