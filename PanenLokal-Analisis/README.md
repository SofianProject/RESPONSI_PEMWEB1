# PanenLokal

Demo frontend interaktif untuk marketplace hasil tani lokal dan Food Rescue, berbahasa Indonesia. Website mengikuti rancangan PanenLokal dan memperagakan katalog, batch panen, harga rescue, keranjang, checkout simulasi, serta tampilan demo pembeli, petani, dan admin.

## Menjalankan Preview lokal

Proyek memakai Node.js bawaan tanpa dependency tambahan. Dari folder proyek, jalankan:

```bash
node server.js
```

Server menyajikan aplikasi pada port 3000. Di komputer sendiri, buka `http://localhost:3000`. Perintah alternatif: `npm run start`.

## Menyiapkan output statis

Untuk menyalin file website ke `dist/` (tanpa memublikasikannya), jalankan:

```bash
sh build.sh
```

Skrip ini tidak menginstal dependency dan menghasilkan `dist/index.html`, CSS/JavaScript, route manifest, serta ikon proyek.

## Interaksi yang dapat dicoba

- Cari dan filter katalog berdasarkan kategori; buka detail batch dan informasi petani.
- Jelajahi Food Rescue, lihat harga khusus, lalu tambahkan hasil tani ke keranjang.
- Ubah kuantitas, hapus item, pilih antar atau ambil, lalu buat konfirmasi checkout demo.
- Ganti pilihan peran melalui menu “Mode …”. Pada ruang petani, coba ubah status Food Rescue; pada ruang admin, coba aksi terima/tolak pembayaran contoh; pada ruang pembeli, buka form ulasan contoh.
- Keranjang, konfirmasi, ulasan, status rescue demo, dan keputusan verifikasi contoh menggunakan `localStorage` di browser.

## Batas cakupan

Ini hanya demo frontend HTML/CSS/JavaScript. Tidak ada backend PHP Native, MySQL/MariaDB, XAMPP, login/session yang aman, pengelolaan data antar pengguna, unggah bukti transfer, payment gateway, atau transaksi sungguhan. Formulir yang mengubah localStorage hanya mensimulasikan interaksi antarmuka. Dashboard memakai data contoh dan bukan laporan finansial/penyelamatan pangan yang nyata.

## Analisis rancangan sistem

Rancangan PanenLokal memiliki alur domain yang utuh dari data petani dan lahan ke komoditas/batch, katalog/pre-order, checkout, verifikasi pembayaran manual, pengantaran/pengambilan, ulasan, dan analitik. Pemisahan 13 entitas relasional mendukung kebutuhan tersebut. `orders` dan `order_items` dipisah sehingga satu checkout bisa memuat beberapa petani; snapshot nama, satuan, dan harga menjaga invoice lama tetap benar. Pembayaran dan pemenuhan masing-masing satu catatan per pesanan sesuai batas versi awal.

Ketentuan penting untuk backend penuh tetap perlu dipertahankan: registrasi publik tidak boleh membuat admin; password harus di-hash; semua query masukan pengguna memakai prepared statement; setiap data privat memeriksa role dan pemiliknya; checkout menghitung ulang harga dan memeriksa/mengurangi stok dalam transaksi DB; stok yang dipulihkan saat pembatalan tidak boleh dikembalikan lebih dari sekali; pembayaran diverifikasi admin; titik pengambilan wajib untuk metode ambil; ulasan hanya setelah pesanan selesai dan hanya sekali per item; metrik rescue menghitung item rescue dari pesanan selesai dan konversi kg tidak boleh menyamakan satuan yang berbeda.

## Struktur

- `index.html`: shell dokumen dan metadata.
- `styles.css`: sistem warna, tipografi, layout responsif, dan komponen.
- `app.js`: data demo, navigasi, katalog/filter, detail, state localStorage, checkout, ulasan, dan dasbor role.
- `server.js`: server statis Node ringan untuk Preview/fallback route.
- `build.sh`: persiapan output statis ke `dist/`.
- `public/manus-routes.json`: deklarasi halaman aplikasi.
- `public/panenlokal-mark.svg`: ikon vektor proyek.
- `plan.md`: analisis dan keputusan desain.
- `TODO.md`: hasil yang dilacak untuk implementasi demo.


## Aset gambar lokal

Preview menggunakan gambar lokal agar tidak bergantung pada URL Managed Storage:
- `assets/images/hero-hasil-tani.jpg` — foto hero beranda.
- `assets/products/` — foto produk untuk kartu katalog, Food Rescue, dan detail produk.

Jalankan dari folder ini dengan `node server.js`, lalu buka `http://localhost:3000`.


### Pembaruan foto katalog
Foto produk dan bagian cerita kini memakai aset lokal di folder `assets/products` dan `assets/images`, dipotong dari kolase foto PanenLokal. Tidak perlu koneksi internet untuk memuat foto-foto tersebut. Kartu produk menggunakan rasio landscape 4:3 agar tidak terlalu tinggi.
