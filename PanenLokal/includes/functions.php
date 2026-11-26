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

function validate_uploaded_file($file, $allowed_map, $max_size, $format_label) {
    if (!isset($file) || !is_array($file)) {
        return 'Berkas tidak ditemukan.';
    }
    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
        return 'Ukuran file maksimal 2MB.';
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return 'Gagal mengunggah berkas (kode error: ' . (int)$file['error'] . ').';
    }
    if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return 'Berkas unggahan tidak valid.';
    }
    if ($file['size'] <= 0 || $file['size'] > $max_size) {
        return 'Ukuran file maksimal 2MB dan tidak boleh kosong.';
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!array_key_exists($ext, $allowed_map)) {
        return 'Format file harus ' . $format_label . '.';
    }

    $finfo = function_exists('finfo_open') ? finfo_open(FILEINFO_MIME_TYPE) : false;
    if (!$finfo) {
        return 'Server tidak dapat memverifikasi tipe file.';
    }
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!$mime || !in_array($mime, $allowed_map[$ext], true)) {
        return 'Isi file tidak cocok dengan ekstensi .' . $ext . '. Pastikan file benar-benar berformat ' . $format_label . '.';
    }

    return null;
}

function csrf_token() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . sanitize(csrf_token()) . '">';
}

function verify_csrf() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $token = $_POST['csrf_token'] ?? '';
    return !empty($_SESSION['csrf_token']) && is_string($token) && hash_equals($_SESSION['csrf_token'], $token);
}

function get_farmer_profile($conn, $user_id) {
    $stmt = $conn->prepare("SELECT id, nama_usaha, status_verifikasi FROM farmer_profiles WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $fp = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $fp ?: ['id' => 0, 'nama_usaha' => '', 'status_verifikasi' => 'menunggu'];
}
?>
