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

// Toggle status atau hapus
if (isset($_GET['action'], $_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($_GET['action'] === 'toggle') {
        $curr = $conn->query("SELECT status_aktif FROM pickup_points WHERE id = $id")->fetch_assoc();
        if ($curr) {
            $new = $curr['status_aktif'] ? 0 : 1;
            $conn->query("UPDATE pickup_points SET status_aktif = $new WHERE id = $id");
            $_SESSION['flash'] = 'Status titik pengambilan berhasil diperbarui.';
            $_SESSION['flash_type'] = 'success';
        }
        header('Location: ' . BASE_URL . '/admin/titik_pengambilan.php');
        exit;
    } elseif ($_GET['action'] === 'hapus') {
        $conn->query("DELETE FROM pickup_points WHERE id = $id");
        $_SESSION['flash'] = 'Titik pengambilan berhasil dihapus.';
        $_SESSION['flash_type'] = 'success';
        header('Location: ' . BASE_URL . '/admin/titik_pengambilan.php');
        exit;
    }
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
        <div class="dashboard-header" style="display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h1>📍 Titik Pengambilan (Pick-up Point)</h1>
                <p class="text-muted">Kelola lokasi titik pengambilan pesanan bagi pembeli.</p>
            </div>
            <a href="<?= BASE_URL ?>/admin/tambah_titik.php" class="btn btn-primary">+ Tambah Titik</a>
        </div>

        <?php if ($pesan): ?><?= alert($pesan, $pesan_tipe) ?><?php endif; ?>

        <div class="table-card">
            <table>
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
                                <a href="?action=toggle&id=<?= $p['id'] ?>" class="btn btn-sm <?= $p['status_aktif'] ? 'btn-warning' : 'btn-success' ?>">
                                    <?= $p['status_aktif'] ? 'Nonaktifkan' : 'Aktifkan' ?>
                                </a>
                                <a href="?action=hapus&id=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus titik pengambilan ini?')">Hapus</a>
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
