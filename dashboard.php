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

$_SESSION['last_activity'] = time();

// 🔒 PROTEKSI LOGIN
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];
$role = $_SESSION['role'];

// 🛡️ SECURITY HEADER
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");

// ============================================================
// DATABASE (ganti sesuai konfigurasi Anda)
// ============================================================
$host = 'localhost';
$db   = 'fisheries_db';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Jika DB belum ada, gunakan data dummy
    $pdo = null;
}

// ============================================================
// HELPER: sanitize input
// ============================================================
function clean($val) {
    return htmlspecialchars(trim($val), ENT_QUOTES, 'UTF-8');
}

// ============================================================
// ACTIVE PAGE (via GET)
// ============================================================
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// ============================================================
// CRUD ACTIONS (hanya admin)
// ============================================================
$msg = '';

if ($role === 'admin' && $_SERVER['REQUEST_METHOD'] === 'POST' && $pdo) {
    $action = $_POST['action'] ?? '';
    $table  = $_POST['table']  ?? '';

    // ── Data Ikan ──────────────────────────────────────────
    if ($table === 'ikan') {
        if ($action === 'add') {
            $stmt = $pdo->prepare("INSERT INTO data_ikan (nama_ikan, jenis, berat_ton, harga_kg, status) VALUES (?,?,?,?,?)");
            $stmt->execute([
                clean($_POST['nama_ikan']),
                clean($_POST['jenis']),
                (float)$_POST['berat_ton'],
                (float)$_POST['harga_kg'],
                clean($_POST['status'])
            ]);
            $msg = 'Data ikan berhasil ditambahkan.';
        } elseif ($action === 'edit') {
            $stmt = $pdo->prepare("UPDATE data_ikan SET nama_ikan=?, jenis=?, berat_ton=?, harga_kg=?, status=? WHERE id=?");
            $stmt->execute([
                clean($_POST['nama_ikan']),
                clean($_POST['jenis']),
                (float)$_POST['berat_ton'],
                (float)$_POST['harga_kg'],
                clean($_POST['status']),
                (int)$_POST['id']
            ]);
            $msg = 'Data ikan berhasil diperbarui.';
        } elseif ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM data_ikan WHERE id=?");
            $stmt->execute([(int)$_POST['id']]);
            $msg = 'Data ikan berhasil dihapus.';
        }
        header("Location: dashboard.php?page=ikan&msg=" . urlencode($msg));
        exit;
    }

    // ── Nelayan ────────────────────────────────────────────
    if ($table === 'nelayan') {
        if ($action === 'add') {
            $stmt = $pdo->prepare("INSERT INTO data_nelayan (nama, alamat, kapal, kapasitas_ton, status) VALUES (?,?,?,?,?)");
            $stmt->execute([
                clean($_POST['nama']),
                clean($_POST['alamat']),
                clean($_POST['kapal']),
                (float)$_POST['kapasitas_ton'],
                clean($_POST['status'])
            ]);
            $msg = 'Data nelayan berhasil ditambahkan.';
        } elseif ($action === 'edit') {
            $stmt = $pdo->prepare("UPDATE data_nelayan SET nama=?, alamat=?, kapal=?, kapasitas_ton=?, status=? WHERE id=?");
            $stmt->execute([
                clean($_POST['nama']),
                clean($_POST['alamat']),
                clean($_POST['kapal']),
                (float)$_POST['kapasitas_ton'],
                clean($_POST['status']),
                (int)$_POST['id']
            ]);
            $msg = 'Data nelayan berhasil diperbarui.';
        } elseif ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM data_nelayan WHERE id=?");
            $stmt->execute([(int)$_POST['id']]);
            $msg = 'Data nelayan berhasil dihapus.';
        }
        header("Location: dashboard.php?page=nelayan&msg=" . urlencode($msg));
        exit;
    }

    // ── Distribusi ─────────────────────────────────────────
    if ($table === 'distribusi') {
        if ($action === 'add') {
            $stmt = $pdo->prepare("INSERT INTO data_distribusi (tujuan, jenis_ikan, jumlah_ton, tanggal, status) VALUES (?,?,?,?,?)");
            $stmt->execute([
                clean($_POST['tujuan']),
                clean($_POST['jenis_ikan']),
                (float)$_POST['jumlah_ton'],
                clean($_POST['tanggal']),
                clean($_POST['status'])
            ]);
            $msg = 'Data distribusi berhasil ditambahkan.';
        } elseif ($action === 'edit') {
            $stmt = $pdo->prepare("UPDATE data_distribusi SET tujuan=?, jenis_ikan=?, jumlah_ton=?, tanggal=?, status=? WHERE id=?");
            $stmt->execute([
                clean($_POST['tujuan']),
                clean($_POST['jenis_ikan']),
                (float)$_POST['jumlah_ton'],
                clean($_POST['tanggal']),
                clean($_POST['status']),
                (int)$_POST['id']
            ]);
            $msg = 'Data distribusi berhasil diperbarui.';
        } elseif ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM data_distribusi WHERE id=?");
            $stmt->execute([(int)$_POST['id']]);
            $msg = 'Data distribusi berhasil dihapus.';
        }
        header("Location: dashboard.php?page=distribusi&msg=" . urlencode($msg));
        exit;
    }
}

