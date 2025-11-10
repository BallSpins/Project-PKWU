<?php
require_once('../../connection/connection.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT username, email, profile_pic FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc() ?? [];

$username = $data['username'] ?? 'Admin';
$profilePic = !empty($data['profile_pic'])
    ? "../../uploads/" . $data['profile_pic']
    : "../../img/Sunny rd.jpg";

$_SESSION['username'] = $username;
$_SESSION['profile_pic'] = $data['profile_pic'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Profil Admin</title>
  <link rel="stylesheet" href="../../dist/output.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Poppins', sans-serif; }
  </style>
</head>
<body class="bg-gray-100">

<div class="flex justify-between max-w-screen mx-auto p-6 bg-indigo-600 items-center relative">
  <h1 class="ml-10 font-bold text-2xl text-white">Admin Dashboard</h1>
  <div class="relative mr-10">
    <button onclick="toggleDropdown()" class="flex items-center focus:outline-none cursor-pointer">
      <img src="<?php echo htmlspecialchars($profilePic); ?>" alt="Profile Picture" class="w-10 h-10 rounded-full mr-2 border-2 border-white" />
      <p class="text-white"><?php echo htmlspecialchars($username); ?></p>
      <svg class="ml-1 w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.292l3.71-4.06a.75.75 0 011.08 1.04l-4.25 4.66a.75.75 0 01-1.08 0l-4.25-4.66a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
      </svg>
    </button>
    <div id="dropdownMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-50">
      <a href="edit-profile-admin.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Edit Profil</a>
      <a href="../../auth/logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</a>
    </div>
  </div>
</div>

<main class="flex flex-col md:flex-row min-h-screen">

  <aside class="w-full md:w-64 bg-gray-200 p-6 flex flex-col justify-between">
    <div>
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-bold">Account Settings</h2>
        <button onclick="history.back()" class="px-4 py-2 bg-gray-300 rounded-md hover:bg-gray-400 transition">Kembali</>

      </div>
      <a href="edit-profile-admin.php" class="block w-full text-center bg-teal-500 text-white py-2 rounded-lg mb-3 hover:bg-teal-600">Edit Profile</a>
    </div>
    <a href="../../auth/logout.php" class="mt-6 block w-full text-center bg-red-500 text-white py-2 rounded-lg hover:bg-red-600">Log out</a>
  </aside>

  <section class="flex-1 bg-white shadow-lg m-4 md:m-6 rounded-lg p-6 sm:p-8">
    <h2 class="text-lg sm:text-xl font-bold mb-6">Edit Profil Admin</h2>

    <form action="../../backend/update-profile-admin.php" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 gap-6">
      
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

      <div>
        <label class="block text-sm font-medium text-gray-700">Username</label>
        <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm p-2">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Password Baru</label>
        <input type="password" name="password" placeholder="Kosongkan jika tidak ingin mengganti" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm p-2">
      </div>

      <div>
        <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 cursor-pointer">Simpan</button>
      </div>
    </form>
  </section>
</main>

<script src="../../backend/dropDown.js"></script>
</body>
</html>
