<?php
session_start();
include '../../config/koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../dashboard.php?error=unauthorized");
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$id      = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    header("Location: ../dashboard.php?page=nelayan&error=invalid");
    exit;
}

$cek = $conn->prepare("SELECT nama FROM data_nelayan WHERE id=?");
$cek->bind_param("i", $id);
$cek->execute();
$row = $cek->get_result()->fetch_assoc();
$nama_log = $row ? $row['nama'] : 'unknown';

$stmt = $conn->prepare("DELETE FROM data_nelayan WHERE id=?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: ../dashboard.php?page=nelayan&success=3");
    exit;
} else {
    header("Location: ../dashboard.php?page=nelayan&error=failed");
    exit;
}
?>