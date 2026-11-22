<?php
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
if (!defined('BASE_URL')) define('BASE_URL', '/RESPONSI_PEMWEB1/PanenLokal');
if (!defined('SITE_NAME')) define('SITE_NAME', 'PanenLokal');
$page_title = 'Daftar Akun';

if (is_logged_in()) { redirect_by_role($_SESSION['role']); }

$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap  = trim($_POST['nama_lengkap'] ?? '');
    $username      = trim($_POST['username'] ?? '');
    $password      = $_POST['password'] ?? '';
    $confirm_pw    = $_POST['confirm_password'] ?? '';
    $role          = $_POST['role'] ?? 'pembeli';
    $no_hp         = trim($_POST['no_hp'] ?? '');
    $alamat        = trim($_POST['alamat'] ?? '');
    $nama_usaha    = trim($_POST['nama_usaha'] ?? '');
    $deskripsi_u   = trim($_POST['deskripsi_usaha'] ?? '');

    // Validasi
    if (!$nama_lengkap || !$username || !$password || !$confirm_pw) {
        $error = 'Semua field wajib tidak boleh kosong.';
    } elseif ($password !== $confirm_pw) {
        $error = 'Konfirmasi password tidak cocok.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif (!in_array($role, ['pembeli','petani'])) {
        $error = 'Role tidak valid.';
    } elseif ($role === 'petani' && $nama_usaha === '') {
        $error = 'Nama usaha tidak boleh kosong untuk akun petani.';
    } else {
        // Cek username unik
        $chk = $conn->prepare("SELECT id FROM users WHERE username=?");
        $chk->bind_param('s', $username);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $error = 'Username sudah digunakan.';
        } else {
            $pw_hash = password_hash($password, PASSWORD_DEFAULT);
            $conn->begin_transaction();
            try {
                $ins = $conn->prepare("INSERT INTO users (nama_lengkap,username,password_hash,role,no_hp,alamat) VALUES (?,?,?,?,?,?)");
                $ins->bind_param('ssssss', $nama_lengkap, $username, $pw_hash, $role, $no_hp, $alamat);
                $ins->execute();
                $user_id = $conn->insert_id;

                if ($role === 'petani') {
                    $fp = $conn->prepare("INSERT INTO farmer_profiles (user_id,nama_usaha,deskripsi) VALUES (?,?,?)");
                    $fp->bind_param('iss', $user_id, $nama_usaha, $deskripsi_u);
                    $fp->execute();
                } elseif ($role === 'pembeli') {
                    // Buat keranjang sekaligus
                    $cart = $conn->prepare("INSERT INTO carts (buyer_id) VALUES (?)");
                    $cart->bind_param('i', $user_id);
                    $cart->execute();
                }

                $conn->commit();
                $success = 'Akun berhasil dibuat! Silakan login.';
            } catch (Exception $e) {
                $conn->rollback();
                $error = 'Terjadi kesalahan: ' . $e->getMessage();
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<section class="section">
    <div class="container">
        <div class="form-card" style="max-width:560px;">
            <h2>🌿 Daftar Akun</h2>
            <?php if ($error):   ?><?= alert($error, 'danger')  ?><?php endif; ?>
            <?php if ($success): ?><?= alert($success, 'success') ?><div class="text-center mt-2"><a href="<?= BASE_URL ?>/login.php" class="btn btn-primary">Login Sekarang</a></div><?php endif; ?>
            <?php if (!$success): ?>
            <form id="register-form" method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="nama_lengkap">Nama Lengkap *</label>
                        <input type="text" id="nama_lengkap" name="nama_lengkap" class="form-control" value="<?= sanitize($_POST['nama_lengkap'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="username">Username *</label>
                        <input type="text" id="username" name="username" class="form-control" value="<?= sanitize($_POST['username'] ?? '') ?>" required autocomplete="username">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="role">Daftar sebagai *</label>
                    <select id="role" name="role" class="form-control" onchange="togglePetaniFields()">
                        <option value="pembeli" <?= (($_POST['role'] ?? '')!='petani')?'selected':'' ?>>🛒 Pembeli</option>
                        <option value="petani"  <?= (($_POST['role'] ?? '')==='petani')?'selected':'' ?>>🌾 Petani/Produsen</option>
                    </select>
                </div>

                <div id="petani-fields" style="display:none;">
                    <div class="form-group">
                        <label class="form-label" for="nama_usaha">Nama Usaha / Kelompok Tani *</label>
                        <input type="text" id="nama_usaha" name="nama_usaha" class="form-control" value="<?= sanitize($_POST['nama_usaha'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="deskripsi_usaha">Deskripsi Usaha</label>
                        <textarea id="deskripsi_usaha" name="deskripsi_usaha" class="form-control" rows="3"><?= sanitize($_POST['deskripsi_usaha'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="password">Password *</label>
                        <input type="password" id="password" name="password" class="form-control" required autocomplete="new-password">
                        <div class="form-hint">Min. 6 karakter</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="confirm_password">Konfirmasi Password *</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required autocomplete="new-password">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="no_hp">No. HP</label>
                        <input type="text" id="no_hp" name="no_hp" class="form-control" value="<?= sanitize($_POST['no_hp'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="alamat">Alamat</label>
                        <input type="text" id="alamat" name="alamat" class="form-control" value="<?= sanitize($_POST['alamat'] ?? '') ?>">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg">Daftar Sekarang</button>
            </form>
            <hr class="divider">
            <p class="text-center text-muted" style="font-size:.9rem;">Sudah punya akun? <a href="<?= BASE_URL ?>/login.php">Login di sini</a></p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
