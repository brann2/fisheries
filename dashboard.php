<?php
session_start();

// ============================================================
// SESSION TIMEOUT (30 menit)
// ============================================================
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

// ============================================================
// PROTEKSI LOGIN
// ============================================================
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];
$role     = $_SESSION['role'];

// ============================================================
// SECURITY HEADER
// ============================================================
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");

// ============================================================
// KONEKSI DATABASE (mysqli - include dari config)
// ============================================================
include 'config/koneksi.php';

// ============================================================
// HELPER
// ============================================================
function clean($val) {
    return htmlspecialchars(trim($val), ENT_QUOTES, 'UTF-8');
}

// ============================================================
// ACTIVE PAGE
// ============================================================
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// ============================================================
// PESAN NOTIFIKASI
// ============================================================
$notif      = '';
$notif_type = 'success';

if (isset($_GET['success'])) {
    $kode = (int) $_GET['success'];
    if ($kode === 1) $notif = 'Data berhasil ditambahkan.';
    if ($kode === 2) $notif = 'Data berhasil diperbarui.';
    if ($kode === 3) $notif = 'Data berhasil dihapus.';
}
if (isset($_GET['error'])) {
    $notif_type = 'error';
    $err = $_GET['error'];
    if ($err === 'invalid')          $notif = 'Input tidak valid. Periksa kembali data Anda.';
    elseif ($err === 'failed')       $notif = 'Gagal menyimpan data. Coba lagi.';
    elseif ($err === 'unauthorized') $notif = 'Akses ditolak.';
    else                             $notif = 'Terjadi kesalahan.';
}

// ============================================================
// FETCH DATA
// ============================================================

// Data Ikan
$ikan_list = array();
$res = mysqli_query($conn, "SELECT * FROM data_ikan ORDER BY id DESC");
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $ikan_list[] = $row;
    }
}

// Data Nelayan
$nelayan_list = array();
$res = mysqli_query($conn, "SELECT * FROM data_nelayan ORDER BY id DESC");
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $nelayan_list[] = $row;
    }
}

// Data Distribusi
$distribusi_list = array();
$res = mysqli_query($conn, "SELECT * FROM data_distribusi ORDER BY id DESC");
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $distribusi_list[] = $row;
    }
}

// Stats untuk dashboard
$total_ikan       = count($ikan_list);
$total_nelayan    = count($nelayan_list);
$total_distribusi = count($distribusi_list);

$total_ton = 0;
foreach ($ikan_list as $i_row) { $total_ton += $i_row['berat_ton']; }

