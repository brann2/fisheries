<?php
session_start();
$_SESSION['csrf'] = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Smart Fisheries Login</title>
  <link rel="stylesheet" href="style.css">
</head>
<body class="login-bg">
  <div class="login-bubbles">
    <div class="bubble b1"></div>
    <div class="bubble b2"></div>
    <div class="bubble b3"></div>
  </div>
  <div class="login-fish-bg">
    <span class="fish f1">🐟</span>
    <span class="fish f2">🐠</span>
    <span class="fish f3">🦑</span>
  </div>
  <div class="login-center">
    <div class="login-card">
      <div class="login-logo">
        <span class="logo-icon">🌊</span>
      </div>
      <h2>Smart Fisheries</h2>
      <p class="login-subtitle">Masuk ke sistem manajemen perikanan</p>
      <form action="proses_login.php" method="POST">
        <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
        <div class="input-wrap">
          <span class="input-icon">👤</span>
          <input type="text" name="username" placeholder="Username" class="login-input" required autofocus>
        </div>
        <div class="input-wrap">
          <span class="input-icon">🔒</span>
          <input type="password" name="password" placeholder="Password" class="login-input" required>
        </div>
        <button type="submit" class="login-btn">Login</button>
      </form>
      <div class="login-divider">
        <hr><span>Smart Fisheries Management System</span><hr>
      </div>
      <p class="login-footer">© 2025 Smart Fisheries · Sistem Perikanan Digital</p>
    </div>
  </div>
</body>
</html>