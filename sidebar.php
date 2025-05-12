 <?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
 
 
 <!-- Sidebar -->
 <div class="sidebar fixed top-0 left-0 h-screen w-64 bg-green-800 text-white p-4 shadow-lg z-50" id="sidebar">
    <div class="flex items-center mb-8">
      <i class="fas fa-heartbeat text-2xl mr-2"></i>
      <h3 class="text-xl font-bold">MediSync</h3>
    </div>
    <nav>
      <a href="dashboard.php" class="flex items-center py-2 px-4 mb-2 rounded hover:bg-green-700 transition">
        <i class="fas fa-tachometer-alt mr-3"></i> Dashboard
      </a>
      <a href="inventory.php" class="flex items-center py-2 px-4 mb-2 rounded hover:bg-green-700 transition">
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