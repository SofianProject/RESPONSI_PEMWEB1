<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
if (!defined('BASE_URL')) define('BASE_URL', '/RESPONSI_PEMWEB1/PanenLokal');
if (!defined('SITE_NAME')) define('SITE_NAME', 'PanenLokal');
$page_title = 'Edit Komoditas';
cek_role('petani');

$stmt = $conn->prepare("SELECT id FROM farmer_profiles WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$fp = $stmt->get_result()->fetch_assoc();
$farmer_id = $fp['id'] ?? 0;

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . BASE_URL . '/petani/komoditas.php');
    exit;
}

$chk = $conn->prepare("SELECT * FROM commodities WHERE id = ? AND farmer_id = ?");
$chk->bind_param("ii", $id, $farmer_id);
$chk->execute();
$c = $chk->get_result()->fetch_assoc();
if (!$c) {
    header('Location: ' . BASE_URL . '/petani/komoditas.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama      = trim($_POST['nama'] ?? '');
    $kategori  = trim($_POST['kategori'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $satuan    = trim($_POST['satuan'] ?? 'kg');

    if (!$nama || !$kategori || !$satuan) {
        $error = 'Nama komoditas, kategori, dan satuan wajib diisi.';
    } else {
        $gambar = $c['gambar'];
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $max_size = 2 * 1024 * 1024; // 2MB
            if (!in_array($ext, $allowed)) {
                $error = 'Format file gambar harus JPG, JPEG, PNG, atau WebP.';
            } elseif ($_FILES['gambar']['size'] > $max_size) {
                $error = 'Ukuran file gambar maksimal 2MB.';
            } else {
                $target_dir = __DIR__ . '/../uploads/products/';
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                $new_gambar = 'prod_' . uniqid() . '.' . $ext;
                $target = $target_dir . $new_gambar;
                if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target)) {
                    // Hapus gambar lama jika ada
                    if ($c['gambar'] && file_exists($target_dir . $c['gambar'])) {
                        @unlink($target_dir . $c['gambar']);
                    }
                    $gambar = $new_gambar;
                } else {
                    $error = 'Gagal mengunggah gambar produk.';
                }
            }
        }

        if (!$error) {
            $upd = $conn->prepare("UPDATE commodities SET nama = ?, kategori = ?, deskripsi = ?, satuan = ?, gambar = ? WHERE id = ? AND farmer_id = ?");
            $upd->bind_param("sssssii", $nama, $kategori, $deskripsi, $satuan, $gambar, $id, $farmer_id);
            if ($upd->execute()) {
                $_SESSION['flash'] = 'Komoditas berhasil diperbarui!';
                $_SESSION['flash_type'] = 'success';
                header('Location: ' . BASE_URL . '/petani/komoditas.php');
                exit;
            } else {
                $error = 'Gagal memperbarui komoditas: ' . $conn->error;
            }
        }
    }
}

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
    <div class="dashboard-header">
      <a href="<?= BASE_URL ?>/petani/komoditas.php" class="btn btn-secondary btn-sm mb-2">← Kembali</a>
      <h1>Edit Komoditas</h1>
    </div>

    <?php if ($error): ?><?= alert($error, 'danger') ?><?php endif; ?>

    <div class="card card-body" style="max-width: 600px;">
      <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
          <label class="form-label">Nama Komoditas *</label>
          <input type="text" name="nama" class="form-control" required value="<?= sanitize($c['nama']) ?>">
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Kategori *</label>
            <select name="kategori" class="form-control" required>
              <?php
              $cats = ['Sayuran Hijau', 'Sayuran Buah', 'Buah-buahan', 'Umbi-umbian', 'Rempah & Herbal', 'Padi & Palawija'];
              foreach ($cats as $cat):
              ?>
                <option value="<?= $cat ?>" <?= $c['kategori'] === $cat ? 'selected' : '' ?>><?= $cat ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Satuan Jual *</label>
            <input type="text" name="satuan" class="form-control" required value="<?= sanitize($c['satuan']) ?>">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Deskripsi Komoditas</label>
          <textarea name="deskripsi" class="form-control" rows="3"><?= sanitize($c['deskripsi'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">Foto Produk (Biarkan kosong jika tidak diganti)</label>
          <?php if ($c['gambar']): ?>
            <div style="margin-bottom: .5rem;">
              <img src="<?= BASE_URL ?>/uploads/products/<?= sanitize($c['gambar']) ?>" alt="" style="width:100px; height:100px; object-fit:cover; border-radius:var(--radius-sm);">
            </div>
          <?php endif; ?>
          <input type="file" name="gambar" class="form-control" accept="image/*">
        </div>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
      </form>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
