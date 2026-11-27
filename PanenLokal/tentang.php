<?php
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
if (!defined('BASE_URL')) define('BASE_URL', '/RESPONSI_PEMWEB1/PanenLokal');
if (!defined('SITE_NAME')) define('SITE_NAME', 'PanenLokal');
$page_title = 'Tentang PanenLokal';

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="page-header">
    <div class="container">
        <h1>Tentang PanenLokal</h1>
        <div class="breadcrumb">Beranda / Tentang</div>
    </div>
</div>

<section class="section">
    <div class="container" style="max-width:860px;">
        <div class="card card-body mb-3" style="text-align:center;background:var(--green-bg);">
            <div style="font-size:4rem;margin-bottom:1rem;">🌿</div>
            <h2 style="color:var(--green-dark);">PanenLokal</h2>
            <p style="font-size:1.1rem;color:var(--gray-700);">Platform Pre-Order Hasil Tani dan Penyelamatan Pangan (Food Loss Rescue)</p>
            <div class="d-flex gap-2 justify-center flex-wrap mt-2">
                <span class="badge badge-primary" style="font-size:.9rem;padding:.5rem 1rem;">SDG 2 — Zero Hunger</span>
                <span class="badge badge-success" style="font-size:.9rem;padding:.5rem 1rem;">PHP Native + MySQL</span>
                <span class="badge badge-secondary" style="font-size:.9rem;padding:.5rem 1rem;">Praktikum Pemweb 1</span>
            </div>
        </div>

        <h3>🎯 Misi Kami</h3>
        <p>PanenLokal hadir untuk menjembatani petani lokal dan konsumen secara langsung. Kami menyediakan platform katalog hasil tani, sistem <em>pre-order</em> sebelum masa panen, dan kanal <em>Food Rescue</em> untuk produk yang perlu segera diserap pasar.</p>

        <h3>🌍 Relevansi SDG 2 — Zero Hunger</h3>
        <p>Salah satu penyebab kelaparan adalah ketidakefisienan rantai distribusi pangan. Petani lokal sering kesulitan memasarkan hasil panen, sementara hasil panen yang tidak terserap berisiko terbuang. PanenLokal berkontribusi pada SDG 2 dengan:</p>
        <ul style="margin-bottom:1rem;">
            <li>Memberikan petani akses langsung ke konsumen tanpa perantara berlebihan</li>
            <li>Mengurangi <em>food loss</em> melalui fitur Food Rescue</li>
            <li>Membantu petani memperkirakan permintaan melalui sistem pre-order</li>
            <li>Meningkatkan pendapatan petani lokal</li>
        </ul>

        <h3>⚙️ Fitur Utama</h3>
        <div class="grid grid-2">
            <div class="card card-body">
                <h4>🌾 Untuk Petani</h4>
                <ul style="font-size:.9rem;">
                    <li>Kelola profil usaha tani</li>
                    <li>Daftarkan lahan dan komoditas</li>
                    <li>Buat batch panen dengan estimasi</li>
                    <li>Aktifkan Food Rescue untuk produk berlebih</li>
                    <li>Pantau pesanan dan pendapatan</li>
                </ul>
            </div>
            <div class="card card-body">
                <h4>🛒 Untuk Pembeli</h4>
                <ul style="font-size:.9rem;">
                    <li>Browse katalog hasil tani lokal</li>
                    <li>Pre-order sebelum tanggal panen</li>
                    <li>Dapatkan produk Food Rescue dengan harga hemat</li>
                    <li>Pilih metode ambil atau antar</li>
                    <li>Berikan ulasan dan rating</li>
                </ul>
            </div>
        </div>

        <h3 class="mt-3">🛠️ Teknologi</h3>
        <div class="table-responsive table-card mt-2">
            <table>
                <thead><tr><th>Lapisan</th><th>Teknologi</th><th>Fungsi</th></tr></thead>
                <tbody>
                    <tr><td>Frontend</td><td>HTML5 + CSS3</td><td>Struktur dan tampilan halaman</td></tr>
                    <tr><td>Interaktif</td><td>JavaScript</td><td>Validasi form, countdown, preview harga</td></tr>
                    <tr><td>Backend</td><td>PHP Native</td><td>Logika bisnis, CRUD, session</td></tr>
                    <tr><td>Database</td><td>MySQL/MariaDB</td><td>Penyimpanan data relasional</td></tr>
                    <tr><td>Server</td><td>Apache / PHP Web Server</td><td>Lingkungan server pengembangan dan produksi</td></tr>
                </tbody>
            </table>
        </div>

        <div class="card card-body mt-3 bg-green-gradient text-center">
            <h3 style="color:#fff;">Bergabunglah Bersama Kami</h3>
            <p style="opacity:.9;">Bersama kita wujudkan sistem pangan lokal yang lebih adil dan berkelanjutan.</p>
            <div class="d-flex gap-2 flex-wrap justify-center mt-1">
                <a href="<?= BASE_URL ?>/register.php" class="btn btn-hero-primary">Daftar Sekarang</a>
                <a href="<?= BASE_URL ?>/katalog.php" class="btn btn-outline-primary">Lihat Katalog</a>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
