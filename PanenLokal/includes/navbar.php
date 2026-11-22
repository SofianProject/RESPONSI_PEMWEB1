<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!function_exists('is_logged_in')) require_once __DIR__ . '/auth.php';
if (!function_exists('sanitize'))      require_once __DIR__ . '/functions.php';
$_user = get_user();
$_role = $_user['role'] ?? '';
?>
<nav class="navbar">
    <div class="navbar-container">
        <a class="navbar-brand" href="<?= BASE_URL ?>/index.php">
            <span class="brand-icon">🌿</span> PanenLokal
        </a>
        <button class="navbar-toggler" onclick="toggleNav()" aria-label="Toggle menu">☰</button>
        <ul class="navbar-menu" id="navbarMenu">
            <li><a href="<?= BASE_URL ?>/index.php" class="nav-link">Beranda</a></li>
            <li><a href="<?= BASE_URL ?>/katalog.php" class="nav-link">Katalog</a></li>
            <li><a href="<?= BASE_URL ?>/food_rescue.php" class="nav-link">🔥 Food Rescue</a></li>
            <li><a href="<?= BASE_URL ?>/tentang.php" class="nav-link">Tentang</a></li>
            <?php if (is_logged_in()): ?>
                <li class="nav-divider"></li>
                <?php if ($_role === 'admin'): ?>
                    <li><a href="<?= BASE_URL ?>/admin/index.php" class="nav-link nav-dash">⚙️ Admin</a></li>
                <?php elseif ($_role === 'petani'): ?>
                    <li><a href="<?= BASE_URL ?>/petani/index.php" class="nav-link nav-dash">🌾 Dashboard</a></li>
                <?php else: ?>
                    <li><a href="<?= BASE_URL ?>/pembeli/keranjang.php" class="nav-link nav-dash">🛒 Keranjang</a></li>
                    <li><a href="<?= BASE_URL ?>/pembeli/index.php" class="nav-link nav-dash">👤 Dashboard</a></li>
                <?php endif; ?>
                <li><a href="<?= BASE_URL ?>/logout.php" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin ingin logout?')">Logout</a></li>
            <?php else: ?>
                <li><a href="<?= BASE_URL ?>/login.php" class="btn btn-sm btn-outline-primary">Login</a></li>
                <li><a href="<?= BASE_URL ?>/register.php" class="btn btn-sm btn-primary">Daftar</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
<div class="content-wrapper">
