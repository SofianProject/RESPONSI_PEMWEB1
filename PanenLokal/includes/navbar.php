<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!function_exists('is_logged_in')) require_once __DIR__ . '/auth.php';
if (!function_exists('sanitize'))      require_once __DIR__ . '/functions.php';
$_user = get_user();
$_role = $_user['role'] ?? '';
$_curr_path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
$_curr_script = basename($_curr_path);
$_in_subdir = strpos($_curr_path, '/admin/') !== false
    || strpos($_curr_path, '/petani/') !== false
    || strpos($_curr_path, '/pembeli/') !== false;
?>
<nav class="navbar">
    <div class="navbar-container">
        <a class="navbar-brand" href="<?= BASE_URL ?>/index.php">
            <span class="brand-mark" aria-hidden="true">🌿</span>
            <span>PanenLokal</span>
        </a>
        <button type="button" class="navbar-toggler" onclick="toggleNav()" aria-label="Toggle menu">☰</button>
        <ul class="navbar-menu" id="navbarMenu">
            <li><a href="<?= BASE_URL ?>/index.php" class="nav-link<?= (!$_in_subdir && ($_curr_script === 'index.php' || $_curr_script === '')) ? ' active' : '' ?>">Beranda</a></li>
            <li><a href="<?= BASE_URL ?>/katalog.php" class="nav-link<?= (!$_in_subdir && $_curr_script === 'katalog.php') ? ' active' : '' ?>">Katalog</a></li>
            <li><a href="<?= BASE_URL ?>/food_rescue.php" class="nav-link<?= (!$_in_subdir && $_curr_script === 'food_rescue.php') ? ' active' : '' ?>">🔥 Food Rescue</a></li>
            <li><a href="<?= BASE_URL ?>/tentang.php" class="nav-link<?= (!$_in_subdir && $_curr_script === 'tentang.php') ? ' active' : '' ?>">Tentang</a></li>
            <?php if (is_logged_in()): ?>
                <li class="nav-divider" aria-hidden="true"></li>
                <?php if ($_role === 'admin'): ?>
                    <li><a href="<?= BASE_URL ?>/admin/index.php" class="nav-link nav-dash<?= strpos($_curr_path, '/admin/') !== false ? ' active' : '' ?>">⚙️ Admin</a></li>
                <?php elseif ($_role === 'petani'): ?>
                    <li><a href="<?= BASE_URL ?>/petani/index.php" class="nav-link nav-dash<?= strpos($_curr_path, '/petani/') !== false ? ' active' : '' ?>">🌾 Dashboard</a></li>
                <?php else: ?>
                    <li><a href="<?= BASE_URL ?>/pembeli/keranjang.php" class="nav-link nav-dash<?= (strpos($_curr_path, '/pembeli/') !== false && $_curr_script === 'keranjang.php') ? ' active' : '' ?>">🛒 Keranjang</a></li>
                    <li><a href="<?= BASE_URL ?>/pembeli/index.php" class="nav-link nav-dash<?= (strpos($_curr_path, '/pembeli/') !== false && $_curr_script !== 'keranjang.php') ? ' active' : '' ?>">👤 Dashboard</a></li>
                <?php endif; ?>
                <li class="nav-cta-item"><a href="<?= BASE_URL ?>/logout.php" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin ingin logout?')">Logout</a></li>
            <?php else: ?>
                <li class="nav-divider" aria-hidden="true"></li>
                <li class="nav-cta-item"><a href="<?= BASE_URL ?>/login.php" class="btn btn-sm btn-outline-primary">Login</a></li>
                <li class="nav-cta-item"><a href="<?= BASE_URL ?>/register.php" class="btn btn-sm btn-nav-register">Daftar</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
<div class="content-wrapper">
