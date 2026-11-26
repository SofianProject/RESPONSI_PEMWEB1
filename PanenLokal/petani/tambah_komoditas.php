<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
if (!defined('BASE_URL')) define('BASE_URL', '/RESPONSI_PEMWEB1/PanenLokal');
if (!defined('SITE_NAME')) define('SITE_NAME', 'PanenLokal');
$page_title = 'Tambah Komoditas';
cek_role('petani');

$fp = get_farmer_profile($conn, $_SESSION['user_id']);
$farmer_id = $fp['id'];
$is_verified = ($fp['status_verifikasi'] === 'terverifikasi');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Sesi formulir tidak valid. Silakan muat ulang halaman dan coba lagi.';
    } elseif (!$is_verified) {
        $error = 'Akun usaha tani Anda belum terverifikasi oleh admin, sehingga belum dapat menambah komoditas.';
    } else {
    $nama      = trim($_POST['nama'] ?? '');
    $kategori  = trim($_POST['kategori'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $satuan    = trim($_POST['satuan'] ?? 'kg');

    if (!$nama || !$kategori || !$satuan) {
        $error = 'Nama komoditas, kategori, dan satuan wajib diisi.';
    } else {
        $gambar = null;
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] !== UPLOAD_ERR_NO_FILE) {
            $allowed_map = [
                'jpg'  => ['image/jpeg', 'image/pjpeg'],
                'jpeg' => ['image/jpeg', 'image/pjpeg'],
                'png'  => ['image/png'],
                'webp' => ['image/webp'],
            ];
            $max_size = 2 * 1024 * 1024; // 2MB
            $upload_err = validate_uploaded_file($_FILES['gambar'], $allowed_map, $max_size, 'JPG, JPEG, PNG, atau WebP');
            if ($upload_err !== null) {
                $error = $upload_err;
            } else {
                $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
                $target_dir = __DIR__ . '/../uploads/products/';
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                $gambar = 'prod_' . uniqid() . '.' . $ext;
                $target = $target_dir . $gambar;
                if (!move_uploaded_file($_FILES['gambar']['tmp_name'], $target)) {
                    $error = 'Gagal mengunggah gambar produk.';
                    $gambar = null;
                }
            }
        }

        if (!$error) {
            $ins = $conn->prepare("INSERT INTO commodities (farmer_id, nama, kategori, deskripsi, satuan, gambar, status_komoditas) VALUES (?, ?, ?, ?, ?, ?, 'aktif')");
            $ins->bind_param("isssss", $farmer_id, $nama, $kategori, $deskripsi, $satuan, $gambar);
            if ($ins->execute()) {
                $_SESSION['flash'] = 'Komoditas berhasil ditambahkan!';
                $_SESSION['flash_type'] = 'success';
                header('Location: ' . BASE_URL . '/petani/komoditas.php');
                exit;
            } else {
                error_log('Tambah komoditas error: ' . $conn->error);
                $error = 'Gagal menyimpan komoditas. Silakan coba lagi.';
            }
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
      <h1>Tambah Komoditas Baru</h1>
    </div>

    <?php if (!$is_verified): ?>
      <div class="alert alert-warning">⏳ Akun usaha tani Anda berstatus <strong><?= sanitize($fp['status_verifikasi']) ?></strong>. Penambahan komoditas baru hanya tersedia setelah verifikasi admin.</div>
    <?php endif; ?>

    <?php if ($error): ?><?= alert($error, 'danger') ?><?php endif; ?>

    <div class="card card-body" style="max-width: 600px;">
      <form method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="form-group">
          <label class="form-label">Nama Komoditas *</label>
          <input type="text" name="nama" class="form-control" required placeholder="Contoh: Tomat Ceri Merah" value="<?= sanitize($_POST['nama'] ?? '') ?>">
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Kategori *</label>
            <select name="kategori" class="form-control" required>
              <option value="">Pilih Kategori...</option>
              <option value="Sayuran Hijau">Sayuran Hijau</option>
              <option value="Sayuran Buah">Sayuran Buah</option>
              <option value="Buah-buahan">Buah-buahan</option>
              <option value="Umbi-umbian">Umbi-umbian</option>
              <option value="Rempah & Herbal">Rempah & Herbal</option>
              <option value="Padi & Palawija">Padi & Palawija</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Satuan Jual *</label>
            <input type="text" name="satuan" class="form-control" required placeholder="kg / ikat / pcs" value="<?= sanitize($_POST['satuan'] ?? 'kg') ?>">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Deskripsi Komoditas</label>
          <textarea name="deskripsi" class="form-control" rows="3" placeholder="Jelaskan kualitas, metode tanam organik, dll..."><?= sanitize($_POST['deskripsi'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">Foto Produk (Opsional)</label>
          <input type="file" name="gambar" class="form-control" accept="image/*">
          <div class="form-hint">Maksimal 2MB, format JPG, PNG, atau WebP.</div>
        </div>
        <button type="submit" class="btn btn-primary">Simpan Komoditas</button>
      </form>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
