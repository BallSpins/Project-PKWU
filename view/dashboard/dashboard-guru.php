<?php
session_start();
require_once('../../connection/connection.php');

if(!isset($_SESSION['username'])) {
    header("Location: ../../auth/login1.php");
    exit;
}

$username = $_SESSION['username'];
$profilePic = !empty($_SESSION['profile_pic']) 
    ? "../../uploads/" . $_SESSION['profile_pic'] 
    : "../../img/Sunny_rd.jpg";

if(isset($_POST['delete_id'])){
    $delete_id = $_POST['delete_id'];

    $absInfo = $conn->query("SELECT m.nama_lengkap, a.tanggal, a.status 
                             FROM absensi a 
                             JOIN murid m ON a.murid_id = m.id 
                             WHERE a.id = $delete_id")->fetch_assoc();

    $stmt = $conn->prepare("DELETE FROM absensi WHERE id=?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();

    $user_id = $_SESSION['user_id'];
    $murid_nama = $absInfo['nama_lengkap'] ?? 'Unknown';
    $tanggal = $absInfo['tanggal'] ?? '-';
    $status = $absInfo['status'] ?? '-';

    $action = "Hapus Data Absensi";
    $details = "Guru ($username) menghapus absensi murid '$murid_nama' untuk tanggal $tanggal (status: $status).";

    $log_stmt = $conn->prepare("INSERT INTO activity_log (user_id, action, details) VALUES (?, ?, ?)");
    $log_stmt->bind_param("iss", $user_id, $action, $details);
    $log_stmt->execute();
    $log_stmt->close();

    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

$absensi = $conn->query("SELECT a.*, m.nama_lengkap, m.kelas, m.jurusan 
                         FROM absensi a 
                         JOIN murid m ON a.murid_id = m.id 
                         ORDER BY a.tanggal DESC");

$today = date('Y-m-d');
$total_today = $conn->query("SELECT COUNT(*) as cnt FROM absensi WHERE tanggal='$today'")->fetch_assoc()['cnt'];
$total_month = $conn->query("SELECT COUNT(*) as cnt FROM absensi WHERE MONTH(tanggal)=MONTH('$today') AND YEAR(tanggal)=YEAR('$today')")->fetch_assoc()['cnt'];

$reasons = ['sakit'=>0,'izin'=>0,'alpha'=>0];
$res = $conn->query("SELECT status, COUNT(*) as cnt FROM absensi GROUP BY status");
$total_abs = 0;
while($row = $res->fetch_assoc()){
    $reasons[$row['status']] = $row['cnt'];
    $total_abs += $row['cnt'];
}
foreach($reasons as $k=>$v){
    $reasons[$k] = $total_abs>0 ? round($v/$total_abs*100) : 0;
}

$weekly = [];
for($i=6;$i>=0;$i--){
    $date = date('Y-m-d', strtotime("-$i days"));
    $cnt = $conn->query("SELECT COUNT(*) as cnt FROM absensi WHERE tanggal='$date'")->fetch_assoc()['cnt'];
    $weekly['labels'][] = date('D', strtotime($date));
    $weekly['data'][] = $cnt;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Guru Absence Dashboard</title>
<link rel="stylesheet" href="../../dist/output.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-50">

<header class="bg-indigo-600 text-white shadow-md">
<div class="container mx-auto px-4 py-4 flex justify-between items-center">
    <div class="flex items-center space-x-2">
        <h1 class="text-2xl font-bold">ABSENCE DASHBOARD (Guru)</h1>
    </div>
    <div class="relative">
        <button onclick="toggleDropdown()" class="flex items-center focus:outline-none cursor-pointer">
            <img src="<?php echo htmlspecialchars($profilePic); ?>" alt="Profile Picture" class="w-10 h-10 rounded-full mr-2 border-2 border-white"/>
            <p class="text-white mr-2"><?php echo htmlspecialchars($username); ?></p>
            <svg class="ml-1 w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.292l3.71-4.06a.75.75 0 011.08 1.04l-4.25 4.66a.75.75 0 01-1.08 0l-4.25-4.66a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
            </svg>
        </button>
        <div id="dropdownMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-50">
            <a href="../edit-profile/edit-profile-guru.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Edit Profil</a>
            <a href="../../auth/logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</a>
        </div>
    </div>
</div>
</header>

<main class="container mx-auto px-4 py-6 space-y-6">

<div class="flex flex-col lg:flex-row gap-6">
    <div class="lg:w-1/3 space-y-6">
        <div class="grid grid-cols-1 gap-4">
            <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-blue-500">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-gray-500">Today's Absences</p>
                        <p class="text-3xl font-bold"><?php echo $total_today; ?></p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-calendar-day text-blue-500 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-red-500">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-gray-500">This Month</p>
                        <p class="text-3xl font-bold"><?php echo $total_month; ?></p>
                    </div>
                    <div class="bg-red-100 p-3 rounded-full">
                        <i class="fas fa-calendar-alt text-red-500 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-purple-500">
                <h3 class="font-semibold mb-4">Absence Reasons</h3>
                <?php foreach($reasons as $reason=>$percent): ?>
                <div class="flex justify-between">
                    <span><?php echo ucfirst($reason); ?></span><span class="font-semibold"><?php echo $percent; ?>%</span>
                </div>
                <div class="h-2 bg-gray-200 rounded-full overflow-hidden mb-2">
                    <div class="h-full <?php
                        echo $reason=='sakit'?'bg-purple-500':
                             ($reason=='izin'?'bg-blue-500':'bg-red-500'); ?> "
                         style="width:<?php echo $percent; ?>%"></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="lg:w-2/3">
        <div class="bg-white p-6 rounded-lg shadow-md">
          <h3 class="font-semibold mb-4">Weekly Absence Trend</h3>
          <canvas id="weeklyChart" class="w-full h-64"></canvas>
        </div>
    </div>
</div>

<div class="bg-white p-6 rounded-lg shadow-md w-full overflow-x-auto">
    <h3 class="font-semibold mb-4">Data Absensi Siswa</h3>
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kelas</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jurusan</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Foto</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php while($row = $absensi->fetch_assoc()): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4"><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                <td class="px-6 py-4"><?php echo htmlspecialchars($row['kelas']); ?></td>
                <td class="px-6 py-4"><?php echo htmlspecialchars($row['jurusan']); ?></td>
                <td class="px-6 py-4">
                    <span class="px-2 py-1 rounded-full text-xs <?php
                        echo $row['status']=='hadir'?'bg-green-100 text-green-800':
                             ($row['status']=='sakit'?'bg-purple-100 text-purple-800':
                             ($row['status']=='izin'?'bg-blue-100 text-blue-800':'bg-red-100 text-red-800')); ?>">
                        <?php echo ucfirst($row['status']); ?>
                    </span>
                </td>
                <td class="px-6 py-4"><?php echo $row['tanggal']; ?></td>
                <td class="px-6 py-4">
                <?php 
                $fotoPath = '';
                if (!empty($row['foto_absen']) && $row['status']=='hadir') {
                    $fotoPath = "../../uploads/absensi/" . $row['foto_absen'];
                } elseif (!empty($row['file_sakit']) && $row['status']=='sakit') {
                    $fotoPath = "../../uploads/sakit/" . $row['file_sakit'];
                } elseif (!empty($row['file_izin']) && $row['status']=='izin') {
                    $fotoPath = "../../uploads/izin/" . $row['file_izin'];
                }

                if ($fotoPath):
                ?>
                    <a href="<?php echo htmlspecialchars($fotoPath); ?>" target="_blank" class="text-blue-600 hover:text-blue-900 underline">
                        Lihat Foto
                    </a>
                <?php else: ?>
                    -
                <?php endif; ?>
            </td>
                <td class="px-6 py-4">
                    <form method="post" onsubmit="return confirm('Yakin ingin menghapus record ini?');">
                        <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</main>
<script src="../../backend/dashboard-guru.js"></script>
<script>
const ctx = document.getElementById('weeklyChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($weekly['labels']); ?>,
        datasets: [{
            label: 'Jumlah Absen',
            data: <?php echo json_encode($weekly['data']); ?>,
            backgroundColor: 'rgba(99,102,241,0.2)',
            borderColor: 'rgba(99,102,241,1)',
            borderWidth: 2,
            tension: 0.3,
            fill: true,
            pointRadius:5,
            pointBackgroundColor:'rgba(99,102,241,1)'
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, stepSize: 1 } }
    }
});
</script>

</body>
</html>
