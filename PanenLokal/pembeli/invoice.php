<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
if (!defined('BASE_URL')) define('BASE_URL', '/RESPONSI_PEMWEB1/PanenLokal');
if (!defined('SITE_NAME')) define('SITE_NAME', 'PanenLokal');
$page_title = 'Invoice Pesanan';
cek_login();

$order_id = (int)($_GET['id'] ?? 0);
$user = get_user();

// Verifikasi hak akses: pembeli pemilik pesanan atau admin
$stmt = $conn->prepare("
    SELECT o.*, u.nama_lengkap AS nama_pembeli, u.no_hp AS hp_pembeli, u.alamat AS alamat_pembeli,
           d.metode, d.pickup_point_id, d.alamat_tujuan, d.jadwal,
           pp.nama_titik, pp.alamat AS alamat_titik,
           p.status_pembayaran, p.verified_at
    FROM orders o
    JOIN users u ON u.id = o.buyer_id
    LEFT JOIN deliveries d ON d.order_id = o.id
    LEFT JOIN pickup_points pp ON pp.id = d.pickup_point_id
    LEFT JOIN payments p ON p.order_id = o.id
    WHERE o.id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

if ($user['role'] !== 'admin' && $order['buyer_id'] != $user['id']) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// Ambil item produk
$q_items = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$q_items->bind_param("i", $order_id);
$q_items->execute();
$items = $q_items->get_result();

require_once __DIR__ . '/../includes/header.php';
?>
<div class="container section">
    <div style="display:flex; justify-content:space-between; align-items:center; max-width:750px; margin:0 auto 1rem;" class="no-print">
        <a href="<?= $user['role'] === 'admin' ? BASE_URL . '/admin/pesanan.php' : BASE_URL . '/pembeli/detail_pesanan.php?id=' . $order_id ?>" class="btn btn-secondary btn-sm">← Kembali</a>
        <button onclick="window.print()" class="btn btn-primary btn-sm">🖨️ Cetak / Simpan PDF</button>
    </div>

    <div class="invoice-box">
        <div class="invoice-header">
            <div>
                <span style="font-size:1.8rem; font-weight:800; color:var(--green-main);">🌿 PanenLokal</span>
                <p style="font-size:.85rem; color:var(--gray-500); margin:0;">Platform Pre-Order & Penyelamatan Hasil Tani</p>
                <div style="font-size:.85rem; color:var(--gray-700); margin-top:.4rem;">Mendukung SDG 2 — Zero Hunger</div>
            </div>
            <div style="text-align:right;">
                <div class="invoice-code"><?= sanitize($order['kode_pesanan']) ?></div>
                <div style="font-size:.85rem; color:var(--gray-500);">Tanggal: <?= format_tanggal($order['tanggal_pesanan'], 'd F Y H:i') ?></div>
                <span class="badge badge-success mt-1">LUNAS / DIVERIFIKASI</span>
            </div>
        </div>

        <div class="grid grid-2 mb-3">
            <div>
                <strong style="color:var(--gray-700);">Ditujukan Kepada:</strong>
                <div><strong><?= sanitize($order['nama_pembeli']) ?></strong></div>
                <div style="font-size:.9rem; color:var(--gray-700);"><?= sanitize($order['hp_pembeli'] ?: '-') ?></div>
            </div>
            <div>
                <strong style="color:var(--gray-700);">Metode Pemenuhan:</strong>
                <div>Metode: <strong><?= ucfirst($order['metode'] ?? 'ambil') ?></strong></div>
                <div style="font-size:.9rem; color:var(--gray-700);">
                    <?php if (($order['metode'] ?? '') === 'ambil'): ?>
                        Lokasi: <?= sanitize($order['nama_titik'] ?: '-') ?>
                    <?php else: ?>
                        Tujuan: <?= sanitize($order['alamat_tujuan'] ?: '-') ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Deskripsi Komoditas</th>
                    <th>Jumlah</th>
                    <th>Harga Snapshot</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($it = $items->fetch_assoc()): ?>
                <tr>
                    <td>
                        <strong><?= sanitize($it['nama_produk_snapshot']) ?></strong>
                        <?php if ($it['is_rescue']): ?>
                            <span class="badge badge-rescue">Rescue</span>
                        <?php endif; ?>
                    </td>
                    <td><?= (float)$it['quantity'] ?> <?= sanitize($it['satuan_snapshot']) ?></td>
                    <td><?= format_rupiah($it['harga_satuan']) ?></td>
                    <td class="text-right"><?= format_rupiah($it['quantity'] * $it['harga_satuan']) ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" class="text-right" style="font-size:1.1rem; padding-top:1rem;">Total Pembayaran:</th>
                    <th class="text-right" style="font-size:1.3rem; color:var(--green-dark); padding-top:1rem;"><?= format_rupiah($order['total_amount']) ?></th>
                </tr>
            </tfoot>
        </table>

        <div style="margin-top:2rem; padding-top:1rem; border-top:1px dashed var(--gray-300); text-align:center; font-size:.85rem; color:var(--gray-500);">
            Terima kasih telah berbelanja hasil tani lokal dan mendukung ketahanan pangan bersama PanenLokal.
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
