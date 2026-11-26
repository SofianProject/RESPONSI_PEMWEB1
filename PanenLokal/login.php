<?php
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
if (!defined('BASE_URL')) define('BASE_URL', '/RESPONSI_PEMWEB1/PanenLokal');
if (!defined('SITE_NAME')) define('SITE_NAME', 'PanenLokal');
$page_title = 'Login';

// Jika sudah login, redirect
if (is_logged_in()) { redirect_by_role($_SESSION['role']); }

$redirect = sanitize_redirect_url($_GET['redirect'] ?? $_POST['redirect'] ?? '');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Sesi formulir tidak valid. Silakan muat ulang halaman dan coba lagi.';
    } else {
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
                if ($redirect !== '') {
                    header('Location: ' . $redirect);
                    exit;
                }
                redirect_by_role($user['role']);
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<section class="section auth-section">
    <div class="container">
        <div class="form-card auth-card">
            <h2>🌿 Masuk ke PanenLokal</h2>
            <p class="auth-subtitle">Kelola pre-order hasil tani segar Anda</p>
            <?php if ($error): ?><?= alert($error, 'danger') ?><?php endif; ?>
            <form id="login-form" method="POST">
                <?= csrf_field() ?>
                <?php if ($redirect !== ''): ?>
                    <input type="hidden" name="redirect" value="<?= sanitize($redirect) ?>">
                <?php endif; ?>
                <div class="form-group">
                    <label class="form-label" for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" value="<?= sanitize($_POST['username'] ?? '') ?>" placeholder="Masukkan username" autocomplete="username">
                </div>
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="password-input-wrap">
                        <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password" autocomplete="current-password">
                        <button type="button" class="password-toggle-btn" id="toggle-password" aria-label="Tampilkan password" aria-pressed="false">
                            <svg class="eye-icon eye-open" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg class="eye-icon eye-closed" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/></svg>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg">Login</button>
            </form>
            <hr class="divider">
            <p class="text-center text-muted" style="font-size:.9rem;">Belum punya akun? <a href="<?= BASE_URL ?>/register.php">Daftar di sini</a></p>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
