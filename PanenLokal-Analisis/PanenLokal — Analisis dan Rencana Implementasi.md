# PanenLokal — Analisis dan Rencana Implementasi

## Analisis rancangan

PanenLokal adalah marketplace hasil tani lokal dengan dua jalur pasokan: pre-order batch panen dan Food Rescue untuk stok yang perlu segera terserap. Rancangan menghubungkan tiga role (admin, petani, pembeli) melalui sembilan modul: autentikasi; lahan dan kalender panen; katalog/pre-order; Food Rescue; keranjang/checkout; pembayaran manual/invoice; antar atau titik pengambilan; ulasan; serta analitik.

Struktur data 13 tabel masuk akal untuk memisahkan akun, profil petani, lahan, komoditas, batch, keranjang, pesanan, pembayaran, pemenuhan, dan ulasan. Dua keputusan desain yang kuat adalah `order_items` menyimpan snapshot nama/harga/satuan agar invoice lama tidak berubah dan metrik rescue hanya menghitung item rescue dari pesanan selesai. Rancangan juga tepat menekankan prepared statement, hash password, pemeriksaan role/ownership, validasi stok sisi server, dan transaksi database saat checkout.

Hal yang perlu dijaga saat implementasi backend nantinya: stok harus dikurangi hanya sekali saat checkout dan dikembalikan tepat sekali bila pembatalan diizinkan; status pesanan dan status delivery perlu bergerak konsisten; batas waktu rescue harus diperiksa lagi saat checkout; dan metrik kilogram hanya boleh menjumlahkan satuan `kg` atau kuantitas yang telah dikonversi. Akun admin tidak boleh berasal dari registrasi publik. Kebutuhan tersebut tetap dicantumkan sebagai batas integrasi backend, bukan disimulasikan seolah sudah diamankan oleh frontend.

## Keputusan cakupan versi website ini (disetujui)

Website ini adalah demo responsif dan interaktif untuk alur utama PanenLokal, menggunakan HTML, CSS, dan JavaScript vanilla, dengan data contoh serta state demo di `localStorage`. Pengunjung dapat menjelajahi katalog dan produk rescue; mencoba pencarian, kategori, serta filter; menambah dan mengubah item keranjang; menjalankan checkout demo; serta berpindah tampilan role untuk melihat gambaran dashboard pembeli, petani, dan admin.

**Batasan penting:** demo ini bukan backend produksi dan tidak menjalankan PHP Native, MySQL/MariaDB, XAMPP, autentikasi aman, unggahan pembayaran, atau transaksi database. Preview WebDev melayani frontend melalui server Node ringan. Source aplikasi PHP-MySQL pada rancangan tetap menjadi target implementasi backend terpisah yang dapat dihubungkan pada tahap lanjutan; interaksi demo tidak boleh dipresentasikan sebagai transaksi atau autentikasi nyata.

## Rencana implementasi

- Satu halaman aplikasi dengan navigasi ke beranda, katalog, Food Rescue, detail produk, keranjang/checkout, dan area demo berdasarkan role.
- Konten dan interaksi berbahasa Indonesia, rupiah diformat konsisten, kartu produk menampilkan petani, satuan, stok, estimasi panen, harga, serta diskon rescue bila berlaku.
- State demo memakai data awal dan `localStorage` untuk keranjang/pilihan role; aksi penting diberi umpan balik yang jelas.
- Aksesibilitas dasar: struktur semantik, label form, fokus keyboard terlihat, dan dukungan `prefers-reduced-motion`.
- Tanpa framework atau dependency runtime; server Node menyajikan Preview pada port 3000. Skrip `build.sh` menyiapkan output statis tanpa memublikasikan website.

## Struktur proyek

