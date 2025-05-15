<?php include 'sidebar.php'; ?>



<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

// Sample user and revenue
$user = htmlspecialchars($_SESSION['user']);
$revenue = 15420.75;

// Set up the database connection
$servername = "localhost";
$username = "root";
$password = ""; // Or your actual password
$database = "medlog"; // Your actual DB name

// Create connection
$conn = new mysqli($servername, $username, $password, $database);




$result = $conn->query("SELECT * FROM prescriptions WHERE is_read = 0 ORDER BY created_at DESC LIMIT 1");
$newPrescription = $result ? $result->fetch_assoc() : null;











// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if prescriptions are already in the session
if (!isset($_SESSION['prescriptions'])) {
    // Fetch prescriptions from a database (or real-time source)
    $query = "SELECT patient_name, medicine, med_dosage FROM prescriptions";
    $result = mysqli_query($conn, $query);
    if (!$result) {
        die("Query failed: " . mysqli_error($conn));
    }
    

    $prescriptions = [];
    while ($row = mysqli_fetch_assoc($result)) {
       $prescriptions[] = [$row['patient_name'], $row['medicine'], $row['med_dosage']];

    }

    // If no prescriptions are found, set an empty array
    if (empty($prescriptions)) {
        $prescriptions = [];
    }

    // Store the real-time prescriptions in the session
    $_SESSION['prescriptions'] = $prescriptions;
}

// Handle add‐prescription form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['patient'], $_POST['medicine'], $_POST['dosage'])) {
    $patient  = htmlspecialchars($_POST['patient']);
    $medicine = htmlspecialchars($_POST['medicine']);
    $dosage   = htmlspecialchars($_POST['dosage']);
    array_unshift($_SESSION['prescriptions'], [$patient, $medicine, $dosage]);
}

// query to get today's appointments
$today = date('Y-m-d');
$query = "SELECT * FROM appointments WHERE appointment_date = '$today'";
$result = mysqli_query($conn, $query);
$appointmentsToday = mysqli_fetch_all($result, MYSQLI_ASSOC);


// Fetch data for the weekly appointments
$query = "SELECT DAYOFWEEK(appointment_date) AS day_of_week, COUNT(*) AS count FROM appointments WHERE WEEK(appointment_date) = WEEK(CURRENT_DATE) GROUP BY DAYOFWEEK(appointment_date)";
$result = mysqli_query($conn, $query);
$appointmentsData = [];
while ($row = mysqli_fetch_assoc($result)) {
    $appointmentsData[$row['day_of_week']] = $row['count'];
}

// Set up the chart data labels dynamically
$daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
$appointmentsCount = array_map(fn($day) => $appointmentsData[$day] ?? 0, range(1, 7)); // Use 0 for missing data




