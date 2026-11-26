<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

cek_role('petani');

// Ambil farmer_profile
$fp = get_farmer_profile($conn, $_SESSION['user_id']);
$farmer_id = $fp['id'];
$status_verifikasi = $fp['status_verifikasi'];

// Stat: total komoditas aktif
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM commodities WHERE farmer_id = ? AND status_komoditas = 'aktif'");
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$total_komoditas = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
$stmt->close();

// Stat: total batch aktif
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM harvest_batches hb JOIN commodities c ON hb.commodity_id = c.id WHERE c.farmer_id = ? AND hb.status_batch = 'dibuka'");
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$total_batch = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
$stmt->close();

// Stat: total pesanan masuk (bukan dibatalkan, berkaitan dengan komoditas petani)
$stmt = $conn->prepare("
    SELECT COUNT(DISTINCT o.id) as total
    FROM orders o
    JOIN order_items oi ON oi.order_id = o.id
    JOIN harvest_batches hb ON oi.batch_id = hb.id
    JOIN commodities c ON hb.commodity_id = c.id
    WHERE c.farmer_id = ? AND o.status != 'dibatalkan'
");
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$total_pesanan = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
$stmt->close();

// Stat: total pendapatan (pesanan selesai)
$stmt = $conn->prepare("
    SELECT COALESCE(SUM(oi.quantity * oi.harga_satuan), 0) as total
    FROM orders o
    JOIN order_items oi ON oi.order_id = o.id
    JOIN harvest_batches hb ON oi.batch_id = hb.id
    JOIN commodities c ON hb.commodity_id = c.id
    WHERE c.farmer_id = ? AND o.status = 'selesai'
");
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$total_pendapatan = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
$stmt->close();

// 5 pesanan terbaru
$stmt = $conn->prepare("
    SELECT o.kode_pesanan, o.total_amount, o.status, o.tanggal_pesanan, o.id
    FROM orders o
    JOIN order_items oi ON oi.order_id = o.id
    JOIN harvest_batches hb ON oi.batch_id = hb.id
    JOIN commodities c ON hb.commodity_id = c.id
    WHERE c.farmer_id = ?
    GROUP BY o.id
    ORDER BY o.tanggal_pesanan DESC
    LIMIT 5
");
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$pesanan_terbaru = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// 5 batch panen terbaru
$stmt = $conn->prepare("
    SELECT hb.id, c.nama AS komoditas, hb.tanggal_estimasi_panen, hb.stok_tersedia, hb.harga_normal, hb.status_batch
    FROM harvest_batches hb
    JOIN commodities c ON hb.commodity_id = c.id
    WHERE c.farmer_id = ?
    ORDER BY hb.created_at DESC
    LIMIT 5
");
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$batch_terbaru = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$page_title = 'Dashboard Petani';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="dashboard-layout">
  <aside class="sidebar">
    <div class="sidebar-title">Menu Petani</div>
    <ul class="sidebar-menu">
      <li><a href="<?= BASE_URL ?>/petani/index.php" class="active">🏠 Dashboard</a></li>
      <li><a href="<?= BASE_URL ?>/petani/lahan.php">🌍 Lahan</a></li>
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
      <h1 class="page-title">🌾 Dashboard Petani</h1>
      <p class="page-subtitle">Selamat datang, <?= sanitize($_SESSION['nama'] ?? 'Petani') ?>!</p>
    </div>

    <?php if ($status_verifikasi === 'menunggu'): ?>
      <div class="alert alert-warning">⏳ Akun usaha tani Anda sedang <strong>menunggu verifikasi admin</strong>. Anda dapat melihat dashboard dan mengelola lahan, namun publikasi komoditas, batch panen, dan Food Rescue baru aktif setelah terverifikasi.</div>
    <?php elseif ($status_verifikasi === 'ditolak'): ?>
      <div class="alert alert-danger">❌ Verifikasi usaha tani Anda <strong>ditolak</strong> oleh admin. Fitur produksi komoditas dan batch panen dinonaktifkan sementara. Hubungi admin untuk informasi lebih lanjut.</div>
    <?php endif; ?>

    <div class="stats-grid stat-cards">
      <div class="stat-card">
        <div class="stat-icon">🥦</div>
        <div class="stat-info">
          <div class="stat-value"><?= sanitize($total_komoditas) ?></div>
          <div class="stat-label">Komoditas Aktif</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">📦</div>
        <div class="stat-info">
          <div class="stat-value"><?= sanitize($total_batch) ?></div>
          <div class="stat-label">Batch Aktif</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">📋</div>
        <div class="stat-info">
          <div class="stat-value"><?= sanitize($total_pesanan) ?></div>
          <div class="stat-label">Total Pesanan Masuk</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div class="stat-info">
          <div class="stat-value" style="font-size:1.3rem;"><?= format_rupiah($total_pendapatan) ?></div>
          <div class="stat-label">Total Pendapatan</div>
        </div>
      </div>
    </div>

    <div class="dashboard-tables">
      <section class="table-card table-section">
        <div class="section-header">
          <h2>📋 5 Pesanan Terbaru</h2>
          <a href="<?= BASE_URL ?>/petani/pesanan.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
        </div>
        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th>Kode Pesanan</th>
                <th>Total</th>
                <th>Status</th>
                <th>Tanggal</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($pesanan_terbaru)): ?>
              <tr><td colspan="5" class="text-center text-muted">Belum ada pesanan</td></tr>
              <?php else: ?>
              <?php foreach ($pesanan_terbaru as $p):
                $sl = get_status_label($p['status']);
              ?>
              <tr>
                <td><code><?= sanitize($p['kode_pesanan']) ?></code></td>
                <td><?= format_rupiah($p['total_amount']) ?></td>
                <td><span class="badge <?= $sl['class'] ?>"><?= sanitize($sl['label']) ?></span></td>
                <td><?= format_tanggal($p['tanggal_pesanan'], 'd M Y H:i') ?></td>
                <td><a href="<?= BASE_URL ?>/petani/detail_pesanan.php?id=<?= (int)$p['id'] ?>" class="btn btn-xs btn-outline-primary">Detail</a></td>
              </tr>
              <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>

      <section class="table-card table-section">
        <div class="section-header">
          <h2>📦 5 Batch Panen Terbaru</h2>
          <a href="<?= BASE_URL ?>/petani/batch_panen.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
        </div>
        <div class="table-responsive">
          <table class="data-table batch-summary-table">
            <thead>
              <tr>
                <th>Komoditas</th>
                <th>Est. Panen</th>
                <th>Stok</th>
                <th>Harga Normal</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($batch_terbaru)): ?>
              <tr><td colspan="5" class="text-center text-muted">Belum ada batch panen</td></tr>
              <?php else: ?>
              <?php
              $batch_badge_map = [
                  'dibuka'     => 'badge-batch-dibuka',
                  'siap_panen' => 'badge-info',
                  'habis'      => 'badge-warning',
                  'ditutup'    => 'badge-secondary',
                  'dibatalkan' => 'badge-danger',
              ];
              foreach ($batch_terbaru as $b):
                  $b_cls = $batch_badge_map[$b['status_batch']] ?? 'badge-secondary';
              ?>
              <tr>
                <td><strong><?= sanitize($b['komoditas']) ?></strong></td>
                <td><?= format_tanggal($b['tanggal_estimasi_panen']) ?></td>
                <td><?= (float)$b['stok_tersedia'] ?></td>
                <td><?= format_rupiah($b['harga_normal']) ?></td>
                <td><span class="badge <?= $b_cls ?>"><?= sanitize(ucfirst(str_replace('_', ' ', $b['status_batch']))) ?></span></td>
              </tr>
              <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
