<?php
session_start();
include '../config/csrf.php';
csrf_verify();
include '../config/koneksi.php';
include '../config/logger.php';

// Proteksi login
if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

// Proteksi role admin
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../dashboard.php?error=unauthorized");
    exit;
}

$user_id  = (int) $_SESSION['user_id'];
$nama_ikan = trim($_POST['nama_ikan'] ?? '');
$jenis     = trim($_POST['jenis'] ?? '');
$berat_ton = $_POST['berat_ton'] ?? 0;
$harga_kg  = $_POST['harga_kg'] ?? 0;
$status    = trim($_POST['status'] ?? '');

// Validasi
$jenis_valid  = ['Pelagis Besar', 'Pelagis Kecil', 'Demersal', 'Karang'];
$status_valid = ['Tersedia', 'Habis'];

if (
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

$stmt = $conn->prepare("INSERT INTO data_ikan (nama_ikan, jenis, berat_ton, harga_kg, status) VALUES (?,?,?,?,?)");
$stmt->bind_param("ssdds", $nama_ikan, $jenis, $berat_ton, $harga_kg, $status);

if ($stmt->execute()) {
    write_log($conn, 'TAMBAH_IKAN', $user_id, "nama={$nama_ikan} jenis={$jenis} berat={$berat_ton}ton harga={$harga_kg}");
    header("Location: ../dashboard.php?page=ikan&success=1");
    exit;
} else {
    header("Location: ../dashboard.php?page=ikan&error=failed");
    exit;
}
?>
