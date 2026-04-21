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

$user_id   = (int) $_SESSION['user_id'];
$id        = (int) ($_POST['id'] ?? 0);
$nama      = trim($_POST['nama'] ?? '');
$alamat    = trim($_POST['alamat'] ?? '');
$kapal     = trim($_POST['kapal'] ?? '');
$kapasitas = $_POST['kapasitas_ton'] ?? 0;
$status    = trim($_POST['status'] ?? '');

$status_valid = ['Aktif', 'Tidak Aktif'];

if (
    $id <= 0 ||
    empty($nama) ||
    empty($alamat) ||
    empty($kapal) ||
    $kapasitas <= 0 ||
    !in_array($status, $status_valid)
) {
    header("Location: ../dashboard.php?page=nelayan&error=invalid");
    exit;
}

$stmt = $conn->prepare("UPDATE data_nelayan SET nama=?, alamat=?, kapal=?, kapasitas_ton=?, status=? WHERE id=?");
$stmt->bind_param("sssdsi", $nama, $alamat, $kapal, $kapasitas, $status, $id);

if ($stmt->execute()) {
    write_log($conn, 'EDIT_NELAYAN', $user_id, "id={$id} nama={$nama} kapal={$kapal}");
    header("Location: ../dashboard.php?page=nelayan&success=2");
    exit;
} else {
    header("Location: ../dashboard.php?page=nelayan&error=failed");
    exit;
}
?>
