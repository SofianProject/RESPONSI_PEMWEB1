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
    <div class="container">
        <div class="hero-grid">
            <div class="hero-content">
                <span class="hero-eyebrow">Marketplace Hasil Tani Lokal</span>
                <h1>Dari Ladang Langsung ke Meja Makanmu</h1>
                <p>Pre-order hasil tani segar langsung dari petani lokal terverifikasi. Harga transparan, jadwal panen jelas, dan ikut selamatkan pangan lewat Food Rescue.</p>
                <div class="hero-actions">
                    <a href="<?= BASE_URL ?>/katalog.php" class="btn btn-lg btn-hero-primary">Lihat Katalog</a>
                    <a href="<?= BASE_URL ?>/food_rescue.php" class="btn btn-lg btn-orange">🔥 Food Rescue</a>
                </div>
            </div>
            <div class="hero-highlights">
                <div class="hero-highlight-card">
                    <div class="hero-highlight-icon" aria-hidden="true">🌱</div>
                    <div>
                        <h3>Pre-Order Terencana</h3>
                        <p>Pesan hasil panen sebelum masa petik agar kesegaran terjaga dan pasokan petani terukur.</p>
                    </div>
                </div>
                <div class="hero-highlight-card">
                    <div class="hero-highlight-icon hero-highlight-icon-orange" aria-hidden="true">🔥</div>
                    <div>
                        <h3>Food Rescue Hemat</h3>
                        <p>Serap hasil panen berlebih dengan harga khusus untuk menekan risiko pangan terbuang.</p>
                    </div>
                </div>
                <div class="hero-highlight-card">
                    <div class="hero-highlight-icon" aria-hidden="true">📍</div>
                    <div>
                        <h3>Ambil atau Antar</h3>
                        <p>Pilih titik pengambilan terdekat atau pengantaran langsung sesuai jadwal panen.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Statistik -->
<section class="home-stats-section">
    <div class="container">
        <div class="home-stats-grid">
            <div class="home-stat-card">
                <div class="home-stat-icon" aria-hidden="true">🧑‍🌾</div>
                <div class="home-stat-info">
                    <div class="home-stat-value" data-countup="<?= (int)$stat_petani ?>"><?= (int)$stat_petani ?></div>
                    <div class="home-stat-label">Petani Terverifikasi</div>
                    <div class="home-stat-sub">Mitra produsen lokal aktif</div>
                </div>
            </div>
            <div class="home-stat-card">
                <div class="home-stat-icon" aria-hidden="true">🥦</div>
                <div class="home-stat-info">
                    <div class="home-stat-value" data-countup="<?= (int)$stat_komoditas ?>"><?= (int)$stat_komoditas ?></div>
                    <div class="home-stat-label">Komoditas Aktif</div>
                    <div class="home-stat-sub">Hasil tani siap pre-order</div>
                </div>
            </div>
            <div class="home-stat-card home-stat-card-accent">
                <div class="home-stat-icon home-stat-icon-orange" aria-hidden="true">♻️</div>
                <div class="home-stat-info">
                    <div class="home-stat-value" data-countup="<?= (int)round((float)$stat_rescue_kg) ?>"><?= number_format($stat_rescue_kg, 0, ',', '.') ?></div>
                    <div class="home-stat-label">Kg Pangan Terselamatkan</div>
                    <div class="home-stat-sub">Melalui program Food Rescue</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Produk Terbaru -->
