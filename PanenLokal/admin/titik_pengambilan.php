<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
if (!defined('BASE_URL')) define('BASE_URL', '/RESPONSI_PEMWEB1/PanenLokal');
if (!defined('SITE_NAME')) define('SITE_NAME', 'PanenLokal');
$page_title = 'Kelola Titik Pengambilan';
cek_role('admin');

$pesan = $_SESSION['flash'] ?? ''; unset($_SESSION['flash']);
$pesan_tipe = $_SESSION['flash_type'] ?? 'success'; unset($_SESSION['flash_type']);

// Toggle status atau hapus (POST + CSRF + prepared statement)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id'])) {
    if (!verify_csrf()) {
        $_SESSION['flash'] = 'Permintaan tidak valid (token keamanan tidak cocok).';
        $_SESSION['flash_type'] = 'danger';
    } else {
        $id = (int)$_POST['id'];
        if ($_POST['action'] === 'toggle' && $id > 0) {
            $stmt_sel = $conn->prepare("SELECT status_aktif FROM pickup_points WHERE id = ?");
            $stmt_sel->bind_param('i', $id);
            $stmt_sel->execute();
            $curr = $stmt_sel->get_result()->fetch_assoc();
            if ($curr) {
                $new = $curr['status_aktif'] ? 0 : 1;
                $stmt_upd = $conn->prepare("UPDATE pickup_points SET status_aktif = ? WHERE id = ?");
                $stmt_upd->bind_param('ii', $new, $id);
                $stmt_upd->execute();
                $_SESSION['flash'] = 'Status titik pengambilan berhasil diperbarui.';
                $_SESSION['flash_type'] = 'success';
            }
        } elseif ($_POST['action'] === 'hapus' && $id > 0) {
            $stmt_del = $conn->prepare("DELETE FROM pickup_points WHERE id = ?");
            $stmt_del->bind_param('i', $id);
            $stmt_del->execute();
            $_SESSION['flash'] = 'Titik pengambilan berhasil dihapus.';
            $_SESSION['flash_type'] = 'success';
        }
    }
    header('Location: ' . BASE_URL . '/admin/titik_pengambilan.php');
    exit;
}

$points = $conn->query("SELECT * FROM pickup_points ORDER BY created_at DESC");

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
            <li><a href="<?= BASE_URL ?>/admin/titik_pengambilan.php" class="active"><span class="menu-icon">📍</span>Titik Pengambilan</a></li>
            <li><a href="<?= BASE_URL ?>/admin/pesanan.php"><span class="menu-icon">📋</span>Semua Pesanan</a></li>
            <li><a href="<?= BASE_URL ?>/admin/laporan.php"><span class="menu-icon">📊</span>Laporan</a></li>
        </ul>
    </aside>
    <main class="dashboard-main">
        <div class="dashboard-header d-flex justify-between align-center flex-wrap gap-2">
            <div>
                <h1>📍 Titik Pengambilan (Pick-up Point)</h1>
                <p class="text-muted">Kelola lokasi titik pengambilan pesanan bagi pembeli.</p>
            </div>
            <a href="<?= BASE_URL ?>/admin/tambah_titik.php" class="btn btn-primary">+ Tambah Titik</a>
        </div>

        <?php if ($pesan): ?><?= alert($pesan, $pesan_tipe) ?><?php endif; ?>

        <div class="table-card">
            <table class="admin-dashboard-table">
                <thead>
                    <tr>
                        <th>Nama Titik</th>
                        <th>Alamat</th>
                        <th>Deskripsi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($points->num_rows > 0): ?>
                    <?php while ($p = $points->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?= sanitize($p['nama_titik']) ?></strong></td>
                        <td><?= sanitize($p['alamat']) ?></td>
                        <td><?= sanitize($p['deskripsi'] ?: '-') ?></td>
                        <td>
                            <span class="badge <?= $p['status_aktif'] ? 'badge-success' : 'badge-danger' ?>">
                                <?= $p['status_aktif'] ? 'Aktif' : 'Nonaktif' ?>
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="<?= BASE_URL ?>/admin/edit_titik.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-info">Edit</a>
                                <form method="POST" style="display:inline;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="toggle">
                                    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                    <button type="submit" class="btn btn-sm <?= $p['status_aktif'] ? 'btn-warning' : 'btn-success' ?>">
                                        <?= $p['status_aktif'] ? 'Nonaktifkan' : 'Aktifkan' ?>
                                    </button>
                                </form>
                                <form method="POST" style="display:inline;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="hapus">
                                    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Hapus titik pengambilan ini?')">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center text-muted">Belum ada data titik pengambilan.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
