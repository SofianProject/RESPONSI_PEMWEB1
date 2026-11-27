<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
if (!defined('BASE_URL')) define('BASE_URL', '/RESPONSI_PEMWEB1/PanenLokal');
if (!defined('SITE_NAME')) define('SITE_NAME', 'PanenLokal');
$page_title = 'Daftar Pesanan Masuk';
cek_role('petani');

$stmt = $conn->prepare("SELECT id FROM farmer_profiles WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$fp = $stmt->get_result()->fetch_assoc();
$farmer_id = $fp['id'] ?? 0;

$status_filter = $_GET['status'] ?? '';
$where = "c.farmer_id = ?";
$params = [&$farmer_id];
$types = 'i';

if ($status_filter) {
    $where .= " AND o.status = ?";
    $params[] = &$status_filter;
    $types .= 's';
}

$sql = "
    SELECT o.id, o.kode_pesanan, o.tanggal_pesanan, o.status,
           u.nama_lengkap AS nama_pembeli,
           COUNT(oi.id) AS jumlah_item,
           SUM(oi.quantity * oi.harga_satuan) AS total_bagian_petani
    FROM orders o
    JOIN users u ON u.id = o.buyer_id
    JOIN order_items oi ON oi.order_id = o.id
    JOIN harvest_batches hb ON hb.id = oi.batch_id
    JOIN commodities c ON c.id = hb.commodity_id
    WHERE $where
    GROUP BY o.id, o.kode_pesanan, o.tanggal_pesanan, o.status, u.nama_lengkap
    ORDER BY o.tanggal_pesanan DESC
";
$stmt2 = $conn->prepare($sql);
$stmt2->bind_param($types, ...$params);
$stmt2->execute();
$orders = $stmt2->get_result();

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
      <li><a href="<?= BASE_URL ?>/petani/pesanan.php" class="active">📋 Pesanan</a></li>
      <li><a href="<?= BASE_URL ?>/petani/kalender_panen.php">📅 Kalender Panen</a></li>
      <li><a href="<?= BASE_URL ?>/petani/laporan.php">📊 Laporan</a></li>
    </ul>
  </aside>
  <main class="dashboard-main">
    <div class="dashboard-header">
      <h1>📋 Daftar Pesanan Masuk</h1>
      <p class="text-muted">Daftar pesanan konsumen yang memuat komoditas hasil tani Anda.</p>
    </div>

    <!-- Filter Tab -->
    <div class="d-flex gap-1 flex-wrap mb-3">
        <a href="<?= BASE_URL ?>/petani/pesanan.php" class="btn btn-sm <?= !$status_filter ? 'btn-primary' : 'btn-secondary' ?>">Semua</a>
        <a href="?status=dibayar" class="btn btn-sm <?= $status_filter==='dibayar'?'btn-success':'btn-secondary' ?>">Dibayar</a>
        <a href="?status=diproses" class="btn btn-sm <?= $status_filter==='diproses'?'btn-primary':'btn-secondary' ?>">Diproses</a>
        <a href="?status=siap_diambil" class="btn btn-sm <?= $status_filter==='siap_diambil'?'btn-primary':'btn-secondary' ?>">Siap Diambil</a>
        <a href="?status=dikirim" class="btn btn-sm <?= $status_filter==='dikirim'?'btn-info':'btn-secondary' ?>">Dikirim</a>
        <a href="?status=selesai" class="btn btn-sm <?= $status_filter==='selesai'?'btn-dark':'btn-secondary' ?>">Selesai</a>
    </div>

    <div class="table-card">
      <table class="petani-table">
        <thead>
          <tr>
            <th>Kode Pesanan</th>
            <th>Pembeli</th>
            <th>Tanggal Pesanan</th>
            <th>Item Anda</th>
            <th>Subtotal Anda</th>
            <th>Status Pesanan</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
        <?php if ($orders->num_rows > 0): ?>
          <?php while ($o = $orders->fetch_assoc()):
              $sl = get_status_label($o['status']);
          ?>
          <tr>
            <td><strong><?= sanitize($o['kode_pesanan']) ?></strong></td>
            <td><?= sanitize($o['nama_pembeli']) ?></td>
            <td><?= format_tanggal($o['tanggal_pesanan'], 'd M Y H:i') ?></td>
            <td><?= $o['jumlah_item'] ?> produk</td>
            <td><?= format_rupiah($o['total_bagian_petani']) ?></td>
            <td><span class="badge <?= $sl['class'] ?>"><?= $sl['label'] ?></span></td>
            <td>
              <a href="<?= BASE_URL ?>/petani/detail_pesanan.php?id=<?= $o['id'] ?>" class="btn btn-sm btn-primary">Lihat Detail</a>
            </td>
          </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="7" class="text-center text-muted">Belum ada pesanan yang sesuai filter.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
