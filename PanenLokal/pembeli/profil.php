<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
if (!defined('BASE_URL')) define('BASE_URL', '/RESPONSI_PEMWEB1/PanenLokal');
if (!defined('SITE_NAME')) define('SITE_NAME', 'PanenLokal');
$page_title = 'Profil Akun';
cek_role('pembeli');

$user_id = $_SESSION['user_id'];
$pesan = $pesan_tipe = '';

// Update profil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $pesan = 'Sesi formulir tidak valid. Silakan muat ulang halaman dan coba lagi.';
        $pesan_tipe = 'danger';
    } else {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_info') {
        $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
        $no_hp        = trim($_POST['no_hp'] ?? '');
        $alamat       = trim($_POST['alamat'] ?? '');

        if (!$nama_lengkap) {
            $pesan = 'Nama lengkap tidak boleh kosong.';
            $pesan_tipe = 'danger';
        } else {
            $up = $conn->prepare("UPDATE users SET nama_lengkap = ?, no_hp = ?, alamat = ? WHERE id = ?");
            $up->bind_param("sssi", $nama_lengkap, $no_hp, $alamat, $user_id);
            $up->execute();
            $_SESSION['nama'] = $nama_lengkap;
            $pesan = 'Data profil berhasil diperbarui!';
            $pesan_tipe = 'success';
        }
    } elseif ($action === 'ganti_password') {
        $pw_lama = $_POST['password_lama'] ?? '';
        $pw_baru = $_POST['password_baru'] ?? '';
        $pw_konf = $_POST['konfirmasi_password'] ?? '';

        $u_chk = $conn->prepare("SELECT password_hash FROM users WHERE id = ?");
        $u_chk->bind_param("i", $user_id);
        $u_chk->execute();
        $user_data = $u_chk->get_result()->fetch_assoc();

        if (!password_verify($pw_lama, $user_data['password_hash'])) {
            $pesan = 'Password lama Anda tidak tepat.';
            $pesan_tipe = 'danger';
        } elseif (strlen($pw_baru) < 6) {
            $pesan = 'Password baru minimal 6 karakter.';
            $pesan_tipe = 'danger';
        } elseif ($pw_baru !== $pw_konf) {
            $pesan = 'Konfirmasi password baru tidak cocok.';
            $pesan_tipe = 'danger';
        } else {
            $new_hash = password_hash($pw_baru, PASSWORD_DEFAULT);
            $up_pw = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $up_pw->bind_param("si", $new_hash, $user_id);
            $up_pw->execute();
            $pesan = 'Password berhasil diganti!';
            $pesan_tipe = 'success';
        }
    }
    }
}

// Ambil info terbaru
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$u = $stmt->get_result()->fetch_assoc();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>
<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-title">Menu Pembeli</div>
        <ul class="sidebar-menu">
            <li><a href="<?= BASE_URL ?>/pembeli/index.php"><span class="menu-icon">🏠</span> Dashboard</a></li>
            <li><a href="<?= BASE_URL ?>/pembeli/keranjang.php"><span class="menu-icon">🛒</span> Keranjang</a></li>
            <li><a href="<?= BASE_URL ?>/pembeli/pesanan.php"><span class="menu-icon">📦</span> Pesanan Saya</a></li>
            <li><a href="<?= BASE_URL ?>/pembeli/profil.php" class="active"><span class="menu-icon">👤</span> Profil</a></li>
        </ul>
    </aside>
    <main class="dashboard-main">
        <div class="dashboard-header">
            <h1>👤 Profil Akun Pembeli</h1>
            <p class="text-muted">Kelola data informasi akun dan alamat pengantaran Anda.</p>
        </div>

        <?php if ($pesan): ?><?= alert($pesan, $pesan_tipe) ?><?php endif; ?>

        <div class="grid grid-2">
            <!-- Info Profil -->
            <div class="card card-body">
                <h3>Informasi Pribadi</h3>
                <form method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update_info">
                    <div class="form-group mt-2">
                        <label class="form-label">Username (Tidak dapat diubah)</label>
                        <input type="text" class="form-control" value="<?= sanitize($u['username']) ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nama Lengkap *</label>
                        <input type="text" name="nama_lengkap" class="form-control" required value="<?= sanitize($u['nama_lengkap']) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nomor WhatsApp / HP</label>
                        <input type="text" name="no_hp" class="form-control" value="<?= sanitize($u['no_hp'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Alamat Pengantaran Default</label>
                        <textarea name="alamat" class="form-control" rows="3"><?= sanitize($u['alamat'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan Profil</button>
                </form>
            </div>

            <!-- Ganti Password -->
            <div class="card card-body">
                <h3>Ganti Password</h3>
                <form method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="ganti_password">
                    <div class="form-group mt-2">
                        <label class="form-label">Password Saat Ini *</label>
                        <input type="password" name="password_lama" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password Baru *</label>
                        <input type="password" name="password_baru" class="form-control" required>
                        <div class="form-hint">Minimal 6 karakter</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Konfirmasi Password Baru *</label>
                        <input type="password" name="konfirmasi_password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-warning">Perbarui Password</button>
                </form>
            </div>
        </div>
    </main>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
