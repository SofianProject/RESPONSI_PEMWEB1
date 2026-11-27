<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
if (!defined('BASE_URL')) define('BASE_URL', '/RESPONSI_PEMWEB1/PanenLokal');
if (!defined('SITE_NAME')) define('SITE_NAME', 'PanenLokal');
$page_title = 'Laporan & Analitik';
cek_role('admin');

// 1. Total transaksi selesai
$total_revenue = $conn->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status = 'selesai'")->fetch_row()[0];

// 2. Total kuantitas pangan rescue terselamatkan (kg)
$rescue_kg = $conn->query("
    SELECT COALESCE(SUM(oi.quantity), 0)
    FROM order_items oi
    JOIN orders o ON o.id = oi.order_id
    WHERE oi.is_rescue = 1 AND o.status = 'selesai'
")->fetch_row()[0];

// 3. Jumlah transaksi selesai
$count_selesai = $conn->query("SELECT COUNT(*) FROM orders WHERE status = 'selesai'")->fetch_row()[0];

// 4. Komoditas paling banyak terjual
$top_commodities = $conn->query("
    SELECT oi.nama_produk_snapshot, oi.satuan_snapshot,
           SUM(oi.quantity) AS total_qty,
           SUM(oi.quantity * oi.harga_satuan) AS total_uang
    FROM order_items oi
    JOIN orders o ON o.id = oi.order_id
    WHERE o.status = 'selesai'
    GROUP BY oi.nama_produk_snapshot, oi.satuan_snapshot
    ORDER BY total_qty DESC
    LIMIT 5
");

// 5. Petani paling aktif berdasarkan penjualan
$top_farmers = $conn->query("
    SELECT fp.nama_usaha, u.nama_lengkap,
           COUNT(DISTINCT o.id) AS total_pesanan,
           COALESCE(SUM(oi.quantity * oi.harga_satuan), 0) AS omzet
    FROM farmer_profiles fp
    JOIN users u ON u.id = fp.user_id
    JOIN commodities c ON c.farmer_id = fp.id
    JOIN harvest_batches hb ON hb.commodity_id = c.id
    JOIN order_items oi ON oi.batch_id = hb.id
    JOIN orders o ON o.id = oi.order_id AND o.status = 'selesai'
    GROUP BY fp.id, fp.nama_usaha, u.nama_lengkap
    ORDER BY omzet DESC
    LIMIT 5
");

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>
<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-title">Menu Admin</div>
        <ul class="sidebar-menu">
            <li><a href="<?= BASE_URL ?>/admin/index.php"><span class="menu-icon">🏠</span>Dashboard</a></li>
            <li><a href="<?= BASE_URL ?>/admin/pengguna.php"><span class="menu-icon">👥</span>Pengguna</a></li>
            <li><a href="<?= BASE_URL ?>/admin/verifikasi_pembayaran.php"><span class="menu-icon">💳</span>Verifikasi Pembayaran</a></li>
            <li><a href="<?= BASE_URL ?>/admin/titik_pengambilan.php"><span class="menu-icon">📍</span>Titik Pengambilan</a></li>
            <li><a href="<?= BASE_URL ?>/admin/pesanan.php"><span class="menu-icon">📋</span>Semua Pesanan</a></li>
            <li><a href="<?= BASE_URL ?>/admin/laporan.php" class="active"><span class="menu-icon">📊</span>Laporan</a></li>
        </ul>
    </aside>
    <main class="dashboard-main">
        <div class="dashboard-header">
            <h1>📊 Laporan Transaksi & Penyelamatan Pangan</h1>
            <p class="text-muted">Statistik menyeluruh kinerja platform PanenLokal dan capaian SDG 2 Zero Hunger.</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-info">
                    <div class="stat-value" style="font-size:1.3rem;"><?= format_rupiah($total_revenue) ?></div>
                    <div class="stat-label">Total Omzet Pesanan Selesai</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📦</div>
                <div class="stat-info">
                    <div class="stat-value"><?= $count_selesai ?></div>
                    <div class="stat-label">Pesanan Berhasil Tuntas</div>
                </div>
            </div>
            <div class="stat-card" style="border-left: 4px solid var(--orange);">
                <div class="stat-icon">♻️</div>
                <div class="stat-info">
                    <div class="stat-value" style="color:var(--orange);"><?= number_format($rescue_kg, 1, ',', '.') ?> kg</div>
                    <div class="stat-label">Pangan Food Rescue Terselamatkan</div>
                </div>
            </div>
        </div>

        <div class="grid grid-2 mt-3">
            <div class="table-card">
                <div style="padding:1rem 1.2rem; border-bottom:1px solid var(--gray-200);">
                    <h3 style="margin:0;">🥦 Komoditas Terlaris</h3>
                </div>
                <table class="admin-dashboard-table">
                    <thead>
                        <tr>
                            <th>Komoditas</th>
                            <th>Total Kuantitas</th>
                            <th>Nilai Penjualan</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($top_commodities->num_rows > 0): ?>
                        <?php while ($tc = $top_commodities->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?= sanitize($tc['nama_produk_snapshot']) ?></strong></td>
                            <td><?= (float)$tc['total_qty'] ?> <?= sanitize($tc['satuan_snapshot']) ?></td>
                            <td><?= format_rupiah($tc['total_uang']) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="3" class="text-center text-muted">Belum ada data transaksi selesai.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="table-card">
                <div style="padding:1rem 1.2rem; border-bottom:1px solid var(--gray-200);">
                    <h3 style="margin:0;">🧑‍🌾 Petani Paling Produktif</h3>
                </div>
                <table class="admin-dashboard-table">
                    <thead>
                        <tr>
                            <th>Usaha Tani</th>
                            <th>Pesanan Selesai</th>
                            <th>Total Omzet</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($top_farmers->num_rows > 0): ?>
                        <?php while ($tf = $top_farmers->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?= sanitize($tf['nama_usaha']) ?></strong><br><small class="text-muted"><?= sanitize($tf['nama_lengkap']) ?></small></td>
                            <td><?= $tf['total_pesanan'] ?> pesanan</td>
                            <td><?= format_rupiah($tf['omzet']) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="3" class="text-center text-muted">Belum ada data petani.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
