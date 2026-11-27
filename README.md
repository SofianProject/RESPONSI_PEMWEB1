# PanenLokal 🌿

> **Platform Pre-Order Hasil Tani Lokal dan Penyelamatan Pangan (Food Rescue)**  
> **Tema SDG 2 — Zero Hunger**  
> Proyek Akhir Responsi Praktikum Pemrograman Web I

---

## 📌 Deskripsi Proyek

**PanenLokal** adalah aplikasi web berbasis PHP Native dan MySQL/MariaDB yang menghubungkan petani lokal secara langsung dengan konsumen. Melalui aplikasi ini, pembeli dapat memesan hasil pertanian sebelum masa panen (*pre-order*), sementara petani dapat menyalurkan hasil panen berlebih atau mendekati batas kesegaran melalui kanal **Food Rescue** dengan harga hemat.

---

## 🌍 Latar Belakang & Tujuan Proyek

### Latar Belakang
Salah satu penyebab permasalahan ketahanan pangan dan rendahnya pendapatan petani adalah rantai distribusi hasil pertanian yang panjang serta ketidaksesuaian antara pasokan panen dan permintaan pasar. Petani kerap kesulitan memasarkan hasil panen secara langsung, sedangkan hasil panen yang tidak segera terserap berisiko terbuang (*food loss*).

### Tujuan Proyek
PanenLokal dibangun untuk mendukung **SDG 2 — Zero Hunger** dengan tujuan:
1. Memberikan akses pemasaran langsung dari petani lokal ke konsumen tanpa perantara berlebihan.
2. Membantu petani merencanakan distribusi hasil panen melalui sistem **pre-order batch** berdasarkan estimasi tanggal panen dan kuota stok.
3. Menekan pemborosan pangan (*food loss*) melalui kanal **Food Rescue** dengan penawaran diskon dan batas waktu yang jelas.
4. Menyediakan alur transaksi yang terstruktur, mulai dari keranjang belanja, pemilihan pengantaran/titik pengambilan, verifikasi pembayaran, hingga invoice digital dan ulasan kesegaran.

---

## 🎯 Fitur & Modul yang Diimplementasikan

Aplikasi ini memiliki 3 peran pengguna (**Admin**, **Petani**, dan **Pembeli**) dengan **9 modul fungsional**:

1. **Modul 1 — Autentikasi & Manajemen Pengguna Multi-Role**
   - Registrasi mandiri sebagai Pembeli atau Petani (`register.php`), login terpadu (`login.php`), proteksi role (`cek_role()`), serta hashing password menggunakan `password_hash()` dan verifikasi `password_verify()`.
2. **Modul 2 — Manajemen Lahan & Kalender Estimasi Panen**
   - Pengelolaan data lahan pertanian petani (nama lahan, lokasi, luas m², keterangan) serta tampilan timeline jadwal estimasi panen (`petani/kalender_panen.php`).
3. **Modul 3 — Katalog Komoditas & Sistem Pre-Order Batch**
   - Pengelolaan komoditas dan batch panen petani, pencarian & filter kategori di halaman katalog (`katalog.php`) dengan pagination, serta halaman detail produk (`detail_produk.php`).
4. **Modul 4 — Kanal Food Rescue**
   - Pengelolaan promo Food Rescue oleh petani (`petani/kelola_rescue.php`) dan etalase khusus produk rescue (`food_rescue.php`) lengkap dengan persentase diskon serta *countdown timer* batas penawaran.
5. **Modul 5 — Keranjang Belanja & Checkout Terpadu**
   - Penambahan dan pembaruan kuantitas item keranjang (`pembeli/keranjang.php`) dengan kalkulasi subtotal otomatis di JavaScript, serta checkout (`pembeli/checkout.php`) berbasis transaksi database (`begin_transaction()`, `commit()`, `rollback()`) dan validasi stok.
6. **Modul 6 — Pembayaran Manual, Verifikasi Admin, & Invoice Digital**
   - Upload bukti transfer (`pembeli/pembayaran.php`), verifikasi terima/tolak oleh admin (`admin/verifikasi_pembayaran.php`, `admin/detail_pembayaran.php`), serta invoice digital siap cetak PDF (`pembeli/invoice.php`).
