<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
if (!defined('BASE_URL')) define('BASE_URL', '/RESPONSI_PEMWEB1/PanenLokal');
if (!defined('SITE_NAME')) define('SITE_NAME', 'PanenLokal');
$page_title = 'Checkout Pesanan';
cek_role('pembeli');

$buyer_id = $_SESSION['user_id'];

// Ambil item cart
$items_q = $conn->prepare("
    SELECT ci.id AS item_id, ci.quantity,
           hb.id AS batch_id, hb.stok_tersedia, hb.harga_normal, hb.rescue_aktif, hb.harga_rescue, hb.batas_rescue,
           c.nama AS nama_komoditas, c.satuan,
           fp.nama_usaha
    FROM cart_items ci
    JOIN carts cart ON cart.id = ci.cart_id
    JOIN harvest_batches hb ON hb.id = ci.batch_id
    JOIN commodities c ON c.id = hb.commodity_id
    JOIN farmer_profiles fp ON fp.id = c.farmer_id
    WHERE cart.buyer_id = ?
");
$items_q->bind_param("i", $buyer_id);
$items_q->execute();
$items_res = $items_q->get_result();

$cart_items = [];
$total_amount = 0;
while ($row = $items_res->fetch_assoc()) {
    $is_rescue = get_rescue_label($row['rescue_aktif'], $row['batas_rescue'], $row['harga_rescue']);
    $price = $is_rescue ? (float)$row['harga_rescue'] : (float)$row['harga_normal'];
    $subtotal = $row['quantity'] * $price;
    $row['effective_price'] = $price;
    $row['is_rescue_applied'] = $is_rescue ? 1 : 0;
    $row['subtotal'] = $subtotal;
    $total_amount += $subtotal;
    $cart_items[] = $row;
}

if (empty($cart_items)) {
    header('Location: ' . BASE_URL . '/pembeli/keranjang.php');
    exit;
}

// Titik pengambilan aktif
$points = $conn->query("SELECT id, nama_titik, alamat FROM pickup_points WHERE status_aktif = 1");

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Sesi formulir tidak valid. Silakan muat ulang halaman dan coba lagi.';
    } else {
        $metode          = $_POST['metode'] ?? 'ambil';
        $pickup_point_id = !empty($_POST['pickup_point_id']) ? (int)$_POST['pickup_point_id'] : null;
        $alamat_tujuan   = trim($_POST['alamat_tujuan'] ?? '');
        $raw_jadwal      = trim($_POST['jadwal'] ?? '');
        $jadwal          = null;
        $catatan         = trim($_POST['catatan'] ?? '');

        if ($raw_jadwal !== '') {
            $dt = DateTime::createFromFormat('Y-m-d', $raw_jadwal);
            if (!$dt || $dt->format('Y-m-d') !== $raw_jadwal) {
                $error = 'Format tanggal jadwal tidak valid.';
            } elseif ($raw_jadwal < date('Y-m-d')) {
                $error = 'Tanggal jadwal tidak boleh di masa lalu.';
            } else {
                $jadwal = $raw_jadwal . ' 00:00:00';
            }
        }

        if ($error !== '') {
            // Biarkan pesan error tanggal tampil
        } elseif ($metode === 'ambil' && !$pickup_point_id) {
            $error = 'Silakan pilih titik pengambilan yang diinginkan.';
        } elseif ($metode === 'antar' && !$alamat_tujuan) {
            $error = 'Alamat tujuan pengantaran wajib diisi.';
        } else {
            // Transaksi checkout database
            $conn->begin_transaction();
            try {
                // 1. Validasi stok terkini semua item
                foreach ($cart_items as $it) {
                    $st_chk = $conn->prepare("SELECT stok_tersedia FROM harvest_batches WHERE id = ? FOR UPDATE");
                    $st_chk->bind_param("i", $it['batch_id']);
                    $st_chk->execute();
                    $cur_st = $st_chk->get_result()->fetch_assoc()['stok_tersedia'] ?? 0;
                    if ($cur_st < $it['quantity']) {
                        throw new Exception("Maaf, stok untuk " . $it['nama_komoditas'] . " tidak mencukupi (tersedia: $cur_st).");
                    }
                }

                // 2. Insert ke orders
                $kode_pesanan = generate_kode_pesanan();
                $ins_ord = $conn->prepare("
                    INSERT INTO orders (buyer_id, kode_pesanan, total_amount, status, catatan)
                    VALUES (?, ?, ?, 'menunggu_pembayaran', ?)
                ");
                $ins_ord->bind_param("isds", $buyer_id, $kode_pesanan, $total_amount, $catatan);
                $ins_ord->execute();
                $order_id = $conn->insert_id;

                // 3. Insert ke order_items & potong stok
                $ins_item = $conn->prepare("
                    INSERT INTO order_items (order_id, batch_id, nama_produk_snapshot, satuan_snapshot, quantity, harga_satuan, is_rescue)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $dec_stok = $conn->prepare("UPDATE harvest_batches SET stok_tersedia = stok_tersedia - ? WHERE id = ?");

                foreach ($cart_items as $it) {
                    $ins_item->bind_param("iissddi", $order_id, $it['batch_id'], $it['nama_komoditas'], $it['satuan'], $it['quantity'], $it['effective_price'], $it['is_rescue_applied']);
                    $ins_item->execute();

                    $dec_stok->bind_param("di", $it['quantity'], $it['batch_id']);
                    $dec_stok->execute();
                }

                // 4. Insert ke deliveries
                $ins_del = $conn->prepare("
                    INSERT INTO deliveries (order_id, metode, pickup_point_id, alamat_tujuan, jadwal, status)
                    VALUES (?, ?, ?, ?, ?, 'menunggu')
                ");
                $ins_del->bind_param("isiss", $order_id, $metode, $pickup_point_id, $alamat_tujuan, $jadwal);
                $ins_del->execute();

                // 5. Insert ke payments
                $ins_pay = $conn->prepare("
                    INSERT INTO payments (order_id, nominal, status_pembayaran)
                    VALUES (?, ?, 'menunggu')
                ");
                $ins_pay->bind_param("id", $order_id, $total_amount);
                $ins_pay->execute();

                // 6. Kosongkan cart pembeli
                $del_cart = $conn->prepare("DELETE FROM cart_items WHERE cart_id = (SELECT id FROM carts WHERE buyer_id = ?)");
                $del_cart->bind_param("i", $buyer_id);
                $del_cart->execute();

                $conn->commit();

                $_SESSION['flash'] = "Pesanan berhasil dibuat dengan kode: $kode_pesanan. Silakan unggah bukti transfer pembayaran.";
                $_SESSION['flash_type'] = "success";
                header("Location: " . BASE_URL . "/pembeli/pembayaran.php?id=" . $order_id);
                exit;
            } catch (mysqli_sql_exception $e) {
                $conn->rollback();
                error_log('Checkout SQL error: ' . $e->getMessage());
                $error = 'Terjadi kesalahan saat memproses pesanan. Silakan coba lagi.';
            } catch (Exception $e) {
                $conn->rollback();
                $error = $e->getMessage();
            }
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
            <li><a href="<?= BASE_URL ?>/pembeli/keranjang.php" class="active"><span class="menu-icon">🛒</span> Keranjang</a></li>
            <li><a href="<?= BASE_URL ?>/pembeli/pesanan.php"><span class="menu-icon">📦</span> Pesanan Saya</a></li>
            <li><a href="<?= BASE_URL ?>/pembeli/profil.php"><span class="menu-icon">👤</span> Profil</a></li>
        </ul>
    </aside>
    <main class="dashboard-main">
        <div class="dashboard-header">
            <a href="<?= BASE_URL ?>/pembeli/keranjang.php" class="btn btn-secondary btn-sm mb-2">← Kembali ke Keranjang</a>
            <h1>Checkout Pesanan</h1>
            <p class="text-muted">Konfirmasi pemenuhan pesanan dan tentukan metode pengiriman hasil panen.</p>
        </div>

        <?php if ($error): ?><?= alert($error, 'danger') ?><?php endif; ?>

        <div class="grid grid-2">
            <!-- Ringkasan Item -->
            <div>
                <div class="table-card">
                    <div style="padding:1rem 1.2rem; border-bottom:1px solid var(--gray-200);">
                        <h3 style="margin:0;">Ringkasan Pesanan</h3>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Kuantitas</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($cart_items as $it): ?>
                            <tr>
                                <td>
                                    <strong><?= sanitize($it['nama_komoditas']) ?></strong>
                                    <?php if ($it['is_rescue_applied']): ?>
                                        <span class="badge badge-rescue">Rescue</span>
                                    <?php endif; ?>
                                    <br><small class="text-muted">🧑‍🌾 <?= sanitize($it['nama_usaha']) ?></small>
                                </td>
                                <td><?= (float)$it['quantity'] ?> <?= sanitize($it['satuan']) ?></td>
                                <td><?= format_rupiah($it['subtotal']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="2" class="text-right">Total Pembayaran:</th>
                                <th style="font-size:1.2rem; color:var(--green-main);"><?= format_rupiah($total_amount) ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Form Pengiriman -->
            <div>
                <div class="card card-body">
                    <h3>Metode Pemenuhan Pesanan</h3>
                    <form method="POST">
                        <?= csrf_field() ?>
                        <div class="form-group mt-2">
                            <label class="form-label">Pilih Metode:</label>
                            <div class="d-flex gap-2">
                                <label style="display:flex; align-items:center; gap:.4rem; cursor:pointer;">
                                    <input type="radio" name="metode" value="ambil" checked onchange="document.getElementById('opt-ambil').style.display='block'; document.getElementById('opt-antar').style.display='none';">
                                    <span>📍 Ambil di Titik Pengambilan</span>
                                </label>
                                <label style="display:flex; align-items:center; gap:.4rem; cursor:pointer;">
                                    <input type="radio" name="metode" value="antar" onchange="document.getElementById('opt-ambil').style.display='none'; document.getElementById('opt-antar').style.display='block';">
                                    <span>🚚 Antar ke Alamat</span>
                                </label>
                            </div>
                        </div>

                        <!-- Opsi Ambil -->
                        <div id="opt-ambil" class="form-group">
                            <label class="form-label">Pilih Titik Pengambilan (Pick-up Point) *</label>
                            <select name="pickup_point_id" class="form-control">
                                <option value="">Pilih Titik Pengambilan...</option>
                                <?php while ($pt = $points->fetch_assoc()): ?>
                                    <option value="<?= $pt['id'] ?>"><?= sanitize($pt['nama_titik']) ?> (<?= sanitize($pt['alamat']) ?>)</option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- Opsi Antar -->
                        <div id="opt-antar" class="form-group" style="display:none;">
                            <label class="form-label">Alamat Lengkap Pengantaran *</label>
                            <textarea name="alamat_tujuan" class="form-control" rows="3" placeholder="Masukkan alamat lengkap tujuan pengantaran..."></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Estimasi Jadwal Pengambilan/Pengantaran (Opsional)</label>
                            <input type="date" name="jadwal" class="form-control" min="<?= date('Y-m-d') ?>" value="<?= sanitize($_POST['jadwal'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Catatan Tambahan untuk Petani (Opsional)</label>
                            <textarea name="catatan" class="form-control" rows="2" placeholder="Contoh: Tolong pilihkan buah yang matang di pohon..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block btn-lg" onclick="return confirm('Buat pesanan sekarang?')">Buat Pesanan & Bayar</button>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
