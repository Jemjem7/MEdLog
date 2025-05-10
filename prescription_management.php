<?php
session_start();
include('db.php');

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Prescription Management</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
        margin: 0;
        font-family: Arial, sans-serif;
        display: flex;
        background-color: #f5f5f5;
    }

    .sidebar {
        width: 250px;
        background: #4b7c67;
        height: 100vh;
        position: fixed;
        top: 0;
        padding: 20px;
        color: white;
        transition: transform 0.3s;
    }

    .sidebar.hidden {
        transform: translateX(-100%);
    }

    .sidebar h3 {
        color: white;
        margin-bottom: 30px;
    }

    .sidebar a {
        color: white;
        text-decoration: none;
        display: block;
        padding: 10px 15px;
        border-radius: 4px;
        background: #2f5d4d;
        margin-bottom: 10px;
    }

    .sidebar a:hover {
        background: #3e6e5c;
    }

    .main-content {
        flex: 1;
        margin-left: 250px;
        padding: 20px;
        transition: margin-left 0.3s;
    }

    .main-content.full {
        margin-left: 0;
    }

    .header {
        display: flex;
        align-items: center;
        padding: 10px 20px;
        background: #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .burger {
        font-size: 24px;
        cursor: pointer;
        margin-right: 20px;
    }

    form {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        margin-bottom: 30px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
    }

    table, th, td {
        border: 1px solid #ccc;
    }

    th, td {
        padding: 10px;
        text-align: left;
    }

    hr {
        margin: 40px 0;
    }
  </style>
</head>
<body>

<div class="sidebar" id="sidebar">
  <h3>MediSync</h3>
  <a href="dashboard.php" class="sidebar-btn">Dashboard</a>
  <a href="inventory.php" class="sidebar-btn">Medicine Inventory</a>
  <a href="prescription_management.php" class="sidebar-btn">Prescription Management</a>
  <a href="orders.php" class="sidebar-btn">Orders & Supplier</a>
  <a href="reports.php" class="sidebar-btn">Reports & Analytics</a>
  <a href="settings.php" class="sidebar-btn">Settings</a>
</div>

<div class="main-content" id="mainContent">
  <div class="header">
    <div class="burger" onclick="toggleSidebar()">☰</div>
    <div class="dashboard-title">Prescription Management</div>
  </div>

  <h2>Add Prescription</h2>
  <form action="prescription_management.php" method="POST">
    <label for="patient_name">Patient Name:</label><br>
    <input type="text" name="patient_name" id="patient_name" required><br><br>

    <label for="age">Age:</label><br>
    <input type="number" name="age" id="age" required><br><br>

    <label for="medicine">Medicine:</label><br>
    <input type="text" name="medicine" id="medicine" required><br><br>

    <label for="frequency">Frequency:</label><br>
    <input type="text" name="frequency" id="frequency" required><br><br>

    <label for="duration">Duration:</label><br>
    <input type="text" name="duration" id="duration" required><br><br>

    <label for="status">Status:</label><br>
    <select name="status" id="status" required>
      <option value="Active">Active</option>
      <option value="Complete">Complete</option>
    </select><br><br>

    <label for="notes">Notes:</label><br>
    <textarea name="notes" id="notes" rows="4" cols="50"></textarea><br><br>

    <label for="doctor_message">Message to Medisync:</label><br>
    <textarea name="doctor_message" id="doctor_message" rows="4" cols="50"></textarea><br><br>

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
      $doctor_message = $_POST['doctor_message'];

      $stmt = $pdo->prepare("INSERT INTO prescriptions (patient_name, age, medicine, frequency, duration, status, notes, doctor_message) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
      $stmt->execute([$patient_name, $age, $medicine, $frequency, $duration, $status, $notes, $doctor_message]);

      echo "<p>Prescription added successfully!</p>";
  }

  // Auto update claim status
  $pdo->prepare("UPDATE prescriptions SET claim_status = 'Pending' WHERE claim_status != 'Complete' AND created_at <= NOW() - INTERVAL 1 DAY")->execute();
  ?>

  <hr>

  <h2>Recent Prescriptions</h2>
  <table>
    <thead>
      <tr>
        <th>Patient Name</th>
        <th>Age</th>
        <th>Medicine</th>
        <th>Frequency</th>
        <th>Duration</th>
        <th>Status</th>
        <th>Notes</th>
        <th>Doctor's Message</th>
        <th>Created At</th>
        <th>Claim Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php
    $stmt = $pdo->query("SELECT * FROM prescriptions ORDER BY created_at DESC");
    while ($row = $stmt->fetch()) {
        echo "<tr>
            <td>{$row['patient_name']}</td>
            <td>{$row['age']}</td>
            <td>{$row['medicine']}</td>
            <td>{$row['frequency']}</td>
            <td>{$row['duration']}</td>
            <td>{$row['status']}</td>
            <td>{$row['notes']}</td>
            <td>{$row['doctor_message']}</td>
            <td>{$row['created_at']}</td>
            <td>{$row['claim_status']}</td>
            <td>
              <a href='?edit={$row['id']}'>Edit</a> |
              <a href='?delete={$row['id']}'>Delete</a>
            </td>
        </tr>";
    }
    ?>
    </tbody>
  </table>

  <?php
  if (isset($_GET['edit'])) {
      $id = $_GET['edit'];
      $stmt = $pdo->prepare("SELECT * FROM prescriptions WHERE id = ?");
      $stmt->execute([$id]);
      $prescription = $stmt->fetch();

      if (isset($_POST['update'])) {
          $status = $_POST['status'];
          $stmt = $pdo->prepare("UPDATE prescriptions SET status = ? WHERE id = ?");
          $stmt->execute([$status, $id]);
          echo "<p>Status updated!</p>";
      }

      echo "
      <h3>Update Status for: {$prescription['patient_name']}</h3>
      <form method='POST'>
          <label for='status'>Status:</label>
          <select name='status'>
              <option value='Active' " . ($prescription['status'] == 'Active' ? 'selected' : '') . ">Active</option>
              <option value='Complete' " . ($prescription['status'] == 'Complete' ? 'selected' : '') . ">Complete</option>
          </select>
          <input type='submit' name='update' value='Update'>
      </form>";
  }

  if (isset($_GET['delete'])) {
      $id = $_GET['delete'];
      $stmt = $pdo->prepare("DELETE FROM prescriptions WHERE id = ?");
      $stmt->execute([$id]);
      header("Location: prescription_management.php");
      exit;
  }
  ?>
</div>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const main = document.getElementById('mainContent');
    sidebar.classList.toggle('hidden');
    main.classList.toggle('full');
}
</script>

</body>
</html>
