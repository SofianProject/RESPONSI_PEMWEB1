<?php
date_default_timezone_set('Asia/Jakarta');

$httpHost = strtolower(trim((string)($_SERVER['HTTP_HOST'] ?? '')));
if (strpos($httpHost, '[') === 0) {
    $endBracket = strpos($httpHost, ']');
    $hostName = $endBracket !== false ? substr($httpHost, 1, $endBracket - 1) : $httpHost;
} else {
    $hostName = explode(':', $httpHost)[0];
}

$isLocal = (PHP_SAPI === 'cli')
    || in_array($hostName, ['localhost', '127.0.0.1', '::1', ''], true)
    || substr($hostName, -6) === '.local'
    || substr($hostName, -5) === '.test';

$localConfigFile = __DIR__ . '/config.local.php';
$prodConfigFile  = __DIR__ . '/config.production.php';

if ($isLocal) {
    $appConfig = file_exists($localConfigFile) ? require $localConfigFile : [
        'db_host'   => 'localhost',
        'db_user'   => 'root',
        'db_pass'   => '',
        'db_name'   => 'panenlokal_db',
        'base_url'  => '/RESPONSI_PEMWEB1/PanenLokal',
        'site_name' => 'PanenLokal',
        'debug'     => true,
    ];
} else {
    if (!file_exists($prodConfigFile)) {
        http_response_code(500);
        die('Konfigurasi production belum ditemukan. Pastikan file config/config.production.php sudah dibuat.');
    }
    $appConfig = require $prodConfigFile;
}

if (!defined('BASE_URL')) {
    define('BASE_URL', $appConfig['base_url'] ?? '');
}
if (!defined('SITE_NAME')) {
    define('SITE_NAME', $appConfig['site_name'] ?? 'PanenLokal');
}

$host     = $appConfig['db_host'] ?? 'localhost';
$user     = $appConfig['db_user'] ?? '';
$password = $appConfig['db_pass'] ?? '';
$database = $appConfig['db_name'] ?? '';
$isDebug  = !empty($appConfig['debug']);

if (!$isDebug) {
    @ini_set('display_errors', '0');
}

try {
    $conn = new mysqli($host, $user, $password, $database);
} catch (mysqli_sql_exception $e) {
    error_log('Koneksi database gagal: ' . $e->getMessage());
    http_response_code(500);
    if ($isDebug) {
        die('Koneksi database gagal: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
    }
    die('Maaf, koneksi ke server database belum berhasil. Silakan periksa konfigurasi database di hosting.');
}

if ($conn->connect_error) {
    error_log('Koneksi database gagal: ' . $conn->connect_error);
    http_response_code(500);
    if ($isDebug) {
        die('Koneksi database gagal: ' . htmlspecialchars($conn->connect_error, ENT_QUOTES, 'UTF-8'));
    }
    die('Maaf, koneksi ke server database belum berhasil. Silakan periksa konfigurasi database di hosting.');
}

$conn->set_charset('utf8mb4');
@$conn->query("SET time_zone = '+07:00'");
?>
