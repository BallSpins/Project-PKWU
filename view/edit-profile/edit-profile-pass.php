<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Profile</title>
  <link rel="stylesheet" href="../../dist/output.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
    }
  </style>
</head>
<body class="bg-gray-100">

<!-- Header -->
<div class="flex justify-between max-w-screen mx-auto p-6 bg-indigo-600 items-center relative">
  <h1 class="ml-10 font-bold text-2xl text-white">Absensi Siswa</h1>

  <div class="relative mr-10">
    <!-- Tombol Untuk Trigger Dropdown -->
    <button onclick="toggleDropdown()" class="flex items-center focus:outline-none cursor-pointer">
      <img src="../../img/Chigiri_6.jpeg" alt="" class="w-10 h-10 rounded-full mr-2 border-2 border-white" />
      <p class="text-white">Chigiri Hyoma</p>
      <svg class="ml-1 w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.292l3.71-4.06a.75.75 0 011.08 1.04l-4.25 4.66a.75.75 0 01-1.08 0l-4.25-4.66a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
      </svg>
    </button>

    <!-- Dropdown -->
    <div id="dropdownMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-50">
      <a href="edit-profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Edit Profil</a>
      <a href="../login/logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</a>
    </div>
  </div>
</div>

  <main class="flex flex-col md:flex-row min-h-screen">
    
    <!-- Sidebar -->
    <aside class="w-full md:w-64 bg-gray-200 p-6 flex flex-col justify-between">
      <div>
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-lg font-bold">Account Settings</h2>
          <a href="../dashboard/dashboard-siswa.php" class="px-3 py-1 text-sm bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400">
            ← Kembali
          </a>
        </div>
        <a href="edit-profile.php" class="block w-full text-center bg-gray-300 text-gray-800 py-2 rounded-lg mb-3 hover:bg-gray-400">Edit Profile</a>
        <a href="edit-profile-pass.php" class="block w-full text-center bg-teal-500 text-white py-2 rounded-lg hover:bg-teal-600">Password</a>
      </div>
      <a href="../../auth/logout.php" class="mt-6 block w-full text-center bg-red-500 text-white py-2 rounded-lg hover:bg-red-600">Log out</a>
    </aside>

    <!-- Main Content -->
    <section class="flex-1 bg-white shadow-lg m-4 md:m-6 rounded-lg p-6 sm:p-8">
    <h2 class="text-lg sm:text-xl font-bold mb-6">Account Details</h2>

        <form class="grid gap-6">
        <!-- Username (atas sendiri) -->
        <div class="w-full md:w-1/2">
            <label class="block text-sm font-medium text-gray-700">Username</label>
            <input type="text" name="username" value="Chigiri Hyoma"
            class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm p-2 
                    focus:ring-indigo-500 focus:border-indigo-500" readonly>
        </div>

        <!-- Password (dibuat sejajar di bawah) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- New Password -->
            <div>
            <label class="block text-sm font-medium text-gray-700">New Password</label>
            <input type="password" name="password"
                class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm p-2 
                    focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <!-- Confirm Password -->
            <div>
            <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
            <input type="password" name="confirm_password"
                class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm p-2 
                    focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            
            <!-- Button -->
            <div>
            <button type="submit" class="w-1/2 bg-indigo-600 text-white font-semibold py-2 rounded-lg  hover:bg-indigo-700 transition cursor-pointer">Simpan</button>
        </div>
        </form>
    </section>

  <script src="../../backend/dropDown.js"></script>
</body>
</html>
