<?php
require_once('../../connection/connection.php');
session_start();

if(!isset($_SESSION['user_id'])) {
  header("Location: ../../auth/login1.php");
  exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT username, profile_pic, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$username = $user['username'] ?? 'Guest';
$profilePic = !empty($user['profile_pic']) ? '../../uploads/' . $user['profile_pic'] : '../../img/Sunny rd.jpg';

$totalSiswa = $conn->query("SELECT COUNT(*) AS total FROM murid")->fetch_assoc()['total'];
$totalGuru = $conn->query("SELECT COUNT(*) AS total FROM guru")->fetch_assoc()['total'];
$totalAkun = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];

$labels = [];
$hadirCounts = [];
$izinCounts = [];
$sakitCounts = [];

for ($i = 6; $i >= 0; $i--) {
  $date = date('Y-m-d', strtotime("-$i days"));
  $dayName = date('D', strtotime($date));
  $dayMap = ['Mon'=>'Sen', 'Tue'=>'Sel', 'Wed'=>'Rab', 'Thu'=>'Kam', 'Fri'=>'Jum', 'Sat'=>'Sab', 'Sun'=>'Min'];
  $label = $dayMap[$dayName];
  $labels[] = $label;

  $hadir = $conn->query("SELECT COUNT(*) AS jml FROM absensi WHERE tanggal = '$date' AND status = 'hadir'")->fetch_assoc()['jml'];
  $izin  = $conn->query("SELECT COUNT(*) AS jml FROM absensi WHERE tanggal = '$date' AND status = 'izin'")->fetch_assoc()['jml'];
  $sakit = $conn->query("SELECT COUNT(*) AS jml FROM absensi WHERE tanggal = '$date' AND status = 'sakit'")->fetch_assoc()['jml'];

  $hadirCounts[] = (int)$hadir;
  $izinCounts[]  = (int)$izin;
  $sakitCounts[] = (int)$sakit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard - Admin</title>
  <link rel="stylesheet" href="../../dist/output.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body { font-family: 'Poppins', sans-serif; overflow-x: hidden; }
    #sidebar { transition: transform 0.4s ease, opacity 0.4s ease; transform: translateX(-100%); opacity: 0; }
    #sidebar.active { transform: translateX(0); opacity: 1; }
    #overlay { display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.5); z-index: 30; opacity: 0; transition: opacity 0.3s ease; }
    #overlay.active { display: block; opacity: 1; }
    #mainContent { transition: margin-left 0.4s ease; margin-left: 0; }
    @media (min-width: 1024px) {
      #sidebar { transform: translateX(0); opacity: 1; }
      #mainContent { margin-left: 16rem; }
      #overlay { display: none !important; }
      #toggleSidebar { display: none; }
    }
    .chart-responsive { width: 100%; max-width: 700px; height: 350px; }
    @media (max-width:1024px){ .chart-responsive{max-width:550px;height:300px;} }
    @media (max-width:640px){ .chart-responsive{max-width:320px;height:220px;} }
  </style>
</head>
<body class="bg-gray-100">
<div id="overlay"></div>

<div class="flex min-h-screen overflow-hidden">

  <aside id="sidebar" class="w-64 bg-indigo-700 text-white flex flex-col justify-between fixed inset-y-0 left-0 z-40">
    <div>
      <div class="p-6 border-b border-indigo-500 flex justify-between items-center">
        <h1 class="font-bold text-2xl">Admin</h1>
        <button id="closeSidebar" class="lg:hidden text-indigo-200 hover:text-white">✕</button>
      </div>

      <nav class="mt-6 space-y-1">
        <a href="dashboard-admin.php" class="flex items-center px-6 py-3 hover:bg-indigo-600 transition bg-indigo-600 rounded-l-full">Dashboard</a>
        <a href="kelola-akun.php" class="flex items-center px-6 py-3 hover:bg-indigo-600 transition">Kelola Akun</a>
        <a href="activity-log.php" class="flex items-center px-6 py-3 hover:bg-indigo-600 transition">Log Aktivitas</a>
      </nav>
    </div>

    <div class="p-6 border-t border-indigo-500 text-center text-sm text-indigo-200">
      © 2025 Student Absence
    </div>
  </aside>

  <main id="mainContent" class="flex-1 transition-all duration-300">
    <div class="flex justify-between items-center p-6 bg-indigo-600 text-white">
      <div class="flex items-center space-x-3">
        <button id="toggleSidebar" class="focus:outline-none">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
        <h2 class="font-semibold text-2xl">Dashboard</h2>
      </div>

      <div class="relative">
        <button id="dropdownButton" class="flex items-center focus:outline-none cursor-pointer">
          <img src="<?= htmlspecialchars($profilePic) ?>" alt="Profile" class="w-10 h-10 rounded-full mr-2 border-2 border-white object-cover" />
          <p><?= htmlspecialchars($username) ?></p>
          <svg class="ml-1 w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd"
              d="M5.23 7.21a.75.75 0 011.06.02L10 11.292l3.71-4.06a.75.75 0 011.08 1.04l-4.25 4.66a.75.75 0 01-1.08 0l-4.25-4.66a.75.75 0 01.02-1.06z"
              clip-rule="evenodd" />
          </svg>
        </button>

        <div id="dropdownMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-50">
          <a href="../edit-profile/edit-profile-admin.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Edit Profil</a>
          <a href="../../auth/logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</a>
        </div>
      </div>
    </div>

    <div class="p-6 text-gray-700 space-y-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-lg shadow border-t-4 border-blue-400">
          <h3 class="text-gray-600 font-semibold">Total Siswa</h3>
          <p class="text-3xl font-bold mt-2"><?= $totalSiswa ?></p>
          <p class="text-sm text-gray-500">akun saat ini</p>
        </div>

        <div class="bg-white p-6 rounded-lg shadow border-t-4 border-green-400">
          <h3 class="text-gray-600 font-semibold">Total Guru</h3>
          <p class="text-3xl font-bold mt-2"><?= $totalGuru ?></p>
          <p class="text-sm text-gray-500">terdaftar</p>
        </div>

        <div class="bg-white p-6 rounded-lg shadow border-t-4 border-purple-400">
          <h3 class="text-gray-600 font-semibold">Total Akun</h3>
          <p class="text-3xl font-bold mt-2"><?= $totalAkun ?></p>
          <p class="text-sm text-gray-500">semua role</p>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="font-semibold text-lg mb-4">Grafik Kehadiran Mingguan</h3>
        <div class="relative w-full flex justify-center">
          <canvas id="chart" class="chart-responsive"></canvas>
        </div>
      </div>
    </div>
  </main>
</div>

<script>
  const dropdownButton = document.getElementById("dropdownButton");
  const dropdownMenu = document.getElementById("dropdownMenu");
  dropdownButton.addEventListener("click", () => dropdownMenu.classList.toggle("hidden"));
  window.addEventListener("click", (e) => {
    if (!dropdownButton.contains(e.target) && !dropdownMenu.contains(e.target))
      dropdownMenu.classList.add("hidden");
  });

  const sidebar = document.getElementById("sidebar");
  const overlay = document.getElementById("overlay");
  const toggleSidebar = document.getElementById("toggleSidebar");
  const closeSidebar = document.getElementById("closeSidebar");
  toggleSidebar.addEventListener("click", () => { sidebar.classList.toggle("active"); overlay.classList.toggle("active"); });
  closeSidebar.addEventListener("click", () => { sidebar.classList.remove("active"); overlay.classList.remove("active"); });
  overlay.addEventListener("click", () => { sidebar.classList.remove("active"); overlay.classList.remove("active"); });

  const labels = <?= json_encode($labels) ?>;
  const hadirData = <?= json_encode($hadirCounts) ?>;
  const izinData = <?= json_encode($izinCounts) ?>;
  const sakitData = <?= json_encode($sakitCounts) ?>;

  new Chart(document.getElementById('chart'), {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [
        {
          label: 'Hadir',
          data: hadirData,
          backgroundColor: 'rgba(79,70,229,0.7)',
          borderRadius: 6
        },
        {
          label: 'Izin',
          data: izinData,
          backgroundColor: 'rgba(34,197,94,0.7)',
          borderRadius: 6
        },
        {
          label: 'Sakit',
          data: sakitData,
          backgroundColor: 'rgba(239,68,68,0.7)',
          borderRadius: 6
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { 
        legend: { display: true, position: 'top' }
      },
      scales: {
        x: { ticks: { color: '#444' } },
        y: { ticks: { color: '#444' }, beginAtZero: true }
      }
    }
  });
</script>
</body>
</html>
