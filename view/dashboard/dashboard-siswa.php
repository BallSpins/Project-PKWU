<?php
require_once('../../connection/connection.php');
session_start();
if(!isset($_SESSION['email'])) {
    header("Location: ../../auth/login1.php");
    exit;
}
$user_id = $_SESSION['user_id']; // ini id murid dari session

// Ambil data murid + user
$query = $conn->prepare("
    SELECT m.username, u.profile_pic 
    FROM murid m
    JOIN users u ON m.user_id = u.id
    WHERE m.id = ?
");
$query->execute([$user_id]);
$dataUser = $query->fetch();

$username = $dataUser['username'] ?? "Murid";
$profilePic = !empty($_SESSION['profile_pic']) 
    ? "../../uploads/" . $_SESSION['profile_pic'] 
    : "../../img/Chigiri_6.jpeg";


$today = date('Y-m-d');
$nowTime = date('H:i:s');

// --- Cek apakah sudah absen hari ini ---
$cek = $conn->prepare("SELECT * FROM absensi WHERE murid_id = ? AND tanggal = ?");
$cek->execute([$user_id, $today]);
$absen = $cek->fetch();

// Default
$sudahAlpha = false;
$pesan = "";

// --- Auto set Alfa kalau lewat jam 07:00 dan belum absen ---
// if(!$absen && $nowTime > '08:00:00') {
//     $insert = $conn->prepare("INSERT INTO absensi (murid_id, tanggal, status) VALUES (?, ?, 'Alpha')");
//     $insert->execute([$user_id, $today]);
//     $sudahAlpha = true;
//     $pesan = "Anda telah diberi status Alfa karena tidak absen sebelum jam 07:00.";
// }

// --- Handle Kirim Absensi ---
if($_SERVER['REQUEST_METHOD'] === 'POST' && !$absen) {
    $status = $_POST['absen'] ?? null;

    if($status) {
        // Simpan absensi sesuai pilihan
        $insert = $conn->prepare("INSERT INTO absensi (murid_id, tanggal, status) VALUES (?, ?, ?)");
        $insert->execute([$user_id, $today, ucfirst($status)]);
        $pesan = "Absensi berhasil dikirim dengan status: " . ucfirst($status);
    } else {
        $pesan = "Pilih salah satu opsi absensi terlebih dahulu!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard Siswa</title>
  <link href="../../dist/output.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style> body { font-family: 'Poppins', sans-serif; } </style>
</head>
<body class="bg-gray-100">

<!-- Header -->
<div class="flex justify-between max-w-screen mx-auto p-6 bg-indigo-600 items-center relative">
  <h1 class="ml-10 font-bold text-2xl text-white">Absensi Siswa</h1>
  <div class="relative mr-10">
    <button onclick="toggleDropdown()" class="flex items-center focus:outline-none cursor-pointer">
      <img src="<?php echo htmlspecialchars($profilePic); ?>" alt="Profile Picture" class="w-10 h-10 rounded-full mr-2 border-2 border-white" />
      <p class="text-white"><?php echo htmlspecialchars($username); ?></p>
      <svg class="ml-1 w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.292l3.71-4.06a.75.75 0 011.08 1.04l-4.25 4.66a.75.75 0 01-1.08 0l-4.25-4.66a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
      </svg>
    </button>
    <div id="dropdownMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-50">
      <a href="../edit-profile/edit-profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Edit Profil</a>
      <a href="../../auth/logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</a>
    </div>
  </div>
</div>

<!-- Konten utama -->
<div class="max-w-7xl mx-auto mt-10 px-6 flex flex-col lg:flex-row gap-6">

  <!-- Card Absensi -->
  <div class="bg-white rounded-lg shadow-md p-6 w-full lg:w-1/3 border border-teal-300">
    <h2 class="text-center font-semibold mb-4">Absensi Sekolah</h2>

    <?php if($pesan): ?>
      <div class="bg-blue-100 text-blue-700 p-3 rounded mb-4 text-center">
        <?= htmlspecialchars($pesan) ?>
      </div>
    <?php endif; ?>

    <?php if(!$absen && !$sudahAlpha): ?>
    <form method="POST" enctype="multipart/form-data">
      <!-- Hadir -->
      <label class="flex items-center justify-between border border-gray-300 rounded-md px-4 py-2 mb-3 cursor-pointer hover:bg-gray-50">
        <div class="flex items-center gap-3">
          <img src="https://img.icons8.com/ios/50/calendar--v1.png" class="w-6 h-6"/>
          <span class="font-medium">Hadir</span>
        </div>
        <input type="radio" name="absen" value="hadir" class="form-radio accent-blue-600" onclick="handleToggleAbsen()" />
      </label>

      <!-- Sakit -->
      <label class="flex items-center justify-between border border-gray-300 rounded-md px-4 py-2 mb-3 cursor-pointer hover:bg-gray-50">
        <div class="flex items-center gap-3">
          <img src="https://img.icons8.com/ios/50/person-female.png" class="w-6 h-6" />
          <span class="font-medium">Sakit</span>
        </div>
        <input type="radio" name="absen" value="sakit" class="form-radio accent-blue-600" onclick="handleToggleAbsen()" />
      </label>

      <!-- Izin -->
      <label class="flex items-center justify-between border border-gray-300 rounded-md px-4 py-2 mb-3 cursor-pointer hover:bg-gray-50">
        <div class="flex items-center gap-3">
          <img src="https://img.icons8.com/ios/50/mail.png" class="w-6 h-6" />
          <span class="font-medium">Izin</span>
        </div>
        <input type="radio" name="absen" value="izin" class="form-radio accent-blue-600" onclick="handleToggleAbsen()" />
      </label>

      <!-- Upload Dokumen -->
      <div id="sakitFile" class="hidden">
        <label class="block mb-2 text-sm font-medium text-gray-700">Upload Surat Dokter</label>
        <input type="file" name="surat_sakit" class="border border-gray-300 p-2 mb-4 w-full rounded" />
      </div>
      <div id="alasanBox" class="hidden">
        <label class="block mb-2 text-sm font-medium text-gray-700">Upload Surat Izin</label>
        <input type="file" name="surat_izin" class="border border-gray-300 p-2 mb-4 w-full rounded" />
      </div>

      <!-- Tombol -->
      <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-2 rounded-md hover:bg-blue-700 transition cursor-pointer">
        Kirim Absensi
      </button>
    </form>
    <?php endif; ?>
  </div>

  <!-- Chart Absensi -->
  <div class="bg-white rounded-lg shadow-md p-6 w-full lg:w-2/3">
    <h3 class="font-semibold text-lg mb-4">Chart Absen Mingguan</h3>
    <div class="chart-container">
      <img src="https://placehold.co/800x300" alt="Placeholder Chart" class="w-full h-full object-contain bg-gray-50 rounded" />
    </div>
  </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
  <!-- Today's Absences -->
  <div class="bg-white p-6 rounded-lg shadow-md border-t-4 border-blue-400">
    <div class="flex justify-between items-center">
      <div>
        <h2 class="text-gray-600 font-semibold">Today's Absences</h2>
        <p class="text-3xl font-bold mt-2">8</p>
        <p class="text-sm text-green-600 mt-1">↓ 2% from yesterday</p>
      </div>
      <div class="text-blue-500 bg-blue-100 p-3 rounded-full">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
             viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8 7V3M16 7V3M3 11h18M5 19h14a2 2 0 002-2V7H3v10a2 2 0 002 2z"/>
        </svg>
      </div>
    </div>
  </div>

  <!-- This Month's Absences -->
  <div class="bg-white p-6 rounded-lg shadow-md border-t-4 border-red-400">
    <div class="flex justify-between items-center">
      <div>
        <h2 class="text-gray-600 font-semibold">Bulan ini</h2>
        <p class="text-3xl font-bold mt-2">10</p>
        <p class="text-sm text-red-500 mt-1">↓ 60% from last month</p>
      </div>
      <div class="text-red-500 bg-red-100 p-3 rounded-full">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
             viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3M16 7V3M3 11h18M5 19h14a2 2 0 002-2V7H3v10a2 2 0 002 2z"/>
        </svg>
      </div>
    </div>
  </div>

  <!-- Absence Reasons -->
  <div class="bg-white p-6 rounded-lg shadow-md border-t-4 border-purple-500">
    <h2 class="text-gray-600 font-semibold mb-4">Alasan Tidak Masuk</h2>

    <div class="mb-4">
      <div class="flex justify-between text-sm font-medium">
        <span>Sakit</span>
        <span>56%</span>
      </div>
      <div class="w-full bg-gray-200 rounded-full h-2.5 mt-1">
        <div class="bg-purple-500 h-2.5 rounded-full" style="width: 56%"></div>
      </div>
    </div>

    <div class="mb-4">
      <div class="flex justify-between text-sm font-medium">
        <span>Izin</span>
        <span>24%</span>
      </div>
      <div class="w-full bg-gray-200 rounded-full h-2.5 mt-1">
        <div class="bg-blue-500 h-2.5 rounded-full" style="width: 24%"></div>
      </div>
    </div>

    <div class="mb-1">
      <div class="flex justify-between text-sm font-medium">
        <span>Tidak ada keterangan</span>
        <span>20%</span>
      </div>
      <div class="w-full bg-gray-200 rounded-full h-2.5 mt-1">
        <div class="bg-orange-400 h-2.5 rounded-full" style="width: 20%"></div>
      </div>
    </div>
  </div>
</div>

<script src="../../backend/dashboard-function.js"></script>
</body>
</html>
