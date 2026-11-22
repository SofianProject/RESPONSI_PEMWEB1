<?php
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
if (!defined('BASE_URL')) define('BASE_URL', '/RESPONSI_PEMWEB1/PanenLokal');
if (!defined('SITE_NAME')) define('SITE_NAME', 'PanenLokal');
$page_title = 'Beranda';

// Statistik
$stat_petani    = $conn->query("SELECT COUNT(*) FROM farmer_profiles WHERE status_verifikasi='terverifikasi'")->fetch_row()[0] ?? 0;
$stat_komoditas = $conn->query("SELECT COUNT(*) FROM commodities WHERE status_komoditas='aktif'")->fetch_row()[0] ?? 0;
$stat_rescue_kg = $conn->query("SELECT COALESCE(SUM(oi.quantity),0) FROM order_items oi JOIN orders o ON o.id=oi.order_id WHERE oi.is_rescue=1 AND o.status='selesai'")->fetch_row()[0] ?? 0;

// Produk terbaru
$produk_terbaru = $conn->query("
    SELECT hb.id, c.nama, c.satuan, c.gambar, c.kategori,
           hb.harga_normal, hb.rescue_aktif, hb.harga_rescue, hb.batas_rescue,
           hb.tanggal_estimasi_panen, hb.stok_tersedia,
           fp.nama_usaha, u.nama_lengkap
    FROM harvest_batches hb
    JOIN commodities c ON c.id = hb.commodity_id
    JOIN farmer_profiles fp ON fp.id = c.farmer_id
    JOIN users u ON u.id = fp.user_id
    WHERE hb.status_batch = 'dibuka' AND c.status_komoditas = 'aktif' AND hb.stok_tersedia > 0
    ORDER BY hb.created_at DESC LIMIT 6
");

// Food Rescue terbaru
$rescue_terbaru = $conn->query("
    SELECT hb.id, c.nama, c.satuan, c.gambar,
           hb.harga_normal, hb.harga_rescue, hb.batas_rescue, hb.stok_tersedia,
           fp.nama_usaha
    FROM harvest_batches hb
    JOIN commodities c ON c.id = hb.commodity_id
    JOIN farmer_profiles fp ON fp.id = c.farmer_id
    WHERE hb.rescue_aktif=1 AND hb.harga_rescue IS NOT NULL
      AND (hb.batas_rescue IS NULL OR hb.batas_rescue > NOW())
      AND hb.stok_tersedia > 0 AND hb.status_batch='dibuka'
      AND c.status_komoditas='aktif'
    ORDER BY hb.batas_rescue ASC LIMIT 3
");

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<!-- Hero -->
<section class="hero-section">
    <div class="hero-content">
        <h1>🌾 Dari Ladang ke Meja Makanmu</h1>
        <p>Pre-order hasil tani lokal langsung dari petani. Dapatkan produk segar, dukung petani lokal, dan bantu selamatkan pangan.</p>
        <div class="hero-actions">
            <a href="<?= BASE_URL ?>/katalog.php" class="btn btn-lg" style="background:#fff;color:var(--green-dark);">Lihat Katalog</a>
            <a href="<?= BASE_URL ?>/food_rescue.php" class="btn btn-lg btn-orange">🔥 Food Rescue</a>
        </div>
    </div>
</section>

<!-- Statistik -->
<section class="section" style="background:var(--white);">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">🧑‍🌾</div>
                <div class="stat-info">
                    <div class="stat-value"><?= $stat_petani ?></div>
                    <div class="stat-label">Petani Terverifikasi</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🥦</div>
                <div class="stat-info">
                    <div class="stat-value"><?= $stat_komoditas ?></div>
                    <div class="stat-label">Komoditas Aktif</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">♻️</div>
                <div class="stat-info">
                    <div class="stat-value"><?= number_format($stat_rescue_kg, 0, ',', '.') ?></div>
                    <div class="stat-label">Kg Pangan Terselamatkan</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Produk Terbaru -->
<section class="section">
    <div class="container">
        <div class="section-title">
            <h2>🌿 Produk Terbaru</h2>
            <p>Hasil tani lokal segar yang siap dipesan</p>
        </div>
        <?php if ($produk_terbaru && $produk_terbaru->num_rows > 0): ?>
        <div class="grid grid-auto">
        <?php while ($p = $produk_terbaru->fetch_assoc()):
            $is_rescue = get_rescue_label($p['rescue_aktif'], $p['batas_rescue'], $p['harga_rescue']);
        ?>
            <div class="card card-product">
                <div class="card-img">
                    <?php if ($p['gambar'] && file_exists(dirname(__FILE__) . '/uploads/products/' . $p['gambar'])): ?>
                        <img src="<?= BASE_URL ?>/uploads/products/<?= sanitize($p['gambar']) ?>" alt="<?= sanitize($p['nama']) ?>">
                    <?php else: ?>
                        <span>🌾</span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if ($is_rescue): ?>
                        <span class="badge badge-rescue mb-1">🔥 RESCUE</span>
                    <?php endif; ?>
                    <div class="card-title"><?= sanitize($p['nama']) ?></div>
                    <div class="card-farmer">🧑‍🌾 <?= sanitize($p['nama_usaha']) ?></div>
                    <?php if ($is_rescue): ?>
                        <div class="card-price card-price-rescue"><?= format_rupiah($p['harga_rescue']) ?>/<?= sanitize($p['satuan']) ?></div>
                        <div class="card-price-normal-crossed"><?= format_rupiah($p['harga_normal']) ?></div>
                    <?php else: ?>
                        <div class="card-price"><?= format_rupiah($p['harga_normal']) ?>/<?= sanitize($p['satuan']) ?></div>
                    <?php endif; ?>
                    <div class="card-meta">
                        <span class="badge badge-secondary">📅 Panen: <?= format_tanggal($p['tanggal_estimasi_panen']) ?></span>
                        <span class="badge badge-success">Stok: <?= (float)$p['stok_tersedia'] ?> <?= sanitize($p['satuan']) ?></span>
                    </div>
                    <div class="card-actions">
                        <a href="<?= BASE_URL ?>/detail_produk.php?id=<?= $p['id'] ?>" class="btn btn-primary btn-block">Lihat Detail</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
        </div>
        <div class="text-center mt-3">
            <a href="<?= BASE_URL ?>/katalog.php" class="btn btn-primary">Lihat Semua Katalog →</a>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">🌱</div>
            <p>Belum ada produk yang tersedia saat ini.</p>
            <a href="<?= BASE_URL ?>/register.php" class="btn btn-primary">Daftar sebagai Petani</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Food Rescue -->
<?php if ($rescue_terbaru && $rescue_terbaru->num_rows > 0): ?>
<section class="section" style="background:var(--orange-light);">
    <div class="container">
        <div class="section-title">
            <h2>🔥 Food Rescue — Selamatkan Pangan!</h2>
            <p>Produk yang perlu segera diserap, harga lebih hemat, stok terbatas</p>
        </div>
        <div class="grid grid-auto">
        <?php while ($r = $rescue_terbaru->fetch_assoc()):
            $pct = hitung_diskon($r['harga_normal'], $r['harga_rescue']);
        ?>
            <div class="card card-product">
                <div class="card-img" style="background:#ffe0b2;">
                    <span>🔥</span>
                </div>
                <div class="card-body">
                    <span class="badge badge-rescue mb-1">HEMAT <?= $pct ?>%</span>
                    <div class="card-title"><?= sanitize($r['nama']) ?></div>
                    <div class="card-farmer">🧑‍🌾 <?= sanitize($r['nama_usaha']) ?></div>
                    <div class="card-price card-price-rescue"><?= format_rupiah($r['harga_rescue']) ?>/<?= sanitize($r['satuan']) ?></div>
                    <div class="card-price-normal-crossed"><?= format_rupiah($r['harga_normal']) ?></div>
                    <?php if ($r['batas_rescue']): ?>
                        <div class="mt-1" id="cd-<?= $r['id'] ?>"></div>
                        <script>startCountdown('cd-<?= $r['id'] ?>','<?= $r['batas_rescue'] ?>')</script>
                    <?php endif; ?>
                    <div class="card-actions mt-2">
                        <a href="<?= BASE_URL ?>/detail_produk.php?id=<?= $r['id'] ?>" class="btn btn-orange btn-block">Dapatkan Sekarang</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
        </div>
        <div class="text-center mt-3">
            <a href="<?= BASE_URL ?>/food_rescue.php" class="btn btn-orange">Lihat Semua Food Rescue →</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Cara Kerja -->
<section class="section" style="background:var(--white);">
    <div class="container">
        <div class="section-title">
            <h2>Bagaimana PanenLokal Bekerja?</h2>
        </div>
        <div class="grid grid-3">
            <div style="text-align:center;padding:1.5rem;">
                <div style="font-size:3rem;margin-bottom:1rem;">🧑‍🌾</div>
                <h3>1. Petani Mendaftar</h3>
                <p>Petani mendaftarkan lahan, komoditas, dan batch panen dengan estimasi tanggal dan stok tersedia.</p>
            </div>
            <div style="text-align:center;padding:1.5rem;">
                <div style="font-size:3rem;margin-bottom:1rem;">🛒</div>
                <h3>2. Pembeli Pre-Order</h3>
                <p>Pembeli memilih produk dari katalog, menambahkan ke keranjang, dan melakukan checkout sebelum panen.</p>
            </div>
            <div style="text-align:center;padding:1.5rem;">
                <div style="font-size:3rem;margin-bottom:1rem;">🚚</div>
                <h3>3. Panen & Kirim</h3>
                <p>Setelah pembayaran terverifikasi, petani menyiapkan pesanan dan dikirim atau diambil di titik pengambilan.</p>
            </div>
        </div>
    </div>
</section>

<!-- SDG Banner -->
<section class="section" style="background:var(--green-dark);color:#fff;text-align:center;">
    <div class="container">
        <div style="font-size:2.5rem;margin-bottom:1rem;">🌍</div>
        <h2 style="color:#fff;">Mendukung SDG 2 — Zero Hunger</h2>
        <p style="opacity:.85;max-width:600px;margin:0 auto 1.5rem;">PanenLokal membantu mengurangi pemborosan pangan dengan menghubungkan petani dan konsumen secara langsung melalui sistem pre-order dan Food Rescue.</p>
        <a href="<?= BASE_URL ?>/tentang.php" class="btn" style="background:#fff;color:var(--green-dark);">Pelajari Lebih Lanjut</a>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