$prescriptions = $_SESSION['prescriptions'];
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
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

  <!-- Notification Panel -->
  <div class="notification-panel fixed top-0 right-0 h-screen w-80 bg-white shadow-lg z-40 p-4 overflow-y-auto">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-lg font-bold">Notifications</h3>
      <button onclick="toggleNotificationPanel()" class="text-gray-500 hover:text-gray-700">
        <i class="fas fa-times"></i>
      </button>
    </div>
    
    <!-- New Prescription Notification -->
    <div class="mb-4 p-3 border border-blue-200 bg-blue-50 rounded-lg prescription-notification">
      <div class="flex items-start">
        <div class="bg-blue-100 p-2 rounded-full mr-3">
          <i class="fas fa-prescription text-blue-600"></i>
        </div>
        <div>
          <h4 class="font-bold text-blue-800">New Prescription Received</h4>
          <p class="text-sm text-gray-600">Dr. Smith has sent a prescription for <span class="font-semibold">John Doe</span></p>
          <div class="mt-2 text-sm">
            <span class="text-gray-500">2 mins ago</span>
            <button class="ml-2 text-blue-600 hover:text-blue-800" onclick="processPrescription()">
              Process Now <i class="fas fa-arrow-right ml-1"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Other Notifications -->
    <div class="mb-4 p-3 border border-gray-200 rounded-lg">
      <div class="flex items-start">
        <div class="bg-green-100 p-2 rounded-full mr-3">
          <i class="fas fa-calendar-check text-green-600"></i>
        </div>
        <div>
          <h4 class="font-bold">New Appointment</h4>
          <p class="text-sm text-gray-600">Sarah Johnson scheduled for tomorrow at 10:00 AM</p>
          <div class="mt-2 text-sm text-gray-500">1 hour ago</div>
        </div>
      </div>
    </div>
    
    <div class="mb-4 p-3 border border-gray-200 rounded-lg">
      <div class="flex items-start">
        <div class="bg-yellow-100 p-2 rounded-full mr-3">
          <i class="fas fa-exclamation-triangle text-yellow-600"></i>
        </div>
        <div>
          <h4 class="font-bold">Low Stock Alert</h4>
          <p class="text-sm text-gray-600">Paracetamol is running low (only 5 left)</p>
          <div class="mt-2 text-sm text-gray-500">3 hours ago</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Main Content -->
  <div class="ml-64 transition-all duration-300" id="main-content">
    <!-- Header -->
    <header class="bg-white shadow-sm p-4 flex justify-between items-center sticky top-0 z-30">
      <div class="flex items-center">
        <button onclick="toggleSidebar()" class="mr-4 text-gray-600 hover:text-gray-900">
          <i class="fas fa-bars text-xl"></i>
        </button>
        <h1 class="text-xl font-bold text-gray-800">Dashboard</h1> 
      </div>
      <div class="flex items-center space-x-4">
        <div class="relative">
          <input type="text" placeholder="Search..." class="pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
          <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
        </div>
        <button onclick="toggleNotificationPanel()" class="relative p-2 text-gray-600 hover:text-gray-900">
          <i class="fas fa-bell text-xl"></i>
          <span class="notification-badge">3</span>
        </button>
        <div class="flex items-center">
                    <img src="image.png" alt="User" class="w-8 h-8 rounded-full mr-2">

          <span class="font-medium">Admin</span>
        </div>
      </div>
    </header>

    <!-- Dashboard Content -->
    <main class="p-6">
      <!-- KPI Cards -->
      <?php
// ...existing code...
// Query to get the total number of unique patients from prescriptions
$totalPatientsResult = $conn->query("SELECT COUNT(DISTINCT patient_name) AS total FROM prescriptions");
$totalPatients = 0;
if ($totalPatientsResult && $row = $totalPatientsResult->fetch_assoc()) {
    $totalPatients = $row['total'];
}
?>
<!-- KPI Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
  <!-- Total Patients -->
  <div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-start">
      <div>
        <p class="text-gray-500">Total Patients</p>
        <h3 class="text-2xl font-bold mt-1"><?php echo number_format($totalPatients); ?></h3>
        <p class="text-green-500 text-sm mt-2 flex items-center">
          <i class="fas fa-arrow-up mr-1"></i> Real-time count
        </p>
      </div>
      <div class="bg-green-100 p-3 rounded-full">
        <i class="fas fa-user-friends text-green-600"></i>
      </div>
    </div>
  </div>

        <!-- Today's Appointments -->
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-gray-500">Today's Appointments</p>
              <h3 class="text-2xl font-bold mt-1">24</h3>
              <p class="text-red-500 text-sm mt-2 flex items-center">
                <i class="fas fa-arrow-down mr-1"></i> 2% from yesterday
              </p>
            </div>
            <div class="bg-blue-100 p-3 rounded-full">
              <i class="fas fa-calendar-day text-blue-600"></i>
            </div>
          </div>
        </div>

        <!-- Pending Prescriptions -->
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-gray-500">Pending Prescriptions</p>
              <h3 class="text-2xl font-bold mt-1">5</h3>
              <p class="text-yellow-500 text-sm mt-2 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i> Needs attention
              </p>
            </div>
            <div class="bg-yellow-100 p-3 rounded-full">
              <i class="fas fa-prescription-bottle-alt text-yellow-600"></i>
            </div>
          </div>
        </div>

        <!-- Revenue -->
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-gray-500">Revenue</p>
              <h3 class="text-2xl font-bold mt-1">₱15,420.75</h3>
              <p class="text-gray-500 text-sm mt-2 flex items-center">
                <i class="fas fa-sync-alt mr-1"></i> Updated real-time
              </p>
            </div>
            <div class="bg-purple-100 p-3 rounded-full">
              <i class="fas fa-money-bill-wave text-purple-600"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Second Row of KPIs -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- New Patients -->
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-gray-500">New Patients This Week</p>
              <h3 class="text-2xl font-bold mt-1">18</h3>
              <p class="text-green-500 text-sm mt-2 flex items-center">
                <i class="fas fa-user-plus mr-1"></i> Growing steadily
              </p>
            </div>
            <div class="bg-teal-100 p-3 rounded-full">
              <i class="fas fa-user-plus text-teal-600"></i>
            </div>
          </div>
        </div>

        <!-- Medicine Inventory -->
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-gray-500">Total Medicine Inventory</p>
              <h3 class="text-2xl font-bold mt-1">142</h3>
              <p class="text-gray-500 text-sm mt-2 flex items-center">
                <i class="fas fa-pills mr-1"></i> Inventory monitored
              </p>
            </div>
            <div class="bg-indigo-100 p-3 rounded-full">
              <i class="fas fa-pills text-indigo-600"></i>
            </div>
          </div>
        </div>

        <!-- Out of Stock -->
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-gray-500">Out of Stock</p>
              <h3 class="text-2xl font-bold mt-1">7</h3>
              <p class="text-red-500 text-sm mt-2 flex items-center">
                <i class="fas fa-exclamation-triangle mr-1"></i> Reorder needed
              </p>
            </div>
            <div class="bg-red-100 p-3 rounded-full">
              <i class="fas fa-box-open text-red-600"></i>
            </div>
          </div>
        </div>

        <!-- Supplier Orders -->
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-gray-500">Supplier Orders This Month</p>
              <h3 class="text-2xl font-bold mt-1">12</h3>
              <p class="text-green-500 text-sm mt-2 flex items-center">
                <i class="fas fa-check-circle mr-1"></i> All received
              </p>
            </div>
            <div class="bg-orange-100 p-3 rounded-full">
              <i class="fas fa-truck-loading text-orange-600"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts and Tables -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Weekly Appointments Chart -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-bold mb-4">Weekly Appointments</h3>
          <canvas id="weeklyAppointmentsChart" height="250"></canvas>
        </div>

        <tbody id="appointmentsBody" class="bg-white divide-y divide-gray-200">
  <!-- New rows will appear here when a prescription is added -->
