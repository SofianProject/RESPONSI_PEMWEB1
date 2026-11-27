<?php
/**
 * Contoh Konfigurasi Production (InfinityFree / Hosting)
 * Salin file ini menjadi config.production.php lalu isi sesuai data MySQL dari panel InfinityFree.
 * Jangan commit config.production.php ke GitHub!
 */
return [
    'db_host'   => 'sqlXXX.infinityfree.com',      // Ganti dengan MySQL Hostname dari InfinityFree
    'db_user'   => 'if0_XXXXXXXX',                 // Ganti dengan MySQL Username dari InfinityFree
    'db_pass'   => 'PASSWORD_MYSQL_ANDA',          // Ganti dengan MySQL Password dari InfinityFree
    'db_name'   => 'if0_XXXXXXXX_panenlokal',      // Ganti dengan Nama Database dari InfinityFree
    'base_url'  => '',                             // Kosongkan ('') jika file di-upload langsung ke folder htdocs/
    'site_name' => 'PanenLokal',
    'debug'     => false,
];
