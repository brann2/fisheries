<?php
session_start();
require_once __DIR__ . '/../config/csrf.php';
csrf_verify();
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/logger.php';

if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../dashboard.php?error=unauthorized");
    exit;
}

$user_id = (int) ($_SESSION['user_id'] ?? 0);
$id      = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    header("Location: ../dashboard.php?page=ikan&error=invalid");
    exit;
}

// Ambil nama dulu untuk log
$cek = $conn->prepare("SELECT nama_ikan FROM data_ikan WHERE id=?");
$cek->bind_param("i", $id);
$cek->execute();
$row = $cek->get_result()->fetch_assoc();
$nama_log = $row ? $row['nama_ikan'] : 'unknown';

$stmt = $conn->prepare("DELETE FROM data_ikan WHERE id=?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    write_log($conn, 'HAPUS_IKAN', $user_id, "id={$id} nama={$nama_log}");
    header("Location: ../dashboard.php?page=ikan&success=3");
    exit;
} else {
    header("Location: ../dashboard.php?page=ikan&error=failed");
    exit;
}
?>
