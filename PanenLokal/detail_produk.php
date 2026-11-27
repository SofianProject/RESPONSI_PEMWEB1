<?php
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
if (!defined('BASE_URL')) define('BASE_URL', '/RESPONSI_PEMWEB1/PanenLokal');
if (!defined('SITE_NAME')) define('SITE_NAME', 'PanenLokal');

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: ' . BASE_URL . '/katalog.php'); exit; }

$stmt = $conn->prepare("
    SELECT hb.*, c.nama, c.kategori, c.deskripsi, c.satuan, c.gambar, c.farmer_id,
           fp.nama_usaha, fp.deskripsi AS deskripsi_usaha,
           u.nama_lengkap, l.nama_lahan, l.lokasi
    FROM harvest_batches hb
    JOIN commodities c ON c.id = hb.commodity_id
    JOIN farmer_profiles fp ON fp.id = c.farmer_id
    JOIN users u ON u.id = fp.user_id
    LEFT JOIN lands l ON l.id = hb.land_id
    WHERE hb.id = ? AND hb.status_batch = 'dibuka' AND c.status_komoditas = 'aktif'
");
$stmt->bind_param('i', $id);
$stmt->execute();
$p = $stmt->get_result()->fetch_assoc();
if (!$p) { header('Location: ' . BASE_URL . '/katalog.php'); exit; }

$page_title = sanitize($p['nama']);
$is_rescue  = get_rescue_label($p['rescue_aktif'], $p['batas_rescue'], $p['harga_rescue']);

// Harga tampil
$harga_tampil  = $is_rescue ? $p['harga_rescue'] : $p['harga_normal'];

// Rating rata-rata
$rat = $conn->prepare("SELECT ROUND(AVG(r.rating),1) AS avg_r, COUNT(*) AS total FROM reviews r JOIN order_items oi ON oi.id=r.order_item_id WHERE oi.batch_id=?");
$rat->bind_param('i', $id);
$rat->execute();
$rating_data = $rat->get_result()->fetch_assoc();

// Ulasan
$ulasan_q = $conn->prepare("SELECT r.rating, r.komentar, r.created_at, u.nama_lengkap FROM reviews r JOIN users u ON u.id=r.buyer_id JOIN order_items oi ON oi.id=r.order_item_id WHERE oi.batch_id=? ORDER BY r.created_at DESC LIMIT 10");
$ulasan_q->bind_param('i', $id);
$ulasan_q->execute();
$ulasan_list = $ulasan_q->get_result();

// Pesan dari session (setelah redirect)
$pesan = $_SESSION['flash'] ?? ''; unset($_SESSION['flash']);
$pesan_tipe = $_SESSION['flash_type'] ?? 'success'; unset($_SESSION['flash_type']);

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="page-header">
    <div class="container">
        <h1><?= sanitize($p['nama']) ?></h1>
        <div class="breadcrumb"><a href="<?= BASE_URL ?>/katalog.php">Katalog</a> / <?= sanitize($p['nama']) ?></div>
    </div>
</div>

<section class="section" style="padding-top:0;">
    <div class="container">
        <?php if ($pesan): ?><?= alert($pesan, $pesan_tipe) ?><?php endif; ?>

        <div class="d-flex gap-2 flex-wrap" style="align-items:flex-start;">
            <!-- Gambar -->
            <div style="flex:0 0 320px;max-width:100%;">
                <div class="card" style="overflow:hidden;">
                    <?php if ($p['gambar'] && file_exists(__DIR__ . '/uploads/products/' . $p['gambar'])): ?>
                        <img src="<?= BASE_URL ?>/uploads/products/<?= sanitize($p['gambar']) ?>" alt="<?= sanitize($p['nama']) ?>" style="width:100%;height:280px;object-fit:cover;">
                    <?php else: ?>
                        <div style="height:280px;display:flex;align-items:center;justify-content:center;background:var(--green-pale);font-size:5rem;">🌾</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Info -->
            <div style="flex:1;min-width:280px;">
                <div class="card card-body">
                    <div class="d-flex gap-1 mb-2">
                        <span class="badge badge-secondary"><?= sanitize($p['kategori']) ?></span>
                        <?php if ($is_rescue): ?><span class="badge badge-rescue">🔥 FOOD RESCUE</span><?php endif; ?>
                    </div>

                    <h2 style="font-size:1.6rem;margin-bottom:.3rem;"><?= sanitize($p['nama']) ?></h2>
                    <p class="text-muted">🧑‍🌾 <?= sanitize($p['nama_usaha']) ?> (<?= sanitize($p['nama_lengkap']) ?>)</p>

                    <?php if ($rating_data['total'] > 0): ?>
                        <div class="stars" style="margin-bottom:.5rem;">
                            <?= str_repeat('★', (int)round($rating_data['avg_r'])) ?><?= str_repeat('☆', 5-(int)round($rating_data['avg_r'])) ?>
                            <span class="text-muted" style="font-size:.85rem;"><?= $rating_data['avg_r'] ?> (<?= $rating_data['total'] ?> ulasan)</span>
                        </div>
                    <?php endif; ?>

                    <?php if ($is_rescue): ?>
                        <div style="margin:1rem 0;">
                            <div class="rescue-banner">
                                <div class="rescue-pct">-<?= hitung_diskon($p['harga_normal'], $p['harga_rescue']) ?>%</div>
                                <div>
                                    <div style="font-size:1.5rem;font-weight:800;"><?= format_rupiah($p['harga_rescue']) ?>/<?= sanitize($p['satuan']) ?></div>
                                    <div style="text-decoration:line-through;opacity:.7;"><?= format_rupiah($p['harga_normal']) ?></div>
                                    <?php if ($p['batas_rescue']): ?>
                                        <div style="margin-top:.4rem;font-size:.85rem;">⏱ Berakhir: <span id="cd-main" data-countdown="<?= sanitize($p['batas_rescue']) ?>"></span></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div style="font-size:1.8rem;font-weight:800;color:var(--green-main);margin:1rem 0;"><?= format_rupiah($p['harga_normal']) ?><span style="font-size:1rem;font-weight:400;">/<?= sanitize($p['satuan']) ?></span></div>
                    <?php endif; ?>

                    <table class="info-table mb-2">
                        <tr><td>📅 Estimasi Panen</td><td><?= format_tanggal($p['tanggal_estimasi_panen']) ?></td></tr>
                        <tr><td>📦 Stok Tersedia</td><td><?= (float)$p['stok_tersedia'] ?> <?= sanitize($p['satuan']) ?></td></tr>
                        <?php if ($p['nama_lahan']): ?>
                        <tr><td>🌍 Lahan</td><td><?= sanitize($p['nama_lahan']) ?></td></tr>
                        <?php endif; ?>
                    </table>

                    <?php if ($p['deskripsi']): ?>
                        <p style="color:var(--gray-700);font-size:.92rem;"><?= nl2br(sanitize($p['deskripsi'])) ?></p>
                    <?php endif; ?>

                    <!-- Form tambah ke keranjang -->
                    <?php
                    $user = get_user();
                    $can_order = is_logged_in() && $user['role'] === 'pembeli' && $p['stok_tersedia'] > 0;
                    ?>
                    <?php if ($can_order): ?>
                    <form method="POST" action="<?= BASE_URL ?>/pembeli/keranjang.php" style="margin-top:1rem;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="tambah">
                        <input type="hidden" name="batch_id" value="<?= $p['id'] ?>">
                        <div class="form-group">
                            <label class="form-label">Jumlah (<?= sanitize($p['satuan']) ?>)</label>
                            <input type="number" name="quantity" class="form-control" value="1" min="0.1" max="<?= (float)$p['stok_tersedia'] ?>" step="0.1" required style="max-width:150px;">
                            <div class="form-hint">Maks: <?= (float)$p['stok_tersedia'] ?> <?= sanitize($p['satuan']) ?></div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg">🛒 Tambah ke Keranjang</button>
                    </form>
                    <?php elseif (!is_logged_in()): ?>
                        <div class="alert alert-info mt-2">Silakan <a href="<?= BASE_URL ?>/login.php">login</a> sebagai pembeli untuk memesan.</div>
                    <?php elseif ($p['stok_tersedia'] <= 0): ?>
                        <div class="alert alert-warning mt-2">Stok habis.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Ulasan -->
        <div class="mt-4">
            <h3>💬 Ulasan Pembeli</h3>
            <hr class="divider">
            <?php if ($ulasan_list->num_rows > 0): ?>
                <?php while ($ul = $ulasan_list->fetch_assoc()): ?>
                    <div class="card card-body mb-2">
                        <div class="d-flex gap-2 align-center mb-1">
                            <strong><?= sanitize($ul['nama_lengkap']) ?></strong>
                            <span class="stars stars-sm"><?= str_repeat('★', $ul['rating']) ?><?= str_repeat('☆', 5-$ul['rating']) ?></span>
                            <span class="text-muted" style="font-size:.8rem;"><?= format_tanggal($ul['created_at'], 'd M Y H:i') ?></span>
                        </div>
                        <?php if ($ul['komentar']): ?><p style="margin:0;font-size:.93rem;"><?= nl2br(sanitize($ul['komentar'])) ?></p><?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state" style="padding:1.5rem;">
                    <p>Belum ada ulasan untuk produk ini.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
