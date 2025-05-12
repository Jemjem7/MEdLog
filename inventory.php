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

<!DOCTYPE html>
<html lang="en">
<head>
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
<body class="bg-gray-100">
    <!-- Sidebar -->
    <div id="sidebar" class="fixed top-0 left-0 h-screen w-64 bg-green-800 text-white p-0 shadow-lg z-40 hidden transition-transform duration-300 -translate-x-64 md:translate-x-0">
        <div class="flex items-center justify-start h-16 px-6 border-b border-green-900">
            <i class="fas fa-heartbeat text-2xl mr-2"></i>
            <span class="text-2xl font-bold tracking-tight">MediSync</span>
        </div>
        <nav class="mt-6 px-4">
            <a href="dashboard.php" class="flex items-center py-2 px-4 mb-2 rounded hover:bg-green-700 transition">
                <i class="fas fa-tachometer-alt mr-3"></i> Dashboard
            </a>
            <a href="inventory.php" class="flex items-center py-2 px-4 mb-2 rounded bg-green-700 transition">
                <i class="fas fa-pills mr-3"></i> Medicine Inventory
            </a>
            <a href="prescription_management.php" class="flex items-center py-2 px-4 mb-2 rounded hover:bg-green-700 transition relative">
                <i class="fas fa-prescription mr-3"></i> Prescription Management
                <span class="notification-badge">1</span>
            </a>
            <a href="orders.php" class="flex items-center py-2 px-4 mb-2 rounded hover:bg-green-700 transition">
                <i class="fas fa-truck mr-3"></i> Orders & Supplier
            </a>
            <a href="reports.php" class="flex items-center py-2 px-4 mb-2 rounded hover:bg-green-700 transition">
                <i class="fas fa-chart-bar mr-3"></i> Reports & Analytics
            </a>
            <a href="settings.php" class="flex items-center py-2 px-4 mb-2 rounded hover:bg-green-700 transition">
                <i class="fas fa-cog mr-3"></i> Settings
            </a>
        </nav>
        <div class="absolute bottom-0 left-0 w-full p-4">
            <button onclick="location.href='logout.php'" class="w-full py-2 px-4 bg-red-600 hover:bg-red-700 rounded transition">
                <i class="fas fa-sign-out-alt mr-2"></i> Logout
            </button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="transition-all duration-300 md:ml-64 min-h-screen" id="main-content">
        <!-- Header -->
        <header class="bg-white shadow-sm flex items-center h-16 px-6 sticky top-0 z-30 border-b">
            <button id="burger-menu" class="mr-4 text-gray-600 hover:text-gray-900 focus:outline-none">
                <i class="fas fa-bars text-2xl"></i>
            </button>
            <span class="text-xl font-semibold text-gray-800">Medicine Inventory</span>
        </header>

        <!-- Content -->
        <main class="p-6">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <?php
                $total = count($inventory);
                $shortage = count(array_filter($inventory, fn($m) => $m['status'] === 'shortage'));
                $expiring = count(array_filter($inventory, fn($m) => $m['status'] === 'expiring'));
                ?>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-600">
                            <i class="fas fa-pills text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-gray-600 text-sm">Total Medicines</h2>
                            <p class="text-2xl font-semibold text-gray-800"><?php echo $total; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                            <i class="fas fa-exclamation-triangle text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-gray-600 text-sm">Low Stock Items</h2>
                            <p class="text-2xl font-semibold text-gray-800"><?php echo $shortage; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-red-100 text-red-600">
                            <i class="fas fa-clock text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-gray-600 text-sm">Expiring Soon</h2>
                            <p class="text-2xl font-semibold text-gray-800"><?php echo $expiring; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add or Edit Medicine Form -->
            <div class="bg-white rounded-lg shadow mb-6">
                <div class="p-4 border-b">
                    <h5 class="mb-0"><?php echo $edit_medicine ? "Edit Medicine" : "Add New Medicine"; ?></h5>
                </div>
                <div class="p-4">
                    <form method="POST" class="grid grid-cols-1 md:grid-cols-6 gap-4">
                        <?php if ($edit_medicine): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_medicine['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="md:col-span-2">
                            <input type="text" class="w-full px-3 py-2 border rounded" name="name" 
                                placeholder="Medicine Name" value="<?php echo $edit_medicine['name'] ?? ''; ?>" required>
                        </div>
                        
                        <div>
                            <input type="number" class="w-full px-3 py-2 border rounded" name="quantity" 
                                placeholder="Quantity" value="<?php echo $edit_medicine['quantity'] ?? ''; ?>" required>
                        </div>
                        
                        <div>
                            <input type="number" class="w-full px-3 py-2 border rounded" name="minimum_stock" 
                                placeholder="Minimum Stock" value="<?php echo $edit_medicine['minimum_stock'] ?? ''; ?>" required>
                        </div>
                        
                        <div>
                            <input type="date" class="w-full px-3 py-2 border rounded" name="expiry_date" 
                                value="<?php echo $edit_medicine['expiry_date'] ?? ''; ?>" required>
                        </div>
                        
                        <div>
                            <select class="w-full px-3 py-2 border rounded" name="category" required>
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
                        
                        <div>
                            <button type="submit" name="<?php echo $edit_medicine ? 'edit_medicine' : 'add_medicine'; ?>" 
                                class="w-full px-4 py-2 text-white rounded <?php echo $edit_medicine ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-green-500 hover:bg-green-600'; ?>">
                                <?php echo $edit_medicine ? 'Update' : 'Add'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Inventory Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="p-4 border-b">
                    <h5 class="mb-0">Medicine Inventory</h5>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Medicine Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Min Stock</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expiry Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($inventory as $item): ?>
                            <tr class="<?php echo $item['status'] === 'shortage' ? 'bg-red-50' : ($item['status'] === 'expiring' ? 'bg-yellow-50' : ''); ?>">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($item['name']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($item['category']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo $item['quantity']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo $item['minimum_stock']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo date('M d, Y', strtotime($item['expiry_date'])); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($item['status'] === 'shortage'): ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Low Stock</span>
                                    <?php elseif ($item['status'] === 'expiring'): ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Expiring Soon</span>
                                    <?php else: ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Normal</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="?edit_id=<?php echo $item['id']; ?>" class="text-green-600 hover:text-green-900 mr-3">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="?delete_id=<?php echo $item['id']; ?>" class="text-red-600 hover:text-red-900" 
                                        onclick="return confirm('Are you sure you want to delete this medicine?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($inventory)): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">No medicines found.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    // Burger menu toggle for sidebar with transition and main content margin adjustment
    document.getElementById('burger-menu').addEventListener('click', function() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');
        const isHidden = sidebar.classList.contains('hidden');
        if (isHidden) {
            sidebar.classList.remove('hidden');
            setTimeout(() => {
                sidebar.classList.remove('-translate-x-64');
                sidebar.classList.add('md:translate-x-0');
            }, 10);
            mainContent.classList.add('md:ml-64');
        } else {
            sidebar.classList.add('-translate-x-64');
            sidebar.classList.remove('md:translate-x-0');
            setTimeout(() => {
                sidebar.classList.add('hidden');
            }, 300);
            mainContent.classList.remove('md:ml-64');
        }
    });
    // On window resize, always show sidebar on desktop
    window.addEventListener('resize', function() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');
        if (window.innerWidth >= 768) {
            sidebar.classList.remove('hidden');
            sidebar.classList.remove('-translate-x-64');
            sidebar.classList.add('md:translate-x-0');
            mainContent.classList.add('md:ml-64');
        } else {
            sidebar.classList.add('-translate-x-64');
            sidebar.classList.remove('md:translate-x-0');
            sidebar.classList.add('hidden');
            mainContent.classList.remove('md:ml-64');
        }
    });
    </script>
</body>
</html>