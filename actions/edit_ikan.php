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

$user_id   = (int) ($_SESSION['user_id'] ?? 0);
$id        = (int) ($_POST['id'] ?? 0);
$nama_ikan = trim($_POST['nama_ikan'] ?? '');
$jenis     = trim($_POST['jenis'] ?? '');
$berat_ton = $_POST['berat_ton'] ?? 0;
$harga_kg  = $_POST['harga_kg'] ?? 0;
$status    = trim($_POST['status'] ?? '');

$jenis_valid  = ['Pelagis Besar', 'Pelagis Kecil', 'Demersal', 'Karang'];
$status_valid = ['Tersedia', 'Habis'];

if (
    $id <= 0 ||
    empty($nama_ikan) ||
    empty($jenis) ||
    !in_array($jenis, $jenis_valid) ||
    $berat_ton <= 0 ||
    $harga_kg <= 0 ||
    !in_array($status, $status_valid)
) {
    header("Location: ../dashboard.php?page=ikan&error=invalid");
    exit;
}

$stmt = $conn->prepare("UPDATE data_ikan SET nama_ikan=?, jenis=?, berat_ton=?, harga_kg=?, status=? WHERE id=?");
$stmt->bind_param("ssddsi", $nama_ikan, $jenis, $berat_ton, $harga_kg, $status, $id);

if ($stmt->execute()) {
    write_log($conn, 'EDIT_IKAN', $user_id, "id={$id} nama={$nama_ikan} jenis={$jenis} berat={$berat_ton}ton");
    header("Location: ../dashboard.php?page=ikan&success=2");
    exit;
} else {
    header("Location: ../dashboard.php?page=ikan&error=failed");
    exit;
}
?>
