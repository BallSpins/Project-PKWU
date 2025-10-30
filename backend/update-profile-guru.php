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
$nip = $_POST['nip'] ?? '';
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

// Update guru
$stmt = $conn->prepare("UPDATE guru SET nama_lengkap = ?, nip = ? WHERE user_id = ?");
$stmt->execute([$nama,$nip,$user_id]);

// Redirect kembali ke edit profile
header("Location: ../view/edit-profile/edit-profile-guru.php?success=1");
exit;
?>
