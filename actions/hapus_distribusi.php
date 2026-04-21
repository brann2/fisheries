<?php
session_start();
include '../config/csrf.php';
csrf_verify();
include '../config/koneksi.php';
include '../config/logger.php';

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
    header("Location: ../dashboard.php?page=distribusi&error=invalid");
    exit;
}

$cek = $conn->prepare("SELECT tujuan, jenis_ikan FROM data_distribusi WHERE id=?");
$cek->bind_param("i", $id);
$cek->execute();
$row = $cek->get_result()->fetch_assoc();
$log_info = $row ? "tujuan={$row['tujuan']} ikan={$row['jenis_ikan']}" : "id={$id}";

$stmt = $conn->prepare("DELETE FROM data_distribusi WHERE id=?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    write_log($conn, 'HAPUS_DISTRIBUSI', $user_id, $log_info);
    header("Location: ../dashboard.php?page=distribusi&success=3");
    exit;
} else {
    header("Location: ../dashboard.php?page=distribusi&error=failed");
    exit;
}
?>