7. **Modul 7 — Pengiriman & Titik Pengambilan (*Pick-up Point*)**
   - Pilihan metode pemenuhan pesanan (**Ambil di Titik Pengambilan** atau **Antar ke Alamat**), serta manajemen titik pengambilan oleh admin (`admin/titik_pengambilan.php`).
8. **Modul 8 — Ulasan & Rating Kesegaran Komoditas**
   - Pemberian rating 1–5 bintang dan ulasan oleh pembeli setelah pesanan selesai (`pembeli/ulasan.php`), yang ditampilkan pada halaman detail produk.
9. **Modul 9 — Dashboard Analitik & Laporan Penyelamatan Pangan**
   - Ringkasan statistik omzet, jumlah pesanan, dan total kilogram pangan Food Rescue terselamatkan pada dashboard serta halaman laporan admin (`admin/laporan.php`) dan petani (`petani/laporan.php`).

### Keamanan Aplikasi
- **Prepared Statements (`MySQLi`):** Query dengan input pengguna menggunakan parameterized query.
- **Proteksi CSRF & XSS:** Formulir menggunakan CSRF token (`csrf_field()` / `verify_csrf()`) dan output dibersihkan dengan `sanitize()` (`htmlspecialchars`).
- **Validasi Upload:** Upload foto komoditas dan bukti bayar divalidasi ukuran, ekstensi, dan MIME type-nya menggunakan `fileinfo` (`finfo_open()`), serta folder `uploads/` dilindungi `uploads/.htaccess`.
- **Snapshot Harga & Produk:** Nama, satuan, dan harga saat transaksi disimpan ke tabel `order_items` agar invoice lama tetap akurat meskipun harga batch diperbarui.

---

## 🛠️ Teknologi yang Digunakan

| Lapisan | Teknologi | Keterangan |
|---|---|---|
| **Frontend** | HTML5, CSS3, JavaScript Native | Layout responsif, validasi form, kalkulasi keranjang, countdown timer, font `Plus Jakarta Sans` |
| **Backend** | PHP Native (PHP 8.x, `mysqli`, `fileinfo`) | Logika bisnis, autentikasi session, CRUD, tanpa framework |
| **Database** | MySQL / MariaDB (InnoDB, `utf8mb4`) | 13 tabel relasional (`users`, `farmer_profiles`, `lands`, `commodities`, `harvest_batches`, `carts`, `cart_items`, `orders`, `order_items`, `payments`, `pickup_points`, `deliveries`, `reviews`) |
| **Web Server** | Apache (XAMPP Lokal & Shared Hosting) | Konfigurasi keamanan direktori melalui `.htaccess` dan `uploads/.htaccess` |

---

## 📂 Struktur Folder Repository

