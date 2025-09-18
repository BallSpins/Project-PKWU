<?php
require_once('../../connection/connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $profilePic = ''; // default profile_pic = NULL

    if ($role === 'murid') {
        $email = $_POST['email'] ?? null; // admin input email
        $username = null; // biar aman, bisa diupdate nanti
        $stmt = $conn->prepare("INSERT INTO users (email, password, role, profile_pic) VALUES (?, ?, 'murid', ?)");
        $stmt->bind_param("sss", $email, $password, $profilePic);
        $stmt->execute();
    } elseif ($role === 'guru') {
        $username = $_POST['username'] ?? null; // admin input username
        $email = null; // biar aman, bisa diupdate nanti
        $stmt = $conn->prepare("INSERT INTO users (username, password, role, profile_pic) VALUES (?, ?, 'guru', ?)");
        $stmt->bind_param("sss", $username, $password, $profilePic);
        $stmt->execute();
    }

    echo "<p style='color: green;'>Akun $role berhasil ditambahkan!</p>";
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin - Tambah Akun</title>
  <link href="../../dist/output.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
  <div class="bg-white p-6 rounded shadow w-96">
    <h2 class="text-xl font-bold mb-4">Tambah Akun</h2>
    
    <form method="POST">
      <label class="block mb-2 text-sm">Role</label>
      <select name="role" id="role" class="border p-2 mb-4 w-full rounded" required>
        <option value="">Pilih Role</option>
        <option value="murid">Murid</option>
        <option value="guru">Guru</option>
      </select>

      <div id="muridFields" class="hidden">
        <label class="block mb-2 text-sm">Email</label>
        <input type="email" name="email" class="border p-2 mb-4 w-full rounded" placeholder="Tulis email disini..">
      </div>

      <div id="guruFields" class="hidden">
        <label class="block mb-2 text-sm">Username</label>
        <input type="text" name="username" class="border p-2 mb-4 w-full rounded" placeholder="Tulis username disini">
      </div>

      <label class="block mb-2 text-sm">Password</label>
      <input type="password" name="password" class="border p-2 mb-4 w-full rounded" required>

      <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded w-full">Tambah</button>
    </form>
  </div>

  <script>
    const roleSelect = document.getElementById('role');
    const muridFields = document.getElementById('muridFields');
    const guruFields = document.getElementById('guruFields');

    roleSelect.addEventListener('change', () => {
      muridFields.classList.add('hidden');
      guruFields.classList.add('hidden');
      if (roleSelect.value === 'murid') muridFields.classList.remove('hidden');
      else if (roleSelect.value === 'guru') guruFields.classList.remove('hidden');
    });
  </script>
</body>
</html>
