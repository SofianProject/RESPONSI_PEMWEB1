<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
if (!defined('BASE_URL')) define('BASE_URL', '/RESPONSI_PEMWEB1/PanenLokal');
if (!defined('SITE_NAME')) define('SITE_NAME', 'PanenLokal');
$page_title = 'Tambah Batch Panen';
cek_role('petani');

$stmt = $conn->prepare("SELECT id FROM farmer_profiles WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$fp = $stmt->get_result()->fetch_assoc();
$farmer_id = $fp['id'] ?? 0;

// Daftar komoditas aktif milik petani
$comms = $conn->query("SELECT id, nama, satuan FROM commodities WHERE farmer_id = $farmer_id AND status_komoditas = 'aktif'");
// Daftar lahan milik petani
$lands = $conn->query("SELECT id, nama_lahan FROM lands WHERE farmer_id = $farmer_id");

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $commodity_id  = (int)($_POST['commodity_id'] ?? 0);
    $land_id       = !empty($_POST['land_id']) ? (int)$_POST['land_id'] : null;
    $tgl_panen     = trim($_POST['tanggal_estimasi_panen'] ?? '');
    $estimasi_jml  = (float)($_POST['estimasi_jumlah'] ?? 0);
    $stok_tersedia = (float)($_POST['stok_tersedia'] ?? 0);
    $harga_normal  = (float)($_POST['harga_normal'] ?? 0);
    $rescue_aktif  = isset($_POST['rescue_aktif']) ? 1 : 0;
    $harga_rescue  = !empty($_POST['harga_rescue']) ? (float)$_POST['harga_rescue'] : null;
    $batas_rescue  = !empty($_POST['batas_rescue']) ? trim($_POST['batas_rescue']) : null;

    // Verifikasi komoditas milik petani
    $chk_c = $conn->query("SELECT id FROM commodities WHERE id = $commodity_id AND farmer_id = $farmer_id");
    if ($chk_c->num_rows === 0) {
        $error = 'Komoditas tidak valid.';
    } elseif ($land_id && $conn->query("SELECT id FROM lands WHERE id = $land_id AND farmer_id = $farmer_id")->num_rows === 0) {
        $error = 'Lahan tidak valid.';
    } elseif (!$tgl_panen || $estimasi_jml <= 0 || $stok_tersedia < 0 || $harga_normal <= 0) {
        $error = 'Tanggal panen, estimasi jumlah, dan harga normal harus valid (>0).';
    } elseif ($rescue_aktif && (!$harga_rescue || $harga_rescue <= 0 || $harga_rescue >= $harga_normal)) {
        $error = 'Harga rescue harus lebih besar dari 0 dan lebih murah dari harga normal.';
    } else {
        $ins = $conn->prepare("
            INSERT INTO harvest_batches 
            (commodity_id, land_id, tanggal_estimasi_panen, estimasi_jumlah, stok_tersedia, harga_normal, rescue_aktif, harga_rescue, batas_rescue, status_batch)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'dibuka')
        ");
        $ins->bind_param("iisdddidss", $commodity_id, $land_id, $tgl_panen, $estimasi_jml, $stok_tersedia, $harga_normal, $rescue_aktif, $harga_rescue, $batas_rescue);
        if ($ins->execute()) {
            $_SESSION['flash'] = 'Batch panen berhasil dibuat!';
            $_SESSION['flash_type'] = 'success';
            header('Location: ' . BASE_URL . '/petani/batch_panen.php');
            exit;
        } else {
            $error = 'Gagal menyimpan batch: ' . $conn->error;
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
      <li><a href="<?= BASE_URL ?>/petani/lahan.php">🌍 Lahan</a></li>
      <li><a href="<?= BASE_URL ?>/petani/komoditas.php">🥦 Komoditas</a></li>
      <li><a href="<?= BASE_URL ?>/petani/batch_panen.php" class="active">📦 Batch Panen</a></li>
      <li><a href="<?= BASE_URL ?>/petani/kelola_rescue.php">🔥 Food Rescue</a></li>
      <li><a href="<?= BASE_URL ?>/petani/pesanan.php">📋 Pesanan</a></li>
      <li><a href="<?= BASE_URL ?>/petani/kalender_panen.php">📅 Kalender Panen</a></li>
      <li><a href="<?= BASE_URL ?>/petani/laporan.php">📊 Laporan</a></li>
    </ul>
  </aside>
  <main class="dashboard-main">
    <div class="dashboard-header">
      <a href="<?= BASE_URL ?>/petani/batch_panen.php" class="btn btn-secondary btn-sm mb-2">← Kembali</a>
      <h1>Buat Batch Panen (Pre-Order)</h1>
    </div>

    <?php if ($error): ?><?= alert($error, 'danger') ?><?php endif; ?>

    <div class="card card-body" style="max-width: 650px;">
      <form id="batch-form" method="POST">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Pilih Komoditas *</label>
            <select name="commodity_id" class="form-control" required>
              <option value="">Pilih Komoditas...</option>
              <?php while ($cm = $comms->fetch_assoc()): ?>
                <option value="<?= $cm['id'] ?>"><?= sanitize($cm['nama']) ?> (<?= sanitize($cm['satuan']) ?>)</option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Lahan Lokasi Panen (Opsional)</label>
            <select name="land_id" class="form-control">
              <option value="">Tidak Ditentukan</option>
              <?php while ($lh = $lands->fetch_assoc()): ?>
                <option value="<?= $lh['id'] ?>"><?= sanitize($lh['nama_lahan']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Tanggal Estimasi Panen *</label>
            <input type="date" name="tanggal_estimasi_panen" class="form-control" required value="<?= sanitize($_POST['tanggal_estimasi_panen'] ?? date('Y-m-d', strtotime('+7 days'))) ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Harga Normal Satuan (Rp) *</label>
            <input type="number" id="harga_normal" name="harga_normal" class="form-control" required placeholder="Contoh: 15000" value="<?= sanitize($_POST['harga_normal'] ?? '') ?>">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Estimasi Hasil Panen *</label>
            <input type="number" step="0.1" name="estimasi_jumlah" class="form-control" required placeholder="Contoh: 100" value="<?= sanitize($_POST['estimasi_jumlah'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Kuota Pre-Order (Stok Awal) *</label>
            <input type="number" step="0.1" id="stok_tersedia" name="stok_tersedia" class="form-control" required placeholder="Contoh: 80" value="<?= sanitize($_POST['stok_tersedia'] ?? '') ?>">
          </div>
        </div>

        <!-- Section Rescue -->
        <hr class="divider">
        <div class="form-group">
          <label style="display:flex; align-items:center; gap:.5rem; cursor:pointer;">
            <input type="checkbox" id="rescue_aktif" name="rescue_aktif" value="1" onchange="toggleRescueFields()">
            <strong style="color:var(--orange);">🔥 Aktifkan sebagai Penawaran Food Rescue</strong>
          </label>
          <div class="form-hint">Gunakan fitur ini jika produk hasil panen berlebih atau perlu segera diserap dengan harga diskon.</div>
        </div>

        <div id="rescue-fields" style="display:none; background:var(--orange-light); padding:1rem; border-radius:var(--radius-sm); margin-bottom:1rem;">
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Harga Food Rescue (Rp) *</label>
              <input type="number" id="harga_rescue" name="harga_rescue" class="form-control" placeholder="Harus lebih murah dari normal">
            </div>
            <div class="form-group">
              <label class="form-label">Batas Waktu Penawaran</label>
              <input type="datetime-local" name="batas_rescue" class="form-control">
            </div>
          </div>
        </div>

        <button type="submit" class="btn btn-primary">Simpan Batch Panen</button>
      </form>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
