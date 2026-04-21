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

$user_id    = (int) $_SESSION['user_id'];
$tujuan     = trim($_POST['tujuan'] ?? '');
$jenis_ikan = trim($_POST['jenis_ikan'] ?? '');
$jumlah_ton = $_POST['jumlah_ton'] ?? 0;
$tanggal    = $_POST['tanggal'] ?? '';
$status     = trim($_POST['status'] ?? '');

$status_valid = ['Pending', 'Proses', 'Terkirim'];

if (
    empty($tujuan) ||
    empty($jenis_ikan) ||
    $jumlah_ton <= 0 ||
    empty($tanggal) ||
    !in_array($status, $status_valid)
) {
    header("Location: ../dashboard.php?page=distribusi&error=invalid");
    exit;
}

$stmt = $conn->prepare("INSERT INTO data_distribusi (tujuan, jenis_ikan, jumlah_ton, tanggal, status) VALUES (?,?,?,?,?)");
$stmt->bind_param("ssdss", $tujuan, $jenis_ikan, $jumlah_ton, $tanggal, $status);

if ($stmt->execute()) {
    write_log($conn, 'TAMBAH_DISTRIBUSI', $user_id, "tujuan={$tujuan} ikan={$jenis_ikan} jumlah={$jumlah_ton}ton tanggal={$tanggal}");
    header("Location: ../dashboard.php?page=distribusi&success=1");
    exit;
} else {
    header("Location: ../dashboard.php?page=distribusi&error=failed");
    exit;
}
?>
