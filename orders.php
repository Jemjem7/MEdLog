<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders & Supplier Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        .order-status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        .order-status-processing {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .order-status-delivered {
            background-color: #dcfce7;
            color: #166534;
        }
        .order-status-cancelled {
            background-color: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>
<body class="bg-gray-100">
    <?php
    session_start();
    
    // Database connection
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "medlog_db";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }

    // Handle form submissions
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['add_order'])) {
            $supplier = $_POST['supplier'];
            $medicine = $_POST['medicine'];
            $quantity = $_POST['quantity'];
            $order_date = $_POST['order_date'];
            $expected_delivery = $_POST['expected_delivery'];
            $status = 'pending';

            $sql = "INSERT INTO orders (supplier, medicine, quantity, order_date, expected_delivery, status) 
                    VALUES (:supplier, :medicine, :quantity, :order_date, :expected_delivery, :status)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':supplier' => $supplier,
                ':medicine' => $medicine,
                ':quantity' => $quantity,
                ':order_date' => $order_date,
                ':expected_delivery' => $expected_delivery,
                ':status' => $status
            ]);
        }
        if (isset($_POST['add_supplier'])) {
            $name = $_POST['supplier_name'];
            $contact_person = $_POST['contact_person'];
            $email = $_POST['email'];
            $phone = $_POST['phone'];
            $address = $_POST['address'];

            $sql = "INSERT INTO suppliers (name, contact_person, email, phone, address) 
                    VALUES (:name, :contact_person, :email, :phone, :address)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':name' => $name,
                ':contact_person' => $contact_person,
                ':email' => $email,
                ':phone' => $phone,
                ':address' => $address
            ]);
        }
        if (isset($_POST['edit_supplier'])) {
            $id = $_POST['supplier_id'];
            $name = $_POST['supplier_name'];
            $contact_person = $_POST['contact_person'];
            $email = $_POST['email'];
            $phone = $_POST['phone'];
            $address = $_POST['address'];

            $sql = "UPDATE suppliers SET name = :name, contact_person = :contact_person, 
                    email = :email, phone = :phone, address = :address 
                    WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':id' => $id,
                ':name' => $name,
                ':contact_person' => $contact_person,
                ':email' => $email,
                ':phone' => $phone,
                ':address' => $address
            ]);
        }
        if (isset($_POST['delete_supplier'])) {
            $id = $_POST['supplier_id'];
            $sql = "DELETE FROM suppliers WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $id]);
        }
        if (isset($_POST['update_order_status'])) {
            $id = $_POST['order_id'];
            $status = $_POST['status'];
            $sql = "UPDATE orders SET status = :status WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $id, ':status' => $status]);
        }
        if (isset($_POST['delete_order'])) {
            $id = $_POST['order_id'];
            $sql = "DELETE FROM orders WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $id]);
        }
    }

    // Fetch orders
    $sql = "SELECT * FROM orders ORDER BY order_date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch suppliers for dropdown
    $sql = "SELECT id, name, contact_person, email, phone, address FROM suppliers ORDER BY name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <!-- Sidebar -->
    <div class="sidebar fixed top-0 left-0 h-screen w-64 bg-green-800 text-white p-4 shadow-lg z-50">
        <div class="flex items-center mb-8">
            <i class="fas fa-heartbeat text-2xl mr-2"></i>
            <h3 class="text-xl font-bold">MediSync</h3>
        </div>
        <nav>
            <a href="dashboard.php" class="flex items-center py-2 px-4 mb-2 rounded hover:bg-green-700 transition">
                <i class="fas fa-tachometer-alt mr-3"></i> Dashboard
            </a>
            <a href="medicine_inventory.php" class="flex items-center py-2 px-4 mb-2 rounded hover:bg-green-700 transition">
                <i class="fas fa-pills mr-3"></i> Medicine Inventory
            </a>
            <a href="prescription_management.php" class="flex items-center py-2 px-4 mb-2 rounded hover:bg-green-700 transition">
                <i class="fas fa-prescription mr-3"></i> Prescription Management
            </a>
            <a href="orders.php" class="flex items-center py-2 px-4 mb-2 rounded bg-green-700 transition">
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
    <div class="ml-64 p-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Orders & Supplier Management</h1>
            <div class="flex space-x-4">
                <button onclick="showAddSupplierModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-plus mr-2"></i> New Supplier
                </button>
                <button onclick="showAddOrderModal()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-plus mr-2"></i> New Order
                </button>
            </div>
        </div>

        <!-- Supplier Management Section -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="p-6">
                <h2 class="text-lg font-bold mb-4">Supplier Management</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact Person</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Address</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($suppliers as $supplier): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($supplier['name']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($supplier['contact_person']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($supplier['email']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($supplier['phone']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($supplier['address']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button onclick="editSupplier(<?php echo $supplier['id']; ?>)" class="text-blue-600 hover:text-blue-800 mr-3">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deleteSupplier(<?php echo $supplier['id']; ?>)" class="text-red-600 hover:text-red-800">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500">Total Orders</p>
                        <h3 class="text-2xl font-bold mt-1"><?php echo count($orders); ?></h3>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-shopping-cart text-blue-600"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500">Pending Orders</p>
                        <h3 class="text-2xl font-bold mt-1">
                            <?php echo count(array_filter($orders, function($order) { return $order['status'] == 'pending'; })); ?>
                        </h3>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-full">
                        <i class="fas fa-clock text-yellow-600"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500">Processing Orders</p>
                        <h3 class="text-2xl font-bold mt-1">
                            <?php echo count(array_filter($orders, function($order) { return $order['status'] == 'processing'; })); ?>
                        </h3>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-full">
                        <i class="fas fa-cog text-purple-600"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500">Delivered Orders</p>
                        <h3 class="text-2xl font-bold mt-1">
                            <?php echo count(array_filter($orders, function($order) { return $order['status'] == 'delivered'; })); ?>
                        </h3>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6">
                <h2 class="text-lg font-bold mb-4">Recent Orders</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Medicine</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expected Delivery</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($orders as $order): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">#<?php echo $order['id']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($order['supplier']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($order['medicine']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($order['quantity']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($order['order_date']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($order['expected_delivery']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full <?php 
                                        echo match($order['status']) {
                                            'pending' => 'order-status-pending',
                                            'processing' => 'order-status-processing',
                                            'delivered' => 'order-status-delivered',
                                            'cancelled' => 'order-status-cancelled',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                    ?>">
                                        <?php echo ucfirst(htmlspecialchars($order['status'])); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button onclick="updateOrderStatus(<?php echo $order['id']; ?>)" class="text-blue-600 hover:text-blue-800 mr-3">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deleteOrder(<?php echo $order['id']; ?>)" class="text-red-600 hover:text-red-800">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Order Modal -->
    <div id="addOrderModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold">Add New Order</h3>
                    <button onclick="hideAddOrderModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Supplier</label>
                        <select name="supplier" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" required>
                            <option value="">Select a Supplier</option>
                            <?php if (!empty($suppliers)): ?>
                                <?php foreach ($suppliers as $supplier): ?>
                                    <option value="<?php echo htmlspecialchars($supplier['id']); ?>">
                                        <?php echo htmlspecialchars($supplier['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled>No suppliers available</option>
                            <?php endif; ?>
                        </select>
                        <?php if (empty($suppliers)): ?>
                            <p class="mt-1 text-sm text-red-600">Please add a supplier first</p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Medicine</label>
                        <input type="text" name="medicine" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Quantity</label>
                        <input type="number" name="quantity" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Order Date</label>
                        <input type="date" name="order_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Expected Delivery</label>
                        <input type="date" name="expected_delivery" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" required>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="hideAddOrderModal()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" name="add_order" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                            Add Order
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Supplier Modal -->
    <div id="addSupplierModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold">Add New Supplier</h3>
                    <button onclick="hideAddSupplierModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Supplier Name</label>
                        <input type="text" name="supplier_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Contact Person</label>
                        <input type="text" name="contact_person" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Phone</label>
                        <input type="tel" name="phone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Address</label>
                        <textarea name="address" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" required></textarea>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="hideAddSupplierModal()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" name="add_supplier" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Add Supplier
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Supplier Modal -->
    <div id="editSupplierModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold">Edit Supplier</h3>
                    <button onclick="hideEditSupplierModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="supplier_id" id="edit_supplier_id">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Supplier Name</label>
                        <input type="text" name="supplier_name" id="edit_supplier_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Contact Person</label>
                        <input type="text" name="contact_person" id="edit_contact_person" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" id="edit_email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Phone</label>
                        <input type="tel" name="phone" id="edit_phone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Address</label>
                        <textarea name="address" id="edit_address" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" required></textarea>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="hideEditSupplierModal()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" name="edit_supplier" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Update Supplier
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Update Order Status Modal -->
    <div id="updateOrderStatusModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold">Update Order Status</h3>
                    <button onclick="hideUpdateOrderStatusModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="order_id" id="update_order_id">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" id="update_status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" required>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="hideUpdateOrderStatusModal()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" name="update_order_status" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                            Update Status
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showAddOrderModal() {
            document.getElementById('addOrderModal').classList.remove('hidden');
            document.getElementById('addOrderModal').classList.add('flex');
        }

        function hideAddOrderModal() {
            document.getElementById('addOrderModal').classList.add('hidden');
            document.getElementById('addOrderModal').classList.remove('flex');
        }

        function showAddSupplierModal() {
            document.getElementById('addSupplierModal').classList.remove('hidden');
            document.getElementById('addSupplierModal').classList.add('flex');
        }

        function hideAddSupplierModal() {
            document.getElementById('addSupplierModal').classList.add('hidden');
            document.getElementById('addSupplierModal').classList.remove('flex');
        }

        function editSupplier(id) {
            // Fetch supplier data using AJAX
            fetch('get_supplier.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_supplier_id').value = data.id;
                    document.getElementById('edit_supplier_name').value = data.name;
                    document.getElementById('edit_contact_person').value = data.contact_person;
                    document.getElementById('edit_email').value = data.email;
                    document.getElementById('edit_phone').value = data.phone;
                    document.getElementById('edit_address').value = data.address;
                    
                    document.getElementById('editSupplierModal').classList.remove('hidden');
                    document.getElementById('editSupplierModal').classList.add('flex');
                });
        }

        function hideEditSupplierModal() {
            document.getElementById('editSupplierModal').classList.add('hidden');
            document.getElementById('editSupplierModal').classList.remove('flex');
        }

        function deleteSupplier(id) {
            if (confirm('Are you sure you want to delete this supplier?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="supplier_id" value="${id}">
                    <input type="hidden" name="delete_supplier" value="1">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function updateOrderStatus(orderId) {
            document.getElementById('update_order_id').value = orderId;
            document.getElementById('updateOrderStatusModal').classList.remove('hidden');
            document.getElementById('updateOrderStatusModal').classList.add('flex');
        }

        function hideUpdateOrderStatusModal() {
            document.getElementById('updateOrderStatusModal').classList.add('hidden');
            document.getElementById('updateOrderStatusModal').classList.remove('flex');
        }

        function deleteOrder(orderId) {
            if (confirm('Are you sure you want to delete this order?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="order_id" value="${orderId}">
                    <input type="hidden" name="delete_order" value="1">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html> 