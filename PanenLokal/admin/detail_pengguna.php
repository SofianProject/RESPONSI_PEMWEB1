<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
if (!defined('BASE_URL')) define('BASE_URL', '/RESPONSI_PEMWEB1/PanenLokal');
if (!defined('SITE_NAME')) define('SITE_NAME', 'PanenLokal');
$page_title = 'Detail Pengguna';
cek_role('admin');

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . BASE_URL . '/admin/pengguna.php');
    exit;
}

$stmt = $conn->prepare("
    SELECT u.*, fp.nama_usaha, fp.deskripsi AS deskripsi_usaha, fp.status_verifikasi
    FROM users u
    LEFT JOIN farmer_profiles fp ON fp.user_id = u.id
    WHERE u.id = ?
");
$stmt->bind_param('i', $id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();

if (!$user_data) {
    header('Location: ' . BASE_URL . '/admin/pengguna.php');
    exit;
}

// Verifikasi petani jika form dikirim
$pesan = $pesan_tipe = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verifikasi_petani'])) {
    $status_ver = $_POST['verifikasi_petani'];
    if (in_array($status_ver, ['terverifikasi', 'ditolak', 'menunggu'])) {
        $up = $conn->prepare("UPDATE farmer_profiles SET status_verifikasi = ? WHERE user_id = ?");
        $up->bind_param('si', $status_ver, $id);
        $up->execute();
        $user_data['status_verifikasi'] = $status_ver;
        $pesan = 'Status verifikasi petani berhasil diubah.';
        $pesan_tipe = 'success';
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
            <li><a href="<?= BASE_URL ?>/admin/pengguna.php" class="active"><span class="menu-icon">👥</span>Pengguna</a></li>
            <li><a href="<?= BASE_URL ?>/admin/verifikasi_pembayaran.php"><span class="menu-icon">💳</span>Verifikasi Pembayaran</a></li>
            <li><a href="<?= BASE_URL ?>/admin/titik_pengambilan.php"><span class="menu-icon">📍</span>Titik Pengambilan</a></li>
            <li><a href="<?= BASE_URL ?>/admin/pesanan.php"><span class="menu-icon">📋</span>Semua Pesanan</a></li>
            <li><a href="<?= BASE_URL ?>/admin/laporan.php"><span class="menu-icon">📊</span>Laporan</a></li>
        </ul>
    </aside>
    <main class="dashboard-main">
        <div class="dashboard-header">
            <a href="<?= BASE_URL ?>/admin/pengguna.php" class="btn btn-secondary btn-sm mb-2">← Kembali ke Daftar</a>
            <h1>Detail Pengguna: <?= sanitize($user_data['nama_lengkap']) ?></h1>
        </div>

        <?php if ($pesan): ?><?= alert($pesan, $pesan_tipe) ?><?php endif; ?>

        <div class="grid grid-2">
            <div class="card card-body">
                <h3>Informasi Akun</h3>
                <table style="font-size:.9rem;" class="mt-2">
                    <tr><td class="text-muted" style="width:130px; padding:.3rem 0;">Nama Lengkap</td><td><strong><?= sanitize($user_data['nama_lengkap']) ?></strong></td></tr>
                    <tr><td class="text-muted">Username</td><td><?= sanitize($user_data['username']) ?></td></tr>
                    <tr><td class="text-muted">Role</td><td><span class="badge <?= $user_data['role']==='petani'?'badge-primary':'badge-info' ?>"><?= ucfirst($user_data['role']) ?></span></td></tr>
                    <tr><td class="text-muted">Nomor HP</td><td><?= sanitize($user_data['no_hp'] ?: '-') ?></td></tr>
                    <tr><td class="text-muted">Alamat</td><td><?= sanitize($user_data['alamat'] ?: '-') ?></td></tr>
                    <tr><td class="text-muted">Status Akun</td><td><span class="badge <?= $user_data['status_akun']==='aktif'?'badge-success':'badge-danger' ?>"><?= ucfirst($user_data['status_akun']) ?></span></td></tr>
                    <tr><td class="text-muted">Terdaftar Pada</td><td><?= format_tanggal($user_data['created_at'], 'd M Y H:i') ?></td></tr>
                </table>
            </div>

            <?php if ($user_data['role'] === 'petani'): ?>
            <div class="card card-body">
                <h3>Profil Usaha Tani</h3>
                <table style="font-size:.9rem;" class="mt-2">
                    <tr><td class="text-muted" style="width:130px; padding:.3rem 0;">Nama Usaha</td><td><strong><?= sanitize($user_data['nama_usaha'] ?: '-') ?></strong></td></tr>
                    <tr><td class="text-muted">Deskripsi</td><td><?= nl2br(sanitize($user_data['deskripsi_usaha'] ?: '-')) ?></td></tr>
                    <tr><td class="text-muted">Status Verifikasi</td><td>
                        <span class="badge <?= $user_data['status_verifikasi']==='terverifikasi'?'badge-success':($user_data['status_verifikasi']==='ditolak'?'badge-danger':'badge-warning') ?>">
                            <?= ucfirst($user_data['status_verifikasi'] ?? 'menunggu') ?>
                        </span>
                    </td></tr>
                </table>

                <form method="POST" class="mt-3">
                    <label class="form-label">Tindakan Verifikasi Petani:</label>
                    <div class="d-flex gap-1">
                        <button type="submit" name="verifikasi_petani" value="terverifikasi" class="btn btn-sm btn-success">Verifikasi</button>
                        <button type="submit" name="verifikasi_petani" value="ditolak" class="btn btn-sm btn-danger">Tolak</button>
                        <button type="submit" name="verifikasi_petani" value="menunggu" class="btn btn-sm btn-secondary">Pending</button>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </main>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
