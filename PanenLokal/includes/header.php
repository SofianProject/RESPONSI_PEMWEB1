<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!defined('BASE_URL')) {
    if (!defined('BASE_URL')) define('BASE_URL', '/RESPONSI_PEMWEB1/PanenLokal');
}
if (!defined('SITE_NAME')) {
    if (!defined('SITE_NAME')) define('SITE_NAME', 'PanenLokal');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="PanenLokal — Platform Pre-Order Hasil Tani Lokal dan Food Rescue. Mendukung SDG 2 Zero Hunger.">
    <title><?= isset($page_title) ? sanitize($page_title) . ' — ' : '' ?><?= SITE_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
</head>
<body>
