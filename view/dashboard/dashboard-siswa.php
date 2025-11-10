<?php
require_once('../../connection/connection.php');
session_start();

if(!isset($_SESSION['email'])) {
    header("Location: ../../auth/login1.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$query = "
    SELECT u.username, u.profile_pic, m.id AS murid_id
    FROM users u
    JOIN murid m ON u.id = m.user_id
    WHERE u.id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$dataUser = $result->fetch_assoc();

if (!$dataUser) {
    echo "Data murid tidak ditemukan.";
    exit;
}

$murid_id = $dataUser['murid_id'];
$_SESSION['username'] = $dataUser['username'];
$_SESSION['profile_pic'] = $dataUser['profile_pic'];

$username = $_SESSION['username'] ?? 'Murid';
$profilePic = !empty($_SESSION['profile_pic']) 
    ? "../../uploads/" . $_SESSION['profile_pic'] 
    : "../../img/Sunny_rd.jpg";

$today = date('Y-m-d');
$currentTime = date('H:i');

$cek = $conn->prepare("SELECT * FROM absensi WHERE murid_id = ? AND tanggal = ?");
$cek->bind_param("is", $murid_id, $today);
$cek->execute();
$absen = $cek->get_result()->fetch_assoc();
$pesan = $pesan ?? "";

if (!$absen && $currentTime > '06:45') {
    $status = 'alpha';
    $insert = $conn->prepare("INSERT INTO absensi (murid_id, tanggal, status) VALUES (?, ?, ?)");
    $insert->bind_param("iss", $murid_id, $today, $status);
    $insert->execute();

    $absen = ['status' => 'alpha'];
    $pesan = "⏰ Kamu telat! Absensi otomatis tercatat sebagai: Alpha";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$absen) {
    $status = $_POST['absen'] ?? null;

    $fotoAbsen = null;
    $fileSakit = null;
    $fileIzin  = null;

    if (!empty($_POST['fotoData'])) {
    $fotoData = $_POST['fotoData'];
    
    list($type, $data) = explode(';', $fotoData);
    list(, $data)      = explode(',', $data);
    
    $data = base64_decode($data);
    
    $targetDir = "../../uploads/absensi/";
    if(!is_dir($targetDir)) mkdir($targetDir, 0777, true);
    
    $fileName = time() . "_absen.jpg";
    $targetFile = $targetDir . $fileName;
    
    file_put_contents($targetFile, $data);
    $fotoAbsen = $fileName;
}

    if (!empty($_FILES['surat_sakit']['name'])) {
        $targetDir = "../../uploads/sakit/";
        if(!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $fileName = time() . "_" . basename($_FILES['surat_sakit']['name']);
        $targetFile = $targetDir . $fileName;
        move_uploaded_file($_FILES['surat_sakit']['tmp_name'], $targetFile);
        $fileSakit = $fileName;
    }

    if (!empty($_FILES['surat_izin']['name'])) {
        $targetDir = "../../uploads/izin/";
        if(!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $fileName = time() . "_" . basename($_FILES['surat_izin']['name']);
        $targetFile = $targetDir . $fileName;
        move_uploaded_file($_FILES['surat_izin']['tmp_name'], $targetFile);
        $fileIzin = $fileName;
    }

    if ($status) {
        $insert = $conn->prepare("INSERT INTO absensi (murid_id, tanggal, status, foto_absen, file_sakit, file_izin) VALUES (?, ?, ?, ?, ?, ?)");
        $insert->bind_param("isssss", $murid_id, $today, $status, $fotoAbsen, $fileSakit, $fileIzin);
        $insert->execute();
        header("Location: dashboard-siswa.php?success=1");
        exit;
    } else {
        $pesan = "❌ Pilih status absensi terlebih dahulu!";
    }
}

$chartQuery = $conn->prepare("
    SELECT tanggal, status 
    FROM absensi 
    WHERE murid_id = ? 
      AND tanggal >= DATE_SUB(NOW(), INTERVAL 6 DAY)
    ORDER BY tanggal ASC
");
$chartQuery->bind_param("i", $murid_id);
$chartQuery->execute();
$chartResult = $chartQuery->get_result();

$labels = [];
$dataHadir = [];
$dataSakit = [];
$dataIzin = [];
$dataAlpha = [];

for ($i = 6; $i >= 0; $i--) {
    $day = date('Y-m-d', strtotime("-$i days"));
    $labels[] = $day;
    $dataHadir[$day] = 0;
    $dataSakit[$day] = 0;
    $dataIzin[$day] = 0;
    $dataAlpha[$day] = 0;
}

while ($row = $chartResult->fetch_assoc()) {
    $tgl = $row['tanggal'];
    if (isset($dataHadir[$tgl])) {
        $status = $row['status'];
        if ($status === 'hadir') $dataHadir[$tgl] = 1;
        if ($status === 'sakit') $dataSakit[$tgl] = 1;
        if ($status === 'izin') $dataIzin[$tgl] = 1;
        if ($status === 'alpha') $dataAlpha[$tgl] = 1;
    }
}

$totalQuery = $conn->prepare("SELECT COUNT(*) AS total FROM absensi WHERE murid_id = ?");
$totalQuery->bind_param("i", $murid_id);
$totalQuery->execute();
$total = (int)$totalQuery->get_result()->fetch_assoc()['total'];

$hadirQuery = $conn->prepare("SELECT COUNT(*) AS hadir FROM absensi WHERE murid_id = ? AND status='hadir'");
$hadirQuery->bind_param("i", $murid_id);
$hadirQuery->execute();
$hadir = (int)$hadirQuery->get_result()->fetch_assoc()['hadir'];

$percentHadirSepanjangMasa = $total > 0 ? round(($hadir / $total) * 100) : 0;

$currentMonth = date('m');
$currentYear = date('Y');

$monthQuery = $conn->prepare("SELECT COUNT(*) AS total FROM absensi WHERE murid_id = ? AND MONTH(tanggal) = ? AND YEAR(tanggal) = ?");
$monthQuery->bind_param("iii", $murid_id, $currentMonth, $currentYear);
$monthQuery->execute();
$monthAbsences = (int)$monthQuery->get_result()->fetch_assoc()['total'];

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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard Siswa</title>
  <link href="../../dist/output.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style> body { font-family: 'Poppins', sans-serif; } </style>
</head>
<body class="bg-gray-100">

<div class="flex justify-between max-w-screen mx-auto p-6 bg-indigo-600 items-center relative">
  <h1 class="ml-10 font-bold text-2xl text-white">STUDENT ABSENCE</h1>
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

<div class="max-w-7xl mx-auto mt-10 px-6 flex flex-col lg:flex-row gap-6">

  <div class="bg-white rounded-lg shadow-md p-6 w-full lg:w-1/3 border border-teal-300">
    <h2 class="text-center font-semibold mb-4">Absensi Sekolah</h2>

    <?php if($pesan): ?>
      <div class="bg-blue-100 text-blue-700 p-3 rounded mb-4 text-center">
        <?= htmlspecialchars($pesan) ?>
      </div>
    <?php endif; ?>
    
    <?php if(!$absen): ?>
      <form method="POST" enctype="multipart/form-data">
        <label class="flex items-center justify-between border border-gray-300 rounded-md px-4 py-2 mb-3 cursor-pointer hover:bg-gray-50">
          <div class="flex items-center gap-3">
            <img src="https://img.icons8.com/ios/50/calendar--v1.png" class="w-6 h-6"/>
            <span class="font-medium">Hadir</span>
          </div>
          <input type="radio" name="absen" value="hadir" class="form-radio accent-blue-600" onclick="handleToggleAbsen()" />
        </label>

        <label class="flex items-center justify-between border border-gray-300 rounded-md px-4 py-2 mb-3 cursor-pointer hover:bg-gray-50">
          <div class="flex items-center gap-3">
            <img src="https://img.icons8.com/ios/50/person-female.png" class="w-6 h-6" />
            <span class="font-medium">Sakit</span>
          </div>
          <input type="radio" name="absen" value="sakit" class="form-radio accent-blue-600" onclick="handleToggleAbsen()" />
        </label>

        <label class="flex items-center justify-between border border-gray-300 rounded-md px-4 py-2 mb-3 cursor-pointer hover:bg-gray-50">
          <div class="flex items-center gap-3">
            <img src="https://img.icons8.com/ios/50/mail.png" class="w-6 h-6" />
            <span class="font-medium">Izin</span>
          </div>
          <input type="radio" name="absen" value="izin" class="form-radio accent-blue-600" onclick="handleToggleAbsen()" />
        </label>

        <div id="absenCam" class="hidden">
          <label class="block mb-2 text-sm font-medium text-gray-700">Ambil Foto</label>
          <input type="file" id="cameraInput" accept="image/*" capture="camera" class="border border-gray-300 p-2 mb-4 w-full rounded" />
          <canvas id="preview" class="hidden"></canvas>
          <input type="hidden" id="fotoData" name="fotoData" />
        </div>  
        <div id="sakitFile" class="hidden">
          <label class="block mb-2 text-sm font-medium text-gray-700">Upload Surat Dokter</label>
          <input type="file" name="surat_sakit" class="border border-gray-300 p-2 mb-4 w-full rounded" />
        </div>
        <div id="alasanBox" class="hidden">
          <label class="block mb-2 text-sm font-medium text-gray-700">Upload Surat Izin</label>
          <input type="file" name="surat_izin" class="border border-gray-300 p-2 mb-4 w-full rounded" />
        </div>

        <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-2 rounded-md hover:bg-blue-700 transition cursor-pointer">
          Kirim Absensi
        </button>
      </form>
    <?php else: ?>
      <div class="bg-green-100 text-green-700 p-3 rounded text-center">
        ✅ Kamu sudah mengisi absensi hari ini dengan status: <strong><?= htmlspecialchars($absen['status']) ?></strong>
      </div>
    <?php endif; ?>
  </div>

  <div class="bg-white rounded-lg shadow-md p-6 w-full lg:w-2/3">
    <h3 class="font-semibold text-lg mb-4">Chart Absen Mingguan</h3>
    <div class="chart-container bg-gray-50 rounded p-3">
      <canvas id="weeklyChart" width="800" height="300"></canvas>
    </div>
  </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
  <div class="bg-white p-6 rounded-lg shadow-md border-t-4 border-blue-400">
    <div class="flex justify-between items-center">
      <div>
        <h2 class="text-gray-600 font-semibold">Persentase Hadir Sepanjang Masa</h2>
        <p class="text-3xl font-bold mt-2"><?= $percentHadirSepanjangMasa ?>%</p>
        <p class="text-sm text-gray-500 mt-1"><?= $hadir ?> kali hadir ke sekolah</p>
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
<script>
  const chartData = {
    labels: <?= json_encode(array_values($labels)) ?>,
    hadir: <?= json_encode(array_values($dataHadir)) ?>,
    sakit: <?= json_encode(array_values($dataSakit)) ?>,
    izin: <?= json_encode(array_values($dataIzin)) ?>,
    alpha: <?= json_encode(array_values($dataAlpha)) ?>
  };
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="../../backend/dashboard-function.js"></script>
<script src="../../backend/dashboard-chart.js"></script>
</body>
</html>