<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
if (!defined('BASE_URL')) define('BASE_URL', '/RESPONSI_PEMWEB1/PanenLokal');
if (!defined('SITE_NAME')) define('SITE_NAME', 'PanenLokal');
$page_title = 'Laporan Usaha Tani';
cek_role('petani');

$stmt = $conn->prepare("SELECT id FROM farmer_profiles WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$fp = $stmt->get_result()->fetch_assoc();
$farmer_id = $fp['id'] ?? 0;

// Total pendapatan pesanan selesai
$q_rev = $conn->prepare("
    SELECT COALESCE(SUM(oi.quantity * oi.harga_satuan), 0)
    FROM order_items oi
    JOIN orders o ON o.id = oi.order_id
    JOIN harvest_batches hb ON hb.id = oi.batch_id
    JOIN commodities c ON c.id = hb.commodity_id
    WHERE c.farmer_id = ? AND o.status = 'selesai'
");
$q_rev->bind_param("i", $farmer_id);
$q_rev->execute();
$total_pendapatan = $q_rev->get_result()->fetch_row()[0];

// Total pangan rescue terselamatkan (kg)
$q_res = $conn->prepare("
    SELECT COALESCE(SUM(oi.quantity), 0)
    FROM order_items oi
    JOIN orders o ON o.id = oi.order_id
    JOIN harvest_batches hb ON hb.id = oi.batch_id
    JOIN commodities c ON c.id = hb.commodity_id
    WHERE c.farmer_id = ? AND oi.is_rescue = 1 AND o.status = 'selesai'
");
$q_res->bind_param("i", $farmer_id);
$q_res->execute();
$rescue_kg = $q_res->get_result()->fetch_row()[0];

// Jumlah pesanan selesai
$q_ord = $conn->prepare("
    SELECT COUNT(DISTINCT o.id)
    FROM orders o
    JOIN order_items oi ON oi.order_id = o.id
    JOIN harvest_batches hb ON hb.id = oi.batch_id
    JOIN commodities c ON c.id = hb.commodity_id
    WHERE c.farmer_id = ? AND o.status = 'selesai'
");
$q_ord->bind_param("i", $farmer_id);
$q_ord->execute();
$count_selesai = $q_ord->get_result()->fetch_row()[0];

// Top 5 komoditas terlaris
$q_top = $conn->prepare("
    SELECT oi.nama_produk_snapshot, oi.satuan_snapshot,
           SUM(oi.quantity) AS total_qty,
           SUM(oi.quantity * oi.harga_satuan) AS omzet
    FROM order_items oi
    JOIN orders o ON o.id = oi.order_id
    JOIN harvest_batches hb ON hb.id = oi.batch_id
    JOIN commodities c ON c.id = hb.commodity_id
    WHERE c.farmer_id = ? AND o.status = 'selesai'
    GROUP BY oi.nama_produk_snapshot, oi.satuan_snapshot
    ORDER BY total_qty DESC
    LIMIT 5
");
$q_top->bind_param("i", $farmer_id);
$q_top->execute();
$top_c = $q_top->get_result();

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
      <li><a href="<?= BASE_URL ?>/petani/kalender_panen.php">📅 Kalender Panen</a></li>
      <li><a href="<?= BASE_URL ?>/petani/laporan.php" class="active">📊 Laporan</a></li>
    </ul>
  </aside>
  <main class="dashboard-main">
    <div class="dashboard-header">
      <h1>📊 Laporan Penjualan & Dampak Food Rescue</h1>
      <p class="text-muted">Ringkasan pendapatan dari pesanan tuntas dan kontribusi penyelamatan pangan Anda.</p>
    </div>

    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div class="stat-info">
          <div class="stat-value" style="font-size:1.3rem;"><?= format_rupiah($total_pendapatan) ?></div>
          <div class="stat-label">Total Pendapatan Tuntas</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">✅</div>
        <div class="stat-info">
          <div class="stat-value"><?= $count_selesai ?></div>
          <div class="stat-label">Pesanan Berhasil Tuntas</div>
        </div>
      </div>
      <div class="stat-card" style="border-left: 4px solid var(--orange);">
        <div class="stat-icon">♻️</div>
        <div class="stat-info">
          <div class="stat-value" style="color:var(--orange);"><?= number_format($rescue_kg, 1, ',', '.') ?> kg</div>
          <div class="stat-label">Pangan Berhasil Diselamatkan</div>
        </div>
      </div>
    </div>

    <div class="table-card mt-3">
      <div style="padding:1rem 1.2rem; border-bottom:1px solid var(--gray-200);">
        <h3 style="margin:0;">🥦 Komoditas Paling Laris Terjual</h3>
      </div>
      <table>
        <thead>
          <tr>
            <th>Nama Komoditas</th>
            <th>Total Kuantitas Terjual</th>
            <th>Total Nilai Transaksi</th>
          </tr>
        </thead>
        <tbody>
        <?php if ($top_c->num_rows > 0): ?>
          <?php while ($tc = $top_c->fetch_assoc()): ?>
          <tr>
            <td><strong><?= sanitize($tc['nama_produk_snapshot']) ?></strong></td>
            <td><?= (float)$tc['total_qty'] ?> <?= sanitize($tc['satuan_snapshot']) ?></td>
            <td><?= format_rupiah($tc['omzet']) ?></td>
          </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="3" class="text-center text-muted">Belum ada data penjualan selesai.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