$aktif_count = 0;
foreach ($nelayan_list as $n_row) { if ($n_row['status'] === 'Aktif') $aktif_count++; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Fisheries Dashboard</title>
<style>
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
.sidebar h2 { margin-bottom: 30px; font-size: 22px; letter-spacing: 1px; }
.sidebar a {
  display: block; padding: 12px; margin: 8px 0;
  text-decoration: none; color: #cbd5f5;
  border-radius: 8px; transition: 0.3s;
}
.sidebar a:hover, .sidebar a.active {
  background: #3b82f6; color: white; transform: translateX(5px);
}

/* CONTENT */
.content { margin-left: 260px; padding: 40px; }

/* CARD */
.card {
  display: inline-block; width: 220px; margin: 15px;
  padding: 25px; background: white; border-radius: 15px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.1);
  transition: 0.3s; position: relative; overflow: hidden; vertical-align: top;
}
.card:hover { transform: translateY(-5px) scale(1.03); }
.card::before {
  content: ""; position: absolute; top: 0; left: 0;
  width: 100%; height: 5px;
  background: linear-gradient(90deg, #3b82f6, #06b6d4);
}
.card h3 { margin: 0; color: #1e293b; }
.card p  { font-size: 22px; font-weight: bold; color: #2563eb; margin-top: 10px; }

/* TOPBAR */
.page-topbar {
  display: flex; align-items: center;
  justify-content: space-between;
  margin-bottom: 24px; flex-wrap: wrap; gap: 12px;
}
.page-topbar h1 { font-size: 1.6rem; color: #1e293b; margin: 0; }

/* BTN TAMBAH */
.btn-add {
  background: linear-gradient(90deg, #1e3a8a, #2563eb);
  color: white; border: none; padding: 11px 22px;
  border-radius: 10px; font-size: 14px; font-weight: 600;
  cursor: pointer; box-shadow: 0 4px 14px rgba(37,99,235,0.35);
  transition: 0.2s; display: inline-flex; align-items: center; gap: 6px;
}
.btn-add:hover { background: linear-gradient(90deg, #163070, #1d4ed8); transform: translateY(-2px); }

/* NOTIFIKASI */
.notif {
  padding: 13px 18px; border-radius: 10px;
  margin-bottom: 20px; font-size: 14px; font-weight: 500;
}
.notif-success { background: #d1fae5; color: #065f46; border-left: 4px solid #10b981; }
.notif-error   { background: #fee2e2; color: #991b1b; border-left: 4px solid #ef4444; }

/* TABEL */
.table-wrap {
  background: white; border-radius: 15px;
  box-shadow: 0 6px 24px rgba(0,0,0,0.08); overflow: hidden;
}
.tbl { width: 100%; border-collapse: collapse; }
.tbl thead { background: linear-gradient(90deg, #1e3a8a, #2563eb); color: white; }
.tbl th { padding: 14px 16px; text-align: left; font-size: 13px; letter-spacing: 0.5px; font-weight: 600; }
.tbl td { padding: 13px 16px; font-size: 14px; color: #334155; border-bottom: 1px solid #f1f5f9; }
.tbl tbody tr:hover { background: #f8fafc; }
.tbl tbody tr:last-child td { border-bottom: none; }

/* BADGE */
.badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
.badge-green  { background: #d1fae5; color: #065f46; }
.badge-red    { background: #fee2e2; color: #991b1b; }
.badge-yellow { background: #fef3c7; color: #92400e; }
.badge-blue   { background: #dbeafe; color: #1e40af; }

/* BTN AKSI */
.btn-edit, .btn-del {
  border: none; padding: 7px 14px; border-radius: 7px;
  font-size: 13px; cursor: pointer; font-weight: 600;
  transition: 0.2s; margin-right: 4px;
}
.btn-edit { background: #dbeafe; color: #1e40af; }
.btn-edit:hover { background: #bfdbfe; }
.btn-del  { background: #fee2e2; color: #991b1b; }
.btn-del:hover  { background: #fecaca; }

/* MODAL */
.modal-overlay {
  display: none; position: fixed; inset: 0;
  background: rgba(15,23,42,0.55); z-index: 999;
  justify-content: center; align-items: center;
}
.modal-overlay.open { display: flex; }
.modal {
  background: white; border-radius: 18px; padding: 36px 32px 28px;
  min-width: 380px; max-width: 95vw;
  box-shadow: 0 20px 60px rgba(15,36,96,0.3);
  animation: fadeInUp 0.3s ease both;
  position: relative; max-height: 90vh; overflow-y: auto;
}
.modal h3 { margin: 0 0 22px; color: #1e3a8a; font-size: 1.2rem; }
.modal-close {
  position: absolute; top: 16px; right: 18px;
  background: none; border: none; font-size: 20px;
  cursor: pointer; color: #94a3b8; line-height: 1; padding: 0;
}
.modal-close:hover { color: #1e293b; }

/* FORM */
.form-group { margin-bottom: 16px; }
.form-group label { display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px; }
.form-group input, .form-group select {
  width: 100%; padding: 11px 14px; border-radius: 9px;
  border: 1.5px solid #cbd5e1; font-size: 14px;
  background: #f8fafc; color: #1e293b;
  transition: border 0.2s, box-shadow 0.2s;
}
.form-group input:focus, .form-group select:focus {
  outline: none; border-color: #2563eb; background: #fff;
  box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
}
.btn-submit {
  width: 100%; padding: 13px;
  background: linear-gradient(90deg, #1e3a8a, #2563eb);
  color: white; border: none; border-radius: 10px;
  font-size: 15px; font-weight: 700; cursor: pointer;
  margin-top: 6px; box-shadow: 0 4px 14px rgba(37,99,235,0.4); transition: 0.2s;
}
.btn-submit:hover { background: linear-gradient(90deg, #163070, #1d4ed8); transform: translateY(-2px); }

/* MODAL KONFIRMASI */
.modal-confirm p { color: #475569; margin: 0 0 22px; line-height: 1.6; }
.btn-row { display: flex; gap: 10px; }
.btn-cancel {
  flex: 1; padding: 12px; border-radius: 10px;
  border: 1.5px solid #cbd5e1; background: white;
  color: #475569; font-size: 15px; font-weight: 600; cursor: pointer; transition: 0.2s;
}
.btn-cancel:hover { background: #f1f5f9; }
.btn-confirm-del {
  flex: 1; padding: 12px; border-radius: 10px; border: none;
  background: #ef4444; color: white; font-size: 15px;
  font-weight: 700; cursor: pointer; width: 100%; transition: 0.2s;
}
.btn-confirm-del:hover { background: #dc2626; }

.welcome-bar { text-align: right; font-weight: bold; color: #1e3a8a; margin-bottom: 4px; }
.page-sub    { color: #475569; margin-bottom: 28px; margin-top: 4px; }

@keyframes fadeInUp {
  from { opacity:0; transform:translateY(24px); }
  to   { opacity:1; transform:translateY(0); }
}

@media (max-width: 768px) {
  .sidebar { width: 200px; }
  .content { margin-left: 210px; }
  .card    { width: 100%; }
  .modal   { min-width: 90vw; }
}
</style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
  <h2>&#127754; Fisheries</h2>
  <a href="dashboard.php?page=dashboard" class="<?php echo $page==='dashboard'?'active':''; ?>">&#128202; Dashboard</a>
  <a href="dashboard.php?page=ikan"      class="<?php echo $page==='ikan'     ?'active':''; ?>">&#128031; Data Ikan</a>
  <a href="dashboard.php?page=nelayan"   class="<?php echo $page==='nelayan'  ?'active':''; ?>">&#128119; Nelayan</a>
  <a href="dashboard.php?page=distribusi"class="<?php echo $page==='distribusi'?'active':''; ?>">&#128666; Distribusi</a>
  <a href="logout.php">&#128682; Logout</a>
</div>

<!-- CONTENT -->
<div class="content">

  <div class="welcome-bar">
    Welcome, <?php echo clean($username); ?>
    (<?php echo $role === 'admin' ? 'Admin &#128081;' : 'User &#128100;'; ?>)
  </div>

  <?php if ($notif !== ''): ?>
  <div class="notif notif-<?php echo $notif_type; ?>">
    <?php echo $notif_type === 'success' ? '&#9989;' : '&#10060;'; ?> <?php echo $notif; ?>
  </div>
  <?php endif; ?>

  <!-- ======================================================
       DASHBOARD
       ====================================================== -->
  <?php if ($page === 'dashboard'): ?>

    <h1>Dashboard</h1>
    <p class="page-sub">Monitoring sektor perikanan Sulawesi Utara secara real-time &#127754;</p>

    <?php if ($role === 'admin'): ?>
      <div class="card">
        <h3>&#128031; Jenis Ikan</h3>
        <p><?php echo $total_ikan; ?></p>
      </div>
      <div class="card">
        <h3>&#128230; Total Stok</h3>
        <p><?php echo number_format($total_ton); ?> Ton</p>
      </div>
      <div class="card">
        <h3>&#128119; Nelayan Aktif</h3>
        <p><?php echo $aktif_count; ?></p>
      </div>
      <div class="card">
        <h3>&#128666; Distribusi</h3>
        <p><?php echo $total_distribusi; ?> Rute</p>
      </div>
    <?php else: ?>
      <div class="card">
        <h3>&#128100; Mode User</h3>
        <p>Akses Terbatas</p>
      </div>
      <div class="card">
        <h3>&#128202; Informasi</h3>
        <p>Data umum saja</p>
      </div>
    <?php endif; ?>

  <!-- ======================================================
       DATA IKAN
       ====================================================== -->
  <?php elseif ($page === 'ikan'): ?>

    <div class="page-topbar">
      <div>
        <h1>&#128031; Data Ikan</h1>
        <p style="margin:4px 0 0;color:#64748b;font-size:14px;">Kelola data tangkapan ikan Sulawesi Utara</p>
      </div>
      <?php if ($role === 'admin'): ?>
        <button class="btn-add" onclick="openModal('modal-add-ikan')">+ Tambah Ikan</button>
      <?php endif; ?>
    </div>

    <div class="table-wrap">
      <table class="tbl">
        <thead>
          <tr>
            <th>#</th><th>Nama Ikan</th><th>Jenis</th>
            <th>Stok (Ton)</th><th>Harga/Kg (Rp)</th><th>Status</th>
            <?php if ($role === 'admin'): ?><th>Aksi</th><?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($ikan_list)): ?>
            <tr><td colspan="7" style="text-align:center;padding:30px;color:#94a3b8;">Belum ada data ikan.</td></tr>
          <?php else: ?>
            <?php foreach ($ikan_list as $idx => $ikan): ?>
            <tr>
              <td><?php echo $idx+1; ?></td>
              <td><strong><?php echo clean($ikan['nama_ikan']); ?></strong></td>
              <td><?php echo clean($ikan['jenis']); ?></td>
              <td><?php echo number_format($ikan['berat_ton']); ?></td>
              <td>Rp <?php echo number_format($ikan['harga_kg']); ?></td>
              <td>
                <span class="badge <?php echo $ikan['status']==='Tersedia'?'badge-green':'badge-red'; ?>">
                  <?php echo clean($ikan['status']); ?>
                </span>
              </td>
              <?php if ($role === 'admin'): ?>
              <td>
                <button class="btn-edit" onclick="openEditIkan(<?php echo htmlspecialchars(json_encode($ikan), ENT_QUOTES); ?>)">Edit</button>
                <button class="btn-del"  onclick="openDelIkan(<?php echo (int)$ikan['id']; ?>,'<?php echo clean($ikan['nama_ikan']); ?>')">Hapus</button>
              </td>
              <?php endif; ?>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <?php if ($role === 'admin'): ?>

    <!-- Modal Tambah Ikan -->
    <div class="modal-overlay" id="modal-add-ikan">
      <div class="modal">
        <button class="modal-close" onclick="closeModal('modal-add-ikan')">x</button>
        <h3>Tambah Data Ikan</h3>
        <form method="POST" action="actions/tambah_ikan.php">
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
            <input type="number" name="berat_ton" step="0.01" min="0.01" placeholder="0" required>
          </div>
          <div class="form-group">
            <label>Harga per Kg (Rp)</label>
            <input type="number" name="harga_kg" min="1" placeholder="0" required>
          </div>
          <div class="form-group">
            <label>Status</label>
            <select name="status" required>
              <option>Tersedia</option>
              <option>Habis</option>
            </select>
          </div>
          <button type="submit" class="btn-submit">Simpan</button>
        </form>
      </div>
    </div>

    <!-- Modal Edit Ikan -->
    <div class="modal-overlay" id="modal-edit-ikan">
      <div class="modal">
        <button class="modal-close" onclick="closeModal('modal-edit-ikan')">x</button>
        <h3>Edit Data Ikan</h3>
        <form method="POST" action="actions/edit_ikan.php">
          <input type="hidden" name="id" id="edit-ikan-id">
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
            <input type="number" name="berat_ton" id="edit-ikan-berat" step="0.01" min="0.01" required>
          </div>
          <div class="form-group">
            <label>Harga per Kg (Rp)</label>
            <input type="number" name="harga_kg" id="edit-ikan-harga" min="1" required>
          </div>
          <div class="form-group">
            <label>Status</label>
            <select name="status" id="edit-ikan-status" required>
              <option>Tersedia</option>
              <option>Habis</option>
            </select>
          </div>
          <button type="submit" class="btn-submit">Simpan Perubahan</button>
        </form>
      </div>
    </div>

    <!-- Modal Hapus Ikan -->
    <div class="modal-overlay" id="modal-del-ikan">
      <div class="modal modal-confirm" style="max-width:380px;">
        <button class="modal-close" onclick="closeModal('modal-del-ikan')">x</button>
        <h3>Hapus Data Ikan</h3>
        <p id="del-ikan-text"></p>
        <div class="btn-row">
          <button class="btn-cancel" onclick="closeModal('modal-del-ikan')">Batal</button>
          <form method="POST" action="actions/hapus_ikan.php" style="flex:1;">
            <input type="hidden" name="id" id="del-ikan-id">
            <button type="submit" class="btn-confirm-del">Hapus</button>
          </form>
        </div>
      </div>
    </div>

    <?php endif; ?>

  <!-- ======================================================
       DATA NELAYAN
       ====================================================== -->
  <?php elseif ($page === 'nelayan'): ?>

    <div class="page-topbar">
      <div>
        <h1>&#128119; Data Nelayan</h1>
        <p style="margin:4px 0 0;color:#64748b;font-size:14px;">Kelola data nelayan &amp; armada kapal</p>
      </div>
      <?php if ($role === 'admin'): ?>
        <button class="btn-add" onclick="openModal('modal-add-nelayan')">+ Tambah Nelayan</button>
      <?php endif; ?>
    </div>

    <div class="table-wrap">
      <table class="tbl">
        <thead>
          <tr>
            <th>#</th><th>Nama</th><th>Alamat</th>
            <th>Nama Kapal</th><th>Kapasitas (Ton)</th><th>Status</th>
            <?php if ($role === 'admin'): ?><th>Aksi</th><?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($nelayan_list)): ?>
            <tr><td colspan="7" style="text-align:center;padding:30px;color:#94a3b8;">Belum ada data nelayan.</td></tr>
          <?php else: ?>
            <?php foreach ($nelayan_list as $idx => $nelayan): ?>
            <tr>
              <td><?php echo $idx+1; ?></td>
              <td><strong><?php echo clean($nelayan['nama']); ?></strong></td>
              <td><?php echo clean($nelayan['alamat']); ?></td>
              <td><?php echo clean($nelayan['kapal']); ?></td>
              <td><?php echo number_format($nelayan['kapasitas_ton']); ?></td>
              <td>
                <span class="badge <?php echo $nelayan['status']==='Aktif'?'badge-green':'badge-red'; ?>">
                  <?php echo clean($nelayan['status']); ?>
                </span>
              </td>
              <?php if ($role === 'admin'): ?>
              <td>
                <button class="btn-edit" onclick="openEditNelayan(<?php echo htmlspecialchars(json_encode($nelayan), ENT_QUOTES); ?>)">Edit</button>
                <button class="btn-del"  onclick="openDelNelayan(<?php echo (int)$nelayan['id']; ?>,'<?php echo clean($nelayan['nama']); ?>')">Hapus</button>
              </td>
              <?php endif; ?>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <?php if ($role === 'admin'): ?>

    <!-- Modal Tambah Nelayan -->
    <div class="modal-overlay" id="modal-add-nelayan">
      <div class="modal">
        <button class="modal-close" onclick="closeModal('modal-add-nelayan')">x</button>
        <h3>Tambah Data Nelayan</h3>
        <form method="POST" action="actions/tambah_nelayan.php">
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
            <label>Kapasitas (Ton)</label>
            <input type="number" name="kapasitas_ton" step="0.01" min="0.01" placeholder="0" required>
          </div>
          <div class="form-group">
            <label>Status</label>
            <select name="status" required>
              <option>Aktif</option>
              <option>Tidak Aktif</option>
            </select>
          </div>
          <button type="submit" class="btn-submit">Simpan</button>
        </form>
      </div>
    </div>

    <!-- Modal Edit Nelayan -->
    <div class="modal-overlay" id="modal-edit-nelayan">
      <div class="modal">
        <button class="modal-close" onclick="closeModal('modal-edit-nelayan')">x</button>
        <h3>Edit Data Nelayan</h3>
        <form method="POST" action="actions/edit_nelayan.php">
          <input type="hidden" name="id" id="edit-nelayan-id">
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
            <label>Kapasitas (Ton)</label>
            <input type="number" name="kapasitas_ton" id="edit-nelayan-kapasitas" step="0.01" min="0.01" required>
          </div>
          <div class="form-group">
            <label>Status</label>
            <select name="status" id="edit-nelayan-status" required>
              <option>Aktif</option>
              <option>Tidak Aktif</option>
            </select>
          </div>
          <button type="submit" class="btn-submit">Simpan Perubahan</button>
        </form>
      </div>
    </div>

    <!-- Modal Hapus Nelayan -->
    <div class="modal-overlay" id="modal-del-nelayan">
      <div class="modal modal-confirm" style="max-width:380px;">
        <button class="modal-close" onclick="closeModal('modal-del-nelayan')">x</button>
        <h3>Hapus Data Nelayan</h3>
        <p id="del-nelayan-text"></p>
        <div class="btn-row">
          <button class="btn-cancel" onclick="closeModal('modal-del-nelayan')">Batal</button>
          <form method="POST" action="actions/hapus_nelayan.php" style="flex:1;">
            <input type="hidden" name="id" id="del-nelayan-id">
            <button type="submit" class="btn-confirm-del">Hapus</button>
          </form>
        </div>
      </div>
    </div>

    <?php endif; ?>

  <!-- ======================================================
       DATA DISTRIBUSI
       ====================================================== -->
  <?php elseif ($page === 'distribusi'): ?>

    <div class="page-topbar">
      <div>
        <h1>&#128666; Data Distribusi</h1>
        <p style="margin:4px 0 0;color:#64748b;font-size:14px;">Kelola pengiriman hasil tangkapan</p>
      </div>
      <?php if ($role === 'admin'): ?>
        <button class="btn-add" onclick="openModal('modal-add-distribusi')">+ Tambah Distribusi</button>
      <?php endif; ?>
    </div>

    <div class="table-wrap">
      <table class="tbl">
        <thead>
          <tr>
            <th>#</th><th>Tujuan</th><th>Jenis Ikan</th>
            <th>Jumlah (Ton)</th><th>Tanggal</th><th>Status</th>
            <?php if ($role === 'admin'): ?><th>Aksi</th><?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($distribusi_list)): ?>
            <tr><td colspan="7" style="text-align:center;padding:30px;color:#94a3b8;">Belum ada data distribusi.</td></tr>
          <?php else: ?>
            <?php foreach ($distribusi_list as $idx => $dist): ?>
            <tr>
              <td><?php echo $idx+1; ?></td>
              <td><strong><?php echo clean($dist['tujuan']); ?></strong></td>
              <td><?php echo clean($dist['jenis_ikan']); ?></td>
              <td><?php echo number_format($dist['jumlah_ton']); ?></td>
              <td><?php echo clean($dist['tanggal']); ?></td>
              <td>
                <?php
                  if ($dist['status'] === 'Terkirim')   { $bc = 'badge-green'; }
                  elseif ($dist['status'] === 'Proses') { $bc = 'badge-blue';  }
                  else                                  { $bc = 'badge-yellow';}
                ?>
                <span class="badge <?php echo $bc; ?>"><?php echo clean($dist['status']); ?></span>
              </td>
              <?php if ($role === 'admin'): ?>
              <td>
                <button class="btn-edit" onclick="openEditDistribusi(<?php echo htmlspecialchars(json_encode($dist), ENT_QUOTES); ?>)">Edit</button>
                <button class="btn-del"  onclick="openDelDistribusi(<?php echo (int)$dist['id']; ?>,'<?php echo clean($dist['tujuan']); ?>')">Hapus</button>
              </td>
              <?php endif; ?>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <?php if ($role === 'admin'): ?>

    <!-- Modal Tambah Distribusi -->
    <div class="modal-overlay" id="modal-add-distribusi">
      <div class="modal">
        <button class="modal-close" onclick="closeModal('modal-add-distribusi')">x</button>
        <h3>Tambah Data Distribusi</h3>
        <form method="POST" action="actions/tambah_distribusi.php">
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
            <input type="number" name="jumlah_ton" step="0.01" min="0.01" placeholder="0" required>
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
          <button type="submit" class="btn-submit">Simpan</button>
        </form>
      </div>
    </div>

    <!-- Modal Edit Distribusi -->
    <div class="modal-overlay" id="modal-edit-distribusi">
      <div class="modal">
        <button class="modal-close" onclick="closeModal('modal-edit-distribusi')">x</button>
        <h3>Edit Data Distribusi</h3>
        <form method="POST" action="actions/edit_distribusi.php">
          <input type="hidden" name="id" id="edit-dist-id">
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
            <input type="number" name="jumlah_ton" id="edit-dist-jumlah" step="0.01" min="0.01" required>
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
          <button type="submit" class="btn-submit">Simpan Perubahan</button>
        </form>
      </div>
    </div>

    <!-- Modal Hapus Distribusi -->
    <div class="modal-overlay" id="modal-del-distribusi">
      <div class="modal modal-confirm" style="max-width:380px;">
        <button class="modal-close" onclick="closeModal('modal-del-distribusi')">x</button>
        <h3>Hapus Data Distribusi</h3>
        <p id="del-distribusi-text"></p>
        <div class="btn-row">
          <button class="btn-cancel" onclick="closeModal('modal-del-distribusi')">Batal</button>
          <form method="POST" action="actions/hapus_distribusi.php" style="flex:1;">
            <input type="hidden" name="id" id="del-distribusi-id">
            <button type="submit" class="btn-confirm-del">Hapus</button>
          </form>
        </div>
      </div>
    </div>

    <?php endif; ?>

  <?php endif; ?>

</div><!-- /content -->

<script>
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
  overlay.addEventListener('click', function(e) {
    if (e.target === overlay) overlay.classList.remove('open');
  });
});

function setSelect(id, val) {
  var sel = document.getElementById(id);
  for (var i = 0; i < sel.options.length; i++) {
    if (sel.options[i].value === val || sel.options[i].text === val) {
      sel.selectedIndex = i; break;
    }
  }
}

function openEditIkan(data) {
  document.getElementById('edit-ikan-id').value    = data.id;
  document.getElementById('edit-ikan-nama').value  = data.nama_ikan;
  document.getElementById('edit-ikan-berat').value = data.berat_ton;
  document.getElementById('edit-ikan-harga').value = data.harga_kg;
  setSelect('edit-ikan-jenis',  data.jenis);
  setSelect('edit-ikan-status', data.status);
  openModal('modal-edit-ikan');
}

function openEditNelayan(data) {
  document.getElementById('edit-nelayan-id').value        = data.id;
  document.getElementById('edit-nelayan-nama').value      = data.nama;
  document.getElementById('edit-nelayan-alamat').value    = data.alamat;
  document.getElementById('edit-nelayan-kapal').value     = data.kapal;
  document.getElementById('edit-nelayan-kapasitas').value = data.kapasitas_ton;
  setSelect('edit-nelayan-status', data.status);
  openModal('modal-edit-nelayan');
}

function openEditDistribusi(data) {
  document.getElementById('edit-dist-id').value      = data.id;
  document.getElementById('edit-dist-tujuan').value  = data.tujuan;
  document.getElementById('edit-dist-jenis').value   = data.jenis_ikan;
  document.getElementById('edit-dist-jumlah').value  = data.jumlah_ton;
  document.getElementById('edit-dist-tanggal').value = data.tanggal;
  setSelect('edit-dist-status', data.status);
  openModal('modal-edit-distribusi');
}

function openDelIkan(id, nama) {
  document.getElementById('del-ikan-id').value = id;
  document.getElementById('del-ikan-text').textContent =
    'Yakin ingin menghapus ikan "' + nama + '"? Tindakan ini tidak bisa dibatalkan.';
  openModal('modal-del-ikan');
}

function openDelNelayan(id, nama) {
  document.getElementById('del-nelayan-id').value = id;
  document.getElementById('del-nelayan-text').textContent =
    'Yakin ingin menghapus nelayan "' + nama + '"? Tindakan ini tidak bisa dibatalkan.';
  openModal('modal-del-nelayan');
}

function openDelDistribusi(id, tujuan) {
  document.getElementById('del-distribusi-id').value = id;
  document.getElementById('del-distribusi-text').textContent =
    'Yakin ingin menghapus distribusi ke "' + tujuan + '"? Tindakan ini tidak bisa dibatalkan.';
  openModal('modal-del-distribusi');
}
</script>

</body>
</html>