<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
if (!defined('BASE_URL')) define('BASE_URL', '/RESPONSI_PEMWEB1/PanenLokal');
if (!defined('SITE_NAME')) define('SITE_NAME', 'PanenLokal');
$page_title = 'Verifikasi Pembayaran';
cek_role('admin');

$pesan = $_SESSION['flash'] ?? ''; unset($_SESSION['flash']);
$pesan_tipe = $_SESSION['flash_type'] ?? 'success'; unset($_SESSION['flash_type']);

$filter = $_GET['filter'] ?? 'menunggu';

$where = "1=1";
$params = [];
$types  = '';
if (in_array($filter, ['menunggu', 'terverifikasi', 'ditolak'], true)) {
    $where .= " AND p.status_pembayaran = ?";
    $params[] = &$filter;
    $types .= 's';
}

$sql = "
    SELECT p.id, p.nominal, p.bukti_pembayaran, p.status_pembayaran, p.catatan_admin, p.created_at,
           o.kode_pesanan, o.total_amount, o.status AS order_status,
           u.nama_lengkap, u.username
    FROM payments p
    JOIN orders o ON o.id = p.order_id
    JOIN users u ON u.id = o.buyer_id
    WHERE $where
    ORDER BY p.created_at DESC
";
$stmt = $conn->prepare($sql);
if ($types !== '') {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$payments = $stmt->get_result();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>
<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-title">Menu Admin</div>
        <ul class="sidebar-menu">
            <li><a href="<?= BASE_URL ?>/admin/index.php"><span class="menu-icon">🏠</span>Dashboard</a></li>
            <li><a href="<?= BASE_URL ?>/admin/pengguna.php"><span class="menu-icon">👥</span>Pengguna</a></li>
            <li><a href="<?= BASE_URL ?>/admin/verifikasi_pembayaran.php" class="active"><span class="menu-icon">💳</span>Verifikasi Pembayaran</a></li>
            <li><a href="<?= BASE_URL ?>/admin/titik_pengambilan.php"><span class="menu-icon">📍</span>Titik Pengambilan</a></li>
            <li><a href="<?= BASE_URL ?>/admin/pesanan.php"><span class="menu-icon">📋</span>Semua Pesanan</a></li>
            <li><a href="<?= BASE_URL ?>/admin/laporan.php"><span class="menu-icon">📊</span>Laporan</a></li>
        </ul>
    </aside>
    <main class="dashboard-main">
        <div class="dashboard-header">
            <h1>💳 Verifikasi Pembayaran</h1>
        </div>
        <?php if ($pesan): ?><?= alert($pesan, $pesan_tipe) ?><?php endif; ?>

        <div class="admin-filter-group mb-3">
            <a href="?filter=menunggu"      class="filter-pill <?= $filter==='menunggu'?'active':'' ?>">Menunggu</a>
            <a href="?filter=terverifikasi" class="filter-pill <?= $filter==='terverifikasi'?'active':'' ?>">Terverifikasi</a>
            <a href="?filter=ditolak"       class="filter-pill <?= $filter==='ditolak'?'active':'' ?>">Ditolak</a>
        </div>

        <div class="table-card">
            <table class="admin-dashboard-table">
                <thead><tr><th>Kode Pesanan</th><th>Pembeli</th><th>Nominal</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr></thead>
                <tbody>
                <?php if ($payments && $payments->num_rows > 0): ?>
                    <?php while ($pay = $payments->fetch_assoc()): ?>
                        <tr>
                            <td><?= sanitize($pay['kode_pesanan']) ?></td>
                            <td><?= sanitize($pay['nama_lengkap']) ?></td>
                            <td><?= format_rupiah($pay['nominal']) ?></td>
                            <td><span class="badge <?= $pay['status_pembayaran']==='menunggu'?'badge-warning':($pay['status_pembayaran']==='terverifikasi'?'badge-success':'badge-danger') ?>"><?= ucfirst($pay['status_pembayaran']) ?></span></td>
                            <td style="font-size:.82rem;"><?= format_tanggal($pay['created_at'], 'd M Y H:i') ?></td>
                            <td><a href="<?= BASE_URL ?>/admin/detail_pembayaran.php?id=<?= $pay['id'] ?>" class="btn btn-sm btn-primary">Detail</a></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr class="empty-row"><td colspan="6" class="text-center text-muted admin-table-empty">Tidak ada pembayaran menunggu verifikasi</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
