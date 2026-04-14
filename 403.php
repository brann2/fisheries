<?php
http_response_code(403);
?>

<!DOCTYPE html>
<html>
<head>
<title>403 Akses Ditolak</title>
<style>
body {
  margin: 0;
  font-family: 'Segoe UI', sans-serif;
  background: linear-gradient(120deg, #1e3a8a, #3b82f6);
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
}

/* BOX */
.box {
  background: white;
  padding: 50px;
  border-radius: 20px;
  text-align: center;
  width: 350px;
  box-shadow: 0 20px 40px rgba(0,0,0,0.2);
  animation: fadeIn 0.5s ease;
}

h1 {
  font-size: 70px;
  margin: 0;
  color: #1e3a8a;
}

h3 {
  margin: 10px 0;
  color: #334155;
}

p {
  color: #64748b;
}

/* BUTTON */
a {
  display: inline-block;
  margin-top: 20px;
  padding: 12px 25px;
  background: #3b82f6;
  color: white;
  text-decoration: none;
  border-radius: 10px;
  transition: 0.3s;
}

a:hover {
  background: #1e3a8a;
  transform: scale(1.05);
}

/* ANIMATION */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
</head>
<body>

<div class="box">
  <h1>403</h1>
  <h3>Akses Ditolak</h3>
  <p>Maaf, Anda tidak memiliki izin untuk mengakses halaman ini.</p>

  <!-- 🔙 KEMBALI KE DASHBOARD -->
  <a href="dashboard.php">⬅ Kembali ke Beranda</a>
</div>

</body>
</html>
