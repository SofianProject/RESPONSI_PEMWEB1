<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
if (!defined('BASE_URL')) define('BASE_URL', '/RESPONSI_PEMWEB1/PanenLokal');
if (!defined('SITE_NAME')) define('SITE_NAME', 'PanenLokal');
$page_title = 'Kalender Estimasi Panen';
cek_role('petani');

$stmt = $conn->prepare("SELECT id FROM farmer_profiles WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$fp = $stmt->get_result()->fetch_assoc();
$farmer_id = $fp['id'] ?? 0;

$sql = "
    SELECT hb.*, c.nama AS komoditas, c.satuan, l.nama_lahan
    FROM harvest_batches hb
    JOIN commodities c ON c.id = hb.commodity_id
    LEFT JOIN lands l ON l.id = hb.land_id
    WHERE c.farmer_id = ?
    ORDER BY hb.tanggal_estimasi_panen ASC
";
$q = $conn->prepare($sql);
$q->bind_param("i", $farmer_id);
$q->execute();
$panen = $q->get_result();

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
      <li><a href="<?= BASE_URL ?>/petani/kelola_rescue.php">🔥 Food Rescue</a></li>
      <li><a href="<?= BASE_URL ?>/petani/pesanan.php">📋 Pesanan</a></li>
      <li><a href="<?= BASE_URL ?>/petani/kalender_panen.php" class="active">📅 Kalender Panen</a></li>
      <li><a href="<?= BASE_URL ?>/petani/laporan.php">📊 Laporan</a></li>
    </ul>
  </aside>
  <main class="dashboard-main">
    <div class="dashboard-header">
      <h1>📅 Kalender & Timeline Estimasi Panen</h1>
      <p class="text-muted">Pantau jadwal tanggal panen mendatang untuk manajemen pasokan dan pre-order.</p>
    </div>

    <div class="table-card">
      <table class="petani-table">
        <thead>
          <tr>
            <th>Tanggal Estimasi</th>
            <th>Komoditas</th>
            <th>Lahan</th>
            <th>Estimasi Hasil</th>
            <th>Sisa Kuota Pre-Order</th>
            <th>Status Batch</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
        <?php if ($panen->num_rows > 0): ?>
          <?php while ($p = $panen->fetch_assoc()):
              $diff = (strtotime($p['tanggal_estimasi_panen']) - strtotime(date('Y-m-d'))) / 86400;
              $badge_time = $diff < 0 ? 'badge-danger' : ($diff <= 3 ? 'badge-warning' : 'badge-info');
              $label_time = $diff < 0 ? 'Lewat' : ($diff == 0 ? 'Hari ini' : ceil($diff) . ' hari lagi');
          ?>
          <tr>
            <td>
              <strong><?= format_tanggal($p['tanggal_estimasi_panen']) ?></strong><br>
              <span class="badge <?= $badge_time ?>"><?= $label_time ?></span>
            </td>
            <td><strong><?= sanitize($p['komoditas']) ?></strong></td>
            <td><?= sanitize($p['nama_lahan'] ?: '-') ?></td>
            <td><?= (float)$p['estimasi_jumlah'] ?> <?= sanitize($p['satuan']) ?></td>
            <td>
              <span class="badge badge-success"><?= (float)$p['stok_tersedia'] ?> <?= sanitize($p['satuan']) ?></span>
            </td>
            <td>
              <span class="badge badge-secondary"><?= ucfirst($p['status_batch']) ?></span>
            </td>
            <td>
              <a href="<?= BASE_URL ?>/petani/edit_batch.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary">Atur Batch</a>
            </td>
          </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="7" class="text-center text-muted">Belum ada jadwal panen tercatat.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
