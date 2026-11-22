<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
if (!defined('BASE_URL')) define('BASE_URL', '/RESPONSI_PEMWEB1/PanenLokal');
if (!defined('SITE_NAME')) define('SITE_NAME', 'PanenLokal');
$page_title = 'Dashboard Admin';
cek_role('admin');

// Stats
$total_users    = $conn->query("SELECT COUNT(*) FROM users WHERE role != 'admin'")->fetch_row()[0];
$total_petani   = $conn->query("SELECT COUNT(*) FROM farmer_profiles")->fetch_row()[0];
$total_pesanan  = $conn->query("SELECT COUNT(*) FROM orders")->fetch_row()[0];
$menunggu_ver   = $conn->query("SELECT COUNT(*) FROM payments WHERE status_pembayaran='menunggu'")->fetch_row()[0];
$total_transaksi= $conn->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status='selesai'")->fetch_row()[0];
$rescue_kg      = $conn->query("SELECT COALESCE(SUM(oi.quantity),0) FROM order_items oi JOIN orders o ON o.id=oi.order_id WHERE oi.is_rescue=1 AND o.status='selesai'")->fetch_row()[0];

// Pesanan terbaru
$pesanan_terbaru = $conn->query("SELECT o.kode_pesanan,o.total_amount,o.status,o.tanggal_pesanan,u.nama_lengkap FROM orders o JOIN users u ON u.id=o.buyer_id ORDER BY o.tanggal_pesanan DESC LIMIT 8");

// Pembayaran menunggu verifikasi
$bayar_menunggu = $conn->query("SELECT p.id,o.kode_pesanan,o.total_amount,p.created_at,u.nama_lengkap FROM payments p JOIN orders o ON o.id=p.order_id JOIN users u ON u.id=o.buyer_id WHERE p.status_pembayaran='menunggu' ORDER BY p.created_at ASC LIMIT 5");

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>
<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-title">Menu Admin</div>
        <ul class="sidebar-menu">
            <li><a href="<?= BASE_URL ?>/admin/index.php" class="active"><span class="menu-icon">🏠</span>Dashboard</a></li>
            <li><a href="<?= BASE_URL ?>/admin/pengguna.php"><span class="menu-icon">👥</span>Pengguna</a></li>
            <li><a href="<?= BASE_URL ?>/admin/verifikasi_pembayaran.php"><span class="menu-icon">💳</span>Verifikasi Pembayaran</a></li>
            <li><a href="<?= BASE_URL ?>/admin/titik_pengambilan.php"><span class="menu-icon">📍</span>Titik Pengambilan</a></li>
            <li><a href="<?= BASE_URL ?>/admin/pesanan.php"><span class="menu-icon">📋</span>Semua Pesanan</a></li>
            <li><a href="<?= BASE_URL ?>/admin/laporan.php"><span class="menu-icon">📊</span>Laporan</a></li>
        </ul>
    </aside>
    <main class="dashboard-main">
        <div class="dashboard-header">
            <h1>Dashboard Admin</h1>
            <p class="text-muted">Selamat datang, <?= sanitize($_SESSION['nama']) ?>!</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-info"><div class="stat-value"><?= $total_users ?></div><div class="stat-label">Total Pengguna</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🧑‍🌾</div>
                <div class="stat-info"><div class="stat-value"><?= $total_petani ?></div><div class="stat-label">Petani Terdaftar</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📋</div>
                <div class="stat-info"><div class="stat-value"><?= $total_pesanan ?></div><div class="stat-label">Total Pesanan</div></div>
            </div>
            <div class="stat-card" style="border-left:4px solid var(--yellow);">
                <div class="stat-icon">💳</div>
                <div class="stat-info"><div class="stat-value" style="color:var(--yellow);"><?= $menunggu_ver ?></div><div class="stat-label">Menunggu Verifikasi</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-info"><div class="stat-value" style="font-size:1.2rem;"><?= format_rupiah($total_transaksi) ?></div><div class="stat-label">Total Transaksi Selesai</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">♻️</div>
                <div class="stat-info"><div class="stat-value"><?= number_format($rescue_kg, 1, ',', '.') ?></div><div class="stat-label">Kg Pangan Terselamatkan</div></div>
            </div>
        </div>

        <?php if ($menunggu_ver > 0): ?>
        <div class="alert alert-warning">⚠️ Ada <strong><?= $menunggu_ver ?></strong> pembayaran menunggu verifikasi. <a href="<?= BASE_URL ?>/admin/verifikasi_pembayaran.php">Verifikasi sekarang →</a></div>
        <?php endif; ?>

        <div class="grid grid-2">
            <div>
                <h3>Pembayaran Menunggu Verifikasi</h3>
                <div class="table-card mt-2">
                    <table>
                        <thead><tr><th>Kode Pesanan</th><th>Pembeli</th><th>Total</th><th>Aksi</th></tr></thead>
                        <tbody>
                        <?php if ($bayar_menunggu->num_rows > 0): while ($b = $bayar_menunggu->fetch_assoc()): ?>
                            <tr>
                                <td><?= sanitize($b['kode_pesanan']) ?></td>
                                <td><?= sanitize($b['nama_lengkap']) ?></td>
                                <td><?= format_rupiah($b['total_amount']) ?></td>
                                <td><a href="<?= BASE_URL ?>/admin/detail_pembayaran.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-primary">Periksa</a></td>
                            </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="4" class="text-center text-muted">Tidak ada pembayaran menunggu</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div>
                <h3>Pesanan Terbaru</h3>
                <div class="table-card mt-2">
                    <table>
                        <thead><tr><th>Kode</th><th>Pembeli</th><th>Total</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php while ($ps = $pesanan_terbaru->fetch_assoc()):
                            $sl = get_status_label($ps['status']);
                        ?>
                            <tr>
                                <td style="font-size:.82rem;"><?= sanitize($ps['kode_pesanan']) ?></td>
                                <td><?= sanitize($ps['nama_lengkap']) ?></td>
                                <td><?= format_rupiah($ps['total_amount']) ?></td>
                                <td><span class="badge <?= $sl['class'] ?>"><?= $sl['label'] ?></span></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
