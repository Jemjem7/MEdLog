<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medicine Inventory Management</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .alert-expiring {
            background-color: #fff3cd;
            border-color: #ffeeba;
        }
        .alert-shortage {
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        .table-responsive {
            overflow-x: auto;
        }
        @media (max-width: 768px) {
            .card {
                margin-bottom: 1rem;
            }
        }
    </style>
</head>
<body>

<?php
// Database connection
$host = "localhost";
$db = "medlog_db";
$user = "root";
$pass = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Add new medicine
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_medicine'])) {
    $name = htmlspecialchars($_POST['name']);
    $quantity = (int)$_POST['quantity'];
    $minimum_stock = (int)$_POST['minimum_stock'];
    $expiry_date = $_POST['expiry_date'];
    $category = htmlspecialchars($_POST['category']);

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
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Delete medicine
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM medicines WHERE id = :id");
    $stmt->execute([':id' => $delete_id]);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Get medicines with status
function getInventoryStatus($conn) {
    // SQL query to fetch medicines with status
    $sql = "SELECT *, 
            CASE 
                WHEN quantity <= minimum_stock THEN 'shortage'
                WHEN DATEDIFF(expiry_date, CURDATE()) <= 30 THEN 'expiring'
                ELSE 'normal'
            END as status
            FROM medicines 
            ORDER BY expiry_date ASC";
    
    // Prepare statement
    $stmt = $conn->prepare($sql);

    // Execute the statement
    try {
        $stmt->execute();
    } catch (PDOException $e) {
        // Catch any exceptions and show the error message
        echo "Error executing query: " . $e->getMessage();
        return [];
    }

    // Return the fetched data as an associative array
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


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

    <!-- Add Medicine Form -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Add New Medicine</h5>
        </div>
        <div class="card-body">
            <form method="POST" class="row g-3">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="name" placeholder="Medicine Name" required>
                </div>
                <div class="col-md-2">
                    <input type="number" class="form-control" name="quantity" placeholder="Quantity" required>
                </div>
                <div class="col-md-2">
                    <input type="number" class="form-control" name="minimum_stock" placeholder="Minimum Stock" required>
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" name="expiry_date" required>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="category" required>
                        <option value="">Select Category</option>
                        <option value="Tablet">Tablet</option>
                        <option value="Capsule">Capsule</option>
                        <option value="Syrup">Syrup</option>
                        <option value="Injection">Injection</option>
                        <option value="Cream">Cream</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" name="add_medicine" class="btn btn-primary w-100">Add</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Medicine Inventory Table -->
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
                                <button class="btn btn-sm btn-secondary" disabled title="Edit not yet implemented">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="?delete_id=<?= $item['id'] ?>" onclick="return confirm('Delete this medicine?')" class="btn btn-sm btn-danger">
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