</tbody>


      <!-- Recent Prescriptions -->
<div class="bg-white rounded-lg shadow p-6 mb-6">
  <div class="flex justify-between items-center mb-4">
    <h3 class="text-lg font-bold">Recent Prescriptions</h3>
    <button onclick="showAddPrescriptionModal()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
      <i class="fas fa-plus mr-2"></i> Add Prescription
    </button>
  </div>

  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200" id="prescriptionTable">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient</th>
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Medicine</th>
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dosage</th>
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
        </tr>
      </thead>
      <tbody id="prescriptionTableBody" class="bg-white divide-y divide-gray-200">
        <!-- Dynamic rows will be added here -->
      </tbody>
    </table>
  </div>
</div>


  <!-- Add Prescription Modal -->
  <div id="addPrescriptionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
      <div class="p-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-bold">Add New Prescription</h3>
          <button onclick="hideAddPrescriptionModal()" class="text-gray-500 hover:text-gray-700">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <form id="prescriptionForm">
          <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="patient">
              Patient Name
            </label>
            <input type="text" id="patient" name="patient" required
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
          </div>
          <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="medicine">
              Medicine
            </label>
            <input type="text" id="medicine" name="medicine" required
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
          </div>
          <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="dosage">
              Dosage
            </label>
            <input type="text" id="dosage" name="dosage" required
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
          </div>
          <div class="flex justify-end">
            <button type="button" onclick="hideAddPrescriptionModal()" class="mr-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
              Cancel
            </button>
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
              Save Prescription
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    // Toggle sidebar
    function toggleSidebar() {
      const sidebar = document.getElementById('sidebar');
      const mainContent = document.getElementById('main-content');
      sidebar.classList.toggle('hidden');
      mainContent.classList.toggle('ml-64');
      mainContent.classList.toggle('ml-0');
    }

    // Toggle notification panel
    function toggleNotificationPanel() {
      const panel = document.querySelector('.notification-panel');
      panel.classList.toggle('open');
    }

    // Show add prescription modal
    function showAddPrescriptionModal() {
      document.getElementById('addPrescriptionModal').classList.remove('hidden');
    }

    // Hide add prescription modal
    function hideAddPrescriptionModal() {
      document.getElementById('addPrescriptionModal').classList.add('hidden');
    }

    // Process prescription (notification action)
    function processPrescription() {
      alert('Redirecting to prescription processing...');
      // In a real app, you would redirect to the processing page
      // window.location.href = 'prescription_management.php';
    }

    // Initialize chart
    document.addEventListener('DOMContentLoaded', function() {
      const ctx = document.getElementById('weeklyAppointmentsChart').getContext('2d');
      new Chart(ctx, {
        type: 'bar',
        data: {
          labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
          datasets: [{
            label: 'Appointments',
            data: [20, 18, 22, 25, 24, 15, 10],
            backgroundColor: '#4b7c67',
            borderColor: '#3a6150',
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: {
              display: false
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                stepSize: 5
              }
            }
          }
        }
      });

      // Form submission
      document.getElementById('prescriptionForm').addEventListener('submit', function(e) {
        e.preventDefault();
        // Here you would typically send the data to the server via AJAX
        alert('Prescription added successfully!');
        hideAddPrescriptionModal();
        // Reset form
        this.reset();
      });
    });

    // Show notification panel on page load if there are unread notifications
    window.onload = function() {
      // In a real app, you would check if there are unread notifications
      // For demo purposes, we're showing it automatically
      setTimeout(() => {
        document.querySelector('.notification-panel').classList.add('open');
      }, 1000);
    };







