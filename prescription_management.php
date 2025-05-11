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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Prescription Management</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
    .prescription-notification {
      animation: pulse 2s infinite;
    }
    @keyframes pulse {
      0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.4); }
      70% { box-shadow: 0 0 0 10px rgba(59, 130, 246, 0); }
      100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
    }
    .status-badge {
      padding: 0.25rem 0.5rem;
      border-radius: 0.25rem;
      font-size: 0.75rem;
      font-weight: 600;
    }
    .status-pending {
      background-color: #fef3c7;
      color: #92400e;
    }
    .status-processing {
      background-color: #bfdbfe;
      color: #1e40af;
    }
    .status-completed {
      background-color: #d1fae5;
      color: #065f46;
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
      <a href="prescription_management.php" class="flex items-center py-2 px-4 mb-2 rounded bg-green-900 transition relative">
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
  <div class="ml-64 transition-all duration-300" id="main-content">
    <!-- Header -->
    <header class="bg-white shadow-sm p-4 flex justify-between items-center sticky top-0 z-30">
      <div class="flex items-center">
        <button onclick="toggleSidebar()" class="mr-4 text-gray-600 hover:text-gray-900">
          <i class="fas fa-bars text-xl"></i>
        </button>
        <h1 class="text-xl font-bold text-gray-800">Prescription Management</h1>
      </div>
      <div class="flex items-center space-x-4">
        <div class="relative">
          <input type="text" placeholder="Search prescriptions..." class="pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
          <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
        </div>
        <button class="relative p-2 text-gray-600 hover:text-gray-900">
          <i class="fas fa-bell text-xl"></i>
          <span class="notification-badge">3</span>
        </button>
        <div class="flex items-center">
          <img src="image.png" alt="User" class="w-8 h-8 rounded-full mr-2">

          <span class="font-medium">Admin</span>
        </div>
      </div>
    </header>

    <!-- Prescription Content -->
    <main class="p-6">
      <!-- New Prescription Alert -->
      <div class="mb-6 p-4 border border-blue-200 bg-blue-50 rounded-lg prescription-notification">
        <div class="flex items-start">
          <div class="bg-blue-100 p-3 rounded-full mr-3">
            <i class="fas fa-prescription text-blue-600 text-xl"></i>
          </div>
          <div class="flex-1">
            <h4 class="font-bold text-blue-800">New Prescription Received</h4>
            <p class="text-sm text-gray-600">Dr. Smith has sent a prescription for <span class="font-semibold">John Doe</span></p>
            <div class="mt-3 flex items-center">
              <span class="text-gray-500 text-sm mr-4"><i class="far fa-clock mr-1"></i> Just now</span>
              <button onclick="processNewPrescription()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                Process Now <i class="fas fa-arrow-right ml-2"></i>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Prescription Actions -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div class="mb-4 sm:mb-0">
          <h2 class="text-lg font-semibold text-gray-800">All Prescriptions</h2>
          <p class="text-sm text-gray-600">Manage and process patient prescriptions</p>
        </div>
        <div class="flex space-x-2">
          <button onclick="showAddPrescriptionModal()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
            <i class="fas fa-plus mr-2"></i> Add Prescription
          </button>
          <div class="relative">
            <select class="appearance-none bg-white border border-gray-300 rounded-lg pl-3 pr-8 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
              <option>All Status</option>
              <option>Pending</option>
              <option>Processing</option>
              <option>Completed</option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
              <i class="fas fa-chevron-down"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Prescriptions Table -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prescription ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Doctor</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Medicines</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200" id="prescriptionsTable">
              <!-- Prescriptions will be loaded here -->
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination -->
      <div class="flex items-center justify-between mt-4 px-4 py-3 bg-white border-t border-gray-200 sm:px-6 rounded-b-lg">
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
          <div>
            <p class="text-sm text-gray-700">
              Showing <span class="font-medium">1</span> to <span class="font-medium">5</span> of <span class="font-medium">12</span> prescriptions
            </p>
          </div>
          <div>
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
              <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                <span class="sr-only">Previous</span>
                <i class="fas fa-chevron-left"></i>
              </a>
              <a href="#" aria-current="page" class="z-10 bg-green-50 border-green-500 text-green-600 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                1
              </a>
              <a href="#" class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                2
              </a>
              <a href="#" class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                3
              </a>
              <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                <span class="sr-only">Next</span>
                <i class="fas fa-chevron-right"></i>
              </a>
            </nav>
          </div>
        </div>
      </div>
    </main>
  </div>

  <!-- Add/Edit Prescription Modal -->
<div id="prescriptionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
  <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl">
    <div class="p-6">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-bold" id="modalTitle">Add New Prescription</h3>
        <button onclick="hidePrescriptionModal()" class="text-gray-500 hover:text-gray-700">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <form id="prescriptionForm">
        <input type="hidden" id="prescriptionId">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
          <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="patientName">Patient Name <span class="text-red-500">*</span></label>
            <input type="text" id="patientName" name="patientName" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
          </div>
          <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="doctorName">Doctor Name <span class="text-red-500">*</span></label>
            <input type="text" id="doctorName" name="doctorName" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
          </div>
        </div>
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2" for="diagnosis">Diagnosis</label>
          <textarea id="diagnosis" name="diagnosis" rows="2" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
        </div>
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">Medicines <span class="text-red-500">*</span></label>
          <div id="medicinesContainer"></div>
          <button type="button" onclick="addMedicineRow()" class="mt-2 bg-gray-200 hover:bg-gray-300 text-gray-800 py-1 px-3 rounded text-sm">
            <i class="fas fa-plus mr-1"></i> Add Medicine
          </button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
          <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="prescriptionDate">Date <span class="text-red-500">*</span></label>
            <input type="date" id="prescriptionDate" name="prescriptionDate" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
          </div>
          <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="status">Status <span class="text-red-500">*</span></label>
            <select id="status" name="status" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
              <option value="pending">Pending</option>
              <option value="processing">Processing</option>
              <option value="completed">Completed</option>
            </select>
          </div>
        </div>
        <div class="flex justify-end">
          <button type="button" onclick="hidePrescriptionModal()" class="mr-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">Cancel</button>
          <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Save Prescription</button>
        </div>
      </form>
    </div>
  </div>
</div>


 <!-- View Prescription Modal -->
  <div id="viewPrescriptionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl">
      <div class="p-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-bold">Prescription Details</h3>
          <button onclick="hideViewPrescriptionModal()" class="text-gray-500 hover:text-gray-700">
            <i class="fas fa-times"></i>
          </button>
        </div>
        
        <div class="mb-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
              <p class="text-gray-500 text-sm">Prescription ID</p>
              <p class="font-medium" id="viewPrescriptionId">PR-2023-001</p>
            </div>
            <div>
              <p class="text-gray-500 text-sm">Date</p>
              <p class="font-medium" id="viewDate">June 15, 2023</p>
            </div>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
              <p class="text-gray-500 text-sm">Patient Name</p>
              <p class="font-medium" id="viewPatientName">John Doe</p>
            </div>
            <div>
              <p class="text-gray-500 text-sm">Doctor Name</p>
              <p class="font-medium" id="viewDoctorName">Dr. Smith</p>
            </div>
          </div>
          
          <div class="mb-4">
            <p class="text-gray-500 text-sm">Diagnosis</p>
            <p class="font-medium" id="viewDiagnosis">Upper respiratory infection</p>
          </div>
          
          <div class="mb-4">
            <p class="text-gray-500 text-sm mb-2">Medicines</p>
            <div class="border rounded-lg overflow-hidden">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Medicine</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dosage</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="viewMedicinesTable">
                  <!-- Medicines will be added here -->
                </tbody>
              </table>
            </div>
          </div>
          
          <div>
            <p class="text-gray-500 text-sm">Status</p>
            <p>
              <span class="status-badge status-pending" id="viewStatus">Pending</span>
            </p>
          </div>
        </div>
        
        <div class="flex justify-end">
          <button onclick="hideViewPrescriptionModal()" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Sample prescription data
    let prescriptions = [
     
    ];

    // Toggle sidebar
    function toggleSidebar() {
      const sidebar = document.getElementById('sidebar');
      const mainContent = document.getElementById('main-content');
      sidebar.classList.toggle('hidden');
      mainContent.classList.toggle('ml-64');
      mainContent.classList.toggle('ml-0');
    }

    // Show add prescription modal
    function showAddPrescriptionModal() {
      document.getElementById('modalTitle').textContent = 'Add New Prescription';
      document.getElementById('prescriptionId').value = '';
      document.getElementById('prescriptionForm').reset();
      
      // Clear medicines container
      const medicinesContainer = document.getElementById('medicinesContainer');
      medicinesContainer.innerHTML = '';
      
      // Add one empty medicine row
      addMedicineRow();
      
      document.getElementById('prescriptionModal').classList.remove('hidden');
    }

    // Show edit prescription modal
    function showEditPrescriptionModal(prescriptionId) {
      const prescription = prescriptions.find(p => p.id === prescriptionId);
      if (!prescription) return;
      
      document.getElementById('modalTitle').textContent = 'Edit Prescription';
      document.getElementById('prescriptionId').value = prescription.id;
      document.getElementById('patientName').value = prescription.patientName;
      document.getElementById('doctorName').value = prescription.doctorName;
      document.getElementById('diagnosis').value = prescription.diagnosis;
      document.getElementById('prescriptionDate').value = prescription.date;
      document.getElementById('status').value = prescription.status;
      
      // Clear medicines container
      const medicinesContainer = document.getElementById('medicinesContainer');
      medicinesContainer.innerHTML = '';
      
      // Add medicine rows
      prescription.medicines.forEach(medicine => {
        addMedicineRow(medicine.name, medicine.dosage, medicine.duration);
      });
      
      document.getElementById('prescriptionModal').classList.remove('hidden');
    }

    // Hide prescription modal
    function hidePrescriptionModal() {
      document.getElementById('prescriptionModal').classList.add('hidden');
    }

    // Show view prescription modal
    function showViewPrescriptionModal(prescriptionId) {
      const prescription = prescriptions.find(p => p.id === prescriptionId);
      if (!prescription) return;
      
      document.getElementById('viewPrescriptionId').textContent = prescription.id;
      document.getElementById('viewPatientName').textContent = prescription.patientName;
      document.getElementById('viewDoctorName').textContent = prescription.doctorName;
      document.getElementById('viewDiagnosis').textContent = prescription.diagnosis;
      
      // Format date
      const date = new Date(prescription.date);
      document.getElementById('viewDate').textContent = date.toLocaleDateString('en-US', { 
        year: 'numeric', month: 'long', day: 'numeric' 
      });
      
      // Set status with appropriate class
      const statusElement = document.getElementById('viewStatus');
      statusElement.textContent = prescription.status.charAt(0).toUpperCase() + prescription.status.slice(1);
      statusElement.className = 'status-badge';
      if (prescription.status === 'pending') {
        statusElement.classList.add('status-pending');
      } else if (prescription.status === 'processing') {
        statusElement.classList.add('status-processing');
      } else {
        statusElement.classList.add('status-completed');
      }
      
      // Add medicines to view table
      const medicinesTable = document.getElementById('viewMedicinesTable');
      medicinesTable.innerHTML = '';
      
      prescription.medicines.forEach(medicine => {
        const row = document.createElement('tr');
        row.innerHTML = `
          <td class="px-4 py-2 whitespace-nowrap">${medicine.name}</td>
          <td class="px-4 py-2 whitespace-nowrap">${medicine.dosage}</td>
          <td class="px-4 py-2 whitespace-nowrap">${medicine.duration}</td>
        `;
        medicinesTable.appendChild(row);
      });
      
      document.getElementById('viewPrescriptionModal').classList.remove('hidden');
    }

    // Hide view prescription modal
    function hideViewPrescriptionModal() {
      document.getElementById('viewPrescriptionModal').classList.add('hidden');
    }

    // Add medicine row to form
    function addMedicineRow(name = '', dosage = '', duration = '') {
      const medicinesContainer = document.getElementById('medicinesContainer');
      const medicineId = Date.now();
      
      const row = document.createElement('div');
      row.className = 'medicine-row grid grid-cols-1 md:grid-cols-3 gap-2 mb-2';
      row.dataset.id = medicineId;
      row.innerHTML = `
        <div>
          <input type="text" placeholder="Medicine name" value="${name}" 
            class="medicine-name shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>
        <div>
          <input type="text" placeholder="Dosage" value="${dosage}"
            class="medicine-dosage shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>
        <div class="flex">
          <input type="text" placeholder="Duration" value="${duration}"
            class="medicine-duration shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
          <button type="button" onclick="removeMedicineRow(${medicineId})" class="ml-2 bg-red-500 hover:bg-red-600 text-white p-2 rounded">
            <i class="fas fa-trash"></i>
          </button>
        </div>
      `;
      medicinesContainer.appendChild(row);
    }

    // Remove medicine row from form
    function removeMedicineRow(medicineId) {
      const row = document.querySelector(`.medicine-row[data-id="${medicineId}"]`);
      if (row) {
        row.remove();
      }
    }

    // Delete prescription
    function deletePrescription(prescriptionId) {
      if (confirm('Are you sure you want to delete this prescription?')) {
        prescriptions = prescriptions.filter(p => p.id !== prescriptionId);
        renderPrescriptionsTable();
      }
    }

    // Process new prescription (from notification)
    function processNewPrescription() {
      // In a real app, this would redirect to the prescription management page
      // For this demo, we'll just scroll to the prescriptions table
      document.getElementById('prescriptionsTable').scrollIntoView({ behavior: 'smooth' });
      
      // Simulate marking the notification as read
      document.querySelector('.prescription-notification').style.display = 'none';
    }

    // Form submission
    document.getElementById('prescriptionForm').addEventListener('submit', function(e) {
      e.preventDefault();
      
      // Get form values
      const id = document.getElementById('prescriptionId').value || `PR-${new Date().getFullYear()}-${String(prescriptions.length + 1).padStart(3, '0')}`;
      const patientName = document.getElementById('patientName').value;
      const doctorName = document.getElementById('doctorName').value;
      const diagnosis = document.getElementById('diagnosis').value;
      const date = document.getElementById('prescriptionDate').value;
      const status = document.getElementById('status').value;
      
      // Get medicines
      const medicines = [];
      document.querySelectorAll('.medicine-row').forEach(row => {
        const name = row.querySelector('.medicine-name').value;
        const dosage = row.querySelector('.medicine-dosage').value;
        const duration = row.querySelector('.medicine-duration').value;
        
        if (name && dosage && duration) {
          medicines.push({ name, dosage, duration });
        }
      });
      
      // Validate medicines
      if (medicines.length === 0) {
        alert('Please add at least one medicine');
        return;
      }
      
      // Create or update prescription
      const existingIndex = prescriptions.findIndex(p => p.id === id);
      const newPrescription = {
        id,
        patientName,
        doctorName,
        diagnosis,
        medicines,
        date,
        status
      };
      
      if (existingIndex >= 0) {
        prescriptions[existingIndex] = newPrescription;
      } else {
        prescriptions.unshift(newPrescription);
      }
      
      // Render updated table
      renderPrescriptionsTable();
      
      // Hide modal
      hidePrescriptionModal();
    });

    // Render prescriptions table
    function renderPrescriptionsTable() {
      const table = document.getElementById('prescriptionsTable');
      table.innerHTML = '';
      
      prescriptions.forEach(prescription => {
        const row = document.createElement('tr');
        row.className = 'hover:bg-gray-50';
        
        // Format date
        const date = new Date(prescription.date);
        const formattedDate = date.toLocaleDateString('en-US', { 
          year: 'numeric', month: 'short', day: 'numeric' 
        });
        
        // Status badge
        let statusClass = 'status-pending';
        if (prescription.status === 'processing') {
          statusClass = 'status-processing';
        } else if (prescription.status === 'completed') {
          statusClass = 'status-completed';
        }
        
        // Medicines list (first 2 medicines)
        let medicinesList = '';
        const maxMedicinesToShow = 2;
        prescription.medicines.slice(0, maxMedicinesToShow).forEach(medicine => {
          medicinesList += `<div class="text-sm">${medicine.name} (${medicine.dosage})</div>`;
        });
        
        if (prescription.medicines.length > maxMedicinesToShow) {
          medicinesList += `<div class="text-xs text-gray-500">+${prescription.medicines.length - maxMedicinesToShow} more</div>`;
        }
        
        row.innerHTML = `
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm font-medium text-gray-900">${prescription.id}</div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm font-medium text-gray-900">${prescription.patientName}</div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm text-gray-900">${prescription.doctorName}</div>
          </td>
          <td class="px-6 py-4">
            ${medicinesList}
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm text-gray-500">${formattedDate}</div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <span class="status-badge ${statusClass}">
              ${prescription.status.charAt(0).toUpperCase() + prescription.status.slice(1)}
            </span>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
            <button onclick="showViewPrescriptionModal('${prescription.id}')" class="text-blue-600 hover:text-blue-900 mr-3">
              <i class="fas fa-eye"></i>
            </button>
            <button onclick="showEditPrescriptionModal('${prescription.id}')" class="text-yellow-600 hover:text-yellow-900 mr-3">
              <i class="fas fa-edit"></i>
            </button>
            <button onclick="deletePrescription('${prescription.id}')" class="text-red-600 hover:text-red-900">
              <i class="fas fa-trash"></i>
            </button>
          </td>
        `;
        
        table.appendChild(row);
      });
    }

    // Initialize the page
    document.addEventListener('DOMContentLoaded', function() {
      renderPrescriptionsTable();
      
      // Set today's date as default for new prescriptions
      const today = new Date().toISOString().split('T')[0];
      document.getElementById('prescriptionDate').value = today;
    });
  </script>
</body>
</html>