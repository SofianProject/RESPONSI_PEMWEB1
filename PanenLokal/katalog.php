<?php
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
if (!defined('BASE_URL')) define('BASE_URL', '/RESPONSI_PEMWEB1/PanenLokal');
if (!defined('SITE_NAME')) define('SITE_NAME', 'PanenLokal');
$page_title = 'Katalog Produk';

// Filter & search
$search   = trim($_GET['search'] ?? '');
$kategori = trim($_GET['kategori'] ?? '');
$page     = max(1, (int)($_GET['page'] ?? 1));
$per_page = 10;
$offset   = ($page - 1) * $per_page;

// Build query
$where = "hb.status_batch='dibuka' AND c.status_komoditas='aktif' AND hb.stok_tersedia > 0";
$params = [];
$types  = '';
if ($search !== '') {
    $where .= " AND (c.nama LIKE ? OR fp.nama_usaha LIKE ?)";
    $like = '%' . $search . '%';
    $params[] = &$like; $params[] = &$like; $types .= 'ss';
}
if ($kategori !== '') {
    $where .= " AND c.kategori = ?";
    $params[] = &$kategori; $types .= 's';
}

// Count
$sql_count = "SELECT COUNT(*) FROM harvest_batches hb JOIN commodities c ON c.id=hb.commodity_id JOIN farmer_profiles fp ON fp.id=c.farmer_id WHERE $where";
$stmt = $conn->prepare($sql_count);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$total = $stmt->get_result()->fetch_row()[0];
$total_pages = ceil($total / $per_page);

// Products
$sql = "SELECT hb.id, c.nama, c.satuan, c.gambar, c.kategori, c.deskripsi,
               hb.harga_normal, hb.rescue_aktif, hb.harga_rescue, hb.batas_rescue,
               hb.tanggal_estimasi_panen, hb.stok_tersedia,
               fp.nama_usaha, u.nama_lengkap
        FROM harvest_batches hb
        JOIN commodities c ON c.id=hb.commodity_id
        JOIN farmer_profiles fp ON fp.id=c.farmer_id
        JOIN users u ON u.id=fp.user_id
        WHERE $where ORDER BY hb.created_at DESC LIMIT ? OFFSET ?";
$stmt2 = $conn->prepare($sql);
$limit_val = $per_page; $offset_val = $offset;
if ($types) {
    $params2 = $params;
    $params2[] = &$limit_val; $params2[] = &$offset_val;
    $stmt2->bind_param($types . 'ii', ...$params2);
} else {
    $stmt2->bind_param('ii', $limit_val, $offset_val);
}
$stmt2->execute();
$produk = $stmt2->get_result();

// Kategori list
$kategori_list = $conn->query("SELECT DISTINCT kategori FROM commodities WHERE status_komoditas='aktif' ORDER BY kategori");

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="page-header">
    <div class="container">
        <h1>🌿 Katalog Produk</h1>
        <div class="breadcrumb">Beranda / Katalog</div>
    </div>
</div>

<section class="section" style="padding-top:0;">
    <div class="container">
        <!-- Filter bar -->
        <form method="GET" class="d-flex gap-2 flex-wrap mb-3" style="align-items:flex-end;">
            <div class="form-group mb-0">
                <input type="text" name="search" class="form-control" placeholder="Cari produk..." value="<?= sanitize($search) ?>">
            </div>
            <div class="form-group mb-0">
                <select name="kategori" class="form-control">
                    <option value="">Semua Kategori</option>
                    <?php while ($kat = $kategori_list->fetch_assoc()): ?>
                        <option value="<?= sanitize($kat['kategori']) ?>" <?= $kategori === $kat['kategori'] ? 'selected' : '' ?>><?= sanitize($kat['kategori']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Cari</button>
            <?php if ($search || $kategori): ?>
                <a href="<?= BASE_URL ?>/katalog.php" class="btn btn-secondary">Reset</a>
            <?php endif; ?>
        </form>

        <p class="text-muted mb-3">Menampilkan <?= $total ?> produk<?= $search ? ' untuk "<strong>' . sanitize($search) . '</strong>"' : '' ?></p>

        <?php if ($produk && $produk->num_rows > 0): ?>
        <div class="grid grid-auto">
        <?php while ($p = $produk->fetch_assoc()):
            $is_rescue = get_rescue_label($p['rescue_aktif'], $p['batas_rescue'], $p['harga_rescue']);
        ?>
            <div class="card card-product">
                <div class="card-img">
                    <?php if ($p['gambar'] && file_exists(__DIR__ . '/uploads/products/' . $p['gambar'])): ?>
                        <img src="<?= BASE_URL ?>/uploads/products/<?= sanitize($p['gambar']) ?>" alt="<?= sanitize($p['nama']) ?>">
                    <?php else: ?>
                        <span>🌾</span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <div class="d-flex gap-1 mb-1">
                        <span class="badge badge-secondary"><?= sanitize($p['kategori']) ?></span>
                        <?php if ($is_rescue): ?><span class="badge badge-rescue">🔥 RESCUE</span><?php endif; ?>
                    </div>
                    <div class="card-title"><?= sanitize($p['nama']) ?></div>
                    <div class="card-farmer">🧑‍🌾 <?= sanitize($p['nama_usaha']) ?></div>
                    <?php if ($is_rescue): ?>
                        <div class="card-price card-price-rescue"><?= format_rupiah($p['harga_rescue']) ?>/<?= sanitize($p['satuan']) ?></div>
                        <div class="card-price-normal-crossed"><?= format_rupiah($p['harga_normal']) ?></div>
                    <?php else: ?>
                        <div class="card-price"><?= format_rupiah($p['harga_normal']) ?>/<?= sanitize($p['satuan']) ?></div>
                    <?php endif; ?>
                    <div class="card-meta">
                        <span class="badge badge-secondary">📅 <?= format_tanggal($p['tanggal_estimasi_panen']) ?></span>
                        <span class="badge badge-success">Stok: <?= (float)$p['stok_tersedia'] ?> <?= sanitize($p['satuan']) ?></span>
                    </div>
                    <div class="card-actions">
                        <a href="<?= BASE_URL ?>/detail_produk.php?id=<?= $p['id'] ?>" class="btn btn-primary btn-block">Lihat Detail</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>&kategori=<?= urlencode($kategori) ?>">‹ Sebelumnya</a>
            <?php endif; ?>
            <?php for ($i = max(1,$page-2); $i <= min($total_pages,$page+2); $i++): ?>
                <?php if ($i == $page): ?>
                    <span class="active"><?= $i ?></span>
                <?php else: ?>
                    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&kategori=<?= urlencode($kategori) ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>&kategori=<?= urlencode($kategori) ?>">Selanjutnya ›</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">🌱</div>
            <p>Tidak ada produk ditemukan.</p>
            <a href="<?= BASE_URL ?>/katalog.php" class="btn btn-secondary">Lihat Semua Produk</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
