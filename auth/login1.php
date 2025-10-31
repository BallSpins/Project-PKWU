<?php
session_start();
require_once '../connection/connection.php'; 

$errors = ['guru' => '', 'murid' => ''];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Login Form Guru
    if (isset($_POST['guru'])) {
        $username = $_POST['username'] ?? '';
        $pass = $_POST['pass'] ?? '';

        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if (!$user) {
            $errors['guru'] = "Username tidak ditemukan!";
        } elseif (!password_verify($pass, $user['password'])) {
            $errors['guru'] = "Password salah!";
        } else {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['role']     = $user['role'];
            $_SESSION['username'] = $user['username'];

            // Cek role
            if ($user['role'] === 'admin') {
                header("Location: ../view/dashboard/dashboard-admin.php");
            } elseif ($user['role'] === 'guru') {
                header("Location: ../view/dashboard/dashboard-guru.php");
            } exit;
        }
    }

    // Login Form Murid
    elseif (isset($_POST['murid'])) {
        $email = $_POST['email'] ?? '';
        $pass1 = $_POST['pass1'] ?? '';

        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if (!$user) {
            $errors['murid'] = "Email tidak ditemukan!";
        } elseif (!password_verify($pass1, $user['password'])) {
            $errors['murid'] = "Password salah!";
        } else {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['role']     = $user['role'];
            $_SESSION['email']    = $user['email'];

            // Cek role
            if ($user['role'] === 'admin') {
                header("Location: ../view/dashboard/dashboard-admin.php");
            } elseif ($user['role'] === 'murid') {
                header("Location: ../view/dashboard/dashboard-siswa.php");
            } exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login - Guru & Murid</title>
  <link rel="stylesheet" href="../dist/output.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
    }
    @keyframes fadeZoom {
      0% { opacity: 0; transform: scale(0.95); }
      100% { opacity: 1; transform: scale(1); }
    }
    .animate-fadeZoom {
      animation: fadeZoom 0.5s ease-out;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: scale(0.98); }
      to { opacity: 1; transform: scale(1); }
    }
    .animate-fadeIn {
      animation: fadeIn 0.4s ease-out;
    }
  </style>
</head>
<body class="bg-gradient-to-br from-purple-100 to-blue-100 min-h-screen flex items-center justify-center">

  <div class="bg-white shadow-2xl rounded-2xl overflow-hidden w-full max-w-4xl grid grid-cols-1 md:grid-cols-2 transition-all duration-700">
    <div class="p-8 flex flex-col justify-center items-center md:border-r md:border-gray-200">
      <div id="login-form" class="hidden w-full animate-fadeIn">
        <h2 class="text-2xl font-bold text-gray-700 mb-6">Login Sebagai Guru</h2>
        <form action="" method="POST" class="space-y-4">
          <input required name="username" type="text" placeholder="Username"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-400">
          <input required name="pass" type="password" placeholder="Password"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-400">
          <?php if ($errors['guru']) echo "<p class='text-red-500 text-center'>{$errors['guru']}</p>"; ?>
          <button name="guru" value="1" class="w-full bg-purple-600 hover:bg-purple-700 text-white py-2 rounded-lg transition cursor-pointer">Login</button>
        </form>
        <p class="mt-4 text-sm text-gray-500 text-right">Login Sebagai Murid?
          <button onclick="switchToRegister()" class="text-blue-600 font-semibold hover:underline cursor-pointer">Saya Murid</button>
        </p>
      </div>

      <img id="left-image" src="../img/ilya-pavlov-OqtafYT5kTw-unsplash.jpg"
        class="rounded-lg shadow-md transition-all duration-700 w-4/5" alt="Login Illustration" />
    </div>

    <div class="p-8 flex flex-col justify-center items-center">
      <div id="register-form" class="w-full animate-fadeIn">
        <h2 class="text-2xl font-bold text-gray-700 mb-6">Login Sebagai Murid</h2>
        <form action="" method="POST" class="space-y-4" name="murid">
          <input required name="email" type="email" placeholder="Email"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
          <input required name="pass1" type="password" placeholder="Password"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
          <?php if ($errors['murid']) echo "<p class='text-red-500 text-center'>{$errors['murid']}</p>"; ?>
          <button name="murid" value="1" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg transition cursor-pointer">Login</button>
        </form>
        <p class="mt-4 text-sm text-gray-500 text-right">Login Sebagai Guru?
          <button onclick="switchToLogin()" class="text-purple-600 font-semibold hover:underline cursor-pointer">Saya Guru</button>
        </p>
      </div>

      <img id="right-image" src="../img/ilya-pavlov-OqtafYT5kTw-unsplash.jpg"
        class="rounded-lg hidden shadow-md transition-all duration-700 w-4/5" alt="Register Illustration" />
    </div>
  </div>

<script>
  function switchToLogin() {
    document.getElementById("register-form").classList.add("hidden");
    document.getElementById("right-image").classList.remove("hidden");

    document.getElementById("left-image").classList.add("hidden");
    document.getElementById("login-form").classList.remove("hidden");
    document.getElementById("login-form").classList.add("animate-fadeIn");
  }

  function switchToRegister() {
    document.getElementById("login-form").classList.add("hidden");
    document.getElementById("left-image").classList.remove("hidden");

    document.getElementById("right-image").classList.add("hidden");
    document.getElementById("register-form").classList.remove("hidden");
    document.getElementById("register-form").classList.add("animate-fadeIn");
  }
</script>
</body>
</html>
