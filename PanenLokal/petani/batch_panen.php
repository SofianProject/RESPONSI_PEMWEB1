<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
if (!defined('BASE_URL')) define('BASE_URL', '/RESPONSI_PEMWEB1/PanenLokal');
if (!defined('SITE_NAME')) define('SITE_NAME', 'PanenLokal');
$page_title = 'Kelola Batch Panen';
cek_role('petani');

$fp = get_farmer_profile($conn, $_SESSION['user_id']);
$farmer_id = $fp['id'];
$is_verified = ($fp['status_verifikasi'] === 'terverifikasi');

$pesan = $_SESSION['flash'] ?? ''; unset($_SESSION['flash']);
$pesan_tipe = $_SESSION['flash_type'] ?? 'success'; unset($_SESSION['flash_type']);

// Toggle status batch (POST + CSRF + cek verifikasi)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle') {
    if (!verify_csrf()) {
        $_SESSION['flash'] = 'Permintaan tidak valid (token keamanan tidak cocok).';
        $_SESSION['flash_type'] = 'danger';
    } elseif (!$is_verified) {
        $_SESSION['flash'] = 'Akun usaha tani Anda belum terverifikasi admin sehingga tidak dapat mengubah status batch panen.';
        $_SESSION['flash_type'] = 'danger';
    } else {
        $bid = (int)($_POST['id'] ?? 0);
        $cur = $conn->prepare("
            SELECT hb.status_batch FROM harvest_batches hb
            JOIN commodities c ON c.id = hb.commodity_id
            WHERE hb.id = ? AND c.farmer_id = ?
        ");
        $cur->bind_param("ii", $bid, $farmer_id);
        $cur->execute();
        $bdata = $cur->get_result()->fetch_assoc();
        if ($bdata) {
            $new_st = $bdata['status_batch'] === 'dibuka' ? 'ditutup' : 'dibuka';
            $up = $conn->prepare("UPDATE harvest_batches SET status_batch = ? WHERE id = ?");
            $up->bind_param("si", $new_st, $bid);
            $up->execute();
            $_SESSION['flash'] = 'Status batch panen berhasil diubah.';
            $_SESSION['flash_type'] = 'success';
        }
    }
    header('Location: ' . BASE_URL . '/petani/batch_panen.php');
    exit;
}

$sql = "
    SELECT hb.*, c.nama AS komoditas, c.satuan, l.nama_lahan
    FROM harvest_batches hb
    JOIN commodities c ON c.id = hb.commodity_id
    LEFT JOIN lands l ON l.id = hb.land_id
    WHERE c.farmer_id = ?
    ORDER BY hb.created_at DESC
";
$b_list = $conn->prepare($sql);
$b_list->bind_param("i", $farmer_id);
$b_list->execute();
$batches = $b_list->get_result();

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
    <div class="dashboard-header d-flex justify-between align-center flex-wrap gap-2">
      <div>
        <h1>📦 Kelola Batch Panen (Pre-Order)</h1>
        <p class="text-muted">Buat jadwal panen, kuota pre-order, dan harga komoditas.</p>
      </div>
      <a href="<?= BASE_URL ?>/petani/tambah_batch.php" class="btn btn-primary">+ Buat Batch Panen</a>
    </div>

    <?php if (!$is_verified): ?>
      <div class="alert alert-warning">⏳ Akun usaha tani Anda berstatus <strong><?= sanitize($fp['status_verifikasi']) ?></strong>. Penambahan atau pembukaan batch panen aktif setelah diverifikasi admin.</div>
    <?php endif; ?>

    <?php if ($pesan): ?><?= alert($pesan, $pesan_tipe) ?><?php endif; ?>

    <div class="table-card">
      <table class="petani-table">
        <thead>
          <tr>
            <th>Komoditas</th>
            <th>Lahan</th>
            <th>Tgl Est. Panen</th>
            <th>Stok / Estimasi</th>
            <th>Harga Normal</th>
            <th>Rescue</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
        <?php if ($batches->num_rows > 0): ?>
          <?php while ($b = $batches->fetch_assoc()): ?>
          <tr>
            <td><strong><?= sanitize($b['komoditas']) ?></strong></td>
            <td><?= sanitize($b['nama_lahan'] ?: '-') ?></td>
            <td><?= format_tanggal($b['tanggal_estimasi_panen']) ?></td>
            <td><?= (float)$b['stok_tersedia'] ?> / <?= (float)$b['estimasi_jumlah'] ?> <?= sanitize($b['satuan']) ?></td>
            <td><?= format_rupiah($b['harga_normal']) ?></td>
            <td>
              <?php if ($b['rescue_aktif']): ?>
                <span class="badge badge-rescue">Aktif</span><br>
                <small class="text-orange"><?= format_rupiah($b['harga_rescue']) ?></small>
              <?php else: ?>
                <span class="badge badge-secondary">Tidak</span>
              <?php endif; ?>
            </td>
            <td>
              <span class="badge <?= $b['status_batch'] === 'dibuka' ? 'badge-success' : 'badge-danger' ?>">
                <?= ucfirst($b['status_batch']) ?>
              </span>
            </td>
            <td>
              <div class="d-flex gap-1">
                <a href="<?= BASE_URL ?>/petani/edit_batch.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-info">Edit</a>
                <form method="POST" style="display:inline;">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="toggle">
                  <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
                  <button type="submit" class="btn btn-sm <?= $b['status_batch'] === 'dibuka' ? 'btn-warning' : 'btn-success' ?>" <?= !$is_verified ? 'disabled' : '' ?>>
                    <?= $b['status_batch'] === 'dibuka' ? 'Tutup' : 'Buka' ?>
                  </button>
                </form>
              </div>
            </td>
          </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="8" class="text-center text-muted">Belum ada batch panen. Silakan buat batch baru.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
