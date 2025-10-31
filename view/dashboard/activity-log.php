<?php
require_once('../../connection/connection.php');
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../../auth/login1.php");
  exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, role, profile_pic FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$username = $user['username'] ?? 'Guest';
$role = ucfirst($user['role'] ?? 'User');
$profilePic = !empty($user['profile_pic']) ? '../../uploads/' . $user['profile_pic'] : '../../img/Sunny rd.jpg';

$logsGuru = $conn->query("
  SELECT a.id, u.username, u.role, a.action, a.details, a.created_at
  FROM activity_log a
  JOIN users u ON a.user_id = u.id
");
$logsMurid = $conn->query("
  SELECT ab.id, m.nama_lengkap AS username, 'Murid' AS role, ab.status AS action,
         CONCAT('Absen ', ab.status, ' pada ', ab.tanggal) AS details, ab.created_at
  FROM absensi ab
  JOIN murid m ON ab.murid_id = m.id
");
$allLogs = [];
while ($row = $logsGuru->fetch_assoc()) {
  $allLogs[] = $row;
}
while ($row = $logsMurid->fetch_assoc()) {
  $allLogs[] = $row;
}
usort($allLogs, fn ($a, $b) => strtotime($b['created_at']) - strtotime($a['created_at']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Activity Log - Admin</title>
<link rel="stylesheet" href="../../dist/output.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
  body { font-family: 'Poppins', sans-serif; overflow-x: hidden; }
  #overlay { display: none; position: fixed; inset:0; background: rgba(0,0,0,0.5); z-index: 30; }
  #overlay.active { display: block; }

  #sidebar {
    width: 16rem;
    transition: transform 0.3s ease;
    background-color: #4338ca;
    color: white;
    flex-shrink:0;
    height: 100vh;
    position: fixed;
    top:0; left:0;
    z-index: 40;
    transform: translateX(-100%);
  }
  #sidebar.show { transform: translateX(0); }
  
  @media (min-width:1024px){
    #sidebar { 
        transform: translateX(0); 
    }
  }

  #mainContent { 
    transition: margin-left 0.3s ease; 
    margin-left: 0; 
    width: 100%;
  }
  
  @media (min-width:1024px){
    #mainContent { 
        margin-left:16rem;
        width: calc(100% - 16rem);
    }
  }

  .table-wrapper { overflow-x:auto; }

  @media (max-width: 767px) {
    .table-wrapper {
      overflow-x: hidden;
    }

    .table-wrapper thead {
      display: none;
    }

    .table-wrapper tr {
      display: block;
      border: 1px solid #e5e7eb;
      border-radius: 0.5rem;
      margin-bottom: 1rem;
      box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
      overflow: hidden;
      background: #ffffff;
    }
    
    .table-wrapper tr.hover\:bg-gray-50:hover {
        background-color: #f9fafb;
    }
    
    .table-wrapper tbody {
        display: block;
        width: 100%;
    }

    .table-wrapper td {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0.75rem 1rem;
      text-align: right;
      border-bottom: 1px solid #f3f4f6;
    }
    
    .table-wrapper tr:last-child td:last-child {
        border-bottom: 0;
    }

    .table-wrapper td::before {
      content: attr(data-label);
      font-weight: 600;
      text-align: left;
      color: #4b5563;
      padding-right: 1rem;
    }
    
    .table-wrapper td span.px-2 {
        margin-left: auto;
    }
  }
  
</style>
</head>
<body class="bg-gray-100">

<div id="overlay"></div>

