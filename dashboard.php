<?php
session_start();

// 🔐 SESSION TIMEOUT (30 menit)
$timeout = 1800;

if (isset($_SESSION['last_activity'])) {
    if (time() - $_SESSION['last_activity'] > $timeout) {
        session_unset();
        session_destroy();
        header("Location: login.php");
        exit;
    }
}

// update waktu aktivitas
$_SESSION['last_activity'] = time();

// 🔒 PROTEKSI LOGIN
if (!isset($_SESSION['login'])) {
  header("Location: login.php");
  exit;
}

// 🔥 AMBIL DATA USER
$username = $_SESSION['username'];
$role = $_SESSION['role'];

// 🛡️ SECURITY HEADER
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
?>

<!DOCTYPE html>
<html>
<head>
<title>Dashboard</title>
<style>
body {
  margin: 0;
  font-family: 'Segoe UI', sans-serif;
  background: #f1f5f9;
}

/* SIDEBAR */
.sidebar {
  width: 240px;
  height: 100vh;
  background: linear-gradient(180deg, #0f172a, #1e3a8a);
  color: white;
  position: fixed;
  padding: 25px;
  box-shadow: 4px 0 15px rgba(0,0,0,0.2);
}

.sidebar h2 {
  margin-bottom: 30px;
  font-size: 22px;
  letter-spacing: 1px;
}

.sidebar a {
  display: block;
  padding: 12px;
  margin: 8px 0;
  text-decoration: none;
  color: #cbd5f5;
  border-radius: 8px;
  transition: 0.3s;
}

.sidebar a:hover {
  background: #3b82f6;
  color: white;
  transform: translateX(5px);
}

/* CONTENT */
.content {
  margin-left: 260px;
  padding: 40px;
}

/* CARD */
.card {
  display: inline-block;
  width: 220px;
  margin: 15px;
  padding: 25px;
  background: white;
  border-radius: 15px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.1);
  transition: 0.3s;
  position: relative;
  overflow: hidden;
}

.card:hover {
  transform: translateY(-5px) scale(1.03);
}

/* garis atas card */
.card::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 5px;
  background: linear-gradient(90deg, #3b82f6, #06b6d4);
}

.card h3 {
  margin: 0;
  color: #1e293b;
}

.card p {
  font-size: 22px;
  font-weight: bold;
  color: #2563eb;
  margin-top: 10px;
}

/* RESPONSIVE */
@media (max-width: 768px) {
  .sidebar {
    width: 200px;
  }

  .content {
    margin-left: 210px;
  }

  .card {
    width: 100%;
  }
}
</style>
</head>
<body>

<div class="sidebar">
  <h2>🌊 Fisheries</h2>
  <a href="#">Dashboard</a>
  <a href="#">Data Ikan</a>
  <a href="#">Nelayan</a>
  <a href="#">Distribusi</a>
  <a href="logout.php">Logout</a>
</div>

<div class="content">

  <!-- 🔥 WELCOME USER -->
  <div style="text-align:right; font-weight:bold; color:#1e3a8a;">
    Welcome, <?= htmlspecialchars($username); ?> 
    (<?= $role === 'admin' ? 'Admin 👑' : 'User 👤'; ?>)
  </div>

  <h1>Dashboard</h1>
  <p style="color:#475569;">
    Monitoring sektor perikanan Sulawesi Utara secara real-time 🌊
  </p>

  <?php if ($role === 'admin'): ?>

  <!-- 👑 ADMIN -->
  <div class="card">
    <h3>🐟 Total Tangkapan</h3>
    <p>1200 Ton</p>
  </div>

  <div class="card">
    <h3>👨‍🌾 Nelayan Aktif</h3>
    <p>350</p>
  </div>

  <div class="card">
    <h3>🚚 Distribusi</h3>
    <p>Lancar</p>
  </div>

  <?php else: ?>

  <!-- 👤 USER (TERBATAS) -->
  <div class="card">
    <h3>👤 Mode User</h3>
    <p>Akses Terbatas</p>
  </div>

  <div class="card">
    <h3>📊 Informasi</h3>
    <p>Data umum saja</p>
  </div>

  <?php endif; ?>

</div>

</body>
</html>
