<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
if (!defined('BASE_URL')) define('BASE_URL', '/RESPONSI_PEMWEB1/PanenLokal');
if (!defined('SITE_NAME')) define('SITE_NAME', 'PanenLokal');
$page_title = 'Keranjang Belanja';
cek_role('pembeli');

$buyer_id = $_SESSION['user_id'];

// Pastikan pembeli memiliki cart
$cart_q = $conn->prepare("SELECT id FROM carts WHERE buyer_id = ?");
$cart_q->bind_param("i", $buyer_id);
$cart_q->execute();
$c_res = $cart_q->get_result()->fetch_assoc();
if (!$c_res) {
    $ins_cart = $conn->prepare("INSERT INTO carts (buyer_id) VALUES (?)");
    $ins_cart->bind_param("i", $buyer_id);
    $ins_cart->execute();
    $cart_id = $conn->insert_id;
} else {
    $cart_id = $c_res['id'];
}

$pesan = $_SESSION['flash'] ?? ''; unset($_SESSION['flash']);
$pesan_tipe = $_SESSION['flash_type'] ?? 'success'; unset($_SESSION['flash_type']);

// Handle aksi POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'tambah') {
        $batch_id = (int)($_POST['batch_id'] ?? 0);
        $qty      = (float)($_POST['quantity'] ?? 1);

        // Validasi batch
        $b_chk = $conn->prepare("
            SELECT hb.id, hb.stok_tersedia, c.nama
            FROM harvest_batches hb
            JOIN commodities c ON c.id = hb.commodity_id
            WHERE hb.id = ? AND hb.status_batch = 'dibuka' AND c.status_komoditas = 'aktif'
        ");
        $b_chk->bind_param("i", $batch_id);
        $b_chk->execute();
        $b_data = $b_chk->get_result()->fetch_assoc();

        if ($b_data && $qty > 0 && $qty <= $b_data['stok_tersedia']) {
            // Cek apakah item sudah ada di cart
            $item_chk = $conn->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND batch_id = ?");
            $item_chk->bind_param("ii", $cart_id, $batch_id);
            $item_chk->execute();
            $existing = $item_chk->get_result()->fetch_assoc();

            if ($existing) {
                $new_qty = min($b_data['stok_tersedia'], $existing['quantity'] + $qty);
                $up = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
                $up->bind_param("di", $new_qty, $existing['id']);
                $up->execute();
            } else {
                $ins = $conn->prepare("INSERT INTO cart_items (cart_id, batch_id, quantity) VALUES (?, ?, ?)");
                $ins->bind_param("iid", $cart_id, $batch_id, $qty);
                $ins->execute();
            }
            $_SESSION['flash'] = 'Produk "' . $b_data['nama'] . '" berhasil dimasukkan ke keranjang!';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash'] = 'Stok tidak mencukupi atau batch tidak tersedia.';
            $_SESSION['flash_type'] = 'danger';
        }
        header('Location: ' . BASE_URL . '/pembeli/keranjang.php');
        exit;
    } elseif ($action === 'update') {
        $item_id = (int)($_POST['item_id'] ?? 0);
        $qty     = (float)($_POST['quantity'] ?? 1);

        $chk = $conn->prepare("
            SELECT ci.id, hb.stok_tersedia 
            FROM cart_items ci
            JOIN harvest_batches hb ON hb.id = ci.batch_id
            WHERE ci.id = ? AND ci.cart_id = ?
        ");
        $chk->bind_param("ii", $item_id, $cart_id);
        $chk->execute();
        $citem = $chk->get_result()->fetch_assoc();

        if ($citem && $qty > 0) {
            $real_qty = min($qty, $citem['stok_tersedia']);
            $up = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
            $up->bind_param("di", $real_qty, $item_id);
            $up->execute();
            $_SESSION['flash'] = 'Jumlah kuantitas berhasil diperbarui.';
            $_SESSION['flash_type'] = 'success';
        }
        header('Location: ' . BASE_URL . '/pembeli/keranjang.php');
        exit;
    } elseif ($action === 'hapus') {
        $item_id = (int)($_POST['item_id'] ?? 0);
        $del = $conn->prepare("DELETE FROM cart_items WHERE id = ? AND cart_id = ?");
        $del->bind_param("ii", $item_id, $cart_id);
        $del->execute();
        $_SESSION['flash'] = 'Item berhasil dihapus dari keranjang.';
        $_SESSION['flash_type'] = 'success';
        header('Location: ' . BASE_URL . '/pembeli/keranjang.php');
        exit;
    }
}

