
<?php include('db.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recent Prescriptions</title>
</head>
<body>
    <h2>Recent Prescriptions</h2>

    <table border="1">
        <thead>
            <tr>
                <th>Patient Name</th>
                <th>Age</th>
                <th>Medicine</th>
                <th>Frequency</th>
                <th>Duration</th>
                <th>Status</th>
                <th>Notes</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stmt = $pdo->query("SELECT * FROM prescriptions ORDER BY created_at DESC");
            while ($row = $stmt->fetch()) {
                echo "<tr>";
                echo "<td>{$row['patient_name']}</td>";
                echo "<td>{$row['age']}</td>";
                echo "<td>{$row['medicine']}</td>";
                echo "<td>{$row['frequency']}</td>";
                echo "<td>{$row['duration']}</td>";
                echo "<td>{$row['status']}</td>";
                echo "<td>{$row['notes']}</td>";
                echo "<td>{$row['created_at']}</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>