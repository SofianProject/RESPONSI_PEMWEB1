<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
if (!defined('BASE_URL')) define('BASE_URL', '/RESPONSI_PEMWEB1/PanenLokal');
if (!defined('SITE_NAME')) define('SITE_NAME', 'PanenLokal');
$page_title = 'Edit Titik Pengambilan';
cek_role('admin');

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . BASE_URL . '/admin/titik_pengambilan.php');
    exit;
}

$stmt = $conn->prepare("SELECT * FROM pickup_points WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$titik = $stmt->get_result()->fetch_assoc();
if (!$titik) {
    header('Location: ' . BASE_URL . '/admin/titik_pengambilan.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Sesi formulir tidak valid. Silakan muat ulang halaman dan coba lagi.';
    } else {
        $nama_titik = trim($_POST['nama_titik'] ?? '');
        $alamat     = trim($_POST['alamat'] ?? '');
        $deskripsi  = trim($_POST['deskripsi'] ?? '');
        $status     = isset($_POST['status_aktif']) ? 1 : 0;

        if (!$nama_titik || !$alamat) {
            $error = 'Nama titik dan alamat wajib diisi.';
        } else {
            $upd = $conn->prepare("UPDATE pickup_points SET nama_titik = ?, alamat = ?, deskripsi = ?, status_aktif = ? WHERE id = ?");
            $upd->bind_param('sssii', $nama_titik, $alamat, $deskripsi, $status, $id);
            if ($upd->execute()) {
                $_SESSION['flash'] = 'Titik pengambilan berhasil diperbarui!';
                $_SESSION['flash_type'] = 'success';
                header('Location: ' . BASE_URL . '/admin/titik_pengambilan.php');
                exit;
            } else {
                error_log('Edit titik error: ' . $conn->error);
                $error = 'Gagal memperbarui data titik pengambilan. Silakan coba lagi.';
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
            <li><a href="<?= BASE_URL ?>/admin/verifikasi_pembayaran.php"><span class="menu-icon">💳</span>Verifikasi Pembayaran</a></li>
            <li><a href="<?= BASE_URL ?>/admin/titik_pengambilan.php" class="active"><span class="menu-icon">📍</span>Titik Pengambilan</a></li>
            <li><a href="<?= BASE_URL ?>/admin/pesanan.php"><span class="menu-icon">📋</span>Semua Pesanan</a></li>
            <li><a href="<?= BASE_URL ?>/admin/laporan.php"><span class="menu-icon">📊</span>Laporan</a></li>
        </ul>
    </aside>
    <main class="dashboard-main">
        <div class="dashboard-header">
            <a href="<?= BASE_URL ?>/admin/titik_pengambilan.php" class="btn btn-secondary btn-sm mb-2">← Kembali</a>
            <h1>Edit Titik Pengambilan</h1>
        </div>

        <?php if ($error): ?><?= alert($error, 'danger') ?><?php endif; ?>

        <div class="card card-body" style="max-width: 600px;">
            <form method="POST">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label class="form-label">Nama Titik *</label>
                    <input type="text" name="nama_titik" class="form-control" required value="<?= sanitize($titik['nama_titik']) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Alamat Lengkap *</label>
                    <textarea name="alamat" class="form-control" rows="3" required><?= sanitize($titik['alamat']) ?></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Deskripsi / Petunjuk Pengambilan</label>
                    <textarea name="deskripsi" class="form-control" rows="2"><?= sanitize($titik['deskripsi'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label style="display:flex; align-items:center; gap:.5rem; cursor:pointer;">
                        <input type="checkbox" name="status_aktif" value="1" <?= $titik['status_aktif'] ? 'checked' : '' ?>>
                        <span>Status Aktif</span>
                    </label>
                </div>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </form>
        </div>
    </main>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
