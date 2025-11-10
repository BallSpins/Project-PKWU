<?php
require_once('../../connection/connection.php');
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../../auth/login1.php");
  exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT username, profile_pic, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$username = $user['username'] ?? 'Guest';
$profilePic = !empty($user['profile_pic']) ? '../../uploads/' . $user['profile_pic'] : '../../img/Sunny rd.jpg';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
  $uname = trim($_POST['username']);
  $email = trim($_POST['email']);
  $pass  = password_hash($_POST['password'], PASSWORD_DEFAULT);
  $role  = $_POST['role'];

  $uname = $uname === '' ? NULL : $uname;
  $email = $email === '' ? NULL : $email;

    if ($uname === NULL) {
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (NULL, ?, ?, ?)");
    $stmt->bind_param("sss", $email, $pass, $role);
  } elseif ($email === NULL) {
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, NULL, ?, ?)");
    $stmt->bind_param("sss", $uname, $pass, $role);
  } else {
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $uname, $email, $pass, $role);
  }

  $stmt->execute();
  header("Location: kelola-akun.php?added=1");
  exit;
}

if (isset($_GET['delete'])) {
  $id = intval($_GET['delete']);
  $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  header("Location: kelola-akun.php?deleted=1");
  exit;
}

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$totalQuery = $conn->query("SELECT COUNT(*) AS total FROM users");
$totalUsers = $totalQuery->fetch_assoc()['total'];
$totalPages = ceil($totalUsers / $limit);

