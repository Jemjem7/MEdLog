<?php include('db.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Prescription</title>
</head>
<body>
    <!-- Sidebar menu -->
    <ul>
        <li><a href="add_prescription.php">Add Prescription</a></li>
        <li><a href="recent_prescriptions.php">Recent Prescriptions</a></li>
    </ul>

    <h2>Add Prescription</h2>
    <form action="add_prescription.php" method="POST">
        <label for="patient_name">Patient Name:</label>
        <input type="text" name="patient_name" id="patient_name" required><br><br>

        <label for="age">Age:</label>
        <input type="number" name="age" id="age" required><br><br>

        <label for="medicine">Medicine:</label>
        <input type="text" name="medicine" id="medicine" required><br><br>

        <label for="frequency">Frequency:</label>
        <input type="text" name="frequency" id="frequency" required><br><br>

        <label for="duration">Duration:</label>
        <input type="text" name="duration" id="duration" required><br><br>

        <label for="status">Status:</label>
        <select name="status" id="status" required>
            <option value="Active">Active</option>
            <option value="Complete">Complete</option>
        </select><br><br>

        <label for="notes">Notes:</label><br>
        <textarea name="notes" id="notes" rows="4" cols="50"></textarea><br><br>

        <input type="submit" name="submit" value="Add Prescription">
    </form>

    <?php
    if (isset($_POST['submit'])) {
        $patient_name = $_POST['patient_name'];
        $age = $_POST['age'];
        $medicine = $_POST['medicine'];
        $frequency = $_POST['frequency'];
        $duration = $_POST['duration'];
        $status = $_POST['status'];
        $notes = $_POST['notes'];

        $stmt = $pdo->prepare("INSERT INTO prescriptions (patient_name, age, medicine, frequency, duration, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$patient_name, $age, $medicine, $frequency, $duration, $status, $notes]);

        echo "<p>Prescription added successfully!</p>";
    }
    ?>
</body>
</html>
