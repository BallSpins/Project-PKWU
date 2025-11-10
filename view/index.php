<?php
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Absensi Sekolah</title>
  <link href="../dist/output.css" rel="stylesheet">

  <style>
    body {
      font-family: 'Inter', sans-serif;
      background: linear-gradient(135deg, rgba(41, 32, 177, 1), rgba(63, 81, 181, 0.9));
      color: white;
    }
  </style>
</head>
<body class="min-h-screen flex flex-col overflow-x-hidden">

  <nav class="w-full flex items-center justify-between px-10 py-6 bg-transparent backdrop-blur-md">
    <div class="text-3xl font-extrabold tracking-wide">
      Student<span class="text-blue-300">Absence</span>
    </div>
  </nav>

  <main class="flex-grow flex items-center justify-center px-10 md:px-20 py-10">
    <div class="max-w-7xl w-full grid grid-cols-1 md:grid-cols-2 gap-10 items-center">

      <div class="space-y-6 animate-fadeInUp">
        <h1 class="text-5xl md:text-6xl font-extrabold leading-tight">
          Student Absence<br>
          lebih <span class="text-blue-300">mudah</span> dan <span class="text-blue-500">modern</span>.
        </h1>

        <p class="text-blue-100 text-lg max-w-md">
          Kelola kehadiran siswa dan guru secara digital — cepat, akurat, dan bisa diakses kapan pun dengan tampilan yang sederhana dan menarik.
        </p>

        <div class="flex flex-wrap gap-4 pt-4">
          <a href="../auth/login1.php" class="bg-white text-blue-700 font-semibold px-6 py-3 rounded-lg shadow hover:bg-blue-50 hover:scale-105 transition duration-300">
            Masuk Sekarang
          </a>
        </div>
      </div>

      <div class="flex justify-center relative animate-fadeIn">
        <div class="absolute -z-10 w-[400px] h-[400px] bg-blue-500/30 rounded-full blur-3xl"></div>
        <img 
          src="../img/buku.jpeg" 
          alt="Ilustrasi Absensi"
          class="w-[400px] md:w-[500px] rounded-none drop-shadow-2xl hover:scale-105 transition duration-500"
        >
      </div>
    </div>
  </main>

  <footer class="py-6 text-center text-blue-100 text-sm border-t border-white/20">
    © 2025 AbsensiSekolah. Semua Hak Dilindungi.
  </footer>

  <style>
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .animate-fadeIn { animation: fadeIn 1.5s ease-out; }
    .animate-fadeInUp { animation: fadeIn 1.5s ease-out; }
  </style>
</body>
</html>