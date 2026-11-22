<?php
if (!defined('BASE_URL')) {
    if (!defined('BASE_URL')) define('BASE_URL', '/RESPONSI_PEMWEB1/PanenLokal');
}
if (!defined('SITE_NAME')) {
    if (!defined('SITE_NAME')) define('SITE_NAME', 'PanenLokal');
}

$host     = 'localhost';
$user     = 'root';
$password = '';
$database = 'panenlokal_db';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die('Koneksi database gagal: ' . $conn->connect_error);
}

$conn->set_charset('utf8mb4');
?>
