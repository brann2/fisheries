<?php
session_start();

// kalau belum login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// 🔥 AMBIL USERNAME
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'unknown';

// kalau bukan admin → 403
if ($_SESSION['role'] !== 'admin') {

    // 🔥 LOG AKSES DITOLAK
    file_put_contents("log.txt", date("Y-m-d H:i:s") . " ACCESS DENIED (CONFIG): ".$username."\n", FILE_APPEND);

    http_response_code(403);
    include '403.php';
    exit;
}

// 🔥 LOG ADMIN AKSES CONFIG
file_put_contents("log.txt", date("Y-m-d H:i:s") . " ADMIN ACCESS CONFIG: ".$username."\n", FILE_APPEND);

// kalau admin (opsional isi)
echo "Halaman config (admin only)";
?>
