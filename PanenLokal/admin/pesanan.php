<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
if (!defined('BASE_URL')) define('BASE_URL', '/RESPONSI_PEMWEB1/PanenLokal');
if (!defined('SITE_NAME')) define('SITE_NAME', 'PanenLokal');
$page_title = 'Kelola Pesanan';
cek_role('admin');

$pesan = $_SESSION['flash'] ?? ''; unset($_SESSION['flash']);
$pesan_tipe = $_SESSION['flash_type'] ?? 'success'; unset($_SESSION['flash_type']);

// Filter status
$status_filter = $_GET['status'] ?? '';
$where = "1=1";
$params = [];
$types = '';

if ($status_filter) {
    $where .= " AND o.status = ?";
    $params[] = &$status_filter;
    $types .= 's';
}

$sql = "SELECT o.id, o.kode_pesanan, o.tanggal_pesanan, o.total_amount, o.status,
               u.nama_lengkap AS nama_pembeli,
               d.metode, d.status AS delivery_status
        FROM orders o
        JOIN users u ON u.id = o.buyer_id
        LEFT JOIN deliveries d ON d.order_id = o.id
        WHERE $where
        ORDER BY o.tanggal_pesanan DESC";
$stmt = $conn->prepare($sql);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$orders = $stmt->get_result();

// Update status pesanan jika dipanggil oleh admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['new_status'])) {
    $oid = (int)$_POST['order_id'];
    $nstatus = $_POST['new_status'];
    $allowed = ['menunggu_pembayaran','menunggu_verifikasi','dibayar','diproses','siap_diambil','dikirim','selesai','dibatalkan'];
    if (in_array($nstatus, $allowed)) {
        $up = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $up->bind_param('si', $nstatus, $oid);
        $up->execute();

        // Update status delivery jika relevan
        if (in_array($nstatus, ['diproses','siap_diambil','dikirim','selesai'])) {
            $up_del = $conn->prepare("UPDATE deliveries SET status = ? WHERE order_id = ?");
            $up_del->bind_param('si', $nstatus, $oid);
            $up_del->execute();
        }

        $_SESSION['flash'] = "Status pesanan berhasil diubah menjadi: $nstatus";
        $_SESSION['flash_type'] = 'success';
        header('Location: ' . BASE_URL . '/admin/pesanan.php' . ($status_filter ? '?status=' . urlencode($status_filter) : ''));
        exit;
    }
}

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
            <li><a href="<?= BASE_URL ?>/admin/pesanan.php" class="active"><span class="menu-icon">📋</span>Semua Pesanan</a></li>
            <li><a href="<?= BASE_URL ?>/admin/laporan.php"><span class="menu-icon">📊</span>Laporan</a></li>
        </ul>
    </aside>
    <main class="dashboard-main">
        <div class="dashboard-header">
            <h1>📋 Semua Pesanan</h1>
            <p class="text-muted">Pantau dan kelola seluruh pesanan konsumen.</p>
        </div>

        <?php if ($pesan): ?><?= alert($pesan, $pesan_tipe) ?><?php endif; ?>

        <!-- Filter Tab -->
        <div class="d-flex gap-1 flex-wrap mb-3">
            <a href="<?= BASE_URL ?>/admin/pesanan.php" class="btn btn-sm <?= !$status_filter ? 'btn-primary' : 'btn-secondary' ?>">Semua</a>
            <a href="?status=menunggu_pembayaran" class="btn btn-sm <?= $status_filter==='menunggu_pembayaran'?'btn-warning':'btn-secondary' ?>">Menunggu Pembayaran</a>
            <a href="?status=menunggu_verifikasi" class="btn btn-sm <?= $status_filter==='menunggu_verifikasi'?'btn-info':'btn-secondary' ?>">Menunggu Verifikasi</a>
            <a href="?status=dibayar" class="btn btn-sm <?= $status_filter==='dibayar'?'btn-success':'btn-secondary' ?>">Dibayar</a>
            <a href="?status=diproses" class="btn btn-sm <?= $status_filter==='diproses'?'btn-primary':'btn-secondary' ?>">Diproses</a>
            <a href="?status=siap_diambil" class="btn btn-sm <?= $status_filter==='siap_diambil'?'btn-primary':'btn-secondary' ?>">Siap Diambil</a>
            <a href="?status=dikirim" class="btn btn-sm <?= $status_filter==='dikirim'?'btn-info':'btn-secondary' ?>">Dikirim</a>
            <a href="?status=selesai" class="btn btn-sm <?= $status_filter==='selesai'?'btn-dark':'btn-secondary' ?>">Selesai</a>
            <a href="?status=dibatalkan" class="btn btn-sm <?= $status_filter==='dibatalkan'?'btn-danger':'btn-secondary' ?>">Dibatalkan</a>
        </div>

        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>Kode Pesanan</th>
                        <th>Pembeli</th>
                        <th>Tanggal</th>
                        <th>Total Tagihan</th>
                        <th>Metode</th>
                        <th>Status</th>
                        <th>Ubah Status</th>
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
                        <td><?= format_rupiah($o['total_amount']) ?></td>
                        <td>
                            <span class="badge badge-secondary"><?= ucfirst($o['metode'] ?? 'ambil') ?></span>
                        </td>
                        <td>
                            <span class="badge <?= $sl['class'] ?>"><?= $sl['label'] ?></span>
                        </td>
                        <td>
                            <form method="POST" style="display:inline-flex; gap:.3rem; align-items:center;">
                                <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                <select name="new_status" class="form-control" style="padding:.2rem .4rem; font-size:.8rem; height:auto;">
                                    <?php
                                    $all_st = ['menunggu_pembayaran','menunggu_verifikasi','dibayar','diproses','siap_diambil','dikirim','selesai','dibatalkan'];
                                    foreach ($all_st as $st):
                                    ?>
                                        <option value="<?= $st ?>" <?= $o['status'] === $st ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$st)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-sm btn-outline-primary" style="padding:.2rem .5rem;" onclick="return confirm('Ubah status pesanan ini?')">Simpan</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center text-muted">Tidak ada pesanan ditemukan.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