<section class="section home-section">
    <div class="container">
        <div class="section-header-home">
            <div>
                <span class="section-eyebrow">Panen Pilihan</span>
                <h2>Produk Terbaru</h2>
                <p>Hasil tani lokal segar yang siap dipesan sebelum masa panen.</p>
            </div>
            <a href="<?= BASE_URL ?>/katalog.php" class="btn btn-outline-primary">Lihat Semua Katalog →</a>
        </div>
        <?php if ($produk_terbaru && $produk_terbaru->num_rows > 0): ?>
        <div class="home-product-grid">
        <?php
        $shown_produk = 0;
        while ($shown_produk < 3 && ($p = $produk_terbaru->fetch_assoc())):
            $shown_produk++;
            $is_rescue = get_rescue_label($p['rescue_aktif'], $p['batas_rescue'], $p['harga_rescue']);
        ?>
            <article class="card card-product">
                <div class="card-img">
                    <?php if ($p['gambar'] && file_exists(dirname(__FILE__) . '/uploads/products/' . $p['gambar'])): ?>
                        <img src="<?= BASE_URL ?>/uploads/products/<?= sanitize($p['gambar']) ?>" alt="<?= sanitize($p['nama']) ?>">
                    <?php else: ?>
                        <span aria-hidden="true">🌾</span>
                    <?php endif; ?>
                    <div class="card-img-badges">
                        <span class="badge badge-category"><?= sanitize($p['kategori']) ?></span>
                        <?php if ($is_rescue): ?>
                            <span class="badge badge-rescue">🔥 Rescue</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <h3 class="card-title"><?= sanitize($p['nama']) ?></h3>
                    <div class="card-farmer">🧑‍🌾 <?= sanitize($p['nama_usaha']) ?></div>
                    <div class="card-price-wrap">
                        <?php if ($is_rescue): ?>
                            <div class="card-price card-price-rescue"><?= format_rupiah($p['harga_rescue']) ?> <span class="card-unit">/<?= sanitize($p['satuan']) ?></span></div>
                            <div class="card-price-normal-crossed"><?= format_rupiah($p['harga_normal']) ?></div>
                        <?php else: ?>
                            <div class="card-price"><?= format_rupiah($p['harga_normal']) ?> <span class="card-unit">/<?= sanitize($p['satuan']) ?></span></div>
                        <?php endif; ?>
                    </div>
                    <div class="card-meta">
                        <span class="badge badge-secondary">Panen <?= format_tanggal($p['tanggal_estimasi_panen']) ?></span>
                        <span class="badge badge-success">Stok <?= (float)$p['stok_tersedia'] ?> <?= sanitize($p['satuan']) ?></span>
                    </div>
                    <div class="card-actions">
                        <a href="<?= BASE_URL ?>/detail_produk.php?id=<?= $p['id'] ?>" class="btn btn-primary btn-block">Lihat Detail</a>
                    </div>
                </div>
            </article>
        <?php endwhile; ?>
        </div>
        <?php else: ?>
        <div class="empty-state card card-body">
            <div class="empty-icon">🌱</div>
            <p>Belum ada produk yang tersedia saat ini.</p>
            <a href="<?= BASE_URL ?>/register.php" class="btn btn-primary">Daftar sebagai Petani</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Food Rescue -->
<?php if ($rescue_terbaru && $rescue_terbaru->num_rows > 0): ?>
<section class="section home-rescue-section">
    <div class="container">
        <div class="section-header-home">
            <div>
                <span class="section-eyebrow section-eyebrow-orange">Penawaran Terbatas</span>
                <h2>Food Rescue — Selamatkan Pangan</h2>
                <p>Produk segar yang perlu segera diserap pasar dengan harga hemat dan stok terbatas.</p>
            </div>
            <a href="<?= BASE_URL ?>/food_rescue.php" class="btn btn-orange">Lihat Semua Rescue →</a>
        </div>
        <div class="home-product-grid">
        <?php while ($r = $rescue_terbaru->fetch_assoc()):
            $pct = hitung_diskon($r['harga_normal'], $r['harga_rescue']);
        ?>
            <article class="card card-product card-rescue">
                <div class="card-img card-img-rescue">
                    <?php if ($r['gambar'] && file_exists(dirname(__FILE__) . '/uploads/products/' . $r['gambar'])): ?>
                        <img src="<?= BASE_URL ?>/uploads/products/<?= sanitize($r['gambar']) ?>" alt="<?= sanitize($r['nama']) ?>">
                    <?php else: ?>
                        <span aria-hidden="true">🔥</span>
                    <?php endif; ?>
                    <div class="card-img-badges">
                        <span class="badge badge-rescue">Hemat <?= $pct ?>%</span>
                    </div>
                </div>
                <div class="card-body">
                    <h3 class="card-title"><?= sanitize($r['nama']) ?></h3>
                    <div class="card-farmer">🧑‍🌾 <?= sanitize($r['nama_usaha']) ?></div>
                    <div class="card-price-wrap">
                        <div class="card-price card-price-rescue"><?= format_rupiah($r['harga_rescue']) ?> <span class="card-unit">/<?= sanitize($r['satuan']) ?></span></div>
                        <div class="card-price-normal-crossed"><?= format_rupiah($r['harga_normal']) ?></div>
                    </div>
                    <div class="card-meta">
                        <span class="badge badge-warning">Stok <?= (float)$r['stok_tersedia'] ?> <?= sanitize($r['satuan']) ?></span>
                    </div>
                    <?php if ($r['batas_rescue']): ?>
                        <div class="rescue-countdown-box">
                            <span class="rescue-countdown-label"><span class="rescue-flame" aria-hidden="true">🔥</span> Berakhir dalam</span>
                            <div id="cd-<?= $r['id'] ?>" data-countdown="<?= sanitize($r['batas_rescue']) ?>"></div>
                        </div>
                    <?php endif; ?>
                    <div class="card-actions">
                        <a href="<?= BASE_URL ?>/detail_produk.php?id=<?= $r['id'] ?>" class="btn btn-orange btn-block">Dapatkan Sekarang</a>
                    </div>
                </div>
            </article>
        <?php endwhile; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Cara Kerja -->
