<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
if (!defined('BASE_URL')) define('BASE_URL', '/RESPONSI_PEMWEB1/PanenLokal');
if (!defined('SITE_NAME')) define('SITE_NAME', 'PanenLokal');
$page_title = 'Kelola Komoditas';
cek_role('petani');

$stmt = $conn->prepare("SELECT id FROM farmer_profiles WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$fp = $stmt->get_result()->fetch_assoc();
$farmer_id = $fp['id'] ?? 0;

$pesan = $_SESSION['flash'] ?? ''; unset($_SESSION['flash']);
$pesan_tipe = $_SESSION['flash_type'] ?? 'success'; unset($_SESSION['flash_type']);

// Toggle status komoditas
if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'toggle') {
    $cid = (int)$_GET['id'];
    $cur = $conn->prepare("SELECT status_komoditas FROM commodities WHERE id = ? AND farmer_id = ?");
    $cur->bind_param("ii", $cid, $farmer_id);
    $cur->execute();
    $cdata = $cur->get_result()->fetch_assoc();
    if ($cdata) {
        $new_st = $cdata['status_komoditas'] === 'aktif' ? 'nonaktif' : 'aktif';
        $up = $conn->prepare("UPDATE commodities SET status_komoditas = ? WHERE id = ?");
        $up->bind_param("si", $new_st, $cid);
        $up->execute();
        $_SESSION['flash'] = 'Status komoditas berhasil diubah.';
        $_SESSION['flash_type'] = 'success';
    }
    header('Location: ' . BASE_URL . '/petani/komoditas.php');
    exit;
}

$c_list = $conn->prepare("SELECT * FROM commodities WHERE farmer_id = ? ORDER BY created_at DESC");
$c_list->bind_param("i", $farmer_id);
$c_list->execute();
$commodities = $c_list->get_result();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>
<div class="dashboard-layout">
  <aside class="sidebar">
    <div class="sidebar-title">Menu Petani</div>
    <ul class="sidebar-menu">
      <li><a href="<?= BASE_URL ?>/petani/index.php">🏠 Dashboard</a></li>
      <li><a href="<?= BASE_URL ?>/petani/lahan.php">🌍 Lahan</a></li>
      <li><a href="<?= BASE_URL ?>/petani/komoditas.php" class="active">🥦 Komoditas</a></li>
      <li><a href="<?= BASE_URL ?>/petani/batch_panen.php">📦 Batch Panen</a></li>
      <li><a href="<?= BASE_URL ?>/petani/kelola_rescue.php">🔥 Food Rescue</a></li>
      <li><a href="<?= BASE_URL ?>/petani/pesanan.php">📋 Pesanan</a></li>
      <li><a href="<?= BASE_URL ?>/petani/kalender_panen.php">📅 Kalender Panen</a></li>
      <li><a href="<?= BASE_URL ?>/petani/laporan.php">📊 Laporan</a></li>
    </ul>
  </aside>
  <main class="dashboard-main">
    <div class="dashboard-header" style="display:flex; justify-content:space-between; align-items:center;">
      <div>
        <h1>🥦 Kelola Komoditas</h1>
        <p class="text-muted">Daftar jenis hasil tani yang Anda tawarkan.</p>
      </div>
      <a href="<?= BASE_URL ?>/petani/tambah_komoditas.php" class="btn btn-primary">+ Tambah Komoditas</a>
    </div>

    <?php if ($pesan): ?><?= alert($pesan, $pesan_tipe) ?><?php endif; ?>

    <div class="table-card">
      <table>
        <thead>
          <tr>
            <th>Gambar</th>
            <th>Nama Komoditas</th>
            <th>Kategori</th>
            <th>Satuan</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
        <?php if ($commodities->num_rows > 0): ?>
          <?php while ($c = $commodities->fetch_assoc()): ?>
          <tr>
            <td style="width: 70px;">
              <?php if ($c['gambar'] && file_exists(__DIR__ . '/../uploads/products/' . $c['gambar'])): ?>
                <img src="<?= BASE_URL ?>/uploads/products/<?= sanitize($c['gambar']) ?>" alt="" style="width:50px; height:50px; object-fit:cover; border-radius:var(--radius-sm);">
              <?php else: ?>
                <div style="width:50px; height:50px; background:var(--green-pale); display:flex; align-items:center; justify-content:center; border-radius:var(--radius-sm); font-size:1.5rem;">🥦</div>
              <?php endif; ?>
            </td>
            <td><strong><?= sanitize($c['nama']) ?></strong><br><small class="text-muted"><?= sanitize(truncate_text($c['deskripsi'] ?? '', 50)) ?></small></td>
            <td><span class="badge badge-secondary"><?= sanitize($c['kategori']) ?></span></td>
            <td><?= sanitize($c['satuan']) ?></td>
            <td>
              <span class="badge <?= $c['status_komoditas'] === 'aktif' ? 'badge-success' : 'badge-danger' ?>">
                <?= ucfirst($c['status_komoditas']) ?>
              </span>
            </td>
            <td>
              <div class="d-flex gap-1">
                <a href="<?= BASE_URL ?>/petani/edit_komoditas.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-info">Edit</a>
                <a href="?action=toggle&id=<?= $c['id'] ?>" class="btn btn-sm <?= $c['status_komoditas'] === 'aktif' ? 'btn-warning' : 'btn-success' ?>">
                  <?= $c['status_komoditas'] === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' ?>
                </a>
              </div>
            </td>
          </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="6" class="text-center text-muted">Belum ada komoditas. Silakan tambah komoditas baru.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