$stmt = $conn->prepare("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC LIMIT ?, ?");
$stmt->bind_param("ii", $offset, $limit);
$stmt->execute();
$users = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kelola Akun - Admin</title>
<link rel="stylesheet" href="../../dist/output.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
  body { font-family: 'Poppins', sans-serif; background: #f5f6ff; overflow-x: hidden; }

  /* Sidebar */
  #sidebar { transition: transform 0.3s ease, opacity 0.3s ease; transform: translateX(-100%); opacity: 0; z-index:40; }
  #sidebar.active { transform: translateX(0); opacity: 1; }
  #overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.3); z-index:30; opacity:0; transition: opacity 0.3s ease; }
  #overlay.active { display:block; opacity:1; }
  #mainContent { transition: margin-left 0.3s ease; margin-left:0; }

  @media(min-width:1024px){
    #sidebar { transform: translateX(0); opacity:1; }
    #mainContent { margin-left:16rem; }
    #overlay { display:none !important; }
    #toggleSidebar { display:none; }
  }

  #modalOverlay { display:none; position:fixed; inset:0; z-index:50; justify-content:center; align-items:center; backdrop-filter:blur(10px); background:rgba(255,255,255,0.25); }
  #modalOverlay.active { display:flex; }
  #modal { background:rgba(255,255,255,0.8); backdrop-filter:blur(12px); border-radius:1rem; max-width:400px; width:90%; padding:1.5rem; box-shadow:0 8px 32px rgba(0,0,0,0.2); animation:popIn 0.3s ease; }
  @keyframes popIn { from{transform:scale(0.95); opacity:0} to{transform:scale(1); opacity:1} }

 .table-container { overflow-x:auto; }

 @media (max-width: 767px) {
   .table-container {
     overflow-x: hidden;
   }

   .table-container thead {
     display: none;
   }

   .table-container tr {
     display: block;
     border-width: 1px;
     border-color: #e2e8f0;
     border-radius: 0.75rem;
     margin-bottom: 1rem;
     box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
     overflow: hidden;
     background: #ffffff;
   }
   
   .table-container tr:last-child {
       margin-bottom: 0;
   }

   .table-container td {
     display: flex;
     justify-content: space-between;
     align-items: center;
     padding: 0.75rem 1rem;
     text-align: right;
     border-bottom: 1px solid #f3f4f6;
   }
   
   .table-container tr:last-child td:last-child {
       border-bottom: 0;
   }

   .table-container td::before {
     content: attr(data-label);
     font-weight: 600;
     text-align: left;
     color: #4b5563;
     padding-right: 1rem;
   }

   .table-container td[data-label="Aksi"] {
     justify-content: center;
   }
   .table-container td[data-label="Aksi"]::before {
     display: none;
   }
   
   .table-container td[data-label="No"] {
     background-color: #f9fafb;
     font-weight: 700;
     color: #4f46e5;
   }
 }
</style>
</head>
<body>
<div id="overlay"></div>
<div class="flex min-h-screen overflow-hidden">

  <aside id="sidebar" class="w-64 bg-indigo-700 text-white flex flex-col justify-between fixed inset-y-0 left-0 z-40">
    <div>
      <div class="p-6 border-b border-indigo-500 flex justify-between items-center">
        <h1 class="font-bold text-2xl">Admin</h1>
        <button id="closeSidebar" class="lg:hidden text-indigo-200 hover:text-white">✕</button>
      </div>
      <nav class="mt-6 space-y-1">
        <a href="../dashboard/dashboard-admin.php" class="flex items-center px-6 py-3 hover:bg-indigo-600 transition">Dashboard</a>
        <a href="kelola-akun.php" class="flex items-center px-6 py-3 bg-indigo-600 rounded-l-full">Kelola Akun</a>
        <a href="../dashboard/activity-log.php" class="flex items-center px-6 py-3 hover:bg-indigo-600 transition">Log Aktivitas</a>
      </nav>
    </div>
    <div class="p-6 border-t border-indigo-500 text-center text-sm text-indigo-200">
      © 2025 Student Absence
    </div>
  </aside>

  <main id="mainContent" class="flex-1 transition-all duration-300">
    <div class="flex justify-between items-center p-6 bg-indigo-600 text-white shadow-md flex-wrap gap-3">
      <div class="flex items-center space-x-3">
        <button id="toggleSidebar" class="focus:outline-none">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
        </button>
        <h2 class="font-semibold text-2xl">Kelola Akun</h2>
      </div>

      <div class="relative ml-auto">
        <button id="dropdownButton" class="flex items-center focus:outline-none cursor-pointer">
          <img src="<?= htmlspecialchars($profilePic) ?>" alt="Profile" class="w-10 h-10 rounded-full mr-2 border-2 border-white object-cover">
          <p><?= htmlspecialchars($username) ?></p>
          <svg class="ml-1 w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd"
                  d="M5.23 7.21a.75.75 0 011.06.02L10 11.292l3.71-4.06a.75.75 0 011.08 1.04l-4.25 4.66a.75.75 0 01-1.08 0l-4.25-4.66a.75.75 0 01.02-1.06z"
                  clip-rule="evenodd"/>
          </svg>
        </button>

        <div id="dropdownMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-50">
          <a href="../edit-profile/edit-profile-admin.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Edit Profil</a>
          <a href="../../auth/logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</a>
        </div>
      </div>
    </div>

    <div class="p-6 space-y-6">
      <div class="flex justify-between items-center flex-wrap gap-3">
        <h3 class="text-xl font-semibold text-gray-800">Daftar Akun</h3>
        <button id="openModal" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition shadow-md">+ Tambah Akun</button>
      </div>

      <div class="table-container bg-white rounded-xl shadow-lg border border-gray-200">
        <table class="min-w-full text-left border-collapse">
          <thead class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white">
            <tr>
              <th class="px-4 py-3">No</th>
              <th class="px-4 py-3">Username</th>
              <th class="px-4 py-3">Email</th>
              <th class="px-4 py-3">Role</th>
              <th class="px-4 py-3">Dibuat</th>
              <th class="px-4 py-3 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php $no = 0; while($row = $users->fetch_assoc()): ?>
                <tr class="border-b hover:bg-indigo-50 transition">
                <td data-label="No" class="px-4 py-3 text-gray-700"><?= ++$no + ($limit*($page-1)) ?></td>
                <td data-label="Username" class="px-4 py-3 font-medium text-gray-800"><?= !empty($row['username']) ? htmlspecialchars($row['username']) : '-' ?></td>
                <td data-label="Email" class="px-4 py-3 text-gray-600"><?= !empty($row['email']) ? htmlspecialchars($row['email']) : '-' ?></td>
                <td data-label="Role" class="px-4 py-3 capitalize text-indigo-700 font-semibold"><?= htmlspecialchars($row['role']) ?></td>
                <td data-label="Dibuat" class="px-4 py-3 text-sm text-gray-500"><?= $row['created_at'] ?></td>
                <td data-label="Aksi" class="px-4 py-3 text-center">
                    <?php if ($row['id'] != $_SESSION['user_id']): ?>
                    <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Yakin ingin menghapus akun ini?')" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-md text-sm">Hapus</a>
                    <?php else: ?>
                    <span class="text-gray-400 text-sm italic">Akun sendiri</span>
                    <?php endif; ?>
                </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
      </div>

      <div class="flex justify-center mt-6 flex-wrap gap-2">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <a href="?page=<?= $i ?>"
             class="px-3 py-1 rounded-md <?= ($i==$page)?'bg-indigo-600 text-white':'bg-gray-200 hover:bg-indigo-100 text-gray-700' ?> transition">
            <?= $i ?>
          </a>
        <?php endfor; ?>
      </div>
    </div>
  </main>
</div>

<div id="modalOverlay">
  <div id="modal">
    <h3 class="text-2xl font-semibold mb-4 text-indigo-700 text-center">Tambah Akun Baru</h3>
    <form method="POST">
      <input type="hidden" name="add_user" value="1">
      <div class="mb-3" id="usernameField">
        <label class="block text-sm font-medium mb-1">Username</label>
        <input type="text" name="username" class="w-full border p-2 rounded-md focus:ring focus:ring-indigo-300">
      </div>
      <div class="mb-3" id="emailField">
        <label class="block text-sm font-medium mb-1">Email</label>
        <input type="email" name="email" class="w-full border p-2 rounded-md focus:ring focus:ring-indigo-300">
      </div>
      <div class="mb-3">
        <label class="block text-sm font-medium mb-1">Password</label>
        <input type="password" name="password" required class="w-full border p-2 rounded-md focus:ring focus:ring-indigo-300">
      </div>
      <div class="mb-4">
        <label class="block text-sm font-medium mb-1">Role</label>
        <select name="role" id="roleSelect" class="w-full border p-2 rounded-md">
          <option value="murid" selected>Murid</option>
          <option value="guru">Guru</option>
          <option value="admin">Admin</option>
        </select>
      </div>
      <div class="flex justify-end gap-2 flex-wrap">
        <button type="button" id="closeModal" class="px-4 py-2 bg-gray-300 rounded-md hover:bg-gray-400 transition">Batal</button>
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">Simpan</button>
      </div>
    </form>
  </div>
</div>

<script>
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('overlay');
  const toggleSidebar = document.getElementById('toggleSidebar');
  const closeSidebar = document.getElementById('closeSidebar');
  toggleSidebar.addEventListener('click',()=>{ sidebar.classList.add('active'); overlay.classList.add('active'); });
  closeSidebar.addEventListener('click',()=>{ sidebar.classList.remove('active'); overlay.classList.remove('active'); });
  overlay.addEventListener('click',()=>{ sidebar.classList.remove('active'); overlay.classList.remove('active'); });

  const dropdownButton = document.getElementById('dropdownButton');
  const dropdownMenu = document.getElementById('dropdownMenu');
  dropdownButton.addEventListener('click',()=>dropdownMenu.classList.toggle('hidden'));
  window.addEventListener('click',e=>{if(!dropdownButton.contains(e.target) && !dropdownMenu.contains(e.target)) dropdownMenu.classList.add('hidden');});

  const modalOverlay = document.getElementById('modalOverlay');
  const openModal = document.getElementById('openModal');
  const closeModal = document.getElementById('closeModal');
  openModal.addEventListener('click',()=>modalOverlay.classList.add('active'));
  closeModal.addEventListener('click',()=>modalOverlay.classList.remove('active'));
  modalOverlay.addEventListener('click',e=>{if(e.target===modalOverlay) modalOverlay.classList.remove('active');});

  const roleSelect = document.getElementById('roleSelect');
  const usernameField = document.getElementById('usernameField');
  const emailField = document.getElementById('emailField');
  roleSelect.addEventListener('change',()=>{
    const role=roleSelect.value;
    usernameField.classList.add('hidden');
    emailField.classList.add('hidden');
    if(role==='guru'){usernameField.classList.remove('hidden');}
    else if(role==='murid'){emailField.classList.remove('hidden');}
    else if(role==='admin'){usernameField.classList.remove('hidden'); emailField.classList.remove('hidden');}
  });
  roleSelect.dispatchEvent(new Event('change'));
</script>

</body>
</html>
