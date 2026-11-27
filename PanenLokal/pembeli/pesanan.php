<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
if (!defined('BASE_URL')) define('BASE_URL', '/RESPONSI_PEMWEB1/PanenLokal');
if (!defined('SITE_NAME')) define('SITE_NAME', 'PanenLokal');
$page_title = 'Daftar Pesanan Saya';
cek_role('pembeli');

$buyer_id = $_SESSION['user_id'];
$pesan = $_SESSION['flash'] ?? ''; unset($_SESSION['flash']);
$pesan_tipe = $_SESSION['flash_type'] ?? 'success'; unset($_SESSION['flash_type']);

$sql = "
    SELECT o.*, 
           (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) AS total_item,
           d.metode, d.status AS delivery_status
    FROM orders o
    LEFT JOIN deliveries d ON d.order_id = o.id
    WHERE o.buyer_id = ?
    ORDER BY o.tanggal_pesanan DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $buyer_id);
$stmt->execute();
$orders = $stmt->get_result();

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
        <div class="dashboard-header">
            <h1>📦 Riwayat Pesanan Saya</h1>
            <p class="text-muted">Pantau status pembayaran, proses panen, dan pengiriman pesanan Anda.</p>
        </div>

        <?php if ($pesan): ?><?= alert($pesan, $pesan_tipe) ?><?php endif; ?>

        <div class="table-card">
            <table class="admin-dashboard-table">
                <thead>
                    <tr>
                        <th>Kode Pesanan</th>
                        <th>Tanggal</th>
                        <th>Jumlah Produk</th>
                        <th>Total Tagihan</th>
                        <th>Status</th>
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
                        <td><?= format_tanggal($o['tanggal_pesanan'], 'd M Y H:i') ?></td>
                        <td><?= $o['total_item'] ?> produk</td>
                        <td style="font-weight:700; color:var(--green-main);"><?= format_rupiah($o['total_amount']) ?></td>
                        <td><span class="badge <?= $sl['class'] ?>"><?= $sl['label'] ?></span></td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="<?= BASE_URL ?>/pembeli/detail_pesanan.php?id=<?= $o['id'] ?>" class="btn btn-sm btn-primary">Detail</a>
                                <?php if ($o['status'] === 'menunggu_pembayaran'): ?>
                                    <a href="<?= BASE_URL ?>/pembeli/pembayaran.php?id=<?= $o['id'] ?>" class="btn btn-sm btn-warning">Bayar</a>
                                <?php elseif (!in_array($o['status'], ['menunggu_pembayaran', 'menunggu_verifikasi', 'dibatalkan'])): ?>
                                    <a href="<?= BASE_URL ?>/pembeli/invoice.php?id=<?= $o['id'] ?>" class="btn btn-sm btn-info">Invoice</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr class="empty-row"><td colspan="6" class="text-center text-muted admin-table-empty">Anda belum memiliki riwayat pesanan.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