// ============================================================
// FETCH DATA (dari DB jika ada, otherwise dummy)
// ============================================================
// --- Data Ikan ---
if ($pdo) {
    $ikan_list = $pdo->query("SELECT * FROM data_ikan ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
} else {
    $ikan_list = [
        ['id'=>1,'nama_ikan'=>'Tuna Sirip Kuning','jenis'=>'Pelagis Besar','berat_ton'=>120,'harga_kg'=>85000,'status'=>'Tersedia'],
        ['id'=>2,'nama_ikan'=>'Cakalang','jenis'=>'Pelagis Besar','berat_ton'=>85,'harga_kg'=>45000,'status'=>'Tersedia'],
        ['id'=>3,'nama_ikan'=>'Kerapu Macan','jenis'=>'Demersal','berat_ton'=>30,'harga_kg'=>120000,'status'=>'Tersedia'],
        ['id'=>4,'nama_ikan'=>'Layang','jenis'=>'Pelagis Kecil','berat_ton'=>200,'harga_kg'=>22000,'status'=>'Habis'],
        ['id'=>5,'nama_ikan'=>'Tongkol','jenis'=>'Pelagis Besar','berat_ton'=>95,'harga_kg'=>38000,'status'=>'Tersedia'],
    ];
}

// --- Data Nelayan ---
if ($pdo) {
    $nelayan_list = $pdo->query("SELECT * FROM data_nelayan ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
} else {
    $nelayan_list = [
        ['id'=>1,'nama'=>'Budi Santoso','alamat'=>'Manado','kapal'=>'KM Harapan Jaya','kapasitas_ton'=>15,'status'=>'Aktif'],
        ['id'=>2,'nama'=>'Johan Runtuwene','alamat'=>'Bitung','kapal'=>'KM Sulut Mandiri','kapasitas_ton'=>20,'status'=>'Aktif'],
        ['id'=>3,'nama'=>'Samuel Maramis','alamat'=>'Amurang','kapal'=>'KM Bahari Lestari','kapasitas_ton'=>10,'status'=>'Tidak Aktif'],
        ['id'=>4,'nama'=>'Darius Pangau','alamat'=>'Sangihe','kapal'=>'KM Sumber Rezeki','kapasitas_ton'=>25,'status'=>'Aktif'],
        ['id'=>5,'nama'=>'Effendi Taher','alamat'=>'Kotamobagu','kapal'=>'KM Nusantara','kapasitas_ton'=>18,'status'=>'Aktif'],
    ];
}

// --- Data Distribusi ---
if ($pdo) {
    $distribusi_list = $pdo->query("SELECT * FROM data_distribusi ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
} else {
    $distribusi_list = [
        ['id'=>1,'tujuan'=>'Jakarta','jenis_ikan'=>'Tuna','jumlah_ton'=>40,'tanggal'=>'2025-04-15','status'=>'Terkirim'],
        ['id'=>2,'tujuan'=>'Surabaya','jenis_ikan'=>'Cakalang','jumlah_ton'=>25,'tanggal'=>'2025-04-16','status'=>'Proses'],
        ['id'=>3,'tujuan'=>'Makassar','jenis_ikan'=>'Kerapu','jumlah_ton'=>12,'tanggal'=>'2025-04-17','status'=>'Terkirim'],
        ['id'=>4,'tujuan'=>'Bali','jenis_ikan'=>'Tongkol','jumlah_ton'=>18,'tanggal'=>'2025-04-18','status'=>'Proses'],
        ['id'=>5,'tujuan'=>'Manado Lokal','jenis_ikan'=>'Layang','jumlah_ton'=>30,'tanggal'=>'2025-04-19','status'=>'Pending'],
    ];
}

// Stats ringkas untuk dashboard
$total_ikan      = count($ikan_list);
$total_nelayan   = count($nelayan_list);
$total_distribusi= count($distribusi_list);
$total_ton       = array_sum(array_column($ikan_list, 'berat_ton'));

$msg_display = isset($_GET['msg']) ? clean($_GET['msg']) : $msg;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Fisheries Dashboard</title>
<style>
/* ============================================
   GLOBAL (tidak diubah)
   ============================================ */
* { box-sizing: border-box; }
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
  overflow-y: auto;
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
.sidebar a:hover,
.sidebar a.active {
  background: #3b82f6;
  color: white;
  transform: translateX(5px);
}

/* CONTENT */
.content {
  margin-left: 260px;
  padding: 40px;
}

/* CARD DASHBOARD */
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
  vertical-align: top;
}
.card:hover { transform: translateY(-5px) scale(1.03); }
.card::before {
  content: "";
  position: absolute;
  top: 0; left: 0;
  width: 100%; height: 5px;
  background: linear-gradient(90deg, #3b82f6, #06b6d4);
}
.card h3 { margin: 0; color: #1e293b; }
.card p { font-size: 22px; font-weight: bold; color: #2563eb; margin-top: 10px; }

/* ============================================
   CRUD SECTION (tambahan baru)
   ============================================ */

/* Topbar halaman */
.page-topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 24px;
  flex-wrap: wrap;
  gap: 12px;
}
.page-topbar h1 {
  font-size: 1.6rem;
  color: #1e293b;
  margin: 0;
}

/* Tombol tambah */
.btn-add {
  background: linear-gradient(90deg, #1e3a8a, #2563eb);
  color: white;
  border: none;
  padding: 11px 22px;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 4px 14px rgba(37,99,235,0.35);
  transition: 0.2s;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}
.btn-add:hover {
  background: linear-gradient(90deg, #163070, #1d4ed8);
  transform: translateY(-2px);
}

/* Alert pesan */
.alert {
  padding: 12px 18px;
  border-radius: 10px;
  margin-bottom: 20px;
  font-size: 14px;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 8px;
}
.alert-success {
  background: #d1fae5;
  color: #065f46;
  border-left: 4px solid #10b981;
}

/* Tabel */
.table-wrap {
  background: white;
  border-radius: 15px;
  box-shadow: 0 6px 24px rgba(0,0,0,0.08);
  overflow: hidden;
}
.tbl {
  width: 100%;
  border-collapse: collapse;
}
.tbl thead {
  background: linear-gradient(90deg, #1e3a8a, #2563eb);
  color: white;
}
.tbl th {
  padding: 14px 16px;
  text-align: left;
  font-size: 13px;
  letter-spacing: 0.5px;
  font-weight: 600;
}
.tbl td {
  padding: 13px 16px;
  font-size: 14px;
  color: #334155;
  border-bottom: 1px solid #f1f5f9;
}
.tbl tbody tr:hover { background: #f8fafc; }
.tbl tbody tr:last-child td { border-bottom: none; }

/* Badge status */
.badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
}
.badge-green  { background: #d1fae5; color: #065f46; }
.badge-red    { background: #fee2e2; color: #991b1b; }
.badge-yellow { background: #fef3c7; color: #92400e; }
.badge-blue   { background: #dbeafe; color: #1e40af; }

/* Tombol aksi di tabel */
.btn-edit, .btn-del {
  border: none;
  padding: 7px 14px;
  border-radius: 7px;
  font-size: 13px;
  cursor: pointer;
  font-weight: 600;
  transition: 0.2s;
  margin-right: 4px;
}
.btn-edit {
  background: #dbeafe;
  color: #1e40af;
}
.btn-edit:hover { background: #bfdbfe; }
.btn-del {
  background: #fee2e2;
  color: #991b1b;
}
.btn-del:hover { background: #fecaca; }

/* Modal overlay */
.modal-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(15,23,42,0.55);
  z-index: 999;
  justify-content: center;
  align-items: center;
}
.modal-overlay.open { display: flex; }

.modal {
  background: white;
  border-radius: 18px;
  padding: 36px 32px 28px;
  min-width: 380px;
  max-width: 95vw;
  box-shadow: 0 20px 60px rgba(15,36,96,0.3);
  animation: fadeInUp 0.3s ease both;
  position: relative;
  max-height: 90vh;
  overflow-y: auto;
}
.modal h3 {
  margin: 0 0 22px;
  color: #1e3a8a;
  font-size: 1.2rem;
  display: flex;
  align-items: center;
  gap: 8px;
}
.modal-close {
  position: absolute;
  top: 16px; right: 18px;
  background: none;
  border: none;
  font-size: 20px;
  cursor: pointer;
  color: #94a3b8;
  line-height: 1;
  padding: 0;
}
.modal-close:hover { color: #1e293b; }

/* Form dalam modal */
.form-group {
  margin-bottom: 16px;
}
.form-group label {
  display: block;
  font-size: 13px;
  font-weight: 600;
  color: #475569;
  margin-bottom: 6px;
}
.form-group input,
.form-group select {
  width: 100%;
  padding: 11px 14px;
  border-radius: 9px;
  border: 1.5px solid #cbd5e1;
  font-size: 14px;
  background: #f8fafc;
  color: #1e293b;
  transition: border 0.2s, box-shadow 0.2s;
}
.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #2563eb;
  background: #fff;
  box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
}

/* Tombol submit modal */
.btn-submit {
  width: 100%;
  padding: 13px;
  background: linear-gradient(90deg, #1e3a8a, #2563eb);
  color: white;
  border: none;
  border-radius: 10px;
  font-size: 15px;
  font-weight: 700;
  cursor: pointer;
  margin-top: 6px;
  box-shadow: 0 4px 14px rgba(37,99,235,0.4);
  transition: 0.2s;
}
.btn-submit:hover {
  background: linear-gradient(90deg, #163070, #1d4ed8);
  transform: translateY(-2px);
}

/* Modal konfirmasi hapus */
.modal-confirm p { color: #475569; margin: 0 0 22px; line-height: 1.6; }
.btn-row { display: flex; gap: 10px; }
.btn-cancel {
  flex: 1;
  padding: 12px;
  border-radius: 10px;
  border: 1.5px solid #cbd5e1;
  background: white;
  color: #475569;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition: 0.2s;
}
.btn-cancel:hover { background: #f1f5f9; }
.btn-confirm-del {
  flex: 1;
  padding: 12px;
  border-radius: 10px;
  border: none;
  background: #ef4444;
  color: white;
  font-size: 15px;
  font-weight: 700;
  cursor: pointer;
  transition: 0.2s;
}
.btn-confirm-del:hover { background: #dc2626; }

/* Welcome bar */
.welcome-bar {
  text-align: right;
  font-weight: bold;
  color: #1e3a8a;
  margin-bottom: 4px;
}

/* Page subtitle */
.page-sub {
  color: #475569;
  margin-bottom: 28px;
  margin-top: 4px;
}

/* Animasi */
@keyframes fadeInUp {
  from { opacity:0; transform:translateY(24px); }
  to   { opacity:1; transform:translateY(0); }
}

/* Responsive */
@media (max-width: 768px) {
  .sidebar { width: 200px; }
  .content { margin-left: 210px; }
  .card    { width: 100%; }
  .modal   { min-width: 90vw; }
}
</style>
</head>
<body>

<!-- ==================== SIDEBAR ==================== -->
<div class="sidebar">
  <h2>🌊 Fisheries</h2>
  <a href="dashboard.php?page=dashboard" class="<?= $page==='dashboard' ? 'active' : '' ?>">📊 Dashboard</a>
  <a href="dashboard.php?page=ikan"      class="<?= $page==='ikan'      ? 'active' : '' ?>">🐟 Data Ikan</a>
  <a href="dashboard.php?page=nelayan"   class="<?= $page==='nelayan'   ? 'active' : '' ?>">👨‍🌾 Nelayan</a>
  <a href="dashboard.php?page=distribusi"class="<?= $page==='distribusi'? 'active' : '' ?>">🚚 Distribusi</a>
  <a href="logout.php">🚪 Logout</a>
</div>

<!-- ==================== CONTENT ==================== -->
<div class="content">

  <div class="welcome-bar">
    Welcome, <?= htmlspecialchars($username) ?>
    (<?= $role === 'admin' ? 'Admin 👑' : 'User 👤' ?>)
  </div>

  <?php if ($msg_display): ?>
    <div class="alert alert-success">✅ <?= $msg_display ?></div>
  <?php endif; ?>

  <!-- ======================================================
       HALAMAN: DASHBOARD
       ====================================================== -->
  <?php if ($page === 'dashboard'): ?>

    <h1>Dashboard</h1>
    <p class="page-sub">Monitoring sektor perikanan Sulawesi Utara secara real-time 🌊</p>

    <?php if ($role === 'admin'): ?>
      <div class="card">
        <h3>🐟 Jenis Ikan</h3>
        <p><?= $total_ikan ?></p>
      </div>
      <div class="card">
        <h3>📦 Total Stok</h3>
        <p><?= number_format($total_ton) ?> Ton</p>
      </div>
      <div class="card">
        <h3>👨‍🌾 Nelayan Aktif</h3>
        <p><?= count(array_filter($nelayan_list, fn($n) => $n['status']==='Aktif')) ?></p>
      </div>
      <div class="card">
        <h3>🚚 Distribusi</h3>
        <p><?= $total_distribusi ?> Rute</p>
      </div>
    <?php else: ?>
      <div class="card">
        <h3>👤 Mode User</h3>
        <p>Akses Terbatas</p>
      </div>
      <div class="card">
        <h3>📊 Informasi</h3>
        <p>Data umum saja</p>
      </div>
    <?php endif; ?>

  <!-- ======================================================
       HALAMAN: DATA IKAN
       ====================================================== -->
  <?php elseif ($page === 'ikan'): ?>

    <div class="page-topbar">
      <div>
        <h1>🐟 Data Ikan</h1>
        <p style="margin:4px 0 0; color:#64748b; font-size:14px;">Kelola data tangkapan ikan Sulawesi Utara</p>
      </div>
      <?php if ($role === 'admin'): ?>
        <button class="btn-add" onclick="openModal('modal-add-ikan')">＋ Tambah Ikan</button>
      <?php endif; ?>
    </div>

    <div class="table-wrap">
      <table class="tbl">
        <thead>
          <tr>
            <th>#</th>
            <th>Nama Ikan</th>
            <th>Jenis</th>
            <th>Stok (Ton)</th>
            <th>Harga/Kg (Rp)</th>
            <th>Status</th>
            <?php if ($role === 'admin'): ?><th>Aksi</th><?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($ikan_list as $i => $ikan): ?>
          <tr>
            <td><?= $i+1 ?></td>
            <td><strong><?= clean($ikan['nama_ikan']) ?></strong></td>
            <td><?= clean($ikan['jenis']) ?></td>
            <td><?= number_format($ikan['berat_ton']) ?></td>
            <td><?= number_format($ikan['harga_kg']) ?></td>
            <td>
              <span class="badge <?= $ikan['status']==='Tersedia' ? 'badge-green' : 'badge-red' ?>">
                <?= clean($ikan['status']) ?>
              </span>
            </td>
            <?php if ($role === 'admin'): ?>
            <td>
              <button class="btn-edit" onclick="openEditIkan(<?= htmlspecialchars(json_encode($ikan), ENT_QUOTES) ?>)">✏️ Edit</button>
              <button class="btn-del"  onclick="openDelModal('del-form-ikan','<?= $ikan['id'] ?>','<?= clean($ikan['nama_ikan']) ?>')">🗑️ Hapus</button>
            </td>
            <?php endif; ?>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($ikan_list)): ?>
            <tr><td colspan="7" style="text-align:center; padding:30px; color:#94a3b8;">Belum ada data ikan.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <?php if ($role === 'admin'): ?>
    <!-- Modal Tambah Ikan -->
    <div class="modal-overlay" id="modal-add-ikan">
      <div class="modal">
        <button class="modal-close" onclick="closeModal('modal-add-ikan')">✕</button>
        <h3>🐟 Tambah Data Ikan</h3>
        <form method="POST">
          <input type="hidden" name="action" value="add">
          <input type="hidden" name="table"  value="ikan">
          <div class="form-group">
            <label>Nama Ikan</label>
            <input type="text" name="nama_ikan" placeholder="Contoh: Tuna Sirip Kuning" required>
          </div>
          <div class="form-group">
            <label>Jenis</label>
            <select name="jenis" required>
              <option value="">-- Pilih Jenis --</option>
              <option>Pelagis Besar</option>
              <option>Pelagis Kecil</option>
              <option>Demersal</option>
              <option>Karang</option>
            </select>
          </div>
          <div class="form-group">
            <label>Stok (Ton)</label>
            <input type="number" name="berat_ton" step="0.1" min="0" placeholder="0" required>
          </div>
          <div class="form-group">
            <label>Harga per Kg (Rp)</label>
            <input type="number" name="harga_kg" min="0" placeholder="0" required>
          </div>
          <div class="form-group">
            <label>Status</label>
            <select name="status" required>
              <option>Tersedia</option>
              <option>Habis</option>
            </select>
          </div>
          <button type="submit" class="btn-submit">💾 Simpan</button>
        </form>
      </div>
    </div>

    <!-- Modal Edit Ikan -->
    <div class="modal-overlay" id="modal-edit-ikan">
      <div class="modal">
        <button class="modal-close" onclick="closeModal('modal-edit-ikan')">✕</button>
        <h3>✏️ Edit Data Ikan</h3>
        <form method="POST" id="form-edit-ikan">
          <input type="hidden" name="action" value="edit">
          <input type="hidden" name="table"  value="ikan">
          <input type="hidden" name="id"     id="edit-ikan-id">
          <div class="form-group">
            <label>Nama Ikan</label>
            <input type="text" name="nama_ikan" id="edit-ikan-nama" required>
          </div>
          <div class="form-group">
            <label>Jenis</label>
            <select name="jenis" id="edit-ikan-jenis" required>
              <option>Pelagis Besar</option>
              <option>Pelagis Kecil</option>
              <option>Demersal</option>
              <option>Karang</option>
            </select>
          </div>
          <div class="form-group">
            <label>Stok (Ton)</label>
            <input type="number" name="berat_ton" id="edit-ikan-berat" step="0.1" min="0" required>
          </div>
          <div class="form-group">
            <label>Harga per Kg (Rp)</label>
            <input type="number" name="harga_kg" id="edit-ikan-harga" min="0" required>
          </div>
          <div class="form-group">
            <label>Status</label>
            <select name="status" id="edit-ikan-status" required>
              <option>Tersedia</option>
              <option>Habis</option>
            </select>
          </div>
          <button type="submit" class="btn-submit">💾 Simpan Perubahan</button>
        </form>
      </div>
    </div>

    <!-- Modal Hapus Ikan -->
    <div class="modal-overlay" id="modal-del-ikan">
      <div class="modal modal-confirm" style="max-width:380px;">
        <button class="modal-close" onclick="closeModal('modal-del-ikan')">✕</button>
        <h3>🗑️ Hapus Data</h3>
        <p id="del-ikan-text">Yakin ingin menghapus data ini?</p>
        <div class="btn-row">
          <button class="btn-cancel" onclick="closeModal('modal-del-ikan')">Batal</button>
          <form method="POST" style="flex:1;" id="del-form-ikan">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="table"  value="ikan">
            <input type="hidden" name="id"     id="del-ikan-id">
            <button type="submit" class="btn-confirm-del" style="width:100%;">Hapus</button>
          </form>
        </div>
      </div>
    </div>
    <?php endif; ?>

  <!-- ======================================================
       HALAMAN: NELAYAN
       ====================================================== -->
  <?php elseif ($page === 'nelayan'): ?>

    <div class="page-topbar">
      <div>
        <h1>👨‍🌾 Data Nelayan</h1>
        <p style="margin:4px 0 0; color:#64748b; font-size:14px;">Kelola data nelayan & armada kapal</p>
      </div>
      <?php if ($role === 'admin'): ?>
        <button class="btn-add" onclick="openModal('modal-add-nelayan')">＋ Tambah Nelayan</button>
      <?php endif; ?>
    </div>

    <div class="table-wrap">
      <table class="tbl">
        <thead>
          <tr>
            <th>#</th>
            <th>Nama</th>
            <th>Alamat</th>
            <th>Nama Kapal</th>
            <th>Kapasitas (Ton)</th>
            <th>Status</th>
            <?php if ($role === 'admin'): ?><th>Aksi</th><?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($nelayan_list as $i => $nelayan): ?>
          <tr>
            <td><?= $i+1 ?></td>
            <td><strong><?= clean($nelayan['nama']) ?></strong></td>
            <td><?= clean($nelayan['alamat']) ?></td>
            <td><?= clean($nelayan['kapal']) ?></td>
            <td><?= number_format($nelayan['kapasitas_ton']) ?></td>
            <td>
              <span class="badge <?= $nelayan['status']==='Aktif' ? 'badge-green' : 'badge-red' ?>">
                <?= clean($nelayan['status']) ?>
              </span>
            </td>
            <?php if ($role === 'admin'): ?>
            <td>
              <button class="btn-edit" onclick="openEditNelayan(<?= htmlspecialchars(json_encode($nelayan), ENT_QUOTES) ?>)">✏️ Edit</button>
              <button class="btn-del"  onclick="openDelModal('del-form-nelayan','<?= $nelayan['id'] ?>','<?= clean($nelayan['nama']) ?>')">🗑️ Hapus</button>
            </td>
            <?php endif; ?>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($nelayan_list)): ?>
            <tr><td colspan="7" style="text-align:center; padding:30px; color:#94a3b8;">Belum ada data nelayan.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <?php if ($role === 'admin'): ?>
    <!-- Modal Tambah Nelayan -->
    <div class="modal-overlay" id="modal-add-nelayan">
      <div class="modal">
        <button class="modal-close" onclick="closeModal('modal-add-nelayan')">✕</button>
        <h3>👨‍🌾 Tambah Data Nelayan</h3>
        <form method="POST">
          <input type="hidden" name="action" value="add">
          <input type="hidden" name="table"  value="nelayan">
          <div class="form-group">
            <label>Nama Nelayan</label>
            <input type="text" name="nama" placeholder="Nama lengkap" required>
          </div>
          <div class="form-group">
            <label>Alamat</label>
            <input type="text" name="alamat" placeholder="Kota / Kabupaten" required>
          </div>
          <div class="form-group">
            <label>Nama Kapal</label>
            <input type="text" name="kapal" placeholder="Contoh: KM Harapan Jaya" required>
          </div>
          <div class="form-group">
            <label>Kapasitas Kapal (Ton)</label>
            <input type="number" name="kapasitas_ton" step="0.1" min="0" placeholder="0" required>
          </div>
          <div class="form-group">
            <label>Status</label>
            <select name="status" required>
              <option>Aktif</option>
              <option>Tidak Aktif</option>
            </select>
          </div>
          <button type="submit" class="btn-submit">💾 Simpan</button>
        </form>
      </div>
    </div>

    <!-- Modal Edit Nelayan -->
    <div class="modal-overlay" id="modal-edit-nelayan">
      <div class="modal">
        <button class="modal-close" onclick="closeModal('modal-edit-nelayan')">✕</button>
        <h3>✏️ Edit Data Nelayan</h3>
        <form method="POST">
          <input type="hidden" name="action" value="edit">
          <input type="hidden" name="table"  value="nelayan">
          <input type="hidden" name="id"     id="edit-nelayan-id">
          <div class="form-group">
            <label>Nama Nelayan</label>
            <input type="text" name="nama" id="edit-nelayan-nama" required>
          </div>
          <div class="form-group">
            <label>Alamat</label>
            <input type="text" name="alamat" id="edit-nelayan-alamat" required>
          </div>
          <div class="form-group">
            <label>Nama Kapal</label>
            <input type="text" name="kapal" id="edit-nelayan-kapal" required>
          </div>
          <div class="form-group">
            <label>Kapasitas Kapal (Ton)</label>
            <input type="number" name="kapasitas_ton" id="edit-nelayan-kapasitas" step="0.1" min="0" required>
          </div>
          <div class="form-group">
            <label>Status</label>
            <select name="status" id="edit-nelayan-status" required>
              <option>Aktif</option>
              <option>Tidak Aktif</option>
            </select>
          </div>
          <button type="submit" class="btn-submit">💾 Simpan Perubahan</button>
        </form>
      </div>
    </div>

    <!-- Modal Hapus Nelayan -->
    <div class="modal-overlay" id="modal-del-nelayan">
      <div class="modal modal-confirm" style="max-width:380px;">
        <button class="modal-close" onclick="closeModal('modal-del-nelayan')">✕</button>
        <h3>🗑️ Hapus Data</h3>
        <p id="del-nelayan-text">Yakin ingin menghapus data ini?</p>
        <div class="btn-row">
          <button class="btn-cancel" onclick="closeModal('modal-del-nelayan')">Batal</button>
          <form method="POST" style="flex:1;" id="del-form-nelayan">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="table"  value="nelayan">
            <input type="hidden" name="id"     id="del-nelayan-id">
            <button type="submit" class="btn-confirm-del" style="width:100%;">Hapus</button>
          </form>
        </div>
      </div>
    </div>
    <?php endif; ?>

  <!-- ======================================================
       HALAMAN: DISTRIBUSI
       ====================================================== -->
  <?php elseif ($page === 'distribusi'): ?>

    <div class="page-topbar">
      <div>
        <h1>🚚 Data Distribusi</h1>
        <p style="margin:4px 0 0; color:#64748b; font-size:14px;">Kelola pengiriman hasil tangkapan</p>
      </div>
      <?php if ($role === 'admin'): ?>
        <button class="btn-add" onclick="openModal('modal-add-distribusi')">＋ Tambah Distribusi</button>
      <?php endif; ?>
    </div>

    <div class="table-wrap">
      <table class="tbl">
        <thead>
          <tr>
            <th>#</th>
            <th>Tujuan</th>
            <th>Jenis Ikan</th>
            <th>Jumlah (Ton)</th>
            <th>Tanggal</th>
            <th>Status</th>
            <?php if ($role === 'admin'): ?><th>Aksi</th><?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($distribusi_list as $i => $dist): ?>
          <tr>
            <td><?= $i+1 ?></td>
            <td><strong><?= clean($dist['tujuan']) ?></strong></td>
            <td><?= clean($dist['jenis_ikan']) ?></td>
            <td><?= number_format($dist['jumlah_ton']) ?></td>
            <td><?= clean($dist['tanggal']) ?></td>
            <td>
              <?php
                $bc = match($dist['status']) {
                  'Terkirim' => 'badge-green',
                  'Proses'   => 'badge-blue',
                  default    => 'badge-yellow'
                };
              ?>
              <span class="badge <?= $bc ?>"><?= clean($dist['status']) ?></span>
            </td>
            <?php if ($role === 'admin'): ?>
            <td>
              <button class="btn-edit" onclick="openEditDistribusi(<?= htmlspecialchars(json_encode($dist), ENT_QUOTES) ?>)">✏️ Edit</button>
              <button class="btn-del"  onclick="openDelModal('del-form-distribusi','<?= $dist['id'] ?>','rute ke <?= clean($dist['tujuan']) ?>')">🗑️ Hapus</button>
            </td>
            <?php endif; ?>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($distribusi_list)): ?>
            <tr><td colspan="7" style="text-align:center; padding:30px; color:#94a3b8;">Belum ada data distribusi.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <?php if ($role === 'admin'): ?>
    <!-- Modal Tambah Distribusi -->
    <div class="modal-overlay" id="modal-add-distribusi">
      <div class="modal">
        <button class="modal-close" onclick="closeModal('modal-add-distribusi')">✕</button>
        <h3>🚚 Tambah Data Distribusi</h3>
        <form method="POST">
          <input type="hidden" name="action" value="add">
          <input type="hidden" name="table"  value="distribusi">
          <div class="form-group">
            <label>Tujuan</label>
            <input type="text" name="tujuan" placeholder="Kota tujuan" required>
          </div>
          <div class="form-group">
            <label>Jenis Ikan</label>
            <input type="text" name="jenis_ikan" placeholder="Contoh: Tuna, Cakalang" required>
          </div>
          <div class="form-group">
            <label>Jumlah (Ton)</label>
            <input type="number" name="jumlah_ton" step="0.1" min="0" placeholder="0" required>
          </div>
          <div class="form-group">
            <label>Tanggal</label>
            <input type="date" name="tanggal" required>
          </div>
          <div class="form-group">
            <label>Status</label>
            <select name="status" required>
              <option>Pending</option>
              <option>Proses</option>
              <option>Terkirim</option>
            </select>
          </div>
          <button type="submit" class="btn-submit">💾 Simpan</button>
        </form>
      </div>
    </div>

    <!-- Modal Edit Distribusi -->
    <div class="modal-overlay" id="modal-edit-distribusi">
      <div class="modal">
        <button class="modal-close" onclick="closeModal('modal-edit-distribusi')">✕</button>
        <h3>✏️ Edit Data Distribusi</h3>
        <form method="POST">
          <input type="hidden" name="action" value="edit">
          <input type="hidden" name="table"  value="distribusi">
          <input type="hidden" name="id"     id="edit-dist-id">
          <div class="form-group">
            <label>Tujuan</label>
            <input type="text" name="tujuan" id="edit-dist-tujuan" required>
          </div>
          <div class="form-group">
            <label>Jenis Ikan</label>
            <input type="text" name="jenis_ikan" id="edit-dist-jenis" required>
          </div>
          <div class="form-group">
            <label>Jumlah (Ton)</label>
            <input type="number" name="jumlah_ton" id="edit-dist-jumlah" step="0.1" min="0" required>
          </div>
          <div class="form-group">
            <label>Tanggal</label>
            <input type="date" name="tanggal" id="edit-dist-tanggal" required>
          </div>
          <div class="form-group">
            <label>Status</label>
            <select name="status" id="edit-dist-status" required>
              <option>Pending</option>
              <option>Proses</option>
              <option>Terkirim</option>
            </select>
          </div>
          <button type="submit" class="btn-submit">💾 Simpan Perubahan</button>
        </form>
      </div>
    </div>

    <!-- Modal Hapus Distribusi -->
    <div class="modal-overlay" id="modal-del-distribusi">
      <div class="modal modal-confirm" style="max-width:380px;">
        <button class="modal-close" onclick="closeModal('modal-del-distribusi')">✕</button>
        <h3>🗑️ Hapus Data</h3>
        <p id="del-distribusi-text">Yakin ingin menghapus data ini?</p>
        <div class="btn-row">
          <button class="btn-cancel" onclick="closeModal('modal-del-distribusi')">Batal</button>
          <form method="POST" style="flex:1;" id="del-form-distribusi">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="table"  value="distribusi">
            <input type="hidden" name="id"     id="del-distribusi-id">
            <button type="submit" class="btn-confirm-del" style="width:100%;">Hapus</button>
          </form>
        </div>
      </div>
    </div>
    <?php endif; ?>

  <?php endif; ?>

</div><!-- /content -->

<!-- ==================== JAVASCRIPT ==================== -->
<script>
// Buka / tutup modal
function openModal(id) {
  document.getElementById(id).classList.add('open');
}
function closeModal(id) {
  document.getElementById(id).classList.remove('open');
}

// Tutup modal jika klik di luar box
document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
  overlay.addEventListener('click', function(e) {
    if (e.target === overlay) overlay.classList.remove('open');
  });
});

// ── Edit Ikan ─────────────────────────────────────────────
function openEditIkan(data) {
  document.getElementById('edit-ikan-id').value     = data.id;
  document.getElementById('edit-ikan-nama').value   = data.nama_ikan;
  document.getElementById('edit-ikan-berat').value  = data.berat_ton;
  document.getElementById('edit-ikan-harga').value  = data.harga_kg;
  setSelect('edit-ikan-jenis',   data.jenis);
  setSelect('edit-ikan-status',  data.status);
  openModal('modal-edit-ikan');
}

// ── Edit Nelayan ───────────────────────────────────────────
function openEditNelayan(data) {
  document.getElementById('edit-nelayan-id').value        = data.id;
  document.getElementById('edit-nelayan-nama').value      = data.nama;
  document.getElementById('edit-nelayan-alamat').value    = data.alamat;
  document.getElementById('edit-nelayan-kapal').value     = data.kapal;
  document.getElementById('edit-nelayan-kapasitas').value = data.kapasitas_ton;
  setSelect('edit-nelayan-status', data.status);
  openModal('modal-edit-nelayan');
}

// ── Edit Distribusi ────────────────────────────────────────
function openEditDistribusi(data) {
  document.getElementById('edit-dist-id').value      = data.id;
  document.getElementById('edit-dist-tujuan').value  = data.tujuan;
  document.getElementById('edit-dist-jenis').value   = data.jenis_ikan;
  document.getElementById('edit-dist-jumlah').value  = data.jumlah_ton;
  document.getElementById('edit-dist-tanggal').value = data.tanggal;
  setSelect('edit-dist-status', data.status);
  openModal('modal-edit-distribusi');
}

// ── Delete universal ───────────────────────────────────────
function openDelModal(formId, id, name) {
  // Tentukan modal berdasarkan form id
  var map = {
    'del-form-ikan':       { modal: 'modal-del-ikan',       textEl: 'del-ikan-text',       idEl: 'del-ikan-id'       },
    'del-form-nelayan':    { modal: 'modal-del-nelayan',     textEl: 'del-nelayan-text',    idEl: 'del-nelayan-id'    },
    'del-form-distribusi': { modal: 'modal-del-distribusi',  textEl: 'del-distribusi-text', idEl: 'del-distribusi-id' }
  };
  var cfg = map[formId];
  document.getElementById(cfg.textEl).textContent = 'Yakin ingin menghapus "' + name + '"? Tindakan ini tidak bisa dibatalkan.';
  document.getElementById(cfg.idEl).value = id;
  openModal(cfg.modal);
}

// ── Helper: set select value ───────────────────────────────
function setSelect(id, val) {
  var sel = document.getElementById(id);
  for (var i = 0; i < sel.options.length; i++) {
    if (sel.options[i].value === val) { sel.selectedIndex = i; break; }
  }
}
</script>

</body>
</html>