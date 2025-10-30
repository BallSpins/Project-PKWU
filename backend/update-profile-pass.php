<?php
session_start();
require "../connection/connection.php";

if (!isset($_SESSION['user_id'])) {
  header("Location: ../../auth/login1.php");
  exit();
}

$user_id = $_SESSION['user_id'];
$username = mysqli_real_escape_string($conn, $_POST['username']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

// Ambil data lama
$query_old = "SELECT password FROM users WHERE id = $user_id";
$res_old = mysqli_query($conn, $query_old);
$old = mysqli_fetch_assoc($res_old);
$old_pass = $old['password'];

// Update username
if (!empty($username)) {
  mysqli_query($conn, "UPDATE users SET username='$username' WHERE id=$user_id");
}

// Jika password baru diisi dan cocok
if (!empty($password)) {
  if ($password === $confirm_password) {
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    mysqli_query($conn, "UPDATE users SET password='$hashed' WHERE id=$user_id");
  } else {
    echo "<script>alert('Konfirmasi password tidak cocok!'); window.history.back();</script>";
    exit();
  }
}

echo "<script>alert('Profil berhasil diperbarui!'); window.location='../view/edit-profile/edit-profile-pass.php';</script>";
?>
