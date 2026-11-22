<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
if (!defined('BASE_URL')) define('BASE_URL', '/RESPONSI_PEMWEB1/PanenLokal');
if (!defined('SITE_NAME')) define('SITE_NAME', 'PanenLokal');
$page_title = 'Detail Pesanan';
cek_role('pembeli');

$buyer_id = $_SESSION['user_id'];
$order_id = (int)($_GET['id'] ?? 0);
if ($order_id <= 0) {
    header('Location: ' . BASE_URL . '/pembeli/pesanan.php');
    exit;
}

// Ambil info pesanan
$stmt = $conn->prepare("
    SELECT o.*, 
           d.metode, d.pickup_point_id, d.alamat_tujuan, d.jadwal, d.status AS delivery_status,
           pp.nama_titik, pp.alamat AS alamat_titik,
           p.id AS payment_id, p.nominal AS payment_nominal, p.bukti_pembayaran, p.status_pembayaran, p.catatan_admin
    FROM orders o
    LEFT JOIN deliveries d ON d.order_id = o.id
    LEFT JOIN pickup_points pp ON pp.id = d.pickup_point_id
    LEFT JOIN payments p ON p.order_id = o.id
    WHERE o.id = ? AND o.buyer_id = ?
");
$stmt->bind_param("ii", $order_id, $buyer_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header('Location: ' . BASE_URL . '/pembeli/pesanan.php');
    exit;
}

// Ambil item produk dan ulasannya (jika ada)
$q_items = $conn->prepare("
    SELECT oi.*, r.id AS review_id, r.rating, r.komentar
    FROM order_items oi
    LEFT JOIN reviews r ON r.order_item_id = oi.id
    WHERE oi.order_id = ?
");
$q_items->bind_param("i", $order_id);
$q_items->execute();
$items = $q_items->get_result();

$pesan = $_SESSION['flash'] ?? ''; unset($_SESSION['flash']);
$pesan_tipe = $_SESSION['flash_type'] ?? 'success'; unset($_SESSION['flash_type']);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>
<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-title">Menu Pembeli</div>
        <ul class="sidebar-menu">
            <li><a href="<?= BASE_URL ?>/pembeli/index.php"><span class="menu-icon">🏠</span> Dashboard</a></li>
            <li><a href="<?= BASE_URL ?>/pembeli/keranjang.php"><span class="menu-icon">🛒</span> Keranjang</a></li>
            <li><a href="<?= BASE_URL ?>/pembeli/pesanan.php" class="active"><span class="menu-icon">📦</span> Pesanan Saya</a></li>
            <li><a href="<?= BASE_URL ?>/pembeli/profil.php"><span class="menu-icon">👤</span> Profil</a></li>
        </ul>
    </aside>
    <main class="dashboard-main">
        <div class="dashboard-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem;">
            <div>
                <a href="<?= BASE_URL ?>/pembeli/pesanan.php" class="btn btn-secondary btn-sm mb-2">← Kembali ke Pesanan</a>
                <h1>Detail Pesanan #<?= sanitize($order['kode_pesanan']) ?></h1>
            </div>
            <div class="d-flex gap-1">
                <?php if ($order['status'] === 'menunggu_pembayaran' || $order['status_pembayaran'] === 'ditolak'): ?>
                    <a href="<?= BASE_URL ?>/pembeli/pembayaran.php?id=<?= $order['id'] ?>" class="btn btn-warning">💳 Bayar / Upload Bukti</a>
                <?php endif; ?>
                <?php if (!in_array($order['status'], ['menunggu_pembayaran', 'menunggu_verifikasi', 'dibatalkan'])): ?>
                    <a href="<?= BASE_URL ?>/pembeli/invoice.php?id=<?= $order['id'] ?>" class="btn btn-info">📄 Lihat Invoice</a>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($pesan): ?><?= alert($pesan, $pesan_tipe) ?><?php endif; ?>

        <?php if ($order['status_pembayaran'] === 'ditolak'): ?>
            <div class="alert alert-danger">
                <strong>Pembayaran Anda Ditolak:</strong> <?= sanitize($order['catatan_admin'] ?: 'Bukti transfer tidak valid.') ?>
                <br>Silakan unggah ulang bukti transfer yang valid.
            </div>
        <?php endif; ?>

        <div class="grid grid-2">
            <div class="card card-body">
                <h4>Status & Ringkasan</h4>
                <table style="font-size:.9rem;">
                    <tr><td class="text-muted" style="width:140px; padding:.3rem 0;">Status Pesanan</td><td>
                        <?php $sl = get_status_label($order['status']); ?>
                        <span class="badge <?= $sl['class'] ?>"><?= $sl['label'] ?></span>
                    </td></tr>
                    <tr><td class="text-muted">Waktu Pemesanan</td><td><?= format_tanggal($order['tanggal_pesanan'], 'd M Y H:i') ?></td></tr>
                    <tr><td class="text-muted">Total Pembayaran</td><td><strong style="color:var(--green-main);"><?= format_rupiah($order['total_amount']) ?></strong></td></tr>
                    <tr><td class="text-muted">Status Pembayaran</td><td>
                        <span class="badge <?= $order['status_pembayaran'] === 'terverifikasi' ? 'badge-success' : ($order['status_pembayaran'] === 'ditolak' ? 'badge-danger' : 'badge-warning') ?>">
                            <?= ucfirst($order['status_pembayaran'] ?? 'menunggu') ?>
                        </span>
                    </td></tr>
                    <tr><td class="text-muted">Catatan</td><td><?= sanitize($order['catatan'] ?: '-') ?></td></tr>
                </table>
            </div>

            <div class="card card-body">
                <h4>Informasi Pengambilan / Pengantaran</h4>
                <table style="font-size:.9rem;">
                    <tr><td class="text-muted" style="width:140px; padding:.3rem 0;">Metode</td><td><strong><?= ucfirst($order['metode'] ?? 'ambil') ?></strong></td></tr>
                    <?php if (($order['metode'] ?? '') === 'ambil'): ?>
                        <tr><td class="text-muted">Titik Pengambilan</td><td><?= sanitize($order['nama_titik'] ?: '-') ?><br><small class="text-muted"><?= sanitize($order['alamat_titik'] ?: '') ?></small></td></tr>
                    <?php else: ?>
                        <tr><td class="text-muted">Alamat Tujuan</td><td><?= sanitize($order['alamat_tujuan'] ?: '-') ?></td></tr>
                    <?php endif; ?>
                    <tr><td class="text-muted">Jadwal</td><td><?= $order['jadwal'] ? format_tanggal($order['jadwal'], 'd M Y H:i') : 'Menyesuaikan masa panen' ?></td></tr>
                    <tr><td class="text-muted">Status Eksekusi</td><td><span class="badge badge-secondary"><?= ucfirst(str_replace('_',' ',$order['delivery_status'] ?? 'menunggu')) ?></span></td></tr>
                </table>
            </div>
        </div>

        <!-- Tabel Item Pesanan -->
        <div class="table-card mt-3">
            <div style="padding:1rem 1.2rem; border-bottom:1px solid var(--gray-200);">
                <h3 style="margin:0;">Rincian Komoditas</h3>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Komoditas</th>
                        <th>Kuantitas</th>
                        <th>Harga Saat Transaksi</th>
                        <th>Subtotal</th>
                        <th>Ulasan & Penilaian</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($it = $items->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <strong><?= sanitize($it['nama_produk_snapshot']) ?></strong>
                            <?php if ($it['is_rescue']): ?>
                                <span class="badge badge-rescue">🔥 Rescue</span>
                            <?php endif; ?>
                        </td>
                        <td><?= (float)$it['quantity'] ?> <?= sanitize($it['satuan_snapshot']) ?></td>
                        <td><?= format_rupiah($it['harga_satuan']) ?></td>
                        <td><?= format_rupiah($it['quantity'] * $it['harga_satuan']) ?></td>
                        <td>
                            <?php if ($it['review_id']): ?>
                                <span class="stars stars-sm"><?= str_repeat('★', $it['rating']) ?><?= str_repeat('☆', 5 - $it['rating']) ?></span>
                                <br><small class="text-muted">"<?= sanitize(truncate_text($it['komentar'] ?? '', 40)) ?>"</small>
                            <?php elseif ($order['status'] === 'selesai'): ?>
                                <a href="<?= BASE_URL ?>/pembeli/ulasan.php?id=<?= $it['id'] ?>" class="btn btn-sm btn-orange">⭐ Beri Ulasan</a>
                            <?php else: ?>
                                <small class="text-muted">Tersedia setelah selesai</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
