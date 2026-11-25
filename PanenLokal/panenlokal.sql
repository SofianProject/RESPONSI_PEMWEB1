-- =============================================
-- PanenLokal Database
-- SDG 2 Zero Hunger | Responsi Pemweb 1
-- =============================================

DROP DATABASE IF EXISTS panenlokal_db;

CREATE DATABASE IF NOT EXISTS panenlokal_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE panenlokal_db;

-- 1. Akun seluruh role
CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_lengkap VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','petani','pembeli') NOT NULL DEFAULT 'pembeli',
    no_hp VARCHAR(20) NULL,
    alamat TEXT NULL,
    status_akun ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Profil usaha petani
CREATE TABLE IF NOT EXISTS farmer_profiles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    nama_usaha VARCHAR(120) NOT NULL,
    deskripsi TEXT NULL,
    status_verifikasi ENUM('menunggu','terverifikasi','ditolak') NOT NULL DEFAULT 'menunggu',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_fp_user FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 3. Lahan petani
CREATE TABLE IF NOT EXISTS lands (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    farmer_id BIGINT UNSIGNED NOT NULL,
    nama_lahan VARCHAR(120) NOT NULL,
    lokasi TEXT NOT NULL,
    luas_m2 DECIMAL(10,2) NULL,
    keterangan TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_lands_farmer (farmer_id),
    CONSTRAINT fk_lands_farmer FOREIGN KEY (farmer_id) REFERENCES farmer_profiles(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 4. Komoditas
CREATE TABLE IF NOT EXISTS commodities (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    farmer_id BIGINT UNSIGNED NOT NULL,
    nama VARCHAR(120) NOT NULL,
    kategori VARCHAR(60) NOT NULL,
    deskripsi TEXT NULL,
    satuan VARCHAR(20) NOT NULL DEFAULT 'kg',
    gambar VARCHAR(255) NULL,
    status_komoditas ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_commodities_farmer (farmer_id),
    INDEX idx_commodities_status (status_komoditas),
    CONSTRAINT fk_commodities_farmer FOREIGN KEY (farmer_id) REFERENCES farmer_profiles(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 5. Batch panen
CREATE TABLE IF NOT EXISTS harvest_batches (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    commodity_id BIGINT UNSIGNED NOT NULL,
    land_id BIGINT UNSIGNED NULL,
    tanggal_estimasi_panen DATE NOT NULL,
    estimasi_jumlah DECIMAL(10,2) NOT NULL,
    stok_tersedia DECIMAL(10,2) NOT NULL,
    harga_normal DECIMAL(12,2) NOT NULL,
    rescue_aktif TINYINT(1) NOT NULL DEFAULT 0,
    harga_rescue DECIMAL(12,2) NULL,
    batas_rescue DATETIME NULL,
    status_batch ENUM('dibuka','siap_panen','habis','ditutup','dibatalkan') NOT NULL DEFAULT 'dibuka',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_batches_commodity (commodity_id),
    INDEX idx_batches_land (land_id),
    INDEX idx_batches_status_date (status_batch, tanggal_estimasi_panen),
    INDEX idx_batches_rescue (rescue_aktif, batas_rescue),
    CONSTRAINT fk_batches_commodity FOREIGN KEY (commodity_id) REFERENCES commodities(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_batches_land FOREIGN KEY (land_id) REFERENCES lands(id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

-- 6. Keranjang pembeli
CREATE TABLE IF NOT EXISTS carts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    buyer_id BIGINT UNSIGNED NOT NULL UNIQUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_carts_buyer FOREIGN KEY (buyer_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 7. Item keranjang
CREATE TABLE IF NOT EXISTS cart_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cart_id BIGINT UNSIGNED NOT NULL,
    batch_id BIGINT UNSIGNED NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_cart_batch (cart_id, batch_id),
    INDEX idx_cart_items_batch (batch_id),
    CONSTRAINT fk_cart_items_cart FOREIGN KEY (cart_id) REFERENCES carts(id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_cart_items_batch FOREIGN KEY (batch_id) REFERENCES harvest_batches(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 8. Pesanan
CREATE TABLE IF NOT EXISTS orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    buyer_id BIGINT UNSIGNED NOT NULL,
    kode_pesanan VARCHAR(30) NOT NULL UNIQUE,
    tanggal_pesanan DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    status ENUM('menunggu_pembayaran','menunggu_verifikasi','dibayar','diproses','siap_diambil','dikirim','selesai','dibatalkan') NOT NULL DEFAULT 'menunggu_pembayaran',
    catatan TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_orders_buyer (buyer_id),
    INDEX idx_orders_status_date (status, tanggal_pesanan),
    CONSTRAINT fk_orders_buyer FOREIGN KEY (buyer_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 9. Detail item pesanan (snapshot)
CREATE TABLE IF NOT EXISTS order_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    batch_id BIGINT UNSIGNED NOT NULL,
    nama_produk_snapshot VARCHAR(120) NOT NULL,
    satuan_snapshot VARCHAR(20) NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    harga_satuan DECIMAL(12,2) NOT NULL,
    is_rescue TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_order_items_order (order_id),
    INDEX idx_order_items_batch (batch_id),
    INDEX idx_order_items_rescue (is_rescue),
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_order_items_batch FOREIGN KEY (batch_id) REFERENCES harvest_batches(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 10. Pembayaran
CREATE TABLE IF NOT EXISTS payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL UNIQUE,
    nominal DECIMAL(12,2) NOT NULL,
    bukti_pembayaran VARCHAR(255) NULL,
    status_pembayaran ENUM('menunggu','terverifikasi','ditolak') NOT NULL DEFAULT 'menunggu',
    verified_by BIGINT UNSIGNED NULL,
    verified_at DATETIME NULL,
    catatan_admin TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_payments_status (status_pembayaran),
    CONSTRAINT fk_payments_order FOREIGN KEY (order_id) REFERENCES orders(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_payments_verifier FOREIGN KEY (verified_by) REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

-- 11. Titik pengambilan
CREATE TABLE IF NOT EXISTS pickup_points (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_titik VARCHAR(120) NOT NULL,
    alamat TEXT NOT NULL,
    deskripsi TEXT NULL,
    status_aktif TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 12. Pengiriman / pemenuhan pesanan
CREATE TABLE IF NOT EXISTS deliveries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL UNIQUE,
    metode ENUM('antar','ambil') NOT NULL,
    pickup_point_id BIGINT UNSIGNED NULL,
    alamat_tujuan TEXT NULL,
    jadwal DATETIME NULL,
    status ENUM('menunggu','diproses','siap_diambil','dikirim','selesai') NOT NULL DEFAULT 'menunggu',
    catatan TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_deliveries_pickup_point (pickup_point_id),
    CONSTRAINT fk_deliveries_order FOREIGN KEY (order_id) REFERENCES orders(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_deliveries_pickup_point FOREIGN KEY (pickup_point_id) REFERENCES pickup_points(id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

-- 13. Ulasan pembeli
CREATE TABLE IF NOT EXISTS reviews (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_item_id BIGINT UNSIGNED NOT NULL UNIQUE,
    buyer_id BIGINT UNSIGNED NOT NULL,
    rating TINYINT UNSIGNED NOT NULL,
    komentar TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_reviews_buyer (buyer_id),
    CONSTRAINT fk_reviews_order_item FOREIGN KEY (order_item_id) REFERENCES order_items(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_reviews_buyer FOREIGN KEY (buyer_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- =============================================
-- Data awal
-- =============================================

-- Admin (password: admin123)
-- Hash di-generate dengan: password_hash('admin123', PASSWORD_DEFAULT)
INSERT IGNORE INTO users (id, nama_lengkap, username, password_hash, role, no_hp, alamat, status_akun)
VALUES 
    (1, 'Administrator', 'admin', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B/u3AgG', 'admin', '08123456789', 'Kantor PanenLokal Pusat', 'aktif'),
    (2, 'Pak Budi Pranoto', 'petani1', '$2y$10$IsvKjdvD8fhXkl/etyoyVeC1Yh9l2PCGjgscubNs5jrBF6oCIHi3i', 'petani', '08571234567', 'Desa Sukamaju RT 02/05', 'aktif'),
    (3, 'Siti Rahmawati', 'pembeli1', '$2y$10$IsvKjdvD8fhXkl/etyoyVeC1Yh9l2PCGjgscubNs5jrBF6oCIHi3i', 'pembeli', '08781234567', 'Jl. Kenanga No. 12, Kota Baru', 'aktif');

-- Profil usaha untuk petani
INSERT IGNORE INTO farmer_profiles (id, user_id, nama_usaha, deskripsi, status_verifikasi)
VALUES (1, 2, 'Kelompok Tani Makmur Sejahtera', 'Komunitas petani lokal berfokus pada budidaya sayuran segar dan pertanian organik berkelanjutan.', 'terverifikasi');

-- Lahan pertanian
INSERT IGNORE INTO lands (id, farmer_id, nama_lahan, lokasi, luas_m2, keterangan)
VALUES 
    (1, 1, 'Kebun Lereng Subur', 'Lereng Gunung Ungaran Blok A', 2500.00, 'Tanah vulkanik subur untuk aneka sayuran buah dan umbi.'),
    (2, 1, 'Sawah Organik Mandiri', 'Lembah Hijau Blok C', 4000.00, 'Pengairan mata air alami bebas pestisida sintetis.');

-- Komoditas hasil tani
INSERT IGNORE INTO commodities (id, farmer_id, nama, kategori, deskripsi, satuan, gambar, status_komoditas)
VALUES 
    (1, 1, 'Tomat Ceri Merah', 'Sayuran Buah', 'Tomat ceri manis segar dipetik pada tingkat kematangan optimal.', 'kg', 'prod_tomat.jpg', 'aktif'),
    (2, 1, 'Bayam Hijau Organik', 'Sayuran Hijau', 'Bayam segar hidroponik dan organik, kaya zat besi dan serat.', 'ikat', 'prod_bayam.jpg', 'aktif'),
    (3, 1, 'Cabai Rawit Pedas', 'Rempah & Herbal', 'Cabai rawit merah pedas alami, petikan pagi hari langsung dari ladang.', 'kg', 'prod_cabai.jpg', 'aktif'),
    (4, 1, 'Wortel Segar Brastagi', 'Umbi-umbian', 'Wortel renyah manis kaya vitamin A, hasil panen dataran tinggi.', 'kg', 'prod_wortel.jpg', 'aktif'),
    (5, 1, 'Pisang Raja Manis', 'Buah-buahan', 'Pisang raja matang pohon, harum dan legit tanpa bahan karbit.', 'sisir', 'prod_pisang.jpg', 'aktif');

-- Batch panen (Pre-order & Food Rescue)
INSERT IGNORE INTO harvest_batches (id, commodity_id, land_id, tanggal_estimasi_panen, estimasi_jumlah, stok_tersedia, harga_normal, rescue_aktif, harga_rescue, batas_rescue, status_batch)
VALUES 
    (1, 1, 1, DATE_ADD(CURDATE(), INTERVAL 7 DAY), 100.00, 85.00, 18000.00, 0, NULL, NULL, 'dibuka'),
    (2, 2, 2, DATE_ADD(CURDATE(), INTERVAL 3 DAY), 150.00, 120.00, 5000.00, 1, 3500.00, DATE_ADD(NOW(), INTERVAL 2 DAY), 'dibuka'),
    (3, 3, 1, DATE_ADD(CURDATE(), INTERVAL 5 DAY), 60.00, 50.00, 45000.00, 0, NULL, NULL, 'dibuka'),
    (4, 4, 1, DATE_ADD(CURDATE(), INTERVAL 10 DAY), 120.00, 95.00, 15000.00, 1, 9000.00, DATE_ADD(NOW(), INTERVAL 3 DAY), 'dibuka'),
    (5, 5, 2, DATE_ADD(CURDATE(), INTERVAL 4 DAY), 50.00, 40.00, 25000.00, 0, NULL, NULL, 'dibuka');

-- Titik pengambilan default
INSERT IGNORE INTO pickup_points (id, nama_titik, alamat, deskripsi, status_aktif)
VALUES
    (1, 'Posko PanenLokal Pusat', 'Jl. Pertanian No. 1, Kota Baru', 'Buka Senin–Sabtu pukul 08.00–17.00', 1),
    (2, 'Pasar Tani Minggu', 'Alun-alun Kota, setiap hari Minggu', 'Buka setiap Minggu pukul 06.00–12.00', 1);

-- Keranjang pembeli awal
INSERT IGNORE INTO carts (id, buyer_id) VALUES (1, 3);
