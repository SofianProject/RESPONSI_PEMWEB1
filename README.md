# PanenLokal 🌿

> **Platform Pre-Order Hasil Tani dan Penyelamatan Pangan (Food Loss Rescue)**  
> **Tema SDG 2 — Zero Hunger**  
> Proyek Akhir Responsi Praktikum Pemrograman Web I  
> Stack: **HTML5, CSS3, JavaScript Native, PHP Native (MySQLi), MySQL/MariaDB, XAMPP**

---

## 📌 Ringkasan Proyek

**PanenLokal** adalah platform berbasis web yang menghubungkan petani lokal/urban farming langsung dengan konsumen akhir dan pelaku kuliner. Platform ini menyediakan katalog hasil tani, sistem pemesanan sebelum masa panen (*pre-order*), serta kanal khusus **Food Rescue** untuk komoditas hasil panen berlebih atau mendekati masa kesegaran akhir dengan harga hemat guna menekan angka *food loss* dan mendukung **SDG 2 — Zero Hunger**.

---

## 👥 Akun Demo & Kredensial Pengujian

| Role | Username | Password | Deskripsi Akses |
|---|---|---|---|
| **Admin** | `admin` | `admin123` | Verifikasi pembayaran, kelola pengguna, kelola titik ambil, analitik |
| **Petani Demo** | `petani1` | `password123` | Kelola lahan, komoditas, batch panen, food rescue, laporan usaha tani |
| **Pembeli Demo** | `pembeli1` | `password123` | Belanja katalog, food rescue, checkout, bayar, invoice, ulasan |
| *Akun Baru* | *Daftar via register* | Bebas (min 6 kar) | Mendaftar mandiri sebagai petani atau pembeli |

---

## 🚀 Panduan Instalasi & Menjalankan di XAMPP

1. **Jalankan XAMPP Control Panel**
   - Aktifkan modul **Apache** dan **MySQL**.

