<?php
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
if (!defined('BASE_URL')) define('BASE_URL', '/RESPONSI_PEMWEB1/PanenLokal');
if (!defined('SITE_NAME')) define('SITE_NAME', 'PanenLokal');
$page_title = 'Food Rescue';

$produk = $conn->query("
    SELECT hb.id, c.nama, c.satuan, c.gambar, c.kategori,
           hb.harga_normal, hb.harga_rescue, hb.batas_rescue, hb.stok_tersedia,
           hb.tanggal_estimasi_panen, fp.nama_usaha
    FROM harvest_batches hb
    JOIN commodities c ON c.id = hb.commodity_id
    JOIN farmer_profiles fp ON fp.id = c.farmer_id
    WHERE hb.rescue_aktif=1 AND hb.harga_rescue IS NOT NULL
      AND (hb.batas_rescue IS NULL OR hb.batas_rescue > NOW())
      AND hb.stok_tersedia > 0 AND hb.status_batch='dibuka'
      AND c.status_komoditas='aktif'
    ORDER BY hb.batas_rescue ASC
");

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="page-header" style="background:linear-gradient(135deg,#bf360c,var(--orange));color:#fff;border-bottom:none;">
    <div class="container">
        <h1 style="color:#fff;">🔥 Food Rescue</h1>
        <p style="opacity:.9;margin:0;">Selamatkan pangan, dapatkan harga terbaik. Stok terbatas — pesan sekarang!</p>
    </div>
</div>

<section class="section" style="background:var(--orange-light);padding-top:1.5rem;">
    <div class="container">
        <?php if ($produk && $produk->num_rows > 0): ?>
        <p class="mb-3 text-muted">Ditemukan <strong><?= $produk->num_rows ?></strong> produk rescue aktif</p>
        <div class="grid grid-auto">
        <?php while ($p = $produk->fetch_assoc()):
            $pct = hitung_diskon($p['harga_normal'], $p['harga_rescue']);
        ?>
            <div class="card card-product">
                <div class="card-img" style="background:#ffe0b2;position:relative;">
                    <?php if ($p['gambar'] && file_exists(__DIR__ . '/uploads/products/' . $p['gambar'])): ?>
                        <img src="<?= BASE_URL ?>/uploads/products/<?= sanitize($p['gambar']) ?>" alt="<?= sanitize($p['nama']) ?>">
                    <?php else: ?>
                        <span>🔥</span>
                    <?php endif; ?>
                    <span class="badge badge-rescue" style="position:absolute;top:.5rem;left:.5rem;font-size:.9rem;">HEMAT <?= $pct ?>%</span>
                </div>
                <div class="card-body">
                    <div class="card-title"><?= sanitize($p['nama']) ?></div>
                    <div class="card-farmer">🧑‍🌾 <?= sanitize($p['nama_usaha']) ?></div>
                    <div class="card-price card-price-rescue" style="font-size:1.3rem;"><?= format_rupiah($p['harga_rescue']) ?>/<?= sanitize($p['satuan']) ?></div>
                    <div class="card-price-normal-crossed"><?= format_rupiah($p['harga_normal']) ?></div>

                    <?php if ($p['batas_rescue']): ?>
                        <div class="mt-1" style="font-size:.82rem;color:var(--orange);">⏱ Berakhir:</div>
                        <div id="cd-<?= $p['id'] ?>" data-countdown="<?= sanitize($p['batas_rescue']) ?>"></div>
                    <?php endif; ?>

                    <div class="card-meta mt-2">
                        <span class="badge badge-success">Stok: <?= (float)$p['stok_tersedia'] ?> <?= sanitize($p['satuan']) ?></span>
                        <span class="badge badge-secondary">📅 <?= format_tanggal($p['tanggal_estimasi_panen']) ?></span>
                    </div>
                    <div class="card-actions mt-2">
                        <a href="<?= BASE_URL ?>/detail_produk.php?id=<?= $p['id'] ?>" class="btn btn-orange btn-block">Dapatkan Sekarang!</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">🔥</div>
            <h3>Belum ada produk Food Rescue</h3>
            <p>Saat ini belum ada produk rescue yang aktif. Cek kembali nanti!</p>
            <a href="<?= BASE_URL ?>/katalog.php" class="btn btn-primary">Lihat Katalog Biasa</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<section class="section" style="background:var(--white);">
    <div class="container">
        <div class="section-title">
            <h2>Apa itu Food Rescue?</h2>
        </div>
        <div class="grid grid-3">
            <div style="text-align:center;padding:1rem;">
                <div style="font-size:2.5rem;margin-bottom:.8rem;">🌾</div>
                <h4>Panen Berlebih</h4>
                <p style="font-size:.9rem;color:var(--gray-700);">Petani menawarkan hasil panen berlebih atau mendekati batas kesegaran dengan harga khusus.</p>
            </div>
            <div style="text-align:center;padding:1rem;">
                <div style="font-size:2.5rem;margin-bottom:.8rem;">💰</div>
                <h4>Harga Hemat</h4>
                <p style="font-size:.9rem;color:var(--gray-700);">Pembeli mendapatkan produk segar berkualitas dengan harga yang lebih terjangkau.</p>
            </div>
            <div style="text-align:center;padding:1rem;">
                <div style="font-size:2.5rem;margin-bottom:.8rem;">♻️</div>
                <h4>Kurangi Pemborosan</h4>
                <p style="font-size:.9rem;color:var(--gray-700);">Bersama-sama mengurangi food loss, mendukung SDG 2 Zero Hunger.</p>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