// Ambil item keranjang
$items_q = $conn->prepare("
    SELECT ci.id AS item_id, ci.quantity,
           hb.id AS batch_id, hb.stok_tersedia, hb.harga_normal, hb.rescue_aktif, hb.harga_rescue, hb.batas_rescue,
           c.nama AS nama_komoditas, c.satuan, c.gambar,
           fp.nama_usaha
    FROM cart_items ci
    JOIN harvest_batches hb ON hb.id = ci.batch_id
    JOIN commodities c ON c.id = hb.commodity_id
    JOIN farmer_profiles fp ON fp.id = c.farmer_id
    WHERE ci.cart_id = ?
    ORDER BY ci.created_at DESC
");
$items_q->bind_param("i", $cart_id);
$items_q->execute();
$cart_items = $items_q->get_result();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>
<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-title">Menu Pembeli</div>
        <ul class="sidebar-menu">
            <li><a href="<?= BASE_URL ?>/pembeli/index.php"><span class="menu-icon">🏠</span> Dashboard</a></li>
            <li><a href="<?= BASE_URL ?>/pembeli/keranjang.php" class="active"><span class="menu-icon">🛒</span> Keranjang</a></li>
            <li><a href="<?= BASE_URL ?>/pembeli/pesanan.php"><span class="menu-icon">📦</span> Pesanan Saya</a></li>
            <li><a href="<?= BASE_URL ?>/pembeli/profil.php"><span class="menu-icon">👤</span> Profil</a></li>
        </ul>
    </aside>
    <main class="dashboard-main">
        <div class="dashboard-header">
            <h1>🛒 Keranjang Belanja Anda</h1>
            <p class="text-muted">Periksa kembali item hasil tani yang ingin Anda pesan sebelum checkout.</p>
        </div>

        <?php if ($pesan): ?><?= alert($pesan, $pesan_tipe) ?><?php endif; ?>

        <?php if ($cart_items->num_rows > 0): ?>
        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Petani / Usaha</th>
                        <th>Harga Satuan</th>
                        <th>Kuantitas</th>
                        <th>Subtotal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $grand_total = 0;
                while ($it = $cart_items->fetch_assoc()):
                    $is_rescue = get_rescue_label($it['rescue_aktif'], $it['batas_rescue'], $it['harga_rescue']);
                    $effective_price = $is_rescue ? $it['harga_rescue'] : $it['harga_normal'];
                    $subtotal = $it['quantity'] * $effective_price;
                    $grand_total += $subtotal;
                ?>
                    <tr class="cart-row" data-price="<?= $effective_price ?>">
                        <td>
                            <strong><?= sanitize($it['nama_komoditas']) ?></strong>
                            <?php if ($is_rescue): ?>
                                <span class="badge badge-rescue">🔥 Rescue</span>
                            <?php endif; ?>
                            <br><small class="text-muted">Stok: <?= (float)$it['stok_tersedia'] ?> <?= sanitize($it['satuan']) ?></small>
                        </td>
                        <td><?= sanitize($it['nama_usaha']) ?></td>
                        <td>
                            <?php if ($is_rescue): ?>
                                <span class="text-orange" style="font-weight:700;"><?= format_rupiah($it['harga_rescue']) ?></span>
                                <br><small style="text-decoration:line-through;" class="text-muted"><?= format_rupiah($it['harga_normal']) ?></small>
                            <?php else: ?>
                                <?= format_rupiah($it['harga_normal']) ?>
                            <?php endif; ?>
                            / <?= sanitize($it['satuan']) ?>
                        </td>
                        <td>
                            <form method="POST" style="display:flex; align-items:center; gap:.3rem;">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="item_id" value="<?= $it['item_id'] ?>">
                                <input type="number" name="quantity" class="form-control qty-input" value="<?= (float)$it['quantity'] ?>" min="0.1" max="<?= (float)$it['stok_tersedia'] ?>" step="0.1" style="width:85px; padding:.3rem;">
                                <button type="submit" class="btn btn-sm btn-outline-primary" style="padding:.2rem .4rem;" title="Simpan perubahan">✓</button>
                            </form>
                        </td>
                        <td class="subtotal" style="font-weight:700; color:var(--green-main);">
                            <?= format_rupiah($subtotal) ?>
                        </td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="action" value="hapus">
                                <input type="hidden" name="item_id" value="<?= $it['item_id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus item ini dari keranjang?')">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" class="text-right" style="font-size:1.1rem;">Total Belanja:</th>
                        <th colspan="2" id="cart-total" style="font-size:1.3rem; color:var(--green-dark);">
                            <?= format_rupiah($grand_total) ?>
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="d-flex justify-content:space-between; align-items:center; margin-top:1.5rem; flex-wrap:wrap; gap:1rem;">
            <a href="<?= BASE_URL ?>/katalog.php" class="btn btn-secondary">← Lanjut Belanja</a>
            <a href="<?= BASE_URL ?>/pembeli/checkout.php" class="btn btn-primary btn-lg">Lanjut ke Checkout →</a>
        </div>

        <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">🛒</div>
            <h3>Keranjang Anda Masih Kosong</h3>
            <p>Jelajahi hasil tani segar dari petani lokal dan lakukan pemesanan sekarang!</p>
            <a href="<?= BASE_URL ?>/katalog.php" class="btn btn-primary mt-2">Buka Katalog Produk</a>
        </div>
        <?php endif; ?>
    </main>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
