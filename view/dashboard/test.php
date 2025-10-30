<?php
// test.php
require_once('../../connection/connection.php'); // sesuaikan path connection

$murid_id = 6;

// === Tanggal ===
$today = date('Y-m-d');
$currentMonth = date('m');
$currentYear = date('Y');

// --- Hitung total absen hari ini ---
$totalTodayQuery = $conn->prepare("SELECT COUNT(*) AS total FROM absensi WHERE murid_id = ? AND tanggal = ?");
$totalTodayQuery->bind_param("is", $murid_id, $today);
$totalTodayQuery->execute();
$totalToday = (int)$totalTodayQuery->get_result()->fetch_assoc()['total'];

// --- Hitung hadir hari ini ---
$hadirTodayQuery = $conn->prepare("SELECT COUNT(*) AS hadir FROM absensi WHERE murid_id = ? AND tanggal = ? AND status='hadir'");
$hadirTodayQuery->bind_param("is", $murid_id, $today);
$hadirTodayQuery->execute();
$hadirToday = (int)$hadirTodayQuery->get_result()->fetch_assoc()['hadir'];

// --- Persentase hadir hari ini ---
$percentHadirToday = $totalToday > 0 ? round(($hadirToday / $totalToday) * 100) : 0;

// --- Hitung absensi bulan ini ---
$monthQuery = $conn->prepare("SELECT COUNT(*) AS total FROM absensi WHERE murid_id = ? AND MONTH(tanggal) = ? AND YEAR(tanggal) = ?");
$monthQuery->bind_param("iii", $murid_id, $currentMonth, $currentYear);
$monthQuery->execute();
$monthAbsences = (int)$monthQuery->get_result()->fetch_assoc()['total'];

// --- Hitung alasan tidak masuk (jumlah) ---
$reasonQuery = $conn->prepare("
    SELECT 
        SUM(CASE WHEN status='sakit' THEN 1 ELSE 0 END) AS sakit,
        SUM(CASE WHEN status='izin' THEN 1 ELSE 0 END) AS izin,
        SUM(CASE WHEN status='alpha' THEN 1 ELSE 0 END) AS alpha
    FROM absensi
    WHERE murid_id = ?
");
$reasonQuery->bind_param("i", $murid_id);
$reasonQuery->execute();
$reasonData = $reasonQuery->get_result()->fetch_assoc();

$totalSakit = (int)($reasonData['sakit'] ?? 0);
$totalIzin = (int)($reasonData['izin'] ?? 0);
$totalAlpha = (int)($reasonData['alpha'] ?? 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Test Absensi</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
  <!-- Persentase Hadir Hari Ini -->
  <div class="bg-white p-6 rounded-lg shadow-md border-t-4 border-blue-400">
    <div class="flex justify-between items-center">
      <div>
        <h2 class="text-gray-600 font-semibold">Persentase Hadir Hari Ini</h2>
        <p class="text-3xl font-bold mt-2"><?= $percentHadirToday ?>%</p>
        <p class="text-sm text-gray-500 mt-1"><?= $hadirToday ?> dari <?= $totalToday ?> siswa hadir</p>
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

  <!-- Jumlah Absen Bulan Ini -->
  <div class="bg-white p-6 rounded-lg shadow-md border-t-4 border-red-400">
    <div class="flex justify-between items-center">
      <div>
        <h2 class="text-gray-600 font-semibold">Absensi Bulan Ini</h2>
        <p class="text-3xl font-bold mt-2"><?= $monthAbsences ?></p>
        <p class="text-sm text-gray-500 mt-1">Total absen siswa bulan ini</p>
      </div>
      <div class="text-red-500 bg-red-100 p-3 rounded-full">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
             viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                d="M8 7V3M16 7V3M3 11h18M5 19h14a2 2 0 002-2V7H3v10a2 2 0 002 2z"/>
        </svg>
      </div>
    </div>
  </div>

  <!-- Alasan Tidak Masuk -->
  <div class="bg-white p-6 rounded-lg shadow-md border-t-4 border-purple-500">
    <h2 class="text-gray-600 font-semibold mb-4">Alasan Tidak Masuk</h2>

    <div class="mb-4 flex justify-between">
      <span>Sakit</span>
      <span><?= $totalSakit ?></span>
    </div>
    <div class="w-full bg-gray-200 rounded-full h-2.5 mb-4">
      <div class="bg-purple-500 h-2.5 rounded-full" style="width: <?= $totalSakit > 0 ? '100%' : '0' ?>;"></div>
    </div>

    <div class="mb-4 flex justify-between">
      <span>Izin</span>
      <span><?= $totalIzin ?></span>
    </div>
    <div class="w-full bg-gray-200 rounded-full h-2.5 mb-4">
      <div class="bg-blue-500 h-2.5 rounded-full" style="width: <?= $totalIzin > 0 ? '100%' : '0' ?>;"></div>
    </div>

    <div class="mb-1 flex justify-between">
      <span>Tidak ada keterangan</span>
      <span><?= $totalAlpha ?></span>
    </div>
    <div class="w-full bg-gray-200 rounded-full h-2.5">
      <div class="bg-orange-400 h-2.5 rounded-full" style="width: <?= $totalAlpha > 0 ? '100%' : '0' ?>;"></div>
    </div>
  </div>
</div>

</body>
</html>
