<?php
// Connect to SQLite database
$db = new PDO('sqlite:add_prescription.db');

// Create table if not exists (optional, safeguard)
$db->exec("CREATE TABLE IF NOT EXISTS prescriptions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    prescription_id TEXT NOT NULL,
    patient TEXT NOT NULL,
    doctor TEXT NOT NULL,
    medicines TEXT NOT NULL,
    date TEXT NOT NULL,
    status TEXT NOT NULL
)");

// Sample Insert (example data — replace with real form data)
$prescription_id = 'PR-2025-002';
$patient = 'xdf';
$doctor = 'bxf';
$medicines = 'sdf (gsdf)';
$date = '2025-05-11';
$status = 'pending';

// Prepare statement to avoid SQL injection
$stmt = $db->prepare("INSERT INTO prescriptions (prescription_id, patient, doctor, medicines, date, status) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute([$prescription_id, $patient, $doctor, $medicines, $date, $status]);

echo "Prescription inserted successfully.";
?>
