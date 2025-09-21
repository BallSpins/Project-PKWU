<?php
require_once '../../connection/connection.php'; // koneksi mysqli $conn

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'];
    $email = $_POST['email'] ?? null;
    $username = $_POST['username'] ?? null;
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    if ($role === 'murid') {
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'murid')");
        $stmt->bind_param("sss", $email, $email, $password); 
        // username = email (sementara), email = email
        $stmt->execute();
    } elseif ($role === 'guru') {
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'guru')");
        $stmt->bind_param("sss", $username, $username, $password); 
        // email = sama dengan username (sementara)
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
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
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
        <input type="email" name="email" class="border p-2 mb-4 w-full rounded">
      </div>

      <div id="guruFields" class="hidden">
        <label class="block mb-2 text-sm">Username</label>
        <input type="text" name="username" class="border p-2 mb-4 w-full rounded">
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
      if (roleSelect.value === 'murid') {
        muridFields.classList.remove('hidden');
      } else if (roleSelect.value === 'guru') {
        guruFields.classList.remove('hidden');
      }
    });
  </script>
</body>
</html>
