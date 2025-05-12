
<?php include 'sidebar.php'; ?>


<!DOCTYPE html>
<html lang="en">
<head>



  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>






    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medicine Inventory Management</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
  .sidebar {
    transition: all 0.3s ease;
  }
  .notification-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background-color: #ef4444;
    color: white;
    font-size: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .notification-panel {
    transition: all 0.3s ease;
    transform: translateX(100%);
  }
  .notification-panel.open {
    transform: translateX(0);
  }
  .prescription-notification {
    animation: pulse 2s infinite;
  }
  @keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.4); }
    70% { box-shadow: 0 0 0 10px rgba(59, 130, 246, 0); }
    100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
  }
</style>

</head>
<body>
<?php
// Database configuration
$host = "localhost";
$db = "medlog_db";
$user = "root";
$pass = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Fetch all medicines with computed status
function getInventoryStatus($conn) {
    $sql = "SELECT *, 
                CASE 
                    WHEN quantity <= minimum_stock THEN 'shortage'
                    WHEN DATEDIFF(expiry_date, CURDATE()) <= 30 THEN 'expiring'
                    ELSE 'normal'
                END as status
            FROM medicines";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch single medicine for editing
$edit_medicine = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $sql = "SELECT * FROM medicines WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $edit_id]);
    $edit_medicine = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle medicine addition
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_medicine'])) {
    $name = $_POST['name'];
    $quantity = $_POST['quantity'];
    $minimum_stock = $_POST['minimum_stock'];
    $expiry_date = $_POST['expiry_date'];
    $category = $_POST['category'];

    $sql = "INSERT INTO medicines (name, quantity, minimum_stock, expiry_date, category) 
            VALUES (:name, :quantity, :minimum_stock, :expiry_date, :category)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':name' => $name,
        ':quantity' => $quantity,
        ':minimum_stock' => $minimum_stock,
        ':expiry_date' => $expiry_date,
        ':category' => $category
    ]);
}

// Handle medicine update
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['edit_medicine'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $quantity = $_POST['quantity'];
    $minimum_stock = $_POST['minimum_stock'];
    $expiry_date = $_POST['expiry_date'];
    $category = $_POST['category'];

    $sql = "UPDATE medicines 
            SET name = :name, quantity = :quantity, minimum_stock = :minimum_stock, expiry_date = :expiry_date, category = :category 
            WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':id' => $id,
        ':name' => $name,
        ':quantity' => $quantity,
        ':minimum_stock' => $minimum_stock,
        ':expiry_date' => $expiry_date,
        ':category' => $category
    ]);

    header("Location: " . $_SERVER['PHP_SELF']); // Refresh after update
    exit();
}

// Handle deletion
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $sql = "DELETE FROM medicines WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $delete_id]);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Load inventory
$inventory = getInventoryStatus($conn);
?>





















<div class="container-fluid py-4">
    <h2 class="mb-4">Medicine Inventory Management</h2>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <?php
        $total = count($inventory);
        $shortage = count(array_filter($inventory, fn($m) => $m['status'] === 'shortage'));
        $expiring = count(array_filter($inventory, fn($m) => $m['status'] === 'expiring'));
        ?>
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5>Total Medicines</h5>
                    <h2><?= $total ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5>Low Stock Items</h5>
                    <h2><?= $shortage ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5>Expiring Soon</h5>
                    <h2><?= $expiring ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Add or Edit Medicine Form -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><?= $edit_medicine ? "Edit Medicine" : "Add New Medicine" ?></h5>
    </div>
    <div class="card-body">
        <form method="POST" class="row g-3">
            <?php if ($edit_medicine): ?>
                <input type="hidden" name="id" value="<?= $edit_medicine['id'] ?>">
            <?php endif; ?>
            <div class="col-md-3">
                <input type="text" class="form-control" name="name" placeholder="Medicine Name" value="<?= $edit_medicine['name'] ?? '' ?>" required>
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control" name="quantity" placeholder="Quantity" value="<?= $edit_medicine['quantity'] ?? '' ?>" required>
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control" name="minimum_stock" placeholder="Minimum Stock" value="<?= $edit_medicine['minimum_stock'] ?? '' ?>" required>
            </div>
            <div class="col-md-2">
                <input type="date" class="form-control" name="expiry_date" value="<?= $edit_medicine['expiry_date'] ?? '' ?>" required>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="category" required>
                    <option value="">Select Category</option>
                    <?php
                    $categories = ['Tablet', 'Capsule', 'Syrup', 'Injection', 'Cream'];
                    foreach ($categories as $cat) {
                        $selected = isset($edit_medicine['category']) && $edit_medicine['category'] === $cat ? 'selected' : '';
                        echo "<option value=\"$cat\" $selected>$cat</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" name="<?= $edit_medicine ? 'edit_medicine' : 'add_medicine' ?>" class="btn btn-<?= $edit_medicine ? 'warning' : 'primary' ?> w-100">
                    <?= $edit_medicine ? 'Update' : 'Add' ?>
                </button>
            </div>
        </form>
    </div>
</div>


    <!-- Inventory Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Medicine Inventory</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Medicine Name</th>
                            <th>Category</th>
                            <th>Quantity</th>
                            <th>Min Stock</th>
                            <th>Expiry Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inventory as $item): ?>
                        <tr class="<?= $item['status'] === 'shortage' ? 'alert-shortage' : ($item['status'] === 'expiring' ? 'alert-expiring' : '') ?>">
                            <td><?= htmlspecialchars($item['name']) ?></td>
                            <td><?= htmlspecialchars($item['category']) ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td><?= $item['minimum_stock'] ?></td>
                            <td><?= htmlspecialchars($item['expiry_date']) ?></td>
                            <td>
                                <?php if ($item['status'] === 'shortage'): ?>
                                    <span class="badge bg-danger">Low Stock</span>
                                <?php elseif ($item['status'] === 'expiring'): ?>
                                    <span class="badge bg-warning text-dark">Expiring Soon</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Normal</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="?edit_id=<?= $item['id'] ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="?delete_id=<?= $item['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this item?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($inventory)): ?>
                        <tr><td colspan="7" class="text-center text-muted">No medicines found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