```text
panenlokal/
├── app.config.ts           # Metadata logo proyek
├── package.json            # Perintah start lokal tanpa dependency
├── build.sh                # Salin source frontend ke dist/
├── .gitignore              # Abaikan output dist lokal
├── plan.md                 # Analisis, desain, dan pendekatan
├── TODO.md                 # Hasil dan kriteria cakupan demo
├── README.md               # Cara pakai dan analisis sistem
├── server.js               # Server statis Preview pada port 3000
├── index.html              # Shell semantik website
├── styles.css              # Sistem visual responsif
├── app.js                  # Data demo, navigasi, katalog, keranjang, checkout, role
├── public/
│   ├── manus-routes.json   # Manifest route website
│   └── panenlokal-mark.svg # Ikon vektor proyek
└── dist/                   # Output build lokal, diabaikan Git
```

Foto hero dan produk di-host pada Managed Storage dan dipanggil dari URL `/manus-storage/async-images/...`; generator sedang menyelesaikan aset foto. Website menggunakan URL aset persis yang disediakan dan tidak menyalin file placeholder sebagai aset lokal.

## Arah desain

Tiga arah yang dipertimbangkan (probabilitas tail):
1. Pasar tani editorial kontemporer, dengan ivory hangat, hijau kebun, dan aksen tomat; layout editorial berlapis, fotografi hasil tani besar, dan detail seperti label panen — **dipilih (0,08)**.
2. Dashboard agritech utilitarian, dengan hijau mint dan biru, grid metrik rapat, serta komponen data yang tegas — **0,06**.
3. Koperasi organik rustic, dengan tekstur kertas, cokelat tanah, ilustrasi cap, dan ornamen tangan — **0,05**.

### Arah terpilih: pasar tani editorial kontemporer

- **Design Movement:** editorial marketplace kontemporer dengan kehangatan pasar hasil tani lokal.
- **Prinsip inti:** hasil tani menjadi fokus; provenance petani terlihat; informasi harga/stok mudah dipindai; antarmuka hangat tetapi tetap tepercaya.
- **Filosofi warna:** ivory dan hijau tua memberi rasa segar/percaya, hijau cerah mengisyaratkan panen, aksen jingga-tomat menarik perhatian ke diskon dan CTA tanpa membuat seluruh UI terasa seperti flash sale.
- **Paradigma layout:** hero asimetris bergaya editorial, pita kategori horizontal, katalog yang lapang, dan panel keranjang ringkas; hindari kesan template dashboard generik.
- **Elemen khas:** label batch “Panen berikutnya”; stempel/label “Food Rescue”; garis organik/aksen berbentuk daun yang sederhana.
- **Interaksi & animasi:** feedback langsung untuk tambah keranjang dan filter; transisi lembut dan singkat; hormati `prefers-reduced-motion`; tidak memakai animasi yang menghalangi tugas.
- **Tipografi:** pasangan sans humanis untuk teks UI dan serif editorial untuk heading; ukuran, panjang baris, dan kontras diprioritaskan untuk keterbacaan Bahasa Indonesia.
- **Esensi merek:** pasar digital yang mempertemukan panen petani lokal dengan pembeli sehari-hari dan membantu menyerap surplus; hangat, jujur, tangkas.
- **Suara merek:** CTA langsung dan manusiawi. Contoh: “Pilih panen berikutnya” dan “Selamatkan hasil yang masih segar.”
- **Wordmark/logo:** wordmark PanenLokal dengan ikon dua daun/bedeng sederhana; tidak mengandalkan teks hasil generator gambar.
- **Warna khas:** hijau daun pekat sebagai identitas PanenLokal.

## Status implementasi dan workflow

Arah dan cakupan frontend sudah disetujui; implementasi, manifest route, server Preview, README, dan skrip build statis sudah dibuat. Sintaks source, validitas JSON, output build, dan respons HTTP untuk halaman utama/katalog/detail/dashboard diuji. Build statis telah dikonfigurasi, tetapi website tidak dipublikasikan tanpa permintaan terpisah. Foto hero dan empat foto produk masih dalam proses generasi Managed Storage.
