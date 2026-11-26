<?php
if (!defined('BASE_URL')) define('BASE_URL', '/RESPONSI_PEMWEB1/PanenLokal');
if (!defined('SITE_NAME')) define('SITE_NAME', 'PanenLokal');
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

cek_role('pembeli');

$user_id  = $_SESSION['user_id'];
$nama     = $_SESSION['nama'] ?? 'Pembeli';

// Stat cards
$stmt = $conn->prepare("SELECT
    COUNT(*) AS total_pesanan,
    SUM(CASE WHEN status NOT IN ('selesai','dibatalkan') THEN 1 ELSE 0 END) AS pesanan_aktif,
    SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) AS pesanan_selesai
FROM orders WHERE buyer_id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
$stmt->close();

// 5 pesanan terbaru
$stmt = $conn->prepare("SELECT id, kode_pesanan, tanggal_pesanan, total_amount, status
    FROM orders WHERE buyer_id = ?
    ORDER BY tanggal_pesanan DESC LIMIT 5");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$pesanan_terbaru = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$page_title = 'Dashboard';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>
<div class="dashboard-layout">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-title">Menu Pembeli</div>
        <ul class="sidebar-menu">
            <li><a href="<?= BASE_URL ?>/pembeli/index.php" class="active"><span class="menu-icon">🏠</span> Dashboard</a></li>
            <li><a href="<?= BASE_URL ?>/pembeli/keranjang.php"><span class="menu-icon">🛒</span> Keranjang</a></li>
            <li><a href="<?= BASE_URL ?>/pembeli/pesanan.php"><span class="menu-icon">📦</span> Pesanan Saya</a></li>
            <li><a href="<?= BASE_URL ?>/pembeli/profil.php"><span class="menu-icon">👤</span> Profil</a></li>
        </ul>
    </aside>

    <!-- Main -->
    <main class="dashboard-main">
        <div class="dashboard-header">
            <h1>Halo, <?= sanitize($nama) ?> 👋</h1>
            <p class="text-muted">Selamat datang di dashboard pembeli PanenLokal.</p>
        </div>

        <?php if (!empty($_SESSION['flash'])): ?>
            <?php
            $flash_msg  = is_array($_SESSION['flash']) ? ($_SESSION['flash']['msg'] ?? '') : $_SESSION['flash'];
            $flash_type = is_array($_SESSION['flash']) ? ($_SESSION['flash']['type'] ?? 'success') : ($_SESSION['flash_type'] ?? 'success');
            ?>
            <?php if ($flash_msg !== ''): ?>
                <?= alert($flash_msg, $flash_type) ?>
            <?php endif; ?>
            <?php unset($_SESSION['flash'], $_SESSION['flash_type']); ?>
        <?php endif; ?>

        <!-- Stat Cards -->
        <div class="stats-grid">
            <div class="stat-card stat-accent-green">
                <div class="stat-icon">📋</div>
                <div class="stat-info">
                    <div class="stat-value"><?= (int)$stats['total_pesanan'] ?></div>
                    <div class="stat-label">Total Pesanan</div>
                </div>
            </div>
            <div class="stat-card stat-accent-orange">
                <div class="stat-icon">🔄</div>
                <div class="stat-info">
                    <div class="stat-value"><?= (int)$stats['pesanan_aktif'] ?></div>
                    <div class="stat-label">Pesanan Aktif</div>
                </div>
            </div>
            <div class="stat-card stat-accent-green-dark">
                <div class="stat-icon">✅</div>
                <div class="stat-info">
                    <div class="stat-value"><?= (int)$stats['pesanan_selesai'] ?></div>
                    <div class="stat-label">Pesanan Selesai</div>
                </div>
            </div>
        </div>

        <!-- Pesanan Terbaru -->
        <div class="table-card">
            <div style="padding:1.2rem 1.5rem; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--gray-200);">
                <h3 style="margin:0;">Pesanan Terbaru</h3>
                <a href="<?= BASE_URL ?>/pembeli/pesanan.php" class="btn btn-sm btn-primary">Lihat Semua</a>
            </div>
            <?php if (empty($pesanan_terbaru)): ?>
                <div class="empty-state">
                    <div class="empty-icon">📭</div>
                    <h4 class="empty-title">Belum Ada Pesanan</h4>
                    <p class="text-muted">Anda belum memiliki riwayat pesanan. Mulai jelajahi hasil tani segar langsung dari petani lokal.</p>
                    <a href="<?= BASE_URL ?>/katalog.php" class="btn btn-primary mt-1">Jelajahi Katalog Sekarang</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Kode Pesanan</th>
                                <th>Tanggal</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pesanan_terbaru as $p):
                                $sl = get_status_label($p['status']);
                            ?>
                            <tr>
                                <td><strong><?= sanitize($p['kode_pesanan']) ?></strong></td>
                                <td><?= format_tanggal($p['tanggal_pesanan'], 'd M Y H:i') ?></td>
                                <td><?= format_rupiah($p['total_amount']) ?></td>
                                <td><span class="badge <?= sanitize($sl['class']) ?>"><?= sanitize($sl['label']) ?></span></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/pembeli/detail_pesanan.php?id=<?= (int)($p['id'] ?? 0) ?>" class="btn btn-sm btn-primary">Detail</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>
<?php
require_once __DIR__ . '/../includes/footer.php';
?>
