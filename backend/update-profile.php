<?php
require_once('../connection/connection.php');
session_start();

if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login1.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil input
$nama = $_POST['nama'] ?? '';
$kelas = $_POST['kelas'] ?? '';
$jurusan = $_POST['jurusan'] ?? '';
$nisn = $_POST['nisn'] ?? '';
$email = $_POST['email'] ?? '';
$profilePicName = $_SESSION['profile_pic'] ?? null; // default session lama

// Upload profile picture (kalau ada)
if(isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
    $ext = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
    $profilePicName = "profile_".$user_id.".".$ext;
    move_uploaded_file($_FILES['profile_picture']['tmp_name'], "../uploads/".$profilePicName);

    // Update ke users
    $stmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
    $stmt->execute([$profilePicName, $user_id]);

    // Update session agar header ikut berubah
    $_SESSION['profile_pic'] = $profilePicName;
}

// Update email ke users
$stmt = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
$stmt->execute([$email, $user_id]);

// Update murid
$stmt = $conn->prepare("UPDATE murid SET nama_lengkap = ?, kelas = ?, jurusan = ?, nisn = ? WHERE user_id = ?");
$stmt->execute([$nama, $kelas, $jurusan, $nisn, $user_id]);

// Redirect kembali ke edit profile
header("Location: ../view/edit-profile/edit-profile.php?success=1");
exit;
?>
