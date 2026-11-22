<?php
function format_rupiah($angka) {
    return 'Rp ' . number_format((float)$angka, 0, ',', '.');
}

function generate_kode_pesanan() {
    return 'PL-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

function hitung_diskon($harga_normal, $harga_rescue) {
    if ($harga_normal <= 0) return 0;
    return round((($harga_normal - $harga_rescue) / $harga_normal) * 100);
}

function sanitize($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

function truncate_text($str, $max = 100) {
    if (mb_strlen($str) <= $max) return $str;
    return mb_substr($str, 0, $max) . '...';
}

function get_status_label($status) {
    $map = [
        'menunggu_pembayaran' => ['label' => 'Menunggu Pembayaran', 'class' => 'badge-warning'],
        'menunggu_verifikasi' => ['label' => 'Menunggu Verifikasi', 'class' => 'badge-info'],
        'dibayar'             => ['label' => 'Dibayar',             'class' => 'badge-success'],
        'diproses'            => ['label' => 'Diproses',            'class' => 'badge-primary'],
        'siap_diambil'        => ['label' => 'Siap Diambil',        'class' => 'badge-primary'],
        'dikirim'             => ['label' => 'Dikirim',             'class' => 'badge-info'],
        'selesai'             => ['label' => 'Selesai',             'class' => 'badge-dark'],
        'dibatalkan'          => ['label' => 'Dibatalkan',          'class' => 'badge-danger'],
    ];
    return $map[$status] ?? ['label' => ucfirst($status), 'class' => 'badge-secondary'];
}

function get_rescue_label($rescue_aktif, $batas_rescue, $harga_rescue) {
    if (!$rescue_aktif || !$harga_rescue) return false;
    if ($batas_rescue && strtotime($batas_rescue) < time()) return false;
    return true;
}

function format_tanggal($tanggal, $format = 'd M Y') {
    if (!$tanggal) return '-';
    return date($format, strtotime($tanggal));
}

function alert($pesan, $tipe = 'success') {
    return '<div class="alert alert-' . $tipe . ' alert-dismissible">'
         . sanitize($pesan)
         . '<button type="button" class="alert-close" onclick="this.parentElement.remove()">×</button>'
         . '</div>';
}

function redirect($url) {
    header('Location: ' . $url);
    exit;
}
?>
