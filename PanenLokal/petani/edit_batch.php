<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
if (!defined('BASE_URL')) define('BASE_URL', '/RESPONSI_PEMWEB1/PanenLokal');
if (!defined('SITE_NAME')) define('SITE_NAME', 'PanenLokal');
$page_title = 'Edit Batch Panen';
cek_role('petani');

$stmt = $conn->prepare("SELECT id FROM farmer_profiles WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$fp = $stmt->get_result()->fetch_assoc();
$farmer_id = $fp['id'] ?? 0;

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . BASE_URL . '/petani/batch_panen.php');
    exit;
}

$chk = $conn->prepare("
    SELECT hb.*, c.nama AS komoditas_nama
    FROM harvest_batches hb
    JOIN commodities c ON c.id = hb.commodity_id
    WHERE hb.id = ? AND c.farmer_id = ?
");
$chk->bind_param("ii", $id, $farmer_id);
$chk->execute();
$b = $chk->get_result()->fetch_assoc();
if (!$b) {
    header('Location: ' . BASE_URL . '/petani/batch_panen.php');
    exit;
}

$lands = $conn->query("SELECT id, nama_lahan FROM lands WHERE farmer_id = $farmer_id");

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $land_id       = !empty($_POST['land_id']) ? (int)$_POST['land_id'] : null;
    $tgl_panen     = trim($_POST['tanggal_estimasi_panen'] ?? '');
    $estimasi_jml  = (float)($_POST['estimasi_jumlah'] ?? 0);
    $stok_tersedia = (float)($_POST['stok_tersedia'] ?? 0);
    $harga_normal  = (float)($_POST['harga_normal'] ?? 0);
    $rescue_aktif  = isset($_POST['rescue_aktif']) ? 1 : 0;
    $harga_rescue  = !empty($_POST['harga_rescue']) ? (float)$_POST['harga_rescue'] : null;
    $batas_rescue  = !empty($_POST['batas_rescue']) ? trim($_POST['batas_rescue']) : null;
    $status_batch  = trim($_POST['status_batch'] ?? 'dibuka');

    if ($land_id && $conn->query("SELECT id FROM lands WHERE id = $land_id AND farmer_id = $farmer_id")->num_rows === 0) {
        $error = 'Lahan tidak valid.';
    } elseif (!$tgl_panen || $estimasi_jml <= 0 || $stok_tersedia < 0 || $harga_normal <= 0) {
        $error = 'Tanggal panen, estimasi jumlah, dan harga normal harus valid (>0).';
    } elseif ($rescue_aktif && (!$harga_rescue || $harga_rescue <= 0 || $harga_rescue >= $harga_normal)) {
        $error = 'Harga rescue harus lebih besar dari 0 dan lebih murah dari harga normal.';
    } else {
        $upd = $conn->prepare("
            UPDATE harvest_batches 
            SET land_id = ?, tanggal_estimasi_panen = ?, estimasi_jumlah = ?, stok_tersedia = ?, harga_normal = ?, 
                rescue_aktif = ?, harga_rescue = ?, batas_rescue = ?, status_batch = ?
            WHERE id = ?
        ");
        $upd->bind_param("isdddidssi", $land_id, $tgl_panen, $estimasi_jml, $stok_tersedia, $harga_normal, $rescue_aktif, $harga_rescue, $batas_rescue, $status_batch, $id);
        if ($upd->execute()) {
            $_SESSION['flash'] = 'Batch panen berhasil diperbarui!';
            $_SESSION['flash_type'] = 'success';
            header('Location: ' . BASE_URL . '/petani/batch_panen.php');
            exit;
        } else {
            $error = 'Gagal memperbarui batch: ' . $conn->error;
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
      <h1>Edit Batch Panen — <?= sanitize($b['komoditas_nama']) ?></h1>
    </div>

    <?php if ($error): ?><?= alert($error, 'danger') ?><?php endif; ?>

    <div class="card card-body" style="max-width: 650px;">
      <form id="batch-form" method="POST">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Lahan Lokasi Panen (Opsional)</label>
            <select name="land_id" class="form-control">
              <option value="">Tidak Ditentukan</option>
              <?php while ($lh = $lands->fetch_assoc()): ?>
                <option value="<?= $lh['id'] ?>" <?= $b['land_id'] == $lh['id'] ? 'selected' : '' ?>><?= sanitize($lh['nama_lahan']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Status Batch</label>
            <select name="status_batch" class="form-control">
              <?php
              $all_sb = ['dibuka', 'siap_panen', 'habis', 'ditutup', 'dibatalkan'];
              foreach ($all_sb as $sb):
              ?>
                <option value="<?= $sb ?>" <?= $b['status_batch'] === $sb ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$sb)) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Tanggal Estimasi Panen *</label>
            <input type="date" name="tanggal_estimasi_panen" class="form-control" required value="<?= sanitize($b['tanggal_estimasi_panen']) ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Harga Normal Satuan (Rp) *</label>
            <input type="number" id="harga_normal" name="harga_normal" class="form-control" required value="<?= sanitize($b['harga_normal']) ?>">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Estimasi Hasil Panen *</label>
            <input type="number" step="0.1" name="estimasi_jumlah" class="form-control" required value="<?= sanitize($b['estimasi_jumlah']) ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Stok Tersedia Saat Ini *</label>
            <input type="number" step="0.1" id="stok_tersedia" name="stok_tersedia" class="form-control" required value="<?= sanitize($b['stok_tersedia']) ?>">
          </div>
        </div>

        <!-- Section Rescue -->
        <hr class="divider">
        <div class="form-group">
          <label style="display:flex; align-items:center; gap:.5rem; cursor:pointer;">
            <input type="checkbox" id="rescue_aktif" name="rescue_aktif" value="1" <?= $b['rescue_aktif'] ? 'checked' : '' ?> onchange="toggleRescueFields()">
            <strong style="color:var(--orange);">🔥 Aktifkan sebagai Penawaran Food Rescue</strong>
          </label>
        </div>

        <div id="rescue-fields" style="display:<?= $b['rescue_aktif'] ? 'block' : 'none' ?>; background:var(--orange-light); padding:1rem; border-radius:var(--radius-sm); margin-bottom:1rem;">
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Harga Food Rescue (Rp) *</label>
              <input type="number" id="harga_rescue" name="harga_rescue" class="form-control" value="<?= sanitize($b['harga_rescue'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Batas Waktu Penawaran</label>
              <input type="datetime-local" name="batas_rescue" class="form-control" value="<?= $b['batas_rescue'] ? date('Y-m-d\TH:i', strtotime($b['batas_rescue'])) : '' ?>">
            </div>
          </div>
        </div>

        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
      </form>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