<div class="flex min-h-screen">

  <aside id="sidebar" class="flex flex-col justify-between">
    <div>
      <div class="p-6 border-b border-indigo-500 flex justify-between items-center">
        <h1 class="font-bold text-2xl">Admin</h1>
        <button id="closeSidebar" class="lg:hidden text-white focus:outline-none">✕</button>
      </div>
      <nav class="mt-6 space-y-1">
        <a href="dashboard-admin.php" class="flex items-center px-6 py-3 hover:bg-indigo-600 transition">Dashboard</a>
        <a href="kelola-akun.php" class="flex items-center px-6 py-3 hover:bg-indigo-600 transition">Kelola Akun</a>
        <a href="activity-log.php" class="flex items-center px-6 py-3 bg-indigo-600 rounded-l-full">Log Aktivitas</a>
      </nav>
    </div>
    <div class="p-6 border-t border-indigo-500 text-center text-sm text-indigo-200">
      © 2025 Student Absence
    </div>
  </aside>

  <main id="mainContent" class="flex-1 transition-all duration-300">
    <div class="flex justify-between items-center p-6 bg-indigo-600 text-white flex-wrap gap-4">
      <div class="flex items-center space-x-3">
        <button id="toggleSidebar" class="lg:hidden focus:outline-none">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
        </button>
        <h2 class="font-semibold text-2xl">Activity Log</h2>
      </div>

      <div class="relative">
        <button id="dropdownButton" class="flex items-center focus:outline-none cursor-pointer">
          <img src="<?= htmlspecialchars($profilePic) ?>" alt="Profile" class="w-10 h-10 rounded-full mr-2 border-2 border-white object-cover"/>
          <p><?= htmlspecialchars($username) ?></span></p>
          <svg class="ml-1 w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.292l3.71-4.06a.75.75 0 011.08 1.04l-4.25 4.66a.75.75 0 01-1.08 0l-4.25-4.66a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
          </svg>
        </button>
        <div id="dropdownMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-50">
          <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Edit Profil</a>
          <a href="../../auth/logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</a>
        </div>
      </div>
    </div>

    <div class="p-6">
      <div class="table-wrapper bg-white rounded-lg shadow">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Detail</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <?php foreach ($allLogs as $log): ?>
            <tr class="hover:bg-gray-50">
              <td data-label="User" class="px-6 py-4"><?= !empty($log['username']) ? htmlspecialchars($log['username']) : '-' ?></td>
              <td data-label="Role" class="px-6 py-4">
                <span class="px-2 py-1 rounded-full text-xs 
                  <?= $log['role'] == 'Admin' ? 'bg-red-100 text-red-800' : ($log['role'] == 'Guru' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800') ?>">
                  <?= htmlspecialchars(ucfirst($log['role'])) ?>
                </span>
              </td>
              <td data-label="Aksi" class="px-6 py-4 text-sm text-gray-800"><?= htmlspecialchars($log['action']) ?></td>
              <td data-label="Detail" class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($log['details']) ?></td>
              <td data-label="Waktu" class="px-6 py-4 text-xs text-gray-500">
                <?= date('d M Y, H:i', strtotime($log['created_at'])) ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

  </main>
</div>

<script>
  const dropdownButton = document.getElementById("dropdownButton");
  const dropdownMenu = document.getElementById("dropdownMenu");
  const sidebar = document.getElementById("sidebar");
  const toggleSidebar = document.getElementById("toggleSidebar");
  const closeSidebar = document.getElementById("closeSidebar");
  const overlay = document.getElementById("overlay");

  dropdownButton.addEventListener("click", e => { dropdownMenu.classList.toggle("hidden"); });
  window.addEventListener("click", e => {
    if (!dropdownButton.contains(e.target) && !dropdownMenu.contains(e.target)) {
      dropdownMenu.classList.add("hidden");
    }
  });

  toggleSidebar.addEventListener("click", () => {
    sidebar.classList.add("show");
    overlay.classList.add("active");
  });
  closeSidebar.addEventListener("click", () => {
    sidebar.classList.remove("show");
    overlay.classList.remove("active");
  });
  overlay.addEventListener("click", () => {
    sidebar.classList.remove("show");
    overlay.classList.remove("active");
  });
</script>
</body>
</html>