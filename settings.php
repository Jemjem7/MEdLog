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
            case 'update_system':
                $currency = $_POST['currency'];
                $date_format = $_POST['date_format'];
                $timezone = $_POST['timezone'];
                $low_stock_threshold = (int)$_POST['low_stock_threshold'];
                $expiry_alert_days = (int)$_POST['expiry_alert_days'];
                
                $stmt = $conn->prepare("UPDATE system_settings SET 
                    currency = ?, 
                    date_format = ?, 
                    timezone = ?, 
                    low_stock_threshold = ?, 
                    expiry_alert_days = ? 
                    WHERE id = 1");
                $stmt->execute([$currency, $date_format, $timezone, $low_stock_threshold, $expiry_alert_days]);
                break;
        }
    }
}

// Get current settings
$stmt = $conn->query("SELECT * FROM system_settings WHERE id = 1");
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

// If no settings exist, create default settings
if (!$settings) {
    $stmt = $conn->prepare("INSERT INTO system_settings (currency, date_format, timezone, low_stock_threshold, expiry_alert_days) 
        VALUES (?, ?, ?, ?, ?)");
    $stmt->execute(['PHP', 'Y-m-d', 'Asia/Manolo fortich Bukidnon', 10, 30]);
    $settings = [
        'currency' => 'PHP',
        'date_format' => 'Y-m-d',
        'timezone' => 'Asia/Manila',
        'low_stock_threshold' => 10,
        'expiry_alert_days' => 30
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings - MediSync</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <!-- Sidebar -->
    <div id="sidebar" class="fixed top-0 left-0 h-full w-64 bg-green-800 text-white transition-transform duration-300 transform -translate-x-full md:translate-x-0 z-40 hidden -translate-x-64">
        <div class="p-4">
            <div class="flex items-center justify-center mb-8">
                <i class="fas fa-heartbeat text-3xl mr-2"></i>
                <h1 class="text-xl font-bold">MediSync</h1>
            </div>
            <nav>
                <a href="dashboard.php" class="block py-2 px-4 rounded hover:bg-green-700 mb-2">
                    <i class="fas fa-chart-line mr-2"></i> Dashboard
                </a>
                <a href="inventory.php" class="block py-2 px-4 rounded hover:bg-green-700 mb-2">
                    <i class="fas fa-pills mr-2"></i> Inventory
                </a>
                <a href="orders.php" class="block py-2 px-4 rounded hover:bg-green-700 mb-2">
                    <i class="fas fa-shopping-cart mr-2"></i> Orders
                </a>
                <a href="settings.php" class="block py-2 px-4 rounded bg-green-700 mb-2">
                    <i class="fas fa-cog mr-2"></i> Settings
                </a>
            </nav>
        </div>
    </div>

    <!-- Main Content -->
    <div class="transition-all duration-300 md:ml-64 min-h-screen" id="main-content">
        <!-- Header -->
        <header class="bg-white shadow-sm p-4 flex justify-between items-center sticky top-0 z-30">
            <div class="flex items-center">
                <button id="burger-menu" class="mr-4 text-gray-600 hover:text-gray-900 focus:outline-none">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <h1 class="text-xl font-bold text-gray-800">System Settings</h1>
            </div>
        </header>

        <!-- Content -->
        <main class="p-6">
            <div class="max-w-4xl mx-auto">
                <!-- System Settings Form -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">General Settings</h2>
                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="action" value="update_system">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                                <select name="currency" class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                    <option value="PHP" <?php echo $settings['currency'] == 'PHP' ? 'selected' : ''; ?>>PHP (₱)</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Date Format</label>
                                <select name="date_format" class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                    <option value="Y-m-d" <?php echo $settings['date_format'] == 'Y-m-d' ? 'selected' : ''; ?>>YYYY-MM-DD</option>
                                    <option value="d-m-Y" <?php echo $settings['date_format'] == 'd-m-Y' ? 'selected' : ''; ?>>DD-MM-YYYY</option>
                                    <option value="m-d-Y" <?php echo $settings['date_format'] == 'm-d-Y' ? 'selected' : ''; ?>>MM-DD-YYYY</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Timezone</label>
                                <select name="timezone" class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                    <option value="Asia/Manolo fortichBukidnon" <?php echo $settings['timezone'] == 'Asia/Manolo fortich Bukidnon' ? 'selected' : ''; ?>>Asia/Manolo Fortich Bukidnon</option>
                                   
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Low Stock Threshold</label>
                                <input type="number" name="low_stock_threshold" value="<?php echo $settings['low_stock_threshold']; ?>" 
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" 
                                    min="1" required>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Expiry Alert Days</label>
                                <input type="number" name="expiry_alert_days" value="<?php echo $settings['expiry_alert_days']; ?>" 
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" 
                                    min="1" required>
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Database Information -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Database Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Database Name</p>
                            <p class="font-medium"><?php echo $dbname; ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Server</p>
                            <p class="font-medium"><?php echo $servername; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

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