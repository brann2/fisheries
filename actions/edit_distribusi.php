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
$id         = (int) ($_POST['id'] ?? 0);
$tujuan     = trim($_POST['tujuan'] ?? '');
$jenis_ikan = trim($_POST['jenis_ikan'] ?? '');
$jumlah_ton = $_POST['jumlah_ton'] ?? 0;
$tanggal    = $_POST['tanggal'] ?? '';
$status     = trim($_POST['status'] ?? '');

$status_valid = ['Pending', 'Proses', 'Terkirim'];

if (
    $id <= 0 ||
    empty($tujuan) ||
    empty($jenis_ikan) ||
    $jumlah_ton <= 0 ||
    empty($tanggal) ||
    !in_array($status, $status_valid)
) {
    header("Location: ../dashboard.php?page=distribusi&error=invalid");
    exit;
}

$stmt = $conn->prepare("UPDATE data_distribusi SET tujuan=?, jenis_ikan=?, jumlah_ton=?, tanggal=?, status=? WHERE id=?");
$stmt->bind_param("ssdssi", $tujuan, $jenis_ikan, $jumlah_ton, $tanggal, $status, $id);

if ($stmt->execute()) {
    write_log($conn, 'EDIT_DISTRIBUSI', $user_id, "id={$id} tujuan={$tujuan} ikan={$jenis_ikan} jumlah={$jumlah_ton}ton");
    header("Location: ../dashboard.php?page=distribusi&success=2");
    exit;
} else {
    header("Location: ../dashboard.php?page=distribusi&error=failed");
    exit;
}
?>
