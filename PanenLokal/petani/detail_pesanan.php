<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
if (!defined('BASE_URL')) define('BASE_URL', '/RESPONSI_PEMWEB1/PanenLokal');
if (!defined('SITE_NAME')) define('SITE_NAME', 'PanenLokal');
$page_title = 'Detail Pesanan';
cek_role('petani');

$stmt = $conn->prepare("SELECT id FROM farmer_profiles WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$fp = $stmt->get_result()->fetch_assoc();
$farmer_id = $fp['id'] ?? 0;

$order_id = (int)($_GET['id'] ?? 0);
if ($order_id <= 0) {
    header('Location: ' . BASE_URL . '/petani/pesanan.php');
    exit;
}

// Verifikasi pesanan memuat komoditas milik petani ini
$q_check = $conn->prepare("
    SELECT o.*, u.nama_lengkap AS nama_pembeli, u.no_hp AS hp_pembeli,
           d.metode, d.pickup_point_id, d.alamat_tujuan, d.jadwal, d.status AS delivery_status,
           pp.nama_titik, pp.alamat AS alamat_titik
    FROM orders o
    JOIN users u ON u.id = o.buyer_id
    JOIN order_items oi ON oi.order_id = o.id
    JOIN harvest_batches hb ON hb.id = oi.batch_id
    JOIN commodities c ON c.id = hb.commodity_id
    LEFT JOIN deliveries d ON d.order_id = o.id
    LEFT JOIN pickup_points pp ON pp.id = d.pickup_point_id
    WHERE o.id = ? AND c.farmer_id = ?
    LIMIT 1
");
$q_check->bind_param("ii", $order_id, $farmer_id);
$q_check->execute();
$order = $q_check->get_result()->fetch_assoc();

if (!$order) {
    header('Location: ' . BASE_URL . '/petani/pesanan.php');
    exit;
}

$pesan = '';
// Aksi update status pesanan (misal petani menandai sudah diproses atau siap diambil)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_status'])) {
    if (!verify_csrf()) {
        $pesan = alert('Permintaan tidak valid (token keamanan tidak cocok).', 'danger');
    } else {
        $new_st = $_POST['action_status'];
        if (in_array($new_st, ['diproses', 'siap_diambil', 'dikirim', 'selesai'])) {
            $up = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $up->bind_param("si", $new_st, $order_id);
            $up->execute();

            $up_d = $conn->prepare("UPDATE deliveries SET status = ? WHERE order_id = ?");
            $up_d->bind_param("si", $new_st, $order_id);
            $up_d->execute();

            $order['status'] = $new_st;
            $order['delivery_status'] = $new_st;
            $pesan = alert("Status pesanan berhasil diperbarui menjadi: " . ucfirst(str_replace('_',' ',$new_st)), 'success');
        }
    }
}

// Ambil item produk dalam pesanan khusus milik petani ini
$q_items = $conn->prepare("
    SELECT oi.*, hb.tanggal_estimasi_panen
    FROM order_items oi
    JOIN harvest_batches hb ON hb.id = oi.batch_id
    JOIN commodities c ON c.id = hb.commodity_id
    WHERE oi.order_id = ? AND c.farmer_id = ?
");
$q_items->bind_param("ii", $order_id, $farmer_id);
$q_items->execute();
$items = $q_items->get_result();

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
      <a href="<?= BASE_URL ?>/petani/pesanan.php" class="btn btn-secondary btn-sm mb-2">← Kembali ke Pesanan</a>
      <h1>Detail Pesanan — <?= sanitize($order['kode_pesanan']) ?></h1>
    </div>

    <?= $pesan ?>

    <div class="grid grid-2">
      <div class="card card-body">
        <h4>Informasi Pemesan</h4>
        <table class="info-table mt-2">
          <tr><td>Nama Pembeli</td><td><strong><?= sanitize($order['nama_pembeli']) ?></strong></td></tr>
          <tr><td>No. Kontak</td><td><?= sanitize($order['hp_pembeli'] ?: '-') ?></td></tr>
          <tr><td>Tanggal Pesan</td><td><?= format_tanggal($order['tanggal_pesanan'], 'd M Y H:i') ?></td></tr>
          <tr><td>Catatan Pembeli</td><td><?= sanitize($order['catatan'] ?: '-') ?></td></tr>
        </table>
      </div>

      <div class="card card-body">
        <h4>Pengiriman / Pengambilan</h4>
        <table class="info-table mt-2">
          <tr><td>Metode</td><td><strong><?= ucfirst($order['metode'] ?? 'ambil') ?></strong></td></tr>
          <?php if (($order['metode'] ?? '') === 'ambil'): ?>
            <tr><td>Titik Ambil</td><td><?= sanitize($order['nama_titik']) ?><br><small class="text-muted"><?= sanitize($order['alamat_titik']) ?></small></td></tr>
          <?php else: ?>
            <tr><td>Alamat Antar</td><td><?= sanitize($order['alamat_tujuan'] ?: '-') ?></td></tr>
          <?php endif; ?>
          <tr><td>Status Pesanan</td><td>
            <?php $sl = get_status_label($order['status']); ?>
            <span class="badge <?= $sl['class'] ?>"><?= $sl['label'] ?></span>
          </td></tr>
        </table>
      </div>
    </div>

    <!-- Tabel Item Komoditas Anda -->
    <div class="table-card mt-3">
      <div style="padding:1rem 1.2rem; border-bottom:1px solid var(--gray-200);">
        <h3 style="margin:0;">Produk yang Anda Siapkan</h3>
      </div>
      <div class="table-responsive">
      <table>
        <thead>
          <tr>
            <th>Nama Komoditas</th>
            <th>Estimasi Tanggal Panen</th>
            <th>Kuantitas</th>
            <th>Harga Satuan</th>
            <th>Subtotal</th>
            <th>Food Rescue</th>
          </tr>
        </thead>
        <tbody>
        <?php
        $total_petani = 0;
        while ($it = $items->fetch_assoc()):
            $sub = $it['quantity'] * $it['harga_satuan'];
            $total_petani += $sub;
        ?>
          <tr>
            <td><strong><?= sanitize($it['nama_produk_snapshot']) ?></strong></td>
            <td><?= format_tanggal($it['tanggal_estimasi_panen']) ?></td>
            <td><?= (float)$it['quantity'] ?> <?= sanitize($it['satuan_snapshot']) ?></td>
            <td><?= format_rupiah($it['harga_satuan']) ?></td>
            <td><strong><?= format_rupiah($sub) ?></strong></td>
            <td><?= $it['is_rescue'] ? '<span class="badge badge-rescue">🔥 Rescue</span>' : '-' ?></td>
          </tr>
        <?php endwhile; ?>
        </tbody>
        <tfoot>
          <tr>
            <th colspan="4" class="text-right">Total Bagian Usaha Tani Anda:</th>
            <th colspan="2" style="font-size:1.1rem; color:var(--green-main);"><?= format_rupiah($total_petani) ?></th>
          </tr>
        </tfoot>
      </table>
      </div>
    </div>

    <!-- Tombol Aksi Status oleh Petani -->
    <?php if (in_array($order['status'], ['dibayar', 'diproses', 'siap_diambil', 'dikirim'])): ?>
    <div class="card card-body mt-3">
      <h4>Perbarui Tahap Pemrosesan Hasil Tani</h4>
      <p class="text-muted" style="font-size:.9rem;">Perbarui status untuk memberitahu konsumen perkembangan panen & pemenuhan pesanan.</p>
      <form method="POST" class="d-flex gap-2 flex-wrap">
        <?= csrf_field() ?>
        <?php if ($order['status'] === 'dibayar'): ?>
          <button type="submit" name="action_status" value="diproses" class="btn btn-primary" onclick="return confirm('Mulai siapkan/proses panen ini?')">📦 Tandai Diproses / Disiapkan</button>
        <?php endif; ?>

        <?php if ($order['status'] === 'diproses'): ?>
          <?php if (($order['metode'] ?? '') === 'ambil'): ?>
            <button type="submit" name="action_status" value="siap_diambil" class="btn btn-info" onclick="return confirm('Tandai sudah siap diambil di titik pengambilan?')">📍 Siap Diambil</button>
          <?php else: ?>
            <button type="submit" name="action_status" value="dikirim" class="btn btn-info" onclick="return confirm('Tandai barang sudah dikirimkan?')">🚚 Kirim Pesanan</button>
          <?php endif; ?>
        <?php endif; ?>

        <?php if (in_array($order['status'], ['siap_diambil', 'dikirim'])): ?>
          <button type="submit" name="action_status" value="selesai" class="btn btn-success" onclick="return confirm('Konfirmasi pesanan telah tuntas diterima pembeli?')">✅ Tandai Selesai</button>
        <?php endif; ?>
      </form>
    </div>
    <?php endif; ?>
  </main>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
