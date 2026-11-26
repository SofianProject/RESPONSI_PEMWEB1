<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
if (!defined('BASE_URL')) define('BASE_URL', '/RESPONSI_PEMWEB1/PanenLokal');
if (!defined('SITE_NAME')) define('SITE_NAME', 'PanenLokal');
$page_title = 'Detail Pembayaran';
cek_role('admin');

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: ' . BASE_URL . '/admin/verifikasi_pembayaran.php'); exit; }

$stmt = $conn->prepare("
    SELECT p.*, o.kode_pesanan, o.total_amount, o.status AS order_status, o.catatan,
           u.nama_lengkap, u.username, u.no_hp, u.alamat
    FROM payments p
    JOIN orders o ON o.id = p.order_id
    JOIN users u ON u.id = o.buyer_id
    WHERE p.id = ?
");
$stmt->bind_param('i', $id);
$stmt->execute();
$pay = $stmt->get_result()->fetch_assoc();
if (!$pay) { header('Location: ' . BASE_URL . '/admin/verifikasi_pembayaran.php'); exit; }

// Ambil order items
$items_q = $conn->prepare("SELECT oi.*, hb.id AS batch_id FROM order_items oi JOIN harvest_batches hb ON hb.id=oi.batch_id WHERE oi.order_id=(SELECT order_id FROM payments WHERE id=?)");
$items_q->bind_param('i', $id);
$items_q->execute();
$items = $items_q->get_result();

// Proses verifikasi
$pesan = $pesan_tipe = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $pesan = 'Permintaan tidak valid (token keamanan tidak cocok).';
        $pesan_tipe = 'danger';
    } else {
        $action        = $_POST['action'] ?? '';
        $catatan_admin = trim($_POST['catatan_admin'] ?? '');
        $admin_id      = $_SESSION['user_id'];
        $order_id_pay  = $pay['order_id'];

        if ($action === 'terima') {
            $upd_pay = $conn->prepare("UPDATE payments SET status_pembayaran='terverifikasi', verified_by=?, verified_at=NOW(), catatan_admin=? WHERE id=?");
            $upd_pay->bind_param('isi', $admin_id, $catatan_admin, $id);
            $upd_pay->execute();
            $upd_ord = $conn->prepare("UPDATE orders SET status='dibayar' WHERE id=?");
            $upd_ord->bind_param('i', $order_id_pay);
            $upd_ord->execute();
            $pesan = 'Pembayaran berhasil diverifikasi. Pesanan diubah ke status Dibayar.';
            $pesan_tipe = 'success';
            // Refresh data
            $stmt->execute();
            $pay = $stmt->get_result()->fetch_assoc();
        } elseif ($action === 'tolak') {
            if ($catatan_admin === '') { $pesan = 'Alasan penolakan harus diisi.'; $pesan_tipe = 'danger'; }
            else {
                $upd_pay = $conn->prepare("UPDATE payments SET status_pembayaran='ditolak', verified_by=?, verified_at=NOW(), catatan_admin=? WHERE id=?");
                $upd_pay->bind_param('isi', $admin_id, $catatan_admin, $id);
                $upd_pay->execute();
                $upd_ord = $conn->prepare("UPDATE orders SET status='menunggu_pembayaran' WHERE id=?");
                $upd_ord->bind_param('i', $order_id_pay);
                $upd_ord->execute();
                $pesan = 'Pembayaran ditolak. Pembeli dapat mengunggah ulang bukti.';
                $pesan_tipe = 'warning';
                $stmt->execute();
                $pay = $stmt->get_result()->fetch_assoc();
            }
        }
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
            <li><a href="<?= BASE_URL ?>/admin/verifikasi_pembayaran.php" class="active"><span class="menu-icon">💳</span>Verifikasi Pembayaran</a></li>
            <li><a href="<?= BASE_URL ?>/admin/titik_pengambilan.php"><span class="menu-icon">📍</span>Titik Pengambilan</a></li>
            <li><a href="<?= BASE_URL ?>/admin/pesanan.php"><span class="menu-icon">📋</span>Semua Pesanan</a></li>
            <li><a href="<?= BASE_URL ?>/admin/laporan.php"><span class="menu-icon">📊</span>Laporan</a></li>
        </ul>
    </aside>
    <main class="dashboard-main">
        <div class="dashboard-header">
            <a href="<?= BASE_URL ?>/admin/verifikasi_pembayaran.php" class="btn btn-secondary btn-sm mb-2">← Kembali</a>
            <h1>Detail Pembayaran — <?= sanitize($pay['kode_pesanan']) ?></h1>
        </div>
        <?php if ($pesan): ?><?= alert($pesan, $pesan_tipe) ?><?php endif; ?>

        <div class="grid grid-2">
            <div class="card card-body">
                <h4>Info Pembeli</h4>
                <table style="font-size:.9rem;">
                    <tr><td class="text-muted" style="padding:.3rem .5rem .3rem 0;width:140px;">Nama</td><td><?= sanitize($pay['nama_lengkap']) ?></td></tr>
                    <tr><td class="text-muted">Username</td><td><?= sanitize($pay['username']) ?></td></tr>
                    <tr><td class="text-muted">No. HP</td><td><?= sanitize($pay['no_hp'] ?: '-') ?></td></tr>
                    <tr><td class="text-muted">Alamat</td><td><?= sanitize($pay['alamat'] ?: '-') ?></td></tr>
                </table>
            </div>
            <div class="card card-body">
                <h4>Info Pembayaran</h4>
                <table style="font-size:.9rem;">
                    <tr><td class="text-muted" style="padding:.3rem .5rem .3rem 0;width:140px;">Nominal</td><td class="text-bold"><?= format_rupiah($pay['nominal']) ?></td></tr>
                    <tr><td class="text-muted">Total Pesanan</td><td><?= format_rupiah($pay['total_amount']) ?></td></tr>
                    <tr><td class="text-muted">Status Bayar</td><td><span class="badge <?= $pay['status_pembayaran']==='menunggu'?'badge-warning':($pay['status_pembayaran']==='terverifikasi'?'badge-success':'badge-danger') ?>"><?= ucfirst($pay['status_pembayaran']) ?></span></td></tr>
                    <tr><td class="text-muted">Tanggal</td><td><?= format_tanggal($pay['created_at'], 'd M Y H:i') ?></td></tr>
                    <?php if ($pay['catatan_admin']): ?><tr><td class="text-muted">Catatan</td><td><?= sanitize($pay['catatan_admin']) ?></td></tr><?php endif; ?>
                </table>
            </div>
        </div>

        <!-- Bukti pembayaran -->
        <div class="card card-body mt-3">
            <h4>Bukti Pembayaran</h4>
            <?php if ($pay['bukti_pembayaran']): ?>
                <?php $ext = strtolower(pathinfo($pay['bukti_pembayaran'], PATHINFO_EXTENSION)); ?>
                <?php if (in_array($ext, ['jpg','jpeg','png','webp'])): ?>
                    <img src="<?= BASE_URL ?>/uploads/payments/<?= sanitize($pay['bukti_pembayaran']) ?>" alt="Bukti" style="max-width:500px;border-radius:var(--radius);border:1px solid var(--gray-200);">
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/uploads/payments/<?= sanitize($pay['bukti_pembayaran']) ?>" target="_blank" class="btn btn-info">📄 Lihat File PDF</a>
                <?php endif; ?>
            <?php else: ?>
                <p class="text-muted">Belum ada bukti pembayaran.</p>
            <?php endif; ?>
        </div>

        <!-- Item pesanan -->
        <div class="table-card mt-3">
            <table>
                <thead><tr><th>Produk</th><th>Qty</th><th>Satuan</th><th>Harga/Satuan</th><th>Subtotal</th><th>Rescue</th></tr></thead>
                <tbody>
                <?php $items_q->execute(); $items = $items_q->get_result(); while ($it = $items->fetch_assoc()): ?>
                    <tr>
                        <td><?= sanitize($it['nama_produk_snapshot']) ?></td>
                        <td><?= (float)$it['quantity'] ?></td>
                        <td><?= sanitize($it['satuan_snapshot']) ?></td>
                        <td><?= format_rupiah($it['harga_satuan']) ?></td>
                        <td><?= format_rupiah($it['quantity'] * $it['harga_satuan']) ?></td>
                        <td><?= $it['is_rescue'] ? '<span class="badge badge-rescue">Ya</span>' : '-' ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Form verifikasi -->
        <?php if ($pay['status_pembayaran'] === 'menunggu'): ?>
        <div class="card card-body mt-3">
            <h4>Tindakan Verifikasi</h4>
            <form method="POST">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label class="form-label">Catatan Admin (wajib jika menolak)</label>
                    <textarea name="catatan_admin" class="form-control" rows="3" placeholder="Alasan penolakan atau catatan..."><?= sanitize($pay['catatan_admin'] ?? '') ?></textarea>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" name="action" value="terima" class="btn btn-success" onclick="return confirm('Setujui pembayaran ini?')">✅ Terima Pembayaran</button>
                    <button type="submit" name="action" value="tolak" class="btn btn-danger" onclick="return confirm('Tolak pembayaran ini?')">❌ Tolak Pembayaran</button>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </main>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
