<?php
session_start();
$_SESSION['csrf'] = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html>
<head>
<title>Login</title>
<link rel="stylesheet" href="style.css">
<body class="login-bg">
  <div class="login-center">
    <div class="login-card">
      <div class="login-logo">
        <span class="logo-icon">🌊</span>
      </div>
      <h2>Smart Fisheries Login</h2>
      <form action="proses_login.php" method="POST">
        <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
        <input type="text" name="username" placeholder="Username" class="login-input" required autofocus>
        <input type="password" name="password" placeholder="Password" class="login-input" required>
        <button type="submit" class="login-btn">Login</button>
      </form>
    </div>
  </div>
</body>
</html>
</body>
</html>
