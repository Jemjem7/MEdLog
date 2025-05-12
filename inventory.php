<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "medlog_db";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = $_POST['name'];
                $description = $_POST['description'];
                $quantity = (int)$_POST['quantity'];
                $price = (float)$_POST['price'];
                $expiry_date = $_POST['expiry_date'];
                
                $stmt = $conn->prepare("INSERT INTO medicines (name, description, quantity, price, expiry_date) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$name, $description, $quantity, $price, $expiry_date]);
                break;
                
            case 'edit':
                $id = (int)$_POST['id'];
                $name = $_POST['name'];
                $description = $_POST['description'];
                $quantity = (int)$_POST['quantity'];
                $price = (float)$_POST['price'];
                $expiry_date = $_POST['expiry_date'];
                
                $stmt = $conn->prepare("UPDATE medicines SET name = ?, description = ?, quantity = ?, price = ?, expiry_date = ? WHERE id = ?");
                $stmt->execute([$name, $description, $quantity, $price, $expiry_date, $id]);
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                $stmt = $conn->prepare("DELETE FROM medicines WHERE id = ?");
                $stmt->execute([$id]);
                break;
        }
    }
}

// Get all medicines
$stmt = $conn->query("SELECT * FROM medicines ORDER BY name");
$medicines = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medicine Inventory - MediSync</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <!-- Sidebar -->
    <div id="sidebar" class="fixed top-0 left-0 h-full w-64 bg-green-800 text-white transition-transform duration-300 transform -translate-x-full md:translate-x-0 z-40">
        <div class="p-4">
            <div class="flex items-center justify-center mb-8">
                <i class="fas fa-heartbeat text-3xl mr-2"></i>
                <h1 class="text-xl font-bold">MediSync</h1>
            </div>
            <nav>
                <a href="dashboard.php" class="block py-2 px-4 rounded hover:bg-green-700 mb-2">
                    <i class="fas fa-chart-line mr-2"></i> Dashboard
                </a>
                <a href="inventory.php" class="block py-2 px-4 rounded bg-green-700 mb-2">
                    <i class="fas fa-pills mr-2"></i> Inventory
                </a>
                <a href="orders.php" class="block py-2 px-4 rounded hover:bg-green-700 mb-2">
                    <i class="fas fa-shopping-cart mr-2"></i> Orders
                </a>
                <a href="settings.php" class="block py-2 px-4 rounded hover:bg-green-700 mb-2">
                    <i class="fas fa-cog mr-2"></i> Settings
                </a>
            </nav>
        </div>
    </div>

    <!-- Main Content -->
    <div class="md:ml-64 min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm p-4 flex justify-between items-center sticky top-0 z-30">
            <div class="flex items-center">
                <button onclick="toggleSidebar()" class="mr-4 text-gray-600 hover:text-gray-900 md:hidden">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <h1 class="text-xl font-bold text-gray-800">Medicine Inventory</h1>
            </div>
        </header>

        <!-- Content -->
        <main class="p-6">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-600">
                            <i class="fas fa-pills text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-gray-600 text-sm">Total Medicines</h2>
                            <p class="text-2xl font-semibold text-gray-800"><?php echo count($medicines); ?></p>
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
                            <p class="text-2xl font-semibold text-gray-800">
                                <?php echo count(array_filter($medicines, function($m) { return $m['quantity'] < 10; })); ?>
                            </p>
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
                            <p class="text-2xl font-semibold text-gray-800">
                                <?php 
                                $expiring_soon = array_filter($medicines, function($m) {
                                    return strtotime($m['expiry_date']) - time() < 30 * 24 * 60 * 60; // 30 days
                                });
                                echo count($expiring_soon);
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Medicine Button -->
            <div class="mb-6">
                <button onclick="showAddModal()" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                    <i class="fas fa-plus mr-2"></i> Add New Medicine
                </button>
            </div>

            <!-- Medicine List -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expiry Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($medicines as $medicine): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($medicine['name']); ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($medicine['description']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo $medicine['quantity']; ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">$<?php echo number_format($medicine['price'], 2); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo date('M d, Y', strtotime($medicine['expiry_date'])); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $status_class = 'bg-green-100 text-green-800';
                                $status_text = 'Normal';
                                
                                if ($medicine['quantity'] < 10) {
                                    $status_class = 'bg-yellow-100 text-yellow-800';
                                    $status_text = 'Low Stock';
                                }
                                
                                if (strtotime($medicine['expiry_date']) - time() < 30 * 24 * 60 * 60) {
                                    $status_class = 'bg-red-100 text-red-800';
                                    $status_text = 'Expiring Soon';
                                }
                                ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $status_class; ?>">
                                    <?php echo $status_text; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="showEditModal(<?php echo htmlspecialchars(json_encode($medicine)); ?>)" class="text-green-600 hover:text-green-900 mr-3">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="confirmDelete(<?php echo $medicine['id']; ?>)" class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Add/Edit Modal -->
    <div id="medicineModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Add New Medicine</h3>
                <form id="medicineForm" method="POST" class="mt-4">
                    <input type="hidden" name="action" id="formAction" value="add">
                    <input type="hidden" name="id" id="medicineId">
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="name">Name</label>
                        <input type="text" name="name" id="name" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="description">Description</label>
                        <textarea name="description" id="description" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="quantity">Quantity</label>
                        <input type="number" name="quantity" id="quantity" required min="0" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="price">Price</label>
                        <input type="number" name="price" id="price" required min="0" step="0.01" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="expiry_date">Expiry Date</label>
                        <input type="date" name="expiry_date" id="expiry_date" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <button type="submit" class="bg-green-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Save
                        </button>
                        <button type="button" onclick="hideModal()" class="bg-gray-500 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900">Confirm Delete</h3>
                <p class="mt-2 text-gray-600">Are you sure you want to delete this medicine? This action cannot be undone.</p>
                <form method="POST" class="mt-4">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="deleteId">
                    <div class="flex items-center justify-between">
                        <button type="submit" class="bg-red-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Delete
                        </button>
                        <button type="button" onclick="hideDeleteModal()" class="bg-gray-500 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Sidebar toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('-translate-x-full');
        }

        // Modal functions
        function showAddModal() {
            document.getElementById('modalTitle').textContent = 'Add New Medicine';
            document.getElementById('formAction').value = 'add';
            document.getElementById('medicineForm').reset();
            document.getElementById('medicineModal').classList.remove('hidden');
        }

        function showEditModal(medicine) {
            document.getElementById('modalTitle').textContent = 'Edit Medicine';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('medicineId').value = medicine.id;
            document.getElementById('name').value = medicine.name;
            document.getElementById('description').value = medicine.description;
            document.getElementById('quantity').value = medicine.quantity;
            document.getElementById('price').value = medicine.price;
            document.getElementById('expiry_date').value = medicine.expiry_date;
            document.getElementById('medicineModal').classList.remove('hidden');
        }

        function hideModal() {
            document.getElementById('medicineModal').classList.add('hidden');
        }

        function confirmDelete(id) {
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function hideDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const medicineModal = document.getElementById('medicineModal');
            const deleteModal = document.getElementById('deleteModal');
            if (event.target === medicineModal) {
                hideModal();
            }
            if (event.target === deleteModal) {
                hideDeleteModal();
            }
        }
    </script>
</body>
</html>
