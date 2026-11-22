<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
if (!defined('BASE_URL')) define('BASE_URL', '/RESPONSI_PEMWEB1/PanenLokal');
if (!defined('SITE_NAME')) define('SITE_NAME', 'PanenLokal');
$page_title = 'Pembayaran Pesanan';
cek_role('pembeli');

$buyer_id = $_SESSION['user_id'];
$order_id = (int)($_GET['id'] ?? 0);
if ($order_id <= 0) {
    header('Location: ' . BASE_URL . '/pembeli/pesanan.php');
    exit;
}

// Cek order milik pembeli
$stmt = $conn->prepare("
    SELECT o.*, p.id AS payment_id, p.bukti_pembayaran, p.status_pembayaran, p.catatan_admin
    FROM orders o
    LEFT JOIN payments p ON p.order_id = o.id
    WHERE o.id = ? AND o.buyer_id = ?
");
$stmt->bind_param("ii", $order_id, $buyer_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header('Location: ' . BASE_URL . '/pembeli/pesanan.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['bukti_pembayaran']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
        $max_size = 2 * 1024 * 1024; // 2MB

        if (!in_array($ext, $allowed)) {
            $error = 'Format file bukti transfer harus JPG, PNG, atau PDF.';
        } elseif ($_FILES['bukti_pembayaran']['size'] > $max_size) {
            $error = 'Ukuran file bukti transfer maksimal 2MB.';
        } else {
            $target_dir = __DIR__ . '/../uploads/payments/';
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $filename = 'pay_' . uniqid() . '.' . $ext;
            $target = $target_dir . $filename;

            if (move_uploaded_file($_FILES['bukti_pembayaran']['tmp_name'], $target)) {
                // Update payment & order status
                $up_pay = $conn->prepare("
                    UPDATE payments 
                    SET bukti_pembayaran = ?, status_pembayaran = 'menunggu', nominal = ?
                    WHERE order_id = ?
                ");
                $up_pay->bind_param("sdi", $filename, $order['total_amount'], $order_id);
                $up_pay->execute();

                $up_ord = $conn->prepare("UPDATE orders SET status = 'menunggu_verifikasi' WHERE id = ?");
                $up_ord->bind_param("i", $order_id);
                $up_ord->execute();

                $_SESSION['flash'] = 'Bukti pembayaran berhasil dikirim! Menunggu verifikasi admin.';
                $_SESSION['flash_type'] = 'success';
                header('Location: ' . BASE_URL . '/pembeli/detail_pesanan.php?id=' . $order_id);
                exit;
            } else {
                $error = 'Gagal menyimpan file bukti transfer ke server.';
            }
        }
    } else {
        $error = 'Silakan pilih file bukti pembayaran.';
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
            <a href="<?= BASE_URL ?>/pembeli/detail_pesanan.php?id=<?= $order_id ?>" class="btn btn-secondary btn-sm mb-2">← Kembali ke Detail</a>
            <h1>💳 Pembayaran Pesanan #<?= sanitize($order['kode_pesanan']) ?></h1>
        </div>

        <?php if ($error): ?><?= alert($error, 'danger') ?><?php endif; ?>

        <div class="grid grid-2">
            <!-- Instruksi Transfer -->
            <div class="card card-body">
                <h3>Instruksi Transfer Bank</h3>
                <p class="text-muted">Silakan lakukan transfer pembayaran manual sesuai total tagihan di bawah ini:</p>
                
                <div style="background:var(--green-bg); padding:1.2rem; border-radius:var(--radius); border-left:4px solid var(--green-main); margin-bottom:1rem;">
                    <div style="font-size:.9rem; color:var(--gray-700);">Total Tagihan:</div>
                    <div style="font-size:1.8rem; font-weight:800; color:var(--green-dark);"><?= format_rupiah($order['total_amount']) ?></div>
                </div>

                <div class="table-card">
                    <table>
                        <tr>
                            <td><strong>Bank Panen Lokal</strong></td>
                            <td>123-456-7890</td>
                        </tr>
                        <tr>
                            <td><strong>Atas Nama</strong></td>
                            <td>Koperasi Tani PanenLokal</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Upload Bukti -->
            <div class="card card-body">
                <h3>Unggah Bukti Transfer</h3>
                <?php if ($order['status_pembayaran'] === 'ditolak'): ?>
                    <div class="alert alert-danger">
                        <strong>Catatan Admin:</strong> <?= sanitize($order['catatan_admin'] ?: 'Bukti ditolak.') ?>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group mt-2">
                        <label class="form-label">Pilih Berkas Bukti Transfer (JPG / PNG / PDF, Maks. 2MB)</label>
                        <div class="upload-area" id="upload-area">
                            <div style="font-size:2.5rem; margin-bottom:.5rem;">📄</div>
                            <div id="upload-info">Klik di sini untuk memilih berkas bukti transfer</div>
                            <input type="file" id="bukti_pembayaran" name="bukti_pembayaran" accept=".jpg,.jpeg,.png,.pdf" required>
                        </div>
                    </div>

                    <?php if ($order['bukti_pembayaran']): ?>
                        <div class="mb-2">
                            <small class="text-muted">Bukti sebelumnya:</small><br>
                            <a href="<?= BASE_URL ?>/uploads/payments/<?= sanitize($order['bukti_pembayaran']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">Lihat Berkas Terkirim</a>
                        </div>
                    <?php endif; ?>

                    <button type="submit" class="btn btn-primary btn-block btn-lg" onclick="return confirm('Kirim bukti pembayaran ini?')">Kirim Bukti Pembayaran</button>
                </form>
            </div>
        </div>
    </main>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
