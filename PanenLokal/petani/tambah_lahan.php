<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
if (!defined('BASE_URL')) define('BASE_URL', '/RESPONSI_PEMWEB1/PanenLokal');
if (!defined('SITE_NAME')) define('SITE_NAME', 'PanenLokal');
$page_title = 'Tambah Lahan';
cek_role('petani');

$stmt = $conn->prepare("SELECT id FROM farmer_profiles WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$fp = $stmt->get_result()->fetch_assoc();
$farmer_id = $fp['id'] ?? 0;

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lahan = trim($_POST['nama_lahan'] ?? '');
    $lokasi     = trim($_POST['lokasi'] ?? '');
    $luas_m2    = !empty($_POST['luas_m2']) ? (float)$_POST['luas_m2'] : null;
    $keterangan = trim($_POST['keterangan'] ?? '');

    if (!$nama_lahan || !$lokasi) {
        $error = 'Nama lahan dan lokasi wajib diisi.';
    } else {
        $ins = $conn->prepare("INSERT INTO lands (farmer_id, nama_lahan, lokasi, luas_m2, keterangan) VALUES (?, ?, ?, ?, ?)");
        $ins->bind_param("issds", $farmer_id, $nama_lahan, $lokasi, $luas_m2, $keterangan);
        if ($ins->execute()) {
            $_SESSION['flash'] = 'Lahan berhasil ditambahkan!';
            $_SESSION['flash_type'] = 'success';
            header('Location: ' . BASE_URL . '/petani/lahan.php');
            exit;
        } else {
            $error = 'Gagal menyimpan lahan: ' . $conn->error;
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>
<div class="dashboard-layout">
  <aside class="sidebar">
    <div class="sidebar-title">Menu Petani</div>
    <ul class="sidebar-menu">
      <li><a href="<?= BASE_URL ?>/petani/index.php">🏠 Dashboard</a></li>
      <li><a href="<?= BASE_URL ?>/petani/lahan.php" class="active">🌍 Lahan</a></li>
      <li><a href="<?= BASE_URL ?>/petani/komoditas.php">🥦 Komoditas</a></li>
      <li><a href="<?= BASE_URL ?>/petani/batch_panen.php">📦 Batch Panen</a></li>
      <li><a href="<?= BASE_URL ?>/petani/kelola_rescue.php">🔥 Food Rescue</a></li>
      <li><a href="<?= BASE_URL ?>/petani/pesanan.php">📋 Pesanan</a></li>
      <li><a href="<?= BASE_URL ?>/petani/kalender_panen.php">📅 Kalender Panen</a></li>
      <li><a href="<?= BASE_URL ?>/petani/laporan.php">📊 Laporan</a></li>
    </ul>
  </aside>
  <main class="dashboard-main">
    <div class="dashboard-header">
      <a href="<?= BASE_URL ?>/petani/lahan.php" class="btn btn-secondary btn-sm mb-2">← Kembali</a>
      <h1>Tambah Lahan Tani</h1>
    </div>

    <?php if ($error): ?><?= alert($error, 'danger') ?><?php endif; ?>

    <div class="card card-body" style="max-width: 600px;">
      <form method="POST">
        <div class="form-group">
          <label class="form-label">Nama Lahan *</label>
          <input type="text" name="nama_lahan" class="form-control" required placeholder="Contoh: Kebun Sayur Lereng A" value="<?= sanitize($_POST['nama_lahan'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Lokasi Lahan *</label>
          <textarea name="lokasi" class="form-control" rows="3" required placeholder="Alamat atau koordinat lokasi lahan..."><?= sanitize($_POST['lokasi'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">Luas Lahan (m²)</label>
          <input type="number" step="0.01" name="luas_m2" class="form-control" placeholder="Contoh: 1500" value="<?= sanitize($_POST['luas_m2'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Keterangan / Kondisi Tanah</label>
          <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan tambahan..."><?= sanitize($_POST['keterangan'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Simpan Lahan</button>
      </form>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
