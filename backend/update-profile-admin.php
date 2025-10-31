<?php
require_once('../connection/connection.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = trim($_POST['username']);
$password = trim($_POST['password']);

// Upload foto profil
$uploadOk = true;
$newFileName = null;

if (!empty($_FILES['profile_picture']['name'])) {
    $targetDir = "../uploads/";
    $fileName = basename($_FILES["profile_picture"]["name"]);
    $targetFile = $targetDir . uniqid() . "_" . $fileName;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Validasi ukuran & tipe
    if ($_FILES["profile_picture"]["size"] > 5000000) {
        $uploadOk = false;
    }

    if (!in_array($imageFileType, ['jpg', 'jpeg', 'png'])) {
        $uploadOk = false;
    }

    if ($uploadOk && move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $targetFile)) {
        $newFileName = basename($targetFile);
    }
}

// Update query dinamis
if (!empty($password)) {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    if ($newFileName) {
        $sql = "UPDATE users SET username = ?, password = ?, profile_pic = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $username, $hashedPassword, $newFileName, $user_id);
    } else {
        $sql = "UPDATE users SET username = ?, password = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $username, $hashedPassword, $user_id);
    }
} else {
    if ($newFileName) {
        $sql = "UPDATE users SET username = ?, profile_pic = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $username, $newFileName, $user_id);
    } else {
        $sql = "UPDATE users SET username = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $username, $user_id);
    }
}

if ($stmt->execute()) {
    $_SESSION['username'] = $username;
    if ($newFileName) $_SESSION['profile_pic'] = $newFileName;
    header("Location: ../view/edit-profile/edit-profile-admin.php?success=1");
    exit;
} else {
    echo "Terjadi kesalahan saat update data.";
}
?>
