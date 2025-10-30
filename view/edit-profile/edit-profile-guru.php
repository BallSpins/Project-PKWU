<?php
require_once('../../connection/connection.php');
session_start();

if(!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data user + murid (sesuai table baru)
$sql = "SELECT u.username, u.email, u.profile_pic, g.nip, g.nama_lengkap 
        FROM users u
        JOIN guru g ON u.id = g.user_id
        WHERE u.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc() ?? [];

// Ambil username & profile pic
$username = $data['username'] ?? 'Murid';
$_SESSION['username'] = $username;
$_SESSION['profile_pic'] = $data['profile_pic'] ?? null;

$profilePic = !empty($_SESSION['profile_pic']) 
    ? "../../uploads/".$_SESSION['profile_pic'] 
    : "../../img/Sunny_rd.jpg";

$nama       = $data['nama_lengkap'] ?? '';
$nip        = $data['nip'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Profile</title>
  <link rel="stylesheet" href="../../dist/output.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style> body { font-family: 'Poppins', sans-serif; } </style>
</head>
<body class="bg-gray-100">

<!-- Header -->
<div class="flex justify-between max-w-screen mx-auto p-6 bg-indigo-600 items-center relative">
  <h1 class="ml-10 font-bold text-2xl text-white">Absensi Siswa</h1>
  <div class="relative mr-10">
    <button onclick="toggleDropdown()" class="flex items-center focus:outline-none cursor-pointer">
      <img src="<?php echo htmlspecialchars($profilePic); ?>" alt="" class="w-10 h-10 rounded-full mr-2 border-2 border-white" />
      <p class="text-white"><?php echo htmlspecialchars($username); ?></p>
      <svg class="ml-1 w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.292l3.71-4.06a.75.75 0 011.08 1.04l-4.25 4.66a.75.75 0 01-1.08 0l-4.25-4.66a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
      </svg>
    </button>
    <div id="dropdownMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-50">
      <a href="edit-profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Edit Profil</a>
      <a href="../../auth/logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</a>
    </div>
  </div>
</div>

<main class="flex flex-col md:flex-row min-h-screen">

  <!-- Sidebar -->
  <aside class="w-full md:w-64 bg-gray-200 p-6 flex flex-col justify-between">
    <div>
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-bold">Account Settings</h2>
        <a href="../dashboard/dashboard-guru.php" class="px-3 py-1 text-sm bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400">← Kembali</a>
      </div>
      <a href="edit-profile-guru.php" class="block w-full text-center bg-teal-500 text-white py-2 rounded-lg mb-3 hover:bg-teal-600">Edit Profile</a>
      <a href="edit-profile-guru-pass.php" class="block w-full text-center bg-gray-300 text-gray-800 py-2 rounded-lg hover:bg-gray-400">Password</a>
    </div>
    <a href="../../auth/logout.php" class="mt-6 block w-full text-center bg-red-500 text-white py-2 rounded-lg hover:bg-red-600">Log out</a>
  </aside>

  <!-- Main Content -->
  <section class="flex-1 bg-white shadow-lg m-4 md:m-6 rounded-lg p-6 sm:p-8">
    <h2 class="text-lg sm:text-xl font-bold mb-6">Account</h2>

    <form action="../../backend/update-profile-guru.php" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 gap-6">
      
      <!-- Profile Picture -->
      <div class="flex items-center space-x-6">
        <img src="<?php echo htmlspecialchars($profilePic); ?>" 
             alt="Profile Picture" 
             class="w-16 h-16 sm:w-20 sm:h-20 rounded-full border border-white">
        <div>
          <label class="block text-sm font-medium text-gray-700">Profile Picture</label>
          <input type="file" name="profile_picture" class="mt-2 block w-full text-sm text-gray-700">
          <p class="text-xs text-gray-500">JPEG, PNG under 5mb</p>
        </div>
      </div>

      <!-- Full Name -->
      <div>
        <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
        <input type="text" name="nama" value="<?php echo htmlspecialchars($nama); ?>" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm p-2">
      </div>

      <!-- NIP -->
      <div>
        <label class="block text-sm font-medium text-gray-700">NIP</label>
        <input type="text" name="nip" value="<?php echo htmlspecialchars($nip); ?>" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm p-2">
      </div>

      <!-- Submit -->
      <div>
        <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 cursor-pointer">Simpan</button>
      </div>
    </form>
  </section>
</main>

<script src="../../backend/dropDown.js"></script>
</body>
</html>
