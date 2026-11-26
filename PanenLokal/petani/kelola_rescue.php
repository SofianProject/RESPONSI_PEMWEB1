<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
if (!defined('BASE_URL')) define('BASE_URL', '/RESPONSI_PEMWEB1/PanenLokal');
if (!defined('SITE_NAME')) define('SITE_NAME', 'PanenLokal');
$page_title = 'Kelola Food Rescue';
cek_role('petani');

$fp = get_farmer_profile($conn, $_SESSION['user_id']);
$farmer_id = $fp['id'];
$is_verified = ($fp['status_verifikasi'] === 'terverifikasi');

$pesan = $_SESSION['flash'] ?? ''; unset($_SESSION['flash']);
$pesan_tipe = $_SESSION['flash_type'] ?? 'success'; unset($_SESSION['flash_type']);

// Form update cepat rescue (POST + CSRF + cek verifikasi)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['batch_id'])) {
    if (!verify_csrf()) {
        $pesan = 'Permintaan tidak valid (token keamanan tidak cocok).';
        $pesan_tipe = 'danger';
    } elseif (!$is_verified) {
        $pesan = 'Akun usaha tani Anda belum terverifikasi admin sehingga belum dapat mengelola Food Rescue.';
        $pesan_tipe = 'danger';
    } else {
        $bid          = (int)$_POST['batch_id'];
        $rescue_aktif = isset($_POST['rescue_aktif']) ? 1 : 0;
        $harga_rescue = !empty($_POST['harga_rescue']) ? (float)$_POST['harga_rescue'] : null;
        $batas_rescue = !empty($_POST['batas_rescue']) ? trim($_POST['batas_rescue']) : null;

        // Cek kepemilikan
        $chk = $conn->prepare("
            SELECT hb.harga_normal FROM harvest_batches hb
            JOIN commodities c ON c.id = hb.commodity_id
            WHERE hb.id = ? AND c.farmer_id = ?
        ");
        $chk->bind_param("ii", $bid, $farmer_id);
        $chk->execute();
        $binfo = $chk->get_result()->fetch_assoc();

        if ($binfo) {
            if ($rescue_aktif && (!$harga_rescue || $harga_rescue >= $binfo['harga_normal'])) {
                $pesan = 'Harga rescue harus lebih rendah dari harga normal (' . format_rupiah($binfo['harga_normal']) . ').';
                $pesan_tipe = 'danger';
            } else {
                $up = $conn->prepare("UPDATE harvest_batches SET rescue_aktif = ?, harga_rescue = ?, batas_rescue = ? WHERE id = ?");
                $up->bind_param("idsi", $rescue_aktif, $harga_rescue, $batas_rescue, $bid);
                $up->execute();
                $pesan = 'Pengaturan Food Rescue berhasil diperbarui!';
                $pesan_tipe = 'success';
            }
        }
    }
}

$sql = "
    SELECT hb.*, c.nama AS komoditas, c.satuan
    FROM harvest_batches hb
    JOIN commodities c ON c.id = hb.commodity_id
    WHERE c.farmer_id = ? AND hb.status_batch = 'dibuka'
    ORDER BY hb.rescue_aktif DESC, hb.created_at DESC
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
      <li><a href="<?= BASE_URL ?>/petani/batch_panen.php">📦 Batch Panen</a></li>
      <li><a href="<?= BASE_URL ?>/petani/kelola_rescue.php" class="active">🔥 Food Rescue</a></li>
      <li><a href="<?= BASE_URL ?>/petani/pesanan.php">📋 Pesanan</a></li>
      <li><a href="<?= BASE_URL ?>/petani/kalender_panen.php">📅 Kalender Panen</a></li>
      <li><a href="<?= BASE_URL ?>/petani/laporan.php">📊 Laporan</a></li>
    </ul>
  </aside>
  <main class="dashboard-main">
    <div class="dashboard-header">
      <h1>🔥 Kelola Food Rescue</h1>
      <p class="text-muted">Tandai panen berlebih atau mendekati masa kesegaran untuk diselamatkan dengan harga promo hemat.</p>
    </div>

    <?php if (!$is_verified): ?>
      <div class="alert alert-warning">⏳ Akun usaha tani Anda berstatus <strong><?= sanitize($fp['status_verifikasi']) ?></strong>. Pengelolaan Food Rescue hanya tersedia setelah verifikasi admin.</div>
    <?php endif; ?>

    <?php if ($pesan): ?><?= alert($pesan, $pesan_tipe) ?><?php endif; ?>

    <div class="table-card">
      <table class="petani-table">
        <thead>
          <tr>
            <th>Komoditas</th>
            <th>Stok Tersedia</th>
            <th>Harga Normal</th>
            <th>Status Rescue</th>
            <th>Harga Rescue (Diskon)</th>
            <th>Batas Waktu</th>
            <th>Simpan</th>
          </tr>
        </thead>
        <tbody>
        <?php if ($batches->num_rows > 0): ?>
          <?php while ($b = $batches->fetch_assoc()): ?>
          <tr>
            <form method="POST">
              <?= csrf_field() ?>
              <input type="hidden" name="batch_id" value="<?= $b['id'] ?>">
              <td><strong><?= sanitize($b['komoditas']) ?></strong></td>
              <td><?= (float)$b['stok_tersedia'] ?> <?= sanitize($b['satuan']) ?></td>
              <td><?= format_rupiah($b['harga_normal']) ?></td>
              <td>
                <label style="display:flex; align-items:center; gap:.4rem; cursor:pointer;">
                  <input type="checkbox" name="rescue_aktif" value="1" <?= $b['rescue_aktif'] ? 'checked' : '' ?>>
                  <span class="badge <?= $b['rescue_aktif'] ? 'badge-rescue' : 'badge-secondary' ?>">
                    <?= $b['rescue_aktif'] ? 'Aktif' : 'Nonaktif' ?>
                  </span>
                </label>
              </td>
              <td>
                <input type="number" name="harga_rescue" class="form-control" style="width:130px; font-size:.85rem; padding:.3rem;" placeholder="Harga Rp" value="<?= sanitize($b['harga_rescue'] ?? '') ?>">
              </td>
              <td>
                <input type="datetime-local" name="batas_rescue" class="form-control" style="width:180px; font-size:.85rem; padding:.3rem;" value="<?= $b['batas_rescue'] ? date('Y-m-d\TH:i', strtotime($b['batas_rescue'])) : '' ?>">
              </td>
              <td>
                <button type="submit" class="btn btn-sm btn-orange" <?= !$is_verified ? 'disabled' : '' ?>>Update</button>
              </td>
            </form>
          </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="7" class="text-center text-muted">Belum ada batch panen dengan status dibuka.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
