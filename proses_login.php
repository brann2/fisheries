<?php
session_start();
require_once __DIR__ . '/config/koneksi.php';

// CSRF
if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf']) {
    die("CSRF detected!");
}

$username = trim($_POST['username']);
$password = trim($_POST['password']);

// ambil user
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username=?");
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// login
if ($user && password_verify($password, $user['password'])) {
    session_regenerate_id(true);

    $_SESSION['login'] = true;
    $_SESSION['user_id'] = isset($user['id']) ? (int) $user['id'] : 0;
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];

    // 🔥 LOG LOGIN BERHASIL
    file_put_contents("log.txt", date("Y-m-d H:i:s") . " LOGIN SUCCESS: $username\n", FILE_APPEND);

    header("Location: dashboard.php");
    exit;
} else {

    // 🔥 LOG LOGIN GAGAL
    file_put_contents("log.txt", date("Y-m-d H:i:s") . " LOGIN FAILED: $username\n", FILE_APPEND);

    echo "Login gagal!";
}
?>
