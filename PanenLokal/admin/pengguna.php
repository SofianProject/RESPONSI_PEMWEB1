<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
if (!defined('BASE_URL')) define('BASE_URL', '/RESPONSI_PEMWEB1/PanenLokal');
if (!defined('SITE_NAME')) define('SITE_NAME', 'PanenLokal');
$page_title = 'Kelola Pengguna';
cek_role('admin');

$pesan = $_SESSION['flash'] ?? ''; unset($_SESSION['flash']);
$pesan_tipe = $_SESSION['flash_type'] ?? 'success'; unset($_SESSION['flash_type']);

// Toggle status akun (POST + CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle_status') {
    if (!verify_csrf()) {
        $_SESSION['flash'] = 'Permintaan tidak valid (token keamanan tidak cocok).';
        $_SESSION['flash_type'] = 'danger';
    } else {
        $uid = (int)($_POST['id'] ?? 0);
        if ($uid > 0) {
            $cur = $conn->prepare("SELECT status_akun, role FROM users WHERE id=?");
            $cur->bind_param('i', $uid);
            $cur->execute();
            $row = $cur->get_result()->fetch_assoc();
            if ($row && $row['role'] !== 'admin') {
                $new_status = $row['status_akun'] === 'aktif' ? 'nonaktif' : 'aktif';
                $upd = $conn->prepare("UPDATE users SET status_akun=? WHERE id=?");
                $upd->bind_param('si', $new_status, $uid);
                $upd->execute();
                $_SESSION['flash'] = 'Status akun berhasil diubah.';
                $_SESSION['flash_type'] = 'success';
            }
        }
    }
    header('Location: ' . BASE_URL . '/admin/pengguna.php');
    exit;
}

$search = trim($_GET['search'] ?? '');
$role_filter = $_GET['role'] ?? '';

$where = "role != 'admin'";
$params = []; $types = '';
if ($search) {
    $where .= " AND (nama_lengkap LIKE ? OR username LIKE ?)";
    $like = '%' . $search . '%';
    $params[] = &$like; $params[] = &$like; $types .= 'ss';
}
if ($role_filter && in_array($role_filter, ['pembeli','petani'])) {
    $where .= " AND role=?";
    $params[] = &$role_filter; $types .= 's';
}

$sql = "SELECT id,nama_lengkap,username,role,no_hp,status_akun,created_at FROM users WHERE $where ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$users = $stmt->get_result();

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
            <h1>👥 Kelola Pengguna</h1>
        </div>
        <?php if ($pesan): ?><?= alert($pesan, $pesan_tipe) ?><?php endif; ?>

        <form method="GET" class="d-flex gap-2 flex-wrap mb-3">
            <input type="text" name="search" class="form-control" placeholder="Cari nama / username..." value="<?= sanitize($search) ?>" style="max-width:250px;">
            <select name="role" class="form-control" style="max-width:160px;">
                <option value="">Semua Role</option>
                <option value="pembeli" <?= $role_filter==='pembeli'?'selected':'' ?>>Pembeli</option>
                <option value="petani"  <?= $role_filter==='petani'?'selected':'' ?>>Petani</option>
            </select>
            <button type="submit" class="btn btn-primary">Cari</button>
            <a href="<?= BASE_URL ?>/admin/pengguna.php" class="btn btn-secondary">Reset</a>
        </form>

        <div class="table-card">
            <table class="admin-dashboard-table">
                <thead><tr><th>Nama</th><th>Username</th><th>Role</th><th>No. HP</th><th>Status</th><th>Terdaftar</th><th>Aksi</th></tr></thead>
                <tbody>
                <?php while ($u = $users->fetch_assoc()): ?>
                    <tr>
                        <td><?= sanitize($u['nama_lengkap']) ?></td>
                        <td><?= sanitize($u['username']) ?></td>
                        <td><span class="badge <?= $u['role']==='petani'?'badge-primary':'badge-info' ?>"><?= ucfirst($u['role']) ?></span></td>
                        <td><?= sanitize($u['no_hp'] ?: '-') ?></td>
                        <td><span class="badge <?= $u['status_akun']==='aktif'?'badge-success':'badge-danger' ?>"><?= ucfirst($u['status_akun']) ?></span></td>
                        <td style="font-size:.82rem;"><?= format_tanggal($u['created_at']) ?></td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="<?= BASE_URL ?>/admin/detail_pengguna.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-info">Detail</a>
                                <form method="POST" style="display:inline;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                                    <button type="submit" class="btn btn-sm <?= $u['status_akun']==='aktif'?'btn-warning':'btn-success' ?>" onclick="return confirm('Ubah status akun ini?')">
                                        <?= $u['status_akun']==='aktif'?'Nonaktifkan':'Aktifkan' ?>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
