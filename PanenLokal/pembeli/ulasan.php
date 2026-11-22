<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
if (!defined('BASE_URL')) define('BASE_URL', '/RESPONSI_PEMWEB1/PanenLokal');
if (!defined('SITE_NAME')) define('SITE_NAME', 'PanenLokal');
$page_title = 'Beri Ulasan Produk';
cek_role('pembeli');

$buyer_id      = $_SESSION['user_id'];
$order_item_id = (int)($_GET['id'] ?? 0);
if ($order_item_id <= 0) {
    header('Location: ' . BASE_URL . '/pembeli/pesanan.php');
    exit;
}

// Verifikasi hak ulasan:
// 1. Order milik pembeli ini
// 2. Order status = selesai
// 3. Belum pernah diulas
$stmt = $conn->prepare("
    SELECT oi.*, o.id AS order_id, o.kode_pesanan, o.status, r.id AS review_id
    FROM order_items oi
    JOIN orders o ON o.id = oi.order_id
    LEFT JOIN reviews r ON r.order_item_id = oi.id
    WHERE oi.id = ? AND o.buyer_id = ?
");
$stmt->bind_param("ii", $order_item_id, $buyer_id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

if (!$item || $item['status'] !== 'selesai' || $item['review_id']) {
    header('Location: ' . BASE_URL . '/pembeli/pesanan.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating   = (int)($_POST['rating'] ?? 5);
    $komentar = trim($_POST['komentar'] ?? '');

    if ($rating < 1 || $rating > 5) {
        $error = 'Rating harus bernilai 1 sampai 5 bintang.';
    } elseif (mb_strlen($komentar) > 500) {
        $error = 'Komentar maksimal 500 karakter.';
    } else {
        $ins = $conn->prepare("INSERT INTO reviews (order_item_id, buyer_id, rating, komentar) VALUES (?, ?, ?, ?)");
        $ins->bind_param("iiis", $order_item_id, $buyer_id, $rating, $komentar);
        if ($ins->execute()) {
            $_SESSION['flash'] = 'Terima kasih! Ulasan dan rating Anda berhasil disimpan.';
            $_SESSION['flash_type'] = 'success';
            header('Location: ' . BASE_URL . '/pembeli/detail_pesanan.php?id=' . $item['order_id']);
            exit;
        } else {
            $error = 'Gagal menyimpan ulasan: ' . $conn->error;
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>
<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-title">Menu Pembeli</div>
        <ul class="sidebar-menu">
            <li><a href="<?= BASE_URL ?>/pembeli/index.php"><span class="menu-icon">🏠</span> Dashboard</a></li>
            <li><a href="<?= BASE_URL ?>/pembeli/keranjang.php"><span class="menu-icon">🛒</span> Keranjang</a></li>
            <li><a href="<?= BASE_URL ?>/pembeli/pesanan.php" class="active"><span class="menu-icon">📦</span> Pesanan Saya</a></li>
            <li><a href="<?= BASE_URL ?>/pembeli/profil.php"><span class="menu-icon">👤</span> Profil</a></li>
        </ul>
    </aside>
    <main class="dashboard-main">
        <div class="dashboard-header">
            <a href="<?= BASE_URL ?>/pembeli/detail_pesanan.php?id=<?= $item['order_id'] ?>" class="btn btn-secondary btn-sm mb-2">← Kembali ke Pesanan</a>
            <h1>⭐ Berikan Ulasan & Rating Kesegaran</h1>
        </div>

        <?php if ($error): ?><?= alert($error, 'danger') ?><?php endif; ?>

        <div class="card card-body" style="max-width: 600px;">
            <p>Berikan penilaian atas produk hasil tani berikut:</p>
            <div style="background:var(--green-bg); padding:1rem; border-radius:var(--radius-sm); margin-bottom:1.5rem;">
                <div style="font-size:1.15rem; font-weight:700; color:var(--green-dark);"><?= sanitize($item['nama_produk_snapshot']) ?></div>
                <div class="text-muted" style="font-size:.85rem;">Pesanan #<?= sanitize($item['kode_pesanan']) ?> (<?= (float)$item['quantity'] ?> <?= sanitize($item['satuan_snapshot']) ?>)</div>
            </div>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Rating Kesegaran & Kualitas *</label>
                    <div class="d-flex gap-2" style="font-size:1.2rem;">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <label style="cursor:pointer; display:flex; align-items:center; gap:.2rem;">
                                <input type="radio" name="rating" value="<?= $i ?>" <?= $i === 5 ? 'checked' : '' ?>>
                                <span class="stars" style="font-size:1.1rem;"><?= str_repeat('★', $i) ?></span>
                                <span style="font-size:.85rem; color:var(--gray-700);">(<?= $i ?>)</span>
                            </label>
                        <?php endfor; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Komentar / Ulasan Kesegaran</label>
                    <textarea id="komentar" name="komentar" class="form-control" rows="4" maxlength="500" placeholder="Ceritakan bagaimana kesegaran, rasa, dan pengemasan produk ini..."><?= sanitize($_POST['komentar'] ?? '') ?></textarea>
                    <div id="komentar-counter" class="char-counter">0 / 500</div>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg">Kirim Ulasan</button>
            </form>
        </div>
    </main>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