///////////////////////////////////////////


  function showAddPrescriptionModal() {
    document.getElementById('addPrescriptionModal').classList.remove('hidden');
  }

  function hideAddPrescriptionModal() {
    document.getElementById('addPrescriptionModal').classList.add('hidden');
  }

  document.getElementById('prescriptionForm').addEventListener('submit', function(event) {
    event.preventDefault();

    const patient = document.getElementById('patient').value;
    const medicine = document.getElementById('medicine').value;
    const dosage = document.getElementById('dosage').value;
    const date = new Date().toISOString().split('T')[0];

    const tableBody = document.getElementById('prescriptionTableBody');
    const row = document.createElement('tr');

   tr.innerHTML = `
  <td class="px-4 py-4 whitespace-nowrap">${prescription.patient}</td>
  <td class="px-4 py-4 whitespace-nowrap">${prescription.medicine}</td>
  <td class="px-4 py-4 whitespace-nowrap">${prescription.dosage}</td>
  <td class="px-4 py-4 whitespace-nowrap">${prescription.date}</td>
  <td class="px-4 py-4 whitespace-nowrap">
    <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">${prescription.status}</span>
  </td>
  <td class="px-4 py-4 whitespace-nowrap">
    <button onclick="viewPrescription(${index})" class="text-blue-600 hover:text-blue-800 mr-3">
      <i class="fas fa-eye"></i>
    </button>
    <button onclick="deletePrescription(${index})" class="text-red-600 hover:text-red-800">
      <i class="fas fa-trash"></i>
    </button>
  </td>
`;


    tableBody.appendChild(row);
    hideAddPrescriptionModal();
    document.getElementById('prescriptionForm').reset();
  });









 // Load prescriptions from localStorage on page load
  document.addEventListener("DOMContentLoaded", function () {
    const prescriptions = JSON.parse(localStorage.getItem("prescriptions")) || [];
    renderPrescriptions(prescriptions);
  });

  // Handle form submission
  document.getElementById("prescriptionForm").addEventListener("submit", function (e) {
    e.preventDefault();

    const patient = document.getElementById("patient").value;
    const medicine = document.getElementById("medicine").value;
    const dosage = document.getElementById("dosage").value;
    const date = new Date().toISOString().split("T")[0];
    const status = "Pending";

    const newPrescription = { patient, medicine, dosage, date, status };

    let prescriptions = JSON.parse(localStorage.getItem("prescriptions")) || [];
    prescriptions.push(newPrescription);
    localStorage.setItem("prescriptions", JSON.stringify(prescriptions));

    renderPrescriptions(prescriptions);
    hideAddPrescriptionModal();
    document.getElementById("prescriptionForm").reset();
  });

  // Render function
  function renderPrescriptions(prescriptions) {
    const tbody = document.getElementById("prescriptionTableBody");
    tbody.innerHTML = "";

    prescriptions.forEach((prescription, index) => {
      const tr = document.createElement("tr");
      tr.innerHTML = `
        <td class="px-4 py-4 whitespace-nowrap">${prescription.patient}</td>
        <td class="px-4 py-4 whitespace-nowrap">${prescription.medicine}</td>
        <td class="px-4 py-4 whitespace-nowrap">${prescription.dosage}</td>
        <td class="px-4 py-4 whitespace-nowrap">${prescription.date}</td>
        <td class="px-4 py-4 whitespace-nowrap">
          <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">${prescription.status}</span>
        </td>
        <td class="px-4 py-4 whitespace-nowrap">
          <button onclick="viewPrescription(${index})" class="text-blue-600 hover:text-blue-800 mr-3">
            <i class="fas fa-eye"></i>
          </button>
          <button onclick="deletePrescription(${index})" class="text-red-600 hover:text-red-800">
            <i class="fas fa-trash"></i>
          </button>
        </td>
      `;
      tbody.appendChild(tr);
    });
  }




  
  // Delete prescription
  function deletePrescription(index) {
    let prescriptions = JSON.parse(localStorage.getItem("prescriptions")) || [];
    prescriptions.splice(index, 1);
    localStorage.setItem("prescriptions", JSON.stringify(prescriptions));
    renderPrescriptions(prescriptions);
  }

  // Optional view function
  function viewPrescription(index) {
    const prescriptions = JSON.parse(localStorage.getItem("prescriptions")) || [];
    const p = prescriptions[index];
    alert(`Patient: ${p.patient}\nMedicine: ${p.medicine}\nDosage: ${p.dosage}\nDate: ${p.date}\nStatus: ${p.status}`);
  }







