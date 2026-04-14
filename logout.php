<?php
session_start();

// 🔥 AMBIL USERNAME DULU
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'unknown';

// 🔥 LOG LOGOUT
file_put_contents("log.txt", date("Y-m-d H:i:s") . " LOGOUT: ".$username."\n", FILE_APPEND);

// HAPUS SESSION
session_destroy();

// REDIRECT
header("Location: index.php");
exit;
?><?php
session_start();
session_destroy();
header("Location: index.php");
exit;
?>