<section class="section home-steps-section">
    <div class="container">
        <div class="section-title">
            <span class="section-eyebrow">Alur Mudah</span>
            <h2>Bagaimana PanenLokal Bekerja?</h2>
            <p>Tiga langkah sederhana menghubungkan ladang petani langsung ke meja makan Anda.</p>
        </div>
        <div class="steps-grid">
            <div class="step-card">
                <div class="step-top">
                    <div class="step-icon" aria-hidden="true">🧑‍🌾</div>
                    <span class="step-num">01</span>
                </div>
                <h3>Petani Daftarkan Panen</h3>
                <p>Petani mendaftarkan lahan, komoditas, dan batch panen lengkap dengan estimasi tanggal dan kuota tersedia.</p>
            </div>
            <div class="step-card">
                <div class="step-top">
                    <div class="step-icon" aria-hidden="true">🛒</div>
                    <span class="step-num">02</span>
                </div>
                <h3>Pembeli Pre-Order</h3>
                <p>Pembeli memilih hasil tani dari katalog atau Food Rescue, memasukkan ke keranjang, lalu melakukan checkout.</p>
            </div>
            <div class="step-card">
                <div class="step-top">
                    <div class="step-icon" aria-hidden="true">🚚</div>
                    <span class="step-num">03</span>
                </div>
                <h3>Panen &amp; Kirim</h3>
                <p>Setelah pembayaran terverifikasi, petani menyiapkan pesanan untuk diantar atau diambil di titik pengambilan.</p>
            </div>
        </div>
    </div>
</section>

<!-- SDG Banner -->
<section class="section home-sdg-section">
    <div class="container">
        <div class="sdg-banner-box">
            <div class="sdg-banner-content">
                <span class="sdg-pill">🌍 Sustainable Development Goals</span>
                <h2>Mendukung SDG 2 — Zero Hunger</h2>
                <p>PanenLokal membantu mengurangi pemborosan pangan dan memperkuat ekonomi petani lokal melalui sistem pre-order terencana dan penyelamatan pangan (Food Rescue).</p>
                <a href="<?= BASE_URL ?>/tentang.php" class="btn btn-lg btn-hero-primary">Pelajari Lebih Lanjut</a>
            </div>
            <div class="sdg-pillars">
                <div class="sdg-pillar-item">
                    <strong>Akses Langsung Petani</strong>
                    <span>Memangkas rantai distribusi agar harga tetap adil bagi petani dan pembeli.</span>
                </div>
                <div class="sdg-pillar-item">
                    <strong>Kurangi Food Loss</strong>
                    <span>Menyalurkan panen berlebih sebelum melewati masa kesegaran optimal.</span>
                </div>
                <div class="sdg-pillar-item">
                    <strong>Ketahanan Pangan Lokal</strong>
                    <span>Mendorong konsumsi hasil pertanian daerah yang segar dan berkelanjutan.</span>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