```text
RESPONSI_PEMWEB1/
│
├── .gitignore                                 # Mengabaikan file konfigurasi sensitif, backup, & arsip deploy
├── README.md                                  # Dokumentasi proyek
│
├── deployment/
│   └── panenlokal_hosting.sql                 # Skema 13 tabel & data awal khusus import di hosting
│
└── PanenLokal/
    ├── .htaccess                              # Proteksi directory listing & blokir akses file sensitif
    ├── index.php                              # Beranda / Landing page
    ├── katalog.php                            # Katalog pencarian & filter komoditas
    ├── detail_produk.php                      # Detail batch panen, rating, & tambah ke keranjang
    ├── food_rescue.php                        # Kanal khusus Food Rescue
    ├── tentang.php                            # Informasi visi, misi, & SDG 2 Zero Hunger
    ├── login.php                              # Autentikasi terpadu 3 role
    ├── register.php                           # Registrasi akun pembeli & petani
    ├── logout.php                             # Mengakhiri session
    ├── panenlokal.sql                         # Skema 13 tabel & data awal untuk XAMPP lokal
    │
    ├── config/
    │   ├── koneksi.php                        # Loader konfigurasi (lokal vs production), koneksi MySQLi, & timezone WIB
    │   └── config.production.example.php      # Template contoh konfigurasi database hosting
    │   # Catatan: config.local.php & config.production.php diabaikan oleh .gitignore
    │
    ├── includes/
    │   ├── auth.php                           # Helper session, cek_login(), cek_role(), & validasi redirect
    │   ├── functions.php                      # Helper format rupiah, tanggal, sanitasi, CSRF, & validasi upload
    │   ├── header.php                         # Header HTML, meta, & pemuat CSS
    │   ├── navbar.php                         # Navigasi responsif sesuai status login & role
    │   └── footer.php                         # Footer & pemuat script JS
    │
    ├── admin/                                 # Modul Dashboard Administrator
    │   ├── index.php                          # Ringkasan statistik & antrean verifikasi
    │   ├── pengguna.php                       # Daftar & aktivasi/nonaktifkan akun
    │   ├── detail_pengguna.php                # Detail akun & verifikasi profil petani
    │   ├── verifikasi_pembayaran.php          # Daftar bukti transfer pembayaran
    │   ├── detail_pembayaran.php              # Verifikasi terima/tolak pembayaran
    │   ├── titik_pengambilan.php              # Kelola titik pengambilan (pick-up points)
    │   ├── tambah_titik.php                   # Tambah titik pengambilan
    │   ├── edit_titik.php                     # Edit titik pengambilan
    │   ├── pesanan.php                        # Pantau seluruh pesanan & ubah status
    │   └── laporan.php                        # Laporan omzet, komoditas terlaris, & dampak Food Rescue
    │
    ├── petani/                                # Modul Dashboard Petani / Produsen
    │   ├── index.php                          # Ringkasan statistik, pesanan terbaru, & batch panen
    │   ├── lahan.php                          # Daftar & hapus lahan garapan
    │   ├── tambah_lahan.php                   # Tambah lahan
    │   ├── edit_lahan.php                     # Edit lahan
    │   ├── komoditas.php                      # Daftar & ubah status komoditas
    │   ├── tambah_komoditas.php               # Tambah komoditas & upload foto produk
    │   ├── edit_komoditas.php                 # Edit komoditas & foto produk
    │   ├── batch_panen.php                    # Daftar & buka/tutup batch panen
    │   ├── tambah_batch.php                   # Buat batch panen baru & opsi Food Rescue
    │   ├── edit_batch.php                     # Edit data batch & stok
    │   ├── kelola_rescue.php                  # Pengaturan cepat harga & batas waktu Food Rescue
    │   ├── kalender_panen.php                 # Timeline jadwal estimasi panen
    │   ├── pesanan.php                        # Daftar pesanan masuk untuk komoditas petani
    │   ├── detail_pesanan.php                 # Rincian item & pembaruan status pemrosesan
    │   └── laporan.php                        # Laporan pendapatan & penyelamatan pangan petani
    │
    ├── pembeli/                               # Modul Dashboard Pembeli
    │   ├── index.php                          # Ringkasan & 5 pesanan terbaru pembeli
    │   ├── keranjang.php                      # Keranjang belanja & hitung subtotal real-time
    │   ├── checkout.php                       # Proses checkout, metode ambil/antar, & validasi stok
    │   ├── pesanan.php                        # Riwayat pesanan pembeli
    │   ├── detail_pesanan.php                 # Rincian pesanan, status, & akses ulasan/invoice
    │   ├── pembayaran.php                     # Instruksi transfer & upload bukti pembayaran
    │   ├── invoice.php                        # Invoice digital & cetak PDF
    │   ├── ulasan.php                         # Pemberian rating & ulasan kesegaran
    │   └── profil.php                         # Pengaturan profil & ubah password
    │
    ├── assets/
    │   ├── css/
    │   │   └── style.css                      # Stylesheet responsif PanenLokal
    │   └── js/
    │       └── script.js                      # Validasi form, toggle password, countdown, & subtotal
    │
    └── uploads/
        ├── .htaccess                          # Blokir eksekusi script & directory listing di uploads
        ├── products/                          # Foto komoditas hasil tani
        └── payments/                          # Bukti transfer pembayaran pembeli
```

---

## 🚀 Panduan Instalasi & Konfigurasi

### 1. Persiapan File Proyek (XAMPP Lokal)
1. Pastikan **XAMPP** sudah terpasang (PHP 8.x & MySQL/MariaDB).
2. Clone repository ini atau letakkan foldernya di dalam direktori `htdocs` XAMPP, misalnya:
   ```text
   C:\xampp\htdocs\RESPONSI_PEMWEB1\
   ```
