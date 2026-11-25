# TODO — PanenLokal

## Identitas, struktur, dan batas demo
- [x] Website menggunakan nama PanenLokal, antarmuka berbahasa Indonesia, dan mengangkat marketplace hasil tani lokal, pre-order, serta Food Rescue sesuai rancangan SDG 2.
- [x] Implementasi ini merupakan demo frontend HTML/CSS/JavaScript untuk Preview: data contoh dan state demo disimpan di browser. Tidak mengklaim menjalankan PHP Native, MySQL/MariaDB, XAMPP, autentikasi aman, unggah bukti pembayaran, atau pemrosesan transaksi nyata.
- [x] Proyek menggunakan berkas yang terpisah untuk shell HTML, CSS, interaksi JavaScript, server Preview, serta manifest route; tanpa framework atau dependency runtime.

## Jelajah katalog dan Food Rescue
- [x] Pengunjung dapat melihat katalog produk hasil tani lokal dan detail setiap produk, termasuk petani, harga, satuan, stok, dan estimasi panen.
- [x] Halaman katalog menyediakan pencarian dan filter kategori yang mengubah hasil secara nyata.
- [x] Halaman Food Rescue menampilkan penawaran penyelamatan pangan secara terpisah, harga normal/diskon, dan penanda penawaran; aksi rescue hanya mengubah state demo di browser.
- [x] Kartu produk menyediakan aksi menambah barang ke keranjang dengan umpan balik yang jelas.

## Keranjang dan checkout demo
- [x] Pembeli dapat melihat keranjang, mengubah kuantitas, menghapus item, dan melihat subtotal/total yang dihitung ulang.
- [x] Keranjang dipertahankan melalui `localStorage`; aksi frontend menolak kuantitas yang tidak valid atau melebihi stok demo.
- [x] Checkout demo meminta metode antar atau ambil dan menunjukkan konfirmasi yang secara eksplisit menyatakan bahwa pembayaran/pesanan tidak diproses nyata.

## Role dan gambaran modul
- [x] Pengunjung dapat mengganti tampilan demo admin, petani, dan pembeli untuk melihat area/dasbor yang berbeda; pemilih role adalah simulasi, bukan autentikasi.
- [x] Area pembeli menggambarkan ringkasan pesanan dan keranjang; area petani menggambarkan lahan, komoditas, batch panen, Food Rescue, dan ringkasan pendapatan; area admin menggambarkan verifikasi pembayaran, titik pengambilan, pengguna, dan statistik.
- [x] Status pesanan, pembayaran, ulasan, dan analitik ditampilkan sebagai data contoh yang selaras dengan alur rancangan; dashboard tidak mengklaim melakukan verifikasi atau pencatatan transaksi sungguhan.

## Visual, aksesibilitas, dan penyajian
- [x] Terapkan gaya pasar tani editorial kontemporer: ivory hangat, hijau khas, aksen tomat, hero fotografis asimetris, label “Panen berikutnya” dan “Food Rescue”.
- [x] Aset foto hero dan produk menggunakan berkas lokal di `assets/images/` dan `assets/products/`, sehingga tetap tampil saat Preview dijalankan tanpa Managed Storage.
- [x] Layout responsif pada layar ponsel dan desktop; markup semantik, label form, fokus keyboard terlihat, kontras memadai, dan dukungan `prefers-reduced-motion`.
- [x] Format harga konsisten dalam Rupiah dan seluruh teks antarmuka berbahasa Indonesia.
- [x] Manifest `/manus-routes.json` mendeklarasikan route halaman yang benar-benar dipakai aplikasi dan memberi respons JSON.
- [x] Server Preview berjalan pada port proyek 3000; pemeriksaan HTTP untuk halaman utama, katalog, detail produk, dashboard role, aset CSS/JS, dan manifest route lulus.
