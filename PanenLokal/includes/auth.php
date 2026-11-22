<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function get_user() {
    if (!is_logged_in()) return null;
    return [
        'id'   => $_SESSION['user_id'],
        'nama' => $_SESSION['nama'] ?? '',
        'role' => $_SESSION['role'] ?? '',
    ];
}

function cek_login() {
    if (!is_logged_in()) {
        header('Location: ' . BASE_URL . '/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

function cek_role($role_diizinkan) {
    cek_login();
    $user = get_user();
    if (is_array($role_diizinkan)) {
        if (!in_array($user['role'], $role_diizinkan)) {
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }
    } else {
        if ($user['role'] !== $role_diizinkan) {
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }
    }
}

// Redirect sesuai role setelah login
function redirect_by_role($role) {
    switch ($role) {
        case 'admin':
            header('Location: ' . BASE_URL . '/admin/index.php');
            break;
        case 'petani':
            header('Location: ' . BASE_URL . '/petani/index.php');
            break;
        default:
            header('Location: ' . BASE_URL . '/pembeli/index.php');
    }
    exit;
}
?>