2. **Setup Database**
   - Buka browser dan akses [http://localhost/phpmyadmin](http://localhost/phpmyadmin).
   - Buat database baru dengan nama: `panenlokal_db` (atau langsung import file SQL).
   - Pilih tab **Import**, pilih file: `panenlokal.sql` yang berada di folder root proyek:
     ```
     c:\xampp\htdocs\RESPONSI_PEMWEB1\PanenLokal\panenlokal.sql
     ```
   - Klik **Go** / **Kirim**. Database dan 13 tabel relasional beserta data admin awal akan terbuat otomatis.

3. **Konfigurasi Database (Jika Diperlukan)**
   - Periksa berkas `config/koneksi.php`:
     ```php
     $host     = 'localhost';
     $user     = 'root';
     $password = '';
     $database = 'panenlokal_db';
     ```

4. **Akses Aplikasi Melalui Browser**
   - Buka: [http://localhost/RESPONSI_PEMWEB1/PanenLokal/](http://localhost/RESPONSI_PEMWEB1/PanenLokal/)

---

## 📂 Struktur Folder Proyek

```text
PanenLokal/
│
├── index.php                      # Beranda / Landing page
├── katalog.php                    # Katalog pencarian & filter komoditas
├── detail_produk.php              # Detail batch panen, rating, & order
├── food_rescue.php                # Kanal khusus Food Rescue diskon
├── tentang.php                    # Visi, misi, dan kaitan SDG 2 Zero Hunger
├── login.php                      # Autentikasi terpadu 3 role
├── register.php                   # Registrasi pembeli & petani
├── logout.php                     # Mengakhiri session
├── panenlokal.sql                 # Skema 13 tabel database & data awal
├── README.md                      # Dokumentasi proyek
│
├── config/
│   └── koneksi.php                # Koneksi MySQLi
│
├── includes/
│   ├── auth.php                   # Helper otorisasi & role check
│   ├── functions.php              # Helper rupiah, sanitasi, tanggal, badge
│   ├── header.php                 # Header HTML & meta
│   ├── navbar.php                 # Navigasi responsif dinamis sesuai role
│   └── footer.php                 # Footer & script loader
│
├── admin/                         # Modul Dashboard Administrator
│   ├── index.php                  # Ringkasan statistik & aktivitas
│   ├── pengguna.php               # Kelola & aktif/nonaktifkan akun
│   ├── detail_pengguna.php        # Verifikasi status petani
│   ├── verifikasi_pembayaran.php  # Daftar bukti transfer pembayaran
│   ├── detail_pembayaran.php      # Verifikasi terima/tolak pembayaran
│   ├── titik_pengambilan.php      # Kelola pick-up points
│   ├── tambah_titik.php           # Tambah titik ambil baru
│   ├── edit_titik.php             # Ubah data titik ambil
│   ├── pesanan.php                # Pantau seluruh pesanan & ubah status
│   └── laporan.php                # Analitik transaksi & metrik pangan rescue
│
├── petani/                        # Modul Dashboard Petani / Produsen
│   ├── index.php                  # Ringkasan omzet & pesanan masuk
│   ├── lahan.php                  # CRUD lahan garapan
│   ├── tambah_lahan.php           # Tambah lahan
│   ├── edit_lahan.php             # Edit lahan
│   ├── komoditas.php              # CRUD komoditas hasil tani
│   ├── tambah_komoditas.php       # Tambah komoditas & upload foto
│   ├── edit_komoditas.php         # Edit data komoditas
│   ├── batch_panen.php            # CRUD batch jadwal panen & kuota pre-order
│   ├── tambah_batch.php           # Buat batch panen baru
│   ├── edit_batch.php             # Edit data batch & stok
│   ├── kelola_rescue.php          # Aktivasi cepat food rescue & harga diskon
│   ├── kalender_panen.php         # Timeline jadwal panen mendatang
│   ├── pesanan.php                # Pesanan masuk yang memuat komoditas petani
│   ├── detail_pesanan.php         # Pemrosesan status panen pesanan
│   └── laporan.php                # Laporan pendapatan & penyelamatan pangan
│
├── pembeli/                       # Modul Dashboard Konsumen / Pembeli
│   ├── index.php                  # Ringkasan riwayat belanja
│   ├── keranjang.php              # Keranjang belanja & preview harga JS
│   ├── checkout.php               # Checkout transaksi, pilih ambil/antar
│   ├── pesanan.php                # Riwayat transaksi belanja
│   ├── detail_pesanan.php         # Rincian pesanan & pelacakan status
│   ├── pembayaran.php             # Instruksi & upload bukti transfer manual
│   ├── invoice.php                # Invoice digital & fitur cetak PDF
│   ├── ulasan.php                 # Rating (1-5) & ulasan kesegaran
│   └── profil.php                 # Pengaturan data diri & ganti password
│
├── assets/
│   ├── css/
│   │   └── style.css              # Styling responsif, modern, palet hijau-oranye
│   ├── js/
│   │   └── script.js              # Validasi form, kalkulasi real-time, countdown
│   └── images/
│
└── uploads/
    ├── products/                  # Foto komoditas hasil tani
    └── payments/                  # Bukti transfer pembayaran pembeli
```

---

## 🎯 9 Modul Fungsional yang Diimplementasikan

1. **Modul 1: Autentikasi dan Manajemen Pengguna Multi-Role** (Admin, Petani, Pembeli) dengan enkripsi `password_hash()` dan validasi session.
2. **Modul 2: Manajemen Lahan dan Kalender Estimasi Panen** (Pencatatan lahan, luas m², timeline panen).
3. **Modul 3: Katalog Komoditas & Sistem Pre-Order Batch** (Pemesanan sebelum panen, filter kategori, pagination, detail produk).
4. **Modul 4: Food Rescue / Flash Sale Komoditas** (Penyerapan hasil panen berlebih, harga diskon, countdown timer interaktif).
5. **Modul 5: Keranjang Belanja & Checkout Terpadu** (Kalkulasi kuantitas real-time, transaksi atomik database, validasi stok).
6. **Modul 6: Verifikasi Pembayaran Manual & Invoice Digital** (Upload bukti transfer, approval admin, invoice digital cetak PDF).
7. **Modul 7: Pengiriman & Titik Pengambilan (*Pick-up Point*)** (Opsi ambil di posko tani atau antar ke alamat).
8. **Modul 8: Ulasan dan Rating Kesegaran Komoditas** (Penilaian 1-5 bintang dan ulasan setelah pesanan selesai).
9. **Modul 9: Dashboard Analitik Pendapatan & Penyelamatan Pangan** (Perhitungan otomatis omzet dan kilogram pangan terselamatkan untuk SDG 2).

---

## 🛡️ Aturan Bisnis & Keamanan

- **Prepared Statements:** Seluruh interaksi database yang menerima masukan pengguna memakai parameterized query MySQLi.
- **XSS Prevention:** Seluruh output dinamis dieksekusi melalui `sanitize()` / `htmlspecialchars()`.
- **Role Guard:** Setiap halaman privat dilindungi `cek_role()` di sisi backend PHP.
- **Transaksi DB:** Checkout menggunakan `begin_transaction()`, `commit()`, dan `rollback()` dengan pengecekan stok mutlak.
- **Snapshot Transaksi:** Harga dan nama produk disimpan sebagai snapshot pada tabel `order_items` sehingga perubahan harga di katalog tidak mengubah rekam jejak invoice lama.
