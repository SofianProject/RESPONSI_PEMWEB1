<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

cek_role('petani');

$stmt = $conn->prepare("SELECT id FROM farmer_profiles WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$fp = $stmt->get_result()->fetch_assoc();
$stmt->close();
$farmer_id = $fp['id'] ?? 0;

$pesan = '';

// Proses hapus lahan
if (isset($_GET['action']) && $_GET['action'] === 'hapus' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    // Verifikasi kepemilikan
    $chk = $conn->prepare("SELECT id FROM lands WHERE id = ? AND farmer_id = ?");
    $chk->bind_param("ii", $id, $farmer_id);
    $chk->execute();
    if ($chk->get_result()->num_rows > 0) {
        $del = $conn->prepare("DELETE FROM lands WHERE id = ? AND farmer_id = ?");
        $del->bind_param("ii", $id, $farmer_id);
        $del->execute();
        $del->close();
        $pesan = alert('Lahan berhasil dihapus.', 'success');
    } else {
        $pesan = alert('Lahan tidak ditemukan atau bukan milik Anda.', 'danger');
    }
    $chk->close();
}

// Ambil daftar lahan
$stmt = $conn->prepare("SELECT id, nama_lahan, lokasi, luas_m2, keterangan, created_at FROM lands WHERE farmer_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$lahans = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$page_title = 'Daftar Lahan';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="dashboard-layout">
  <aside class="sidebar">
    <div class="sidebar-title">Menu Petani</div>
    <ul class="sidebar-menu">
      <li><a href="<?= BASE_URL ?>/petani/index.php">🏠 Dashboard</a></li>
      <li><a href="<?= BASE_URL ?>/petani/lahan.php" class="active">🌍 Lahan</a></li>
      <li><a href="<?= BASE_URL ?>/petani/komoditas.php">🥦 Komoditas</a></li>
      <li><a href="<?= BASE_URL ?>/petani/batch_panen.php">📦 Batch Panen</a></li>
      <li><a href="<?= BASE_URL ?>/petani/kelola_rescue.php">🔥 Food Rescue</a></li>
      <li><a href="<?= BASE_URL ?>/petani/pesanan.php">📋 Pesanan</a></li>
      <li><a href="<?= BASE_URL ?>/petani/kalender_panen.php">📅 Kalender Panen</a></li>
      <li><a href="<?= BASE_URL ?>/petani/laporan.php">📊 Laporan</a></li>
    </ul>
  </aside>
  <main class="dashboard-main">
    <div class="section-header">
      <h1 class="page-title">🌍 Daftar Lahan</h1>
      <a href="<?= BASE_URL ?>/petani/tambah_lahan.php" class="btn btn-primary">+ Tambah Lahan</a>
    </div>

    <?= $pesan ?>

    <div class="table-responsive">
      <table class="data-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Nama Lahan</th>
            <th>Lokasi</th>
            <th>Luas (m²)</th>
            <th>Keterangan</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($lahans)): ?>
          <tr><td colspan="6" class="text-center text-muted">Belum ada lahan. <a href="<?= BASE_URL ?>/petani/tambah_lahan.php">Tambah sekarang</a>.</td></tr>
          <?php else: ?>
          <?php foreach ($lahans as $i => $l): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td><?= sanitize($l['nama_lahan']) ?></td>
            <td><?= sanitize($l['lokasi']) ?></td>
            <td><?= $l['luas_m2'] !== null ? number_format((float)$l['luas_m2'], 2) : '-' ?></td>
            <td><?= sanitize(truncate_text($l['keterangan'] ?? '', 60)) ?></td>
            <td class="action-btns">
              <a href="<?= BASE_URL ?>/petani/edit_lahan.php?id=<?= (int)$l['id'] ?>" class="btn btn-xs btn-outline-primary">Edit</a>
              <a href="<?= BASE_URL ?>/petani/lahan.php?action=hapus&id=<?= (int)$l['id'] ?>"
                 class="btn btn-xs btn-outline-danger"
                 onclick="return confirm('Yakin hapus lahan ini?')">Hapus</a>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