3. Jalankan modul **Apache** dan **MySQL** melalui **XAMPP Control Panel**.

### 2. Setup Database Lokal
1. Buka `http://localhost/phpmyadmin`.
2. Pilih tab **Import**, lalu pilih file:
   ```text
   PanenLokal/panenlokal.sql
   ```
3. Klik **Import / Go**. File SQL ini akan membuat database `panenlokal_db`, 13 tabel relasional, serta data awal (akun demo, profil petani, lahan, komoditas, batch panen, dan titik pengambilan).

### 3. Konfigurasi Database Lokal (`config/config.local.php`)
File `PanenLokal/config/koneksi.php` otomatis mendeteksi akses dari `localhost` / `127.0.0.1` dan mengatur zona waktu `Asia/Jakarta` (`+07:00`).
- Secara bawaan (jika file `config/config.local.php` belum dibuat), `koneksi.php` sudah memiliki konfigurasi default XAMPP (`localhost`, user `root`, password kosong, database `panenlokal_db`, dan `base_url` `/RESPONSI_PEMWEB1/PanenLokal`).
- Jika pengaturan MySQL atau folder lokal Anda berbeda, buat file `PanenLokal/config/config.local.php` (file ini diabaikan oleh `.gitignore`):
  ```php
  <?php
  return [
      'db_host'   => 'localhost',
      'db_user'   => 'root',
      'db_pass'   => '',
      'db_name'   => 'panenlokal_db',
      'base_url'  => '/RESPONSI_PEMWEB1/PanenLokal',
      'site_name' => 'PanenLokal',
      'debug'     => true,
  ];
  ```

### 4. Konfigurasi untuk Deployment Hosting
1. **Database Hosting:** Buat database MySQL melalui panel hosting Anda, lalu import file:
   ```text
   deployment/panenlokal_hosting.sql
   ```
   *(File ini sudah disiapkan tanpa perintah `DROP DATABASE`, `CREATE DATABASE`, dan `USE`, sehingga dapat langsung di-import ke database bawaan shared hosting).*
2. **Konfigurasi Production:** Salin `PanenLokal/config/config.production.example.php` menjadi `PanenLokal/config/config.production.php`, lalu isi sesuai kredensial database hosting Anda:
   - `db_host`, `db_user`, `db_pass`, `db_name` dari panel hosting.
   - `base_url` diisi `''` apabila seluruh isi folder `PanenLokal/` diunggah langsung ke root `htdocs/` domain/subdomain.
   - *(File `config/config.production.php` diabaikan oleh `.gitignore` agar kredensial tidak pernah masuk ke GitHub).*
3. **Upload File:** Unggah seluruh isi di dalam folder `PanenLokal/` ke folder `htdocs/` hosting (tidak perlu menyertakan `config/config.local.php` dan `panenlokal.sql`).

---

## ▶️ Panduan Menjalankan Aplikasi

1. Setelah Apache, MySQL, dan database siap, buka browser dan akses alamat lokal berikut:
   ```text
   http://localhost/RESPONSI_PEMWEB1/PanenLokal/
   ```
2. Anda dapat menjelajahi halaman publik (**Beranda**, **Katalog**, **Food Rescue**, **Tentang**), mendaftar akun baru di halaman **Daftar** (`register.php`), atau login menggunakan akun demo yang tersedia.

### Akun Demo Pengujian (Data Awal SQL)

| Role | Username | Password | Deskripsi Akses |
|---|---|---|---|
| **Admin** | `admin` | `admin123` | Verifikasi pembayaran, kelola pengguna & petani, titik ambil, semua pesanan, laporan |
| **Petani Demo** | `petani1` | `password123` | Kelola lahan, komoditas, batch panen, Food Rescue, pesanan masuk, laporan usaha tani |
| **Pembeli Demo** | `pembeli1` | `password123` | Belanja katalog & Food Rescue, keranjang, checkout, upload bukti bayar, invoice, ulasan |
| *Akun Baru* | *Daftar via register* | Bebas (min. 6 karakter) | Mendaftar mandiri sebagai Pembeli atau Petani |
