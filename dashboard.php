<?php
session_start();

// Redirect to login page if the user is not logged in
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$user = htmlspecialchars($_SESSION['user']);

// Sample medicine data (You can replace this with a database)
$medicines = [
    ["name" => "Paracetamol", "expiry" => "2025-06-10"],
    ["name" => "Amoxicillin", "expiry" => "2024-04-15"],
    ["name" => "Ibuprofen", "expiry" => "2023-12-30"], // Expired example
];

$current_date = date("Y-m-d");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medicine Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f4f4f4;
        }
        .expired {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
    
    <h2>Clinic office</h2>
    <table>
        <tr>
            <th>Medicine Name</th>
            <th>Expiration Date</th>
            <th>Status</th>
        </tr>
        <?php foreach ($medicines as $medicine): 
            $is_expired = ($medicine['expiry'] < $current_date) ? "Expired" : "Valid";
            $status_class = ($is_expired === "Expired") ? "expired" : "";
        ?>
        <tr>
            <td><?php echo htmlspecialchars($medicine['name']); ?></td>
            <td><?php echo htmlspecialchars($medicine['expiry']); ?></td>
            <td class="<?php echo $status_class; ?>"><?php echo $is_expired; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <p style="text-align: center; margin-top: 20px;"><button type="button" onclick="location.href='logout.php'">Logout</button></p>
   
</body>
</html>
