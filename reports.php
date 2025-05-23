<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .sidebar {
            transition: all 0.3s ease;
        }
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
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
    $dbname = "medlog";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }

    // Fetch statistics
    // Total medicines
    $sql = "SELECT COUNT(*) as total FROM medicines";
    $stmt = $conn->query($sql);
    $total_medicines = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Low stock medicines
    $sql = "SELECT COUNT(*) as total FROM medicines WHERE quantity <= minimum_stock";
    $stmt = $conn->query($sql);
    $low_stock = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Expiring medicines (within 30 days)
    $sql = "SELECT COUNT(*) as total FROM medicines WHERE expiry_date <= DATE_ADD(CURRENT_DATE, INTERVAL 30 DAY)";
    $stmt = $conn->query($sql);
    $expiring_soon = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Total orders
    $sql = "SELECT COUNT(*) as total FROM orders";
    $stmt = $conn->query($sql);
    $total_orders = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Orders by status
    $sql = "SELECT status, COUNT(*) as count FROM orders GROUP BY status";
    $stmt = $conn->query($sql);
    $orders_by_status = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Monthly orders
    $sql = "SELECT DATE_FORMAT(order_date, '%Y-%m') as month, COUNT(*) as count 
            FROM orders 
            GROUP BY DATE_FORMAT(order_date, '%Y-%m')
            ORDER BY month DESC
            LIMIT 6";
    $stmt = $conn->query($sql);
    $monthly_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Top suppliers
    $sql = "SELECT s.name, COUNT(o.id) as order_count 
            FROM suppliers s 
            LEFT JOIN orders o ON s.id = o.supplier 
            GROUP BY s.id 
            ORDER BY order_count DESC 
            LIMIT 5";
    $stmt = $conn->query($sql);
    $top_suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Most ordered medicines
    $sql = "SELECT medicine, SUM(quantity) as total_quantity 
            FROM orders 
            GROUP BY medicine 
            ORDER BY total_quantity DESC 
            LIMIT 5";
    $stmt = $conn->query($sql);
    $top_medicines = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sql = "SELECT id, name FROM suppliers ORDER BY name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

        header("Location: orders.php");
        exit();
    }
    ?>

    <!-- Sidebar -->
    <div id="sidebar" class="fixed top-0 left-0 h-screen w-64 bg-green-800 text-white p-4 shadow-lg z-50 hidden transition-transform duration-300 -translate-x-64 md:translate-x-0">
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
            <a href="orders.php" class="flex items-center py-2 px-4 mb-2 rounded hover:bg-green-700 transition">
                <i class="fas fa-truck mr-3"></i> Orders & Supplier
            </a>
            <a href="reports.php" class="flex items-center py-2 px-4 mb-2 rounded bg-green-700 transition">
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
    <div class="transition-all duration-300 md:ml-64 p-8" id="main-content">
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center">
                <button id="burger-menu" class="mr-4 text-gray-600 hover:text-gray-900 focus:outline-none">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
                <h1 class="text-2xl font-bold text-gray-800">Reports & Analytics</h1>
            </div>
            <div class="flex space-x-4">
                <button onclick="window.print()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-print mr-2"></i> Print Report
                </button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500">Total Medicines</p>
                        <h3 class="text-2xl font-bold mt-1"><?php echo $total_medicines; ?></h3>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-pills text-blue-600"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500">Low Stock Items</p>
                        <h3 class="text-2xl font-bold mt-1"><?php echo $low_stock; ?></h3>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-full">
                        <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500">Expiring Soon</p>
                        <h3 class="text-2xl font-bold mt-1"><?php echo $expiring_soon; ?></h3>
                    </div>
                    <div class="bg-red-100 p-3 rounded-full">
                        <i class="fas fa-clock text-red-600"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500">Total Orders</p>
                        <h3 class="text-2xl font-bold mt-1"><?php echo $total_orders; ?></h3>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-shopping-cart text-green-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Orders by Status Chart -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-bold mb-4">Orders by Status</h2>
                <div class="chart-container">
                    <canvas id="ordersStatusChart"></canvas>
                </div>
            </div>

            <!-- Monthly Orders Chart -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-bold mb-4">Monthly Orders</h2>
                <div class="chart-container">
                    <canvas id="monthlyOrdersChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Lists -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Top Suppliers -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6">
                    <h2 class="text-lg font-bold mb-4">Top Suppliers</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Orders</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($top_suppliers as $supplier): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($supplier['name']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo $supplier['order_count']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Most Ordered Medicines -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6">
                    <h2 class="text-lg font-bold mb-4">Most Ordered Medicines</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Medicine</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Quantity</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($top_medicines as $medicine): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($medicine['medicine']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo $medicine['total_quantity']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Orders by Status Chart
        const ordersStatusCtx = document.getElementById('ordersStatusChart').getContext('2d');
        new Chart(ordersStatusCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($orders_by_status, 'status')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($orders_by_status, 'count')); ?>,
                    backgroundColor: [
                        '#fef3c7', // pending
                        '#dbeafe', // processing
                        '#dcfce7', // delivered
                        '#fee2e2'  // cancelled
                    ],
                    borderColor: [
                        '#92400e', // pending
                        '#1e40af', // processing
                        '#166534', // delivered
                        '#991b1b'  // cancelled
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Monthly Orders Chart
        const monthlyOrdersCtx = document.getElementById('monthlyOrdersChart').getContext('2d');
        new Chart(monthlyOrdersCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($monthly_orders, 'month')); ?>,
                datasets: [{
                    label: 'Orders',
                    data: <?php echo json_encode(array_column($monthly_orders, 'count')); ?>,
                    borderColor: '#166534',
                    backgroundColor: '#dcfce7',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

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

        function getSupplierDetails(id) {
            fetch('get_supplier.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_supplier_id').value = data.id;
                    document.getElementById('edit_supplier_name').value = data.name;
                    document.getElementById('edit_contact_person').value = data.contact_person;
                    document.getElementById('edit_email').value = data.email;
                    document.getElementById('edit_phone').value = data.phone;
                    document.getElementById('edit_address').value = data.address;
                });
        }
    </script>
</body>
</html> 