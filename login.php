<?php
session_start();
$_SESSION['csrf'] = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html>
<head>
<title>Login</title>
<style>
body {
  margin: 0;
  font-family: 'Segoe UI';
  background: linear-gradient(120deg, #1e3a8a, #3b82f6);
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
}

.login-box {
  background: white;
  padding: 40px;
  border-radius: 15px;
  width: 300px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.2);
  text-align: center;
}

h2 {
  margin-bottom: 20px;
}

input {
  width: 100%;
  padding: 10px;
  margin: 10px 0;
  border-radius: 8px;
  border: 1px solid #ccc;
}

button {
  width: 100%;
  padding: 10px;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
}

button:hover {
  background: #1e3a8a;
}
</style>
</head>
<body>

<div class="login-box">
  <h2>Login System</h2>
  <form action="proses_login.php" method="POST">

    <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">

    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>

    <button type="submit">Login</button>
  </form>
</div>

</body>
</html>
