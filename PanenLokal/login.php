<?php
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
if (!defined('BASE_URL')) define('BASE_URL', '/RESPONSI_PEMWEB1/PanenLokal');
if (!defined('SITE_NAME')) define('SITE_NAME', 'PanenLokal');
$page_title = 'Login';

// Jika sudah login, redirect
if (is_logged_in()) { redirect_by_role($_SESSION['role']); }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username dan password tidak boleh kosong.';
    } else {
        $stmt = $conn->prepare("SELECT id, nama_lengkap, password_hash, role, status_akun FROM users WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if (!$user) {
            $error = 'Username tidak ditemukan.';
        } elseif ($user['status_akun'] !== 'aktif') {
            $error = 'Akun Anda nonaktif. Hubungi admin.';
        } elseif (!password_verify($password, $user['password_hash'])) {
            $error = 'Password salah.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama']    = $user['nama_lengkap'];
            $_SESSION['role']    = $user['role'];
            redirect_by_role($user['role']);
        }
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<section class="section">
    <div class="container">
        <div class="form-card">
            <h2>🌿 Masuk ke PanenLokal</h2>
            <?php if ($error): ?><?= alert($error, 'danger') ?><?php endif; ?>
            <form id="login-form" method="POST">
                <div class="form-group">
                    <label class="form-label" for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" value="<?= sanitize($_POST['username'] ?? '') ?>" placeholder="Masukkan username" autocomplete="username">
                </div>
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password" autocomplete="current-password">
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg">Login</button>
            </form>
            <hr class="divider">
            <p class="text-center text-muted" style="font-size:.9rem;">Belum punya akun? <a href="<?= BASE_URL ?>/register.php">Daftar di sini</a></p>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