// Show & Hide Modal
  function showAddPrescriptionModal() {
    document.getElementById("addPrescriptionModal").classList.remove("hidden");
  }

  function hideAddPrescriptionModal() {
    document.getElementById("addPrescriptionModal").classList.add("hidden");
  }

  // Load prescriptions from localStorage on page load
  document.addEventListener("DOMContentLoaded", function () {
    const prescriptions = JSON.parse(localStorage.getItem("prescriptions")) || [];
    renderPrescriptions(prescriptions);
  });

  // Handle form submission
  document.getElementById("prescriptionForm").addEventListener("submit", function (e) {
    e.preventDefault();

    const patient = document.getElementById("patient").value.trim();
    const medicine = document.getElementById("medicine").value.trim();
    const dosage = document.getElementById("dosage").value.trim();
    const date = new Date().toISOString().split("T")[0];
    const status = "Pending";

    const newPrescription = { patient, medicine, dosage, date, status };

    let prescriptions = JSON.parse(localStorage.getItem("prescriptions")) || [];
    prescriptions.push(newPrescription);
    localStorage.setItem("prescriptions", JSON.stringify(prescriptions));

    renderPrescriptions(prescriptions);
    hideAddPrescriptionModal();
    document.getElementById("prescriptionForm").reset();
  });

  // Render prescription list
  function renderPrescriptions(prescriptions) {
    const tbody = document.getElementById("prescriptionTableBody");
    tbody.innerHTML = "";

    prescriptions.forEach((prescription, index) => {
      const tr = document.createElement("tr");
      tr.innerHTML = `
        <td class="px-4 py-4 whitespace-nowrap">${prescription.patient}</td>
        <td class="px-4 py-4 whitespace-nowrap">${prescription.medicine}</td>
        <td class="px-4 py-4 whitespace-nowrap">${prescription.dosage}</td>
        <td class="px-4 py-4 whitespace-nowrap">${prescription.date}</td>
        <td class="px-4 py-4 whitespace-nowrap">
          <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">${prescription.status}</span>
        </td>
        <td class="px-4 py-4 whitespace-nowrap">
          <button onclick="viewPrescription(${index})" class="text-blue-600 hover:text-blue-800 mr-3">
            <i class="fas fa-eye"></i>
          </button>
          <button onclick="deletePrescription(${index})" class="text-red-600 hover:text-red-800">
            <i class="fas fa-trash"></i>
          </button>
        </td>
      `;
      tbody.appendChild(tr);
    });
  }

  // Optional: Delete prescription
  function deletePrescription(index) {
    let prescriptions = JSON.parse(localStorage.getItem("prescriptions")) || [];
    prescriptions.splice(index, 1);
    localStorage.setItem("prescriptions", JSON.stringify(prescriptions));
    renderPrescriptions(prescriptions);
  }

  // Optional: View prescription
  function viewPrescription(index) {
    const prescriptions = JSON.parse(localStorage.getItem("prescriptions")) || [];
    const p = prescriptions[index];
    alert(`Patient: ${p.patient}\nMedicine: ${p.medicine}\nDosage: ${p.dosage}\nDate: ${p.date}\nStatus: ${p.status}`);
  }


  </script>
</body>
</html>