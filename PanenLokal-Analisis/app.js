const ICONS = {
  leaf: '<svg viewBox="0 0 32 32" fill="none" aria-hidden="true"><path d="M7 25C8 14 15 7 27 6c0 12-7 19-18 20Z" fill="currentColor"/><path d="M8 25c5-6 10-10 17-14" stroke="#F6F5EC" stroke-width="1.8" stroke-linecap="round"/></svg>',
  arrow: '<svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M4 10h11M10 5l5 5-5 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>',
  basket: '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m4 10 2 10h12l2-10H4Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="m8 10 4-6 4 6M9 13v4m6-4v4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>',
  search: '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.7" stroke="currentColor" stroke-width="1.7"/><path d="m16 16 4.2 4.2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>',
  menu: '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>',
  chevron: '<svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="m5 7.5 5 5 5-5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>',
  check: '<svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="m4 10 4 4 8-8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
  clock: '<svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><circle cx="10" cy="10" r="7.4" stroke="currentColor" stroke-width="1.5"/><path d="M10 5.7v4.7l3 1.8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
  star: '<svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="m10 1.7 2.5 5 5.5.8-4 3.9.9 5.5-4.9-2.6-4.9 2.6 1-5.5-4.1-3.9 5.5-.8 2.5-5Z"/></svg>',
  pin: '<svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M16.2 8.2c0 4.3-6.2 9.1-6.2 9.1S3.8 12.5 3.8 8.2a6.2 6.2 0 1 1 12.4 0Z" stroke="currentColor" stroke-width="1.5"/><circle cx="10" cy="8" r="2" stroke="currentColor" stroke-width="1.5"/></svg>',
  sprout: '<svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M10 17V9m0 3c0-4-2.8-6-6.5-6 .1 4.3 2.5 6.2 6.5 6Zm0-3c.4-3.6 2.6-5.5 6.3-5.5-.2 4-2.5 5.8-6.3 5.8Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
  users: '<svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><circle cx="7.5" cy="6.4" r="2.8" stroke="currentColor" stroke-width="1.5"/><path d="M2.7 16c.4-2.7 2-4.1 4.8-4.1s4.4 1.4 4.8 4.1M13 4c2.3 0 3.7 1.4 3.7 3.4 0 1.4-.7 2.4-1.9 3m.1 1c1.5.7 2.3 2 2.4 3.8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',
  parcel: '<svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="m3 6 7-3 7 3v8l-7 3-7-3V6Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><path d="m3.3 6 6.7 3 6.7-3M10 9v8M6.3 4.6l7 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
  chart: '<svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M3 16.5h14M4.5 14V9.5M8.2 14V5.5M11.8 14v-3.7M15.5 14V7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>',
  shield: '<svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M10 2.5 16 5v4.7c0 3.5-2.4 6.1-6 7.8-3.6-1.7-6-4.3-6-7.8V5l6-2.5Z" stroke="currentColor" stroke-width="1.5"/><path d="m7.3 9.9 1.8 1.8 3.7-3.8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'
};

const PRODUCTS = [
  { id: 'tomat-cherry', name: 'Tomat cherry', category: 'Sayuran', farmer: 'Kebun Sari Organik', area: 'Lembang, Bandung', price: 28000, rescuePrice: 18000, rescue: true, rescueUntil: 'Hari ini, 18.00', unit: 'kg', stock: 24, harvest: '24 Nov 2026', image: '/assets/products/tomat.jpg', description: 'Manis segar, dipetik pagi ini. Cocok untuk salad, tumisan, atau camilan keluarga.', rating: '4,9' },
  { id: 'pakcoy-hijau', name: 'Pakcoy hijau', category: 'Sayuran', farmer: 'Tani Muda Cibiru', area: 'Cibiru, Bandung', price: 16000, rescuePrice: 12000, rescue: false, rescueUntil: '', unit: 'ikat', stock: 18, harvest: '25 Nov 2026', image: '/assets/products/bayam.jpg', description: 'Daun renyah dan batang lembut, ditanam tanpa pestisida sintetis.', rating: '4,8' },
  { id: 'wortel-manis', name: 'Wortel manis', category: 'Umbi', farmer: 'Ladang Pagi', area: 'Cisarua, Bandung Barat', price: 20000, rescuePrice: 15000, rescue: false, rescueUntil: '', unit: 'kg', stock: 30, harvest: '26 Nov 2026', image: '/assets/products/wortel.jpg', description: 'Wortel lokal berwarna cerah, manis alami dan dipanen sesuai pesanan.', rating: '4,7' },
  { id: 'stroberi-segar', name: 'Pisang raja', category: 'Buah', farmer: 'Kebun Bukit Merah', area: 'Lembang, Bandung', price: 28000, rescuePrice: 32000, rescue: true, rescueUntil: 'Hari ini, 16.30', unit: 'kg', stock: 12, harvest: 'Siap dipanen', image: '/assets/products/pisang.jpg', description: 'Pisang lokal matang alami, cocok untuk camilan dan olahan rumahan.', rating: '5,0' }
];

const DEFAULT_STATE = { cart: {}, role: 'pembeli', orders: [], paymentStatus: {}, rescueOverrides: {}, reviews: [] };
let state = loadState();
let activeCategory = 'Semua';

function loadState() {
  try {
    const saved = JSON.parse(localStorage.getItem('panenlokal-demo-v1') || '{}');
    return { ...DEFAULT_STATE, ...saved, cart: saved.cart || {}, orders: saved.orders || [], paymentStatus: saved.paymentStatus || {}, rescueOverrides: saved.rescueOverrides || {}, reviews: saved.reviews || [] };
  } catch { return structuredClone(DEFAULT_STATE); }
}
function saveState() { try { localStorage.setItem('panenlokal-demo-v1', JSON.stringify(state)); } catch {} }
function allProducts() { return PRODUCTS; }
function findProduct(id) { return allProducts().find((product) => product.id === id); }
function isRescue(product) { return Object.prototype.hasOwnProperty.call(state.rescueOverrides, product.id) ? state.rescueOverrides[product.id] : product.rescue; }
function rescuePrice(product) { return product.rescuePrice || Math.round(product.price * .75 / 1000) * 1000; }
function livePrice(product) { return isRescue(product) ? rescuePrice(product) : product.price; }
function money(amount) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(amount).replace('IDR', 'Rp'); }
function esc(value) { return String(value ?? '').replace(/[&<>"']/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char])); }
function totalCartItems() { return Object.values(state.cart).reduce((sum, quantity) => sum + Number(quantity || 0), 0); }
function cartRows() { return Object.entries(state.cart).map(([id, quantity]) => ({ product: findProduct(id), quantity: Number(quantity) })).filter((row) => row.product && row.quantity > 0); }
function cartTotal() { return cartRows().reduce((sum, row) => sum + livePrice(row.product) * row.quantity, 0); }
function routePath() { return window.location.pathname.replace(/\/$/, '') || '/'; }
function navigate(path) { history.pushState({}, '', path); document.querySelector('.nav-links')?.classList.remove('open'); render(); window.scrollTo({ top: 0, behavior: 'smooth' }); }
function toast(message, type = '') {
  const region = document.querySelector('#toast-region');
  if (!region) return;
  const item = document.createElement('div');
  item.className = `toast ${type}`;
  item.textContent = message;
  region.append(item);
  window.setTimeout(() => item.remove(), 3200);
}

function brandMarkup() {
  return `<a class="brand" href="/" data-route="/" aria-label="PanenLokal, beranda"><span class="brand-mark">${ICONS.leaf}</span><span class="brand-name">Panen<span>Lokal</span></span></a>`;
}
function headerMarkup() {
  const path = routePath();
  const links = [['/', 'Beranda'], ['/katalog', 'Katalog'], ['/food-rescue', 'Food Rescue'], ['/tentang', 'Tentang']];
  const active = (link) => link === '/' ? path === '/' : path.startsWith(link);
  const roleNames = { pembeli: 'Mode pembeli', petani: 'Mode petani', admin: 'Mode admin' };
  return `<div class="top-note"><strong>Panen lebih dekat, pangan lebih berarti.</strong> Setiap pilihan lokal ikut menggerakkan perubahan.</div>
    <header class="site-header"><div class="nav-wrap">
      <button class="icon-button menu-toggle" type="button" aria-label="Buka navigasi" data-action="toggle-menu">${ICONS.menu}</button>
      ${brandMarkup()}
      <nav class="nav-links" aria-label="Navigasi utama">${links.map(([href, label]) => `<a class="nav-link ${active(href) ? 'active' : ''}" href="${href}" data-route="${href}">${label}</a>`).join('')}</nav>
      <div class="nav-actions">
        <button class="icon-button" type="button" aria-label="Cari produk" data-action="open-search">${ICONS.search}</button>
        <button class="icon-button" type="button" aria-label="Buka keranjang" data-route="/keranjang">${ICONS.basket}${totalCartItems() ? `<span class="cart-count">${totalCartItems()}</span>` : ''}</button>
        <div class="role-control"><button class="role-button" type="button" data-action="toggle-role-menu" aria-expanded="false">${esc(roleNames[state.role] || roleNames.pembeli)} ${ICONS.chevron}</button>
          <div class="role-menu" id="role-menu"><button class="role-option ${state.role === 'pembeli' ? 'current' : ''}" data-role="pembeli">Pembeli — jelajahi & pesan</button><button class="role-option ${state.role === 'petani' ? 'current' : ''}" data-role="petani">Petani — kelola panen</button><button class="role-option ${state.role === 'admin' ? 'current' : ''}" data-role="admin">Admin — pantau platform</button></div>
        </div>
      </div>
    </div></header>`;
}
function footerMarkup() {
  return `<footer class="site-footer"><div class="container footer-main">
    <div class="footer-brand">${brandMarkup()}<p>Hasil tani lokal, langsung dari tangan yang menanamnya. Belanja yang terasa dekat dan lebih bermakna.</p><span class="badge badge-harvest">Demo konsep — data contoh</span></div>
    <div><h3 class="footer-heading">Jelajahi</h3><div class="footer-links"><a href="/katalog" data-route="/katalog">Katalog panen</a><a href="/food-rescue" data-route="/food-rescue">Food Rescue</a><a href="/tentang" data-route="/tentang">Tentang PanenLokal</a></div></div>
    <div><h3 class="footer-heading">Untuk komunitas</h3><div class="footer-links"><a href="/peran/petani" data-route="/peran/petani">Ruang petani</a><a href="/peran/pembeli" data-route="/peran/pembeli">Ruang pembeli</a><a href="/peran/admin" data-route="/peran/admin">Ruang admin</a></div></div>
    <div class="footer-newsletter"><h3 class="footer-heading">Cerita dari kebun</h3><p>Dapatkan kabar panen dan pangan lokal pilihan.</p><form class="newsletter-field" data-form="newsletter"><input type="email" placeholder="Alamat email Anda" aria-label="Alamat email" required /><button aria-label="Daftar newsletter">${ICONS.arrow}</button></form></div>
  </div><div class="container footer-bottom"><span>© 2026 PanenLokal · Demo antarmuka untuk proyek pembelajaran</span><span>Dirancang untuk <strong>panen yang lebih berdaya.</strong></span></div></footer>`;
}
function productCard(product) {
  const rescue = isRescue(product);
  const price = livePrice(product);
  return `<article class="product-card">
    <a class="product-image-wrap" href="/produk/${encodeURIComponent(product.id)}" data-route="/produk/${encodeURIComponent(product.id)}" aria-label="Lihat detail ${esc(product.name)}">
      <img class="product-image" src="${product.image}" onerror="this.onerror=null;this.src='/assets/images/hero-hasil-tani.jpg'" alt="${esc(product.name)} hasil panen lokal" loading="lazy" />
      <span class="product-badges">${rescue ? '<span class="badge badge-rescue">Food Rescue</span>' : ''}<span class="badge badge-harvest">Panen ${esc(product.harvest.split(' ')[0])}</span></span>
    </a>
    <div class="product-info"><div class="product-meta"><span class="product-farmer">${ICONS.pin} ${esc(product.farmer)}</span><span class="product-rating">★ ${product.rating}</span></div>
      <a class="product-name" href="/produk/${encodeURIComponent(product.id)}" data-route="/produk/${encodeURIComponent(product.id)}">${esc(product.name)}</a>
      <p class="product-description">${esc(product.description)}</p>
      <div class="product-bottom"><div class="product-price"><strong>${money(price)}${rescue ? `<del>${money(product.price)}</del>` : ''}</strong><small>per ${esc(product.unit)} · stok ${product.stock} ${esc(product.unit)}</small></div><button class="add-button" type="button" aria-label="Tambah ${esc(product.name)} ke keranjang" data-action="add" data-id="${product.id}">${ICONS.basket}</button></div>
    </div>
  </article>`;
}
function productSection(title, subtitle, products, link = '/katalog') {
  return `<section class="section"><div class="container"><div class="section-head"><div><span class="eyebrow">Dari kebun terdekat</span><h2 class="serif">${title}</h2><p>${subtitle}</p></div><a class="text-link" href="${link}" data-route="${link}">Lihat semua ${ICONS.arrow}</a></div><div class="product-grid">${products.length ? products.map(productCard).join('') : '<div class="empty-products">Belum ada panen di kategori ini.</div>'}</div></div></section>`;
}
function renderHome() {
  const products = allProducts();
  return `<main id="main-content"><section class="hero"><div class="container hero-grid"><div class="hero-copy"><span class="eyebrow">Belanja lokal, tumbuh bersama</span><h1>Panen baik,<br />dekat di <em>rumah.</em></h1><p class="hero-text">Temukan hasil tani segar langsung dari petani sekitar. Pesan sebelum panen, atau bantu selamatkan hasil berlebih lewat Food Rescue.</p><div class="button-row"><a class="btn btn-primary" href="/katalog" data-route="/katalog">Jelajahi panen ${ICONS.arrow}</a><a class="btn btn-outline" href="/food-rescue" data-route="/food-rescue">Lihat Food Rescue</a></div><div class="hero-note"><div class="avatar-stack" aria-hidden="true"><span>KS</span><span>TM</span><span>LP</span></div><span>Panen dari petani sekitar Bandung <strong>· data demo</strong></span></div></div><div class="hero-visual"><div class="hero-photo-shape"></div><img class="hero-photo" src="/assets/images/hero-hasil-tani.jpg" alt="Hasil panen sayur segar dari kebun lokal" /><div class="hero-photo-caption"><div class="caption-top"><span class="caption-label">Batch pekan ini</span><span class="caption-dot"></span></div><strong>Dipetik, bukan ditebak.</strong><small>Jadwal panen transparan</small></div><div class="hero-side-stamp"><span>lebih dekat<br />lebih segar</span>Panen<br />Lokal</div></div></div></section>
    <section class="trust-strip"><div class="container trust-inner"><div class="trust-item"><span class="trust-icon">${ICONS.sprout}</span><span><strong>Langsung dari petani</strong><small>Kenali siapa yang menanam</small></span></div><div class="trust-item"><span class="trust-icon">${ICONS.clock}</span><span><strong>Pre-order lebih pasti</strong><small>Pesan sesuai waktu panen</small></span></div><div class="trust-item"><span class="trust-icon">${ICONS.leaf}</span><span><strong>Kurangi pangan terbuang</strong><small>Surplus tetap punya nilai</small></span></div></div></section>
    ${productSection('Panen yang sedang dinanti', 'Batch pilihan dari kebun lokal. Pesan hari ini dan ketahui kapan hasilnya siap.', products.slice(0, 4))}
    <section class="container"><div class="rescue-band"><div class="rescue-band-inner"><div><span class="eyebrow">Food Rescue · pilih hari ini</span><h2>Masih segar.<br />Jangan biarkan terbuang.</h2><p>Hasil panen berlebih atau yang perlu segera terserap kini hadir dengan harga khusus. Pilihan kecil yang membantu petani dan mengurangi food loss.</p></div><a class="btn" href="/food-rescue" data-route="/food-rescue">Selamatkan hasil panen ${ICONS.arrow}</a></div></div></section>
    <section class="story-section"><div class="container story-grid"><div class="story-visual"><img src="/assets/products/bayam.jpg" alt="Sayuran hijau yang baru dipanen" loading="lazy" /><div class="story-note">Setiap panen punya cerita. Kenali orang di baliknya.</div></div><div class="story-copy"><span class="eyebrow">Dari tanah, untuk meja makan</span><h2>Rantai pangan yang lebih dekat.</h2><p>PanenLokal mempertemukan petani lokal, konsumen, dan warung makan dalam satu ruang. Dengan pre-order, petani dapat membaca permintaan lebih awal. Dengan Food Rescue, hasil berlebih mendapat kesempatan kedua.</p><div class="story-points"><div class="story-point"><span>${ICONS.check}</span>Informasi petani dan estimasi panen yang jelas</div><div class="story-point"><span>${ICONS.check}</span>Harga dan jumlah tersedia transparan</div><div class="story-point"><span>${ICONS.check}</span>Surplus pangan tetap bernilai</div></div><a class="text-link" href="/tentang" data-route="/tentang">Kenali PanenLokal ${ICONS.arrow}</a></div></div></section>
  </main>`;
}
function categoryButtons() {
  const categories = ['Semua', 'Sayuran', 'Buah', 'Umbi'];
  return `<div class="category-row" role="group" aria-label="Filter kategori">${categories.map((category) => `<button class="category-pill ${activeCategory === category ? 'active' : ''}" type="button" data-category="${category}">${category}</button>`).join('')}</div>`;
}
function renderCatalog(rescueOnly = false) {
  const query = new URLSearchParams(location.search).get('q') || '';
  const products = allProducts().filter((product) => (!rescueOnly || isRescue(product)) && (activeCategory === 'Semua' || product.category === activeCategory) && (!query || `${product.name} ${product.farmer} ${product.category}`.toLowerCase().includes(query.toLowerCase())));
  const title = rescueOnly ? 'Food Rescue' : 'Katalog panen';
  const description = rescueOnly ? 'Hasil panen yang masih segar dan perlu segera terserap. Pilih yang Anda butuhkan, bantu hasil baik tetap dinikmati.' : 'Jelajahi batch panen dari kebun lokal. Pilih produk, kenali petaninya, dan pesan sesuai kebutuhan.';
  return `<main id="main-content"><section class="page-intro"><div class="container"><span class="eyebrow">${rescueOnly ? 'Pangan punya kesempatan kedua' : 'Langsung dari kebun'}</span><h1>${title}</h1><p>${description}</p></div></section><section class="catalog-section"><div class="container">${rescueOnly ? `<p class="filter-note">${ICONS.clock} Penawaran demo Food Rescue — harga dan batas waktu adalah data contoh.</p>` : ''}${categoryButtons()}<div class="catalog-tools"><span class="catalog-count">${query ? `Hasil pencarian “${esc(query)}” · ` : ''}${products.length} produk tersedia</span><select class="sort-select" id="sort-select" aria-label="Urutkan produk"><option value="default">Urutkan: rekomendasi</option><option value="price-asc">Harga: rendah ke tinggi</option><option value="price-desc">Harga: tinggi ke rendah</option></select></div><div class="product-grid" id="catalog-grid">${products.length ? products.map(productCard).join('') : '<div class="empty-products">Tidak ada produk yang cocok. Coba kata kunci atau kategori lain.</div>'}</div></div></section></main>`;
}
function renderProductDetail(id) {
  const product = findProduct(decodeURIComponent(id));
  if (!product) return `<main id="main-content"><section class="page-intro"><div class="container"><h1>Produk tidak ditemukan</h1><a class="btn btn-primary" data-route="/katalog" href="/katalog">Kembali ke katalog</a></div></section></main>`;
  const rescue = isRescue(product);
  const price = livePrice(product);
  const reviews = state.reviews.filter((review) => review.productId === product.id);
  return `<main id="main-content"><section class="detail-top"><div class="container"><div class="breadcrumb"><a href="/" data-route="/">Beranda</a><span>›</span><a href="/katalog" data-route="/katalog">Katalog</a><span>›</span><span>${esc(product.name)}</span></div><div class="detail-grid"><img class="detail-photo" src="${product.image}" onerror="this.onerror=null;this.src='/assets/images/hero-hasil-tani.jpg'" alt="${esc(product.name)} dari ${esc(product.farmer)}" /><div class="detail-copy"><span class="eyebrow">${rescue ? 'Food Rescue · penawaran khusus' : 'Pre-order batch panen'}</span><h1>${esc(product.name)}</h1><div class="detail-farmer">${ICONS.pin} ${esc(product.farmer)} · ${esc(product.area)}</div><div class="detail-price">${money(price)} <small>per ${esc(product.unit)}</small>${rescue ? `<del>${money(product.price)}</del>` : ''}</div><p>${esc(product.description)} Ditanam oleh petani lokal dengan perhatian pada kesegaran dan kualitas hasil.</p><div class="detail-facts"><div class="detail-fact"><small>Estimasi panen</small><strong>${esc(product.harvest)}</strong></div><div class="detail-fact"><small>Stok tersedia</small><strong>${product.stock} ${esc(product.unit)}</strong></div><div class="detail-fact"><small>Asal kebun</small><strong>${esc(product.area)}</strong></div><div class="detail-fact"><small>Penilaian contoh</small><strong>★ ${product.rating} / 5</strong></div></div>${rescue ? `<p class="filter-note">${ICONS.clock} ${esc(product.rescueUntil)} · harga rescue merupakan data demo.</p>` : ''}<div class="detail-buy"><input class="form-input" id="detail-quantity" type="number" min="1" max="${product.stock}" value="1" aria-label="Jumlah ${esc(product.name)}" /><button class="btn btn-primary" data-action="add-detail" data-id="${product.id}">Tambah ke keranjang ${ICONS.basket}</button></div><div class="demo-warning">Interaksi ini adalah simulasi frontend. Pesanan, pembayaran, dan stok tidak tersimpan pada database.</div></div></div></div></section><section class="detail-section"><div class="container"><div class="section-head"><div><span class="eyebrow">Dari pembeli</span><h2>Catatan kesegaran</h2><p>Ulasan contoh untuk menunjukkan modul rating pada rancangan PanenLokal.</p></div></div><div class="review-list">${reviews.length ? reviews.map((review) => `<article class="review-card"><div class="review-stars">${'★'.repeat(review.rating)}${'☆'.repeat(5-review.rating)}</div><p>${esc(review.comment || 'Segar dan kualitasnya baik.')}</p><strong>${esc(review.name || 'Pembeli demo')} · ulasan demo</strong></article>`).join('') : `<article class="review-card"><div class="review-stars">★★★★★</div><p>Sayurannya segar, informasi panennya jelas. Senang bisa tahu dari kebun mana hasilnya berasal.</p><strong>Rani · ulasan contoh</strong></article><article class="review-card"><div class="review-stars">★★★★★</div><p>Pemesanan sebelum panen membuat belanja mingguan jadi lebih terencana.</p><strong>Arif · ulasan contoh</strong></article><article class="review-card"><div class="review-stars">★★★★☆</div><p>Produk lokal yang enak dan dikemas sederhana. Akan pesan lagi.</p><strong>Dina · ulasan contoh</strong></article>`}</div></div></section></main>`;
}
function renderCart() {
  const rows = cartRows();
  const subtotal = cartTotal();
  const shipping = subtotal >= 75000 ? 0 : 8000;
  if (!rows.length) return `<main id="main-content"><section class="page-intro"><div class="container"><span class="eyebrow">Belanja terencana</span><h1>Keranjang Anda</h1><p>Periksa kembali pilihan panen sebelum lanjut ke checkout demo.</p></div></section><div class="container cart-layout"><section class="cart-panel empty-cart"><span class="empty-cart-icon">${ICONS.basket}</span><h2>Keranjang masih kosong</h2><p>Temukan panen yang Anda suka dan tambahkan ke keranjang untuk memulai.</p><a class="btn btn-primary" href="/katalog" data-route="/katalog">Jelajahi katalog ${ICONS.arrow}</a></section></div></main>`;
}
function dashboardNavigation(role) {
  const entries = role === 'petani' ? [['Dasbor', '#ringkasan', ICONS.chart], ['Lahan & batch panen', '#batch', ICONS.sprout], ['Food Rescue', '#rescue', ICONS.leaf], ['Pesanan', '#pesanan', ICONS.parcel], ['Laporan', '#laporan', ICONS.chart]] : role === 'admin' ? [['Ringkasan', '#ringkasan', ICONS.chart], ['Verifikasi pembayaran', '#pembayaran', ICONS.shield], ['Titik pengambilan', '#titik', ICONS.pin], ['Pengguna', '#pengguna', ICONS.users], ['Laporan', '#laporan', ICONS.chart]] : [['Ringkasan', '#ringkasan', ICONS.chart], ['Pesanan saya', '#pesanan', ICONS.parcel], ['Ulasan', '#ulasan', ICONS.star]];
  return `<aside class="dashboard-nav"><div class="dashboard-nav-label">Menu demo</div>${entries.map(([label, href, icon], index) => `<a class="${index === 0 ? 'active' : ''}" href="${href}" data-dash-anchor="${href}">${icon}${label}</a>`).join('')}</aside>`;
}
function metric(label, value, foot, icon) { return `<article class="metric-card"><span class="metric-icon">${icon}</span><div class="metric-label">${label}</div><div class="metric-value">${value}</div><div class="metric-foot">${foot}</div></article>`; }
function renderBuyerDashboard() {
  const sampleOrders = [{ code: 'PL-26018', product: 'Paket Sayur Akhir Pekan', date: '20 Nov 2026', status: 'Dalam proses', cls: 'status-wait', total: 68000 }, ...state.orders.map((order) => ({ code: order.code, product: order.items.map((item) => item.name).join(', '), date: order.date, status: 'Konfirmasi demo', cls: 'status-wait', total: order.total }))];
  return `<div class="metric-grid">${metric('Pesanan aktif', String(sampleOrders.length), 'Data pesanan contoh', ICONS.parcel)}${metric('Total belanja demo', money(sampleOrders.reduce((sum, item) => sum + item.total, 0)), 'Tidak ada pembayaran nyata', ICONS.basket)}${metric('Ulasan tersimpan', String(state.reviews.length), 'Dari interaksi browser ini', ICONS.star)}</div><section class="dash-panel" id="pesanan"><div class="dash-panel-head"><h2>Pesanan terbaru</h2><small>Data contoh / demo</small></div><div class="dash-table-wrap"><table class="dash-table"><thead><tr><th>Kode</th><th>Ringkasan</th><th>Tanggal</th><th>Status</th><th>Total contoh</th></tr></thead><tbody>${sampleOrders.map((order) => `<tr><td><strong>${esc(order.code)}</strong></td><td>${esc(order.product)}</td><td>${esc(order.date)}</td><td><span class="status-pill ${order.cls}">${esc(order.status)}</span></td><td>${money(order.total)}</td></tr>`).join('')}</tbody></table></div></section><div class="dashboard-bottom"><section class="dash-panel" id="ulasan"><div class="dash-panel-head"><h2>Belanja lokal lebih bermakna</h2><small>Langkah berikutnya</small></div><div class="activity-list"><div class="activity-item"><span class="activity-dot"></span><div><strong>Jelajahi batch panen lokal</strong><small>Kenali petani, estimasi panen, dan stok.</small></div></div><div class="activity-item"><span class="activity-dot"></span><div><strong>Tulis ulasan kesegaran</strong><small>Modul penuh mensyaratkan pesanan selesai.</small></div></div><div class="dashboard-actions"><button class="dash-action" data-action="open-review" data-id="tomat-cherry">Coba ulasan demo</button></div><a class="btn btn-soft btn-small" href="/katalog" data-route="/katalog">Pilih panen berikutnya ${ICONS.arrow}</a></div></section><article class="impact-card"><span class="eyebrow">Belanja yang menumbuhkan</span><h3>Lebih dekat.</h3><p>Pesan hasil tani lokal, dukung petani sekitar, dan ikut membantu pangan terserap.</p></article></div>`;
}
function renderFarmerDashboard() {
  const products = allProducts();
  const sales = Object.values(state.cart).reduce((sum, quantity) => sum + quantity, 0);
  return `<div class="metric-grid">${metric('Batch aktif', String(products.length), 'Batch contoh pada katalog', ICONS.sprout)}${metric('Pesanan terkait', String(sales + 12), 'Angka ilustrasi untuk dashboard', ICONS.parcel)}${metric('Pendapatan selesai', 'Rp 1.840.000', 'Nilai contoh · bukan saldo nyata', ICONS.chart)}</div><section class="dash-panel" id="batch"><div class="dash-panel-head"><h2>Batch panen saya</h2><button class="btn btn-primary btn-small" data-action="farmer-add-batch">+ Tambah batch demo</button></div><div class="dash-table-wrap"><table class="dash-table"><thead><tr><th>Komoditas</th><th>Estimasi</th><th>Stok contoh</th><th>Harga</th><th>Food Rescue</th><th>Aksi</th></tr></thead><tbody>${products.map((product) => `<tr><td><strong>${esc(product.name)}</strong>${esc(product.farmer)}</td><td>${esc(product.harvest)}</td><td>${product.stock} ${esc(product.unit)}</td><td>${money(livePrice(product))}/${esc(product.unit)}</td><td><span class="status-pill ${isRescue(product) ? 'status-wait' : ''}">${isRescue(product) ? 'Aktif' : 'Nonaktif'}</span></td><td><button class="dash-action" data-action="toggle-rescue" data-id="${product.id}">${isRescue(product) ? 'Nonaktifkan' : 'Aktifkan rescue'}</button></td></tr>`).join('')}</tbody></table></div></section><div class="dashboard-bottom"><section class="dash-panel" id="pesanan"><div class="dash-panel-head"><h2>Pesanan yang perlu disiapkan</h2><small>Cuplikan data demo</small></div><div class="activity-list"><div class="activity-item"><span class="activity-dot"></span><div><strong>PL-26018 · Paket Sayur Akhir Pekan</strong><small>Perkiraan panen 24 Nov · status diproses (contoh)</small></div></div><div class="activity-item"><span class="activity-dot"></span><div><strong>Jadwal panen pekan ini</strong><small>Batch terbuka ditampilkan pada katalog publik.</small></div></div></div></section><article class="impact-card" id="laporan"><span class="eyebrow">Dampak contoh</span><h3>18,5 kg</h3><p>Ilustrasi pangan rescue terjual dari pesanan selesai. Laporan produksi nyata perlu dihitung dari database.</p></article></div><div class="dashboard-banner" id="rescue">Panel petani ini menggambarkan pengelolaan lahan, komoditas, batch panen, dan Food Rescue. Perubahan hanya tersimpan di browser ini.</div>`;
}
function renderAdminDashboard() {
  const payments = [{ id: 'PL-26021', buyer: 'Nadia Putri', amount: 94000, method: 'Transfer bank' }, { id: 'PL-26020', buyer: 'Raka Pratama', amount: 56000, method: 'Transfer bank' }, { id: 'PL-26019', buyer: 'Sinta Ayu', amount: 78000, method: 'Transfer bank' }];
  const status = (payment) => state.paymentStatus[payment.id] || (payment.id === 'PL-26019' ? 'terverifikasi' : 'menunggu');
  return `<div class="metric-grid">${metric('Total pesanan', '128', 'Angka ilustrasi', ICONS.parcel)}${metric('Menunggu verifikasi', String(payments.filter((payment) => status(payment) === 'menunggu').length), 'Data pembayaran contoh', ICONS.shield)}${metric('Nilai transaksi selesai', 'Rp 8.460.000', 'Bukan laporan transaksi nyata', ICONS.chart)}</div><section class="dash-panel" id="pembayaran"><div class="dash-panel-head"><h2>Verifikasi pembayaran</h2><small>Catatan pembayaran contoh</small></div><div class="dash-table-wrap"><table class="dash-table"><thead><tr><th>Kode pesanan</th><th>Pembeli</th><th>Nominal</th><th>Metode</th><th>Status</th><th>Aksi simulasi</th></tr></thead><tbody>${payments.map((payment) => { const current = status(payment); return `<tr><td><strong>${payment.id}</strong></td><td>${payment.buyer}</td><td>${money(payment.amount)}</td><td>${payment.method}</td><td><span class="status-pill ${current === 'menunggu' ? 'status-wait' : current === 'ditolak' ? 'status-rejected' : ''}">${current === 'terverifikasi' ? 'Terverifikasi' : current === 'ditolak' ? 'Ditolak' : 'Menunggu'}</span></td><td>${current === 'menunggu' ? `<div class="dashboard-actions"><button class="dash-action" data-action="verify-payment" data-id="${payment.id}">Terima</button><button class="dash-action danger" data-action="reject-payment" data-id="${payment.id}">Tolak</button></div>` : '<span class="catalog-count">Tidak ada aksi</span>'}</td></tr>`; }).join('')}</tbody></table></div></section><div class="dashboard-bottom"><section class="dash-panel" id="titik"><div class="dash-panel-head"><h2>Titik pengambilan</h2><small>Contoh titik aktif</small></div><div class="activity-list"><div class="activity-item"><span class="activity-dot"></span><div><strong>Pasar Cihapit</strong><small>Bandung Wetan · buka 07.00–11.00</small></div></div><div class="activity-item"><span class="activity-dot"></span><div><strong>Balai RW Cibiru</strong><small>Cibiru, Bandung · buka 08.00–12.00</small></div></div></div></section><article class="impact-card" id="laporan"><span class="eyebrow">Pangan terselamatkan · contoh</span><h3>286 kg</h3><p>Metrik ilustrasi untuk menunjukkan laporan Food Rescue dari pesanan selesai.</p></article></div><div class="dashboard-banner" id="pengguna">Tombol verifikasi hanya mengubah status contoh di localStorage. Tidak ada pembayaran yang diperiksa atau disetujui secara nyata.</div>`;
}
function renderDashboard(role) {
  const labels = { pembeli: ['Ruang pembeli', 'Simpan belanja, ikuti pesanan, dan kenali hasil panen pilihan Anda.'], petani: ['Ruang petani', 'Lihat batch panen, kelola penawaran rescue, dan pantau ringkasan pesanan.'], admin: ['Ruang admin', 'Pantau ringkasan transaksi, simulasi verifikasi, serta titik pengambilan.'] };
  const [title, description] = labels[role] || labels.pembeli;
  const content = role === 'petani' ? renderFarmerDashboard() : role === 'admin' ? renderAdminDashboard() : renderBuyerDashboard();
  return `<main id="main-content"><section class="dashboard-intro"><div class="container dashboard-title-row"><div><span class="eyebrow">Dasbor berbasis rancangan</span><h1>${title}</h1><p>${description}</p></div><span class="demo-chip">${ICONS.shield} SIMULASI FRONTEND</span></div></section><div class="container dashboard-layout">${dashboardNavigation(role)}<section class="dashboard-main" id="ringkasan">${content}</section></div></main>`;
}
function renderAbout() {
  const modules = [
    ['01', 'Akun & peran', 'Admin, petani, dan pembeli mempunyai kebutuhan serta ruang kerja berbeda.'], ['02', 'Lahan & kalender', 'Petani mencatat lokasi lahan, komoditas, batch, dan perkiraan panen.'], ['03', 'Pre-order panen', 'Pembeli dapat merencanakan belanja berdasarkan batch dan estimasi tanggal.'], ['04', 'Food Rescue', 'Penawaran khusus membantu menyerap surplus atau hasil yang perlu segera dijual.'], ['05', 'Keranjang & pesanan', 'Satu pesanan dapat berisi produk dari beberapa petani dengan ringkasan item.'], ['06', 'Pembayaran manual', 'Bukti transfer diverifikasi admin; rancangan tidak memakai payment gateway.'], ['07', 'Antar & ambil', 'Pembeli memilih pengantaran atau mengambil pesanan di titik yang tersedia.'], ['08', 'Ulasan kesegaran', 'Pembeli menilai produk setelah pesanan selesai.'], ['09', 'Analitik dampak', 'Ringkasan pendapatan dan pangan rescue mengikuti aturan pesanan selesai.']
  ];
  return `<main id="main-content"><section class="about-hero"><div class="container about-hero-grid"><div><span class="eyebrow">Tentang PanenLokal</span><h1>Panen lokal.<br />Dampak terasa.</h1><p>PanenLokal dirancang sebagai platform yang mempertemukan petani, konsumen, dan warung makan. Pre-order membantu petani membaca permintaan, sementara Food Rescue memberi hasil panen berlebih kesempatan untuk terserap.</p><p>Gagasan ini selaras dengan SDG 2 — Zero Hunger, dan menjadi konteks pembelajaran untuk membangun aplikasi web yang terintegrasi.</p><a class="btn btn-primary" href="/katalog" data-route="/katalog">Lihat katalog demo ${ICONS.arrow}</a></div><img class="about-image" src="/assets/images/hero-hasil-tani.jpg" alt="Hasil tani lokal yang baru dipanen" /></div></section><section class="section"><div class="container"><div class="section-head"><div><span class="eyebrow">Peta sistem</span><h2>Sembilan modul, satu alur.</h2><p>Ringkasan modul berikut berasal dari rancangan sistem PanenLokal.</p></div></div><div class="module-grid">${modules.map(([number, title, text]) => `<article class="module-card"><span class="module-number">${number}</span><h3>${title}</h3><p>${text}</p></article>`).join('')}</div><div class="demo-warning" style="margin-top:18px">Website ini adalah demo antarmuka interaktif. Backend PHP Native, MySQL/MariaDB, XAMPP, autentikasi aman, pembayaran dan unggah berkas belum diimplementasikan.</div></div></section></main>`;
}
function renderSearchModal() {
  return `<div class="modal-backdrop" data-action="close-modal"><section class="modal-card" role="dialog" aria-modal="true" aria-labelledby="search-title"><div class="modal-head"><h2 id="search-title">Cari hasil panen</h2><button class="modal-close" type="button" data-action="close-modal" aria-label="Tutup">×</button></div><p>Temukan komoditas atau petani lokal di katalog.</p><form data-form="search"><label class="checkout-label" for="search-modal-input">Kata kunci</label><input class="form-input" id="search-modal-input" name="q" placeholder="Contoh: stroberi, sayuran…" autofocus/><button class="btn btn-primary btn-block" style="margin-top:14px" type="submit">Cari di katalog ${ICONS.arrow}</button></form></section></div>`;
}
function openModal(kind, data = {}) {
  const root = document.querySelector('#modal-root');
  if (kind === 'search') root.innerHTML = renderSearchModal();
  else if (kind === 'review') root.innerHTML = `<div class="modal-backdrop" data-action="close-modal"><section class="modal-card" role="dialog" aria-modal="true" aria-labelledby="review-title"><div class="modal-head"><h2 id="review-title">Bagikan kesegaran</h2><button class="modal-close" data-action="close-modal" aria-label="Tutup">×</button></div><p>Ulasan ini disimpan hanya sebagai contoh di browser. Pada sistem lengkap, ulasan mensyaratkan pesanan selesai.</p><form data-form="review" data-product="${esc(data.productId)}"><label class="checkout-label">Penilaian</label><div class="rating-input">${[5,4,3,2,1].map((rating) => `<input type="radio" id="rating-${rating}" name="rating" value="${rating}" ${rating === 5 ? 'checked' : ''}/><label for="rating-${rating}" aria-label="${rating} bintang">★</label>`).join('')}</div><label class="checkout-label" for="review-comment">Catatan kesegaran</label><textarea class="form-textarea" id="review-comment" name="comment" maxlength="240" placeholder="Apa yang paling Anda sukai?" data-counter required></textarea><div class="catalog-count" id="character-count">0/240 karakter</div><button class="btn btn-primary btn-block" style="margin-top:13px" type="submit">Simpan ulasan demo</button></form></section></div>`;
  else return;
  root.querySelector('input,textarea')?.focus();
}
function render() {
  const path = routePath();
  let page;
  if (path === '/') page = renderHome();
  else if (path === '/katalog') page = renderCatalog(false);
  else if (path === '/food-rescue') page = renderCatalog(true);
  else if (path === '/keranjang') page = renderCart();
  else if (path === '/tentang') page = renderAbout();
  else if (path.startsWith('/produk/')) page = renderProductDetail(path.slice('/produk/'.length));
  else if (path.startsWith('/peran/')) page = renderDashboard(path.slice('/peran/'.length));
  else page = `<main id="main-content"><section class="page-intro"><div class="container"><h1>Halaman tidak ditemukan</h1><a class="btn btn-primary" href="/" data-route="/">Kembali ke beranda</a></div></section></main>`;
  document.querySelector('#app').innerHTML = `${headerMarkup()}${page}${footerMarkup()}`;
  if (path === '/katalog' || path === '/food-rescue') bindSort();
  if (path === '/keranjang') bindDeliveryOptions();
  document.title = path === '/' ? 'PanenLokal — Panen baik, dekat di rumah' : `${pageTitle(path)} — PanenLokal`;
}
function pageTitle(path) {
  if (path === '/katalog') return 'Katalog panen';
  if (path === '/food-rescue') return 'Food Rescue';
  if (path === '/keranjang') return 'Keranjang';
  if (path === '/tentang') return 'Tentang';
  if (path.startsWith('/produk/')) return findProduct(decodeURIComponent(path.slice(8)))?.name || 'Produk';
  if (path.startsWith('/peran/')) return 'Dasbor demo';
  return 'Panen baik, dekat di rumah';
}
function bindSort() {
  const select = document.querySelector('#sort-select');
  select?.addEventListener('change', () => {
    const rescueOnly = routePath() === '/food-rescue';
    const query = new URLSearchParams(location.search).get('q') || '';
    const products = allProducts().filter((product) => (!rescueOnly || isRescue(product)) && (activeCategory === 'Semua' || product.category === activeCategory) && (!query || `${product.name} ${product.farmer} ${product.category}`.toLowerCase().includes(query.toLowerCase())));
    if (select.value === 'price-asc') products.sort((a,b) => livePrice(a) - livePrice(b));
    if (select.value === 'price-desc') products.sort((a,b) => livePrice(b) - livePrice(a));
    document.querySelector('#catalog-grid').innerHTML = products.length ? products.map(productCard).join('') : '<div class="empty-products">Tidak ada produk yang cocok.</div>';
  });
}
function bindDeliveryOptions() {
  const syncSummary = () => {
    const method = document.querySelector('input[name="delivery"]:checked')?.value || 'antar';
    const subtotal = cartTotal();
    const shipping = method === 'antar' && subtotal < 75000 ? 8000 : 0;
    const values = document.querySelectorAll('.summary-line strong');
    if (values[1]) values[1].textContent = shipping ? money(shipping) : 'Gratis';
    if (values[2]) values[2].textContent = money(subtotal + shipping);
  };
  const syncDeliveryFields = () => {
    const pickup = document.querySelector('input[name="delivery"]:checked')?.value === 'ambil';
    document.querySelector('#pickup-field').hidden = !pickup;
    document.querySelector('#address-field').hidden = pickup;
    document.querySelector('#pickup-field select').required = pickup;
    document.querySelector('#address-field textarea').required = !pickup;
    syncSummary();
  };
  document.querySelectorAll('input[name="delivery"]').forEach((radio) => radio.addEventListener('change', syncDeliveryFields));
  syncDeliveryFields();
}
function addToCart(id, quantity = 1) {
  const product = findProduct(id);
  if (!product) return;
  const next = Number(state.cart[id] || 0) + Number(quantity);
  if (next > product.stock) { toast(`Stok contoh ${product.name} hanya ${product.stock} ${product.unit}.`, 'error'); return; }
  if (next <= 0) { toast('Jumlah harus lebih dari nol.', 'error'); return; }
  state.cart[id] = next;
  saveState();
  render();
  toast(`${product.name} ditambahkan ke keranjang demo.`);
}
function showConfirm(code) {
  document.querySelector('#modal-root').innerHTML = `<div class="modal-backdrop"><section class="modal-card" role="dialog" aria-modal="true" aria-labelledby="confirm-title"><div class="modal-head"><h2 id="confirm-title">Konfirmasi demo tersimpan</h2><button class="modal-close" data-action="close-modal" aria-label="Tutup">×</button></div><p>Kode contoh <strong>${esc(code)}</strong> tersimpan di browser ini. Ini bukan pesanan sungguhan dan tidak ada pembayaran yang diproses.</p><button class="btn btn-primary btn-block" data-action="go-buyer">Lihat dashboard pembeli</button></section></div>`;
}

document.addEventListener('click', (event) => {
  const routeLink = event.target.closest('[data-route]');
  if (routeLink) { event.preventDefault(); navigate(routeLink.dataset.route); return; }
  const roleChoice = event.target.closest('[data-role]');
  if (roleChoice) { state.role = roleChoice.dataset.role; saveState(); document.querySelector('#role-menu')?.classList.remove('open'); navigate(`/peran/${state.role}`); return; }
  const category = event.target.closest('[data-category]');
  if (category) { activeCategory = category.dataset.category; const path = routePath(); render(); document.querySelector(`[data-category="${CSS.escape(activeCategory)}"]`)?.scrollIntoView({ block: 'nearest', inline: 'nearest' }); return; }
  const anchor = event.target.closest('[data-dash-anchor]');
  if (anchor) { event.preventDefault(); document.querySelector(anchor.dataset.dashAnchor)?.scrollIntoView({ behavior: 'smooth', block: 'start' }); return; }
  const control = event.target.closest('[data-action]');
  if (!control) { if (event.target.classList.contains('modal-backdrop')) document.querySelector('#modal-root').innerHTML = ''; return; }
  const id = control.dataset.id;
  switch (control.dataset.action) {
    case 'toggle-menu': document.querySelector('.nav-links')?.classList.toggle('open'); break;
    case 'toggle-role-menu': { const menu = document.querySelector('#role-menu'); menu.classList.toggle('open'); control.setAttribute('aria-expanded', menu.classList.contains('open')); break; }
    case 'open-search': openModal('search'); break;
    case 'close-modal': document.querySelector('#modal-root').innerHTML = ''; break;
    case 'add': addToCart(id); break;
    case 'add-detail': { const quantity = Number(document.querySelector('#detail-quantity')?.value || 1); addToCart(id, quantity); break; }
    case 'remove': delete state.cart[id]; saveState(); render(); toast('Produk dihapus dari keranjang demo.'); break;
    case 'increase': addToCart(id); break;
    case 'decrease': { state.cart[id] = Number(state.cart[id] || 0) - 1; if (state.cart[id] <= 0) delete state.cart[id]; saveState(); render(); break; }
    case 'toggle-rescue': { const product = findProduct(id); state.rescueOverrides[id] = !isRescue(product); saveState(); render(); toast(state.rescueOverrides[id] ? 'Food Rescue diaktifkan pada batch demo.' : 'Food Rescue dinonaktifkan pada batch demo.'); break; }
    case 'verify-payment': state.paymentStatus[id] = 'terverifikasi'; saveState(); render(); toast(`Status contoh ${id} diubah menjadi terverifikasi.`); break;
    case 'reject-payment': state.paymentStatus[id] = 'ditolak'; saveState(); render(); toast(`Status contoh ${id} diubah menjadi ditolak.`); break;
    case 'farmer-add-batch': toast('Form tambah batch dan penyimpanan database belum termasuk demo frontend.'); break;
    case 'go-buyer': document.querySelector('#modal-root').innerHTML = ''; state.role = 'pembeli'; saveState(); navigate('/peran/pembeli'); break;
    case 'open-review': openModal('review', { productId: id }); break;
    default: break;
  }
});

document.addEventListener('submit', (event) => {
  const form = event.target;
  if (form.matches('[data-form="search"]')) {
    event.preventDefault(); const query = new FormData(form).get('q')?.toString().trim() || ''; document.querySelector('#modal-root').innerHTML = ''; activeCategory = 'Semua'; navigate(`/katalog${query ? `?q=${encodeURIComponent(query)}` : ''}`);
  }
  if (form.matches('[data-form="newsletter"]')) { event.preventDefault(); form.reset(); toast('Terima kasih! Newsletter ini hanya simulasi.'); }
  if (form.matches('[data-form="checkout"]')) {
    event.preventDefault();
    const data = new FormData(form); const method = data.get('delivery');
    if (method === 'ambil' && !data.get('pickup')) { toast('Pilih titik pengambilan terlebih dahulu.', 'error'); return; }
    if (method === 'antar' && !String(data.get('address') || '').trim()) { toast('Isi alamat tujuan terlebih dahulu.', 'error'); return; }
    const rows = cartRows();
    if (!rows.length) { toast('Keranjang demo kosong.', 'error'); return; }
    const stamp = Date.now().toString().slice(-6);
    const code = `PL-DEMO-${stamp}`;
    const shipping = method === 'antar' && cartTotal() < 75000 ? 8000 : 0;
    const order = { code, date: new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }).format(new Date()), total: cartTotal() + shipping, delivery: method, buyer: String(data.get('name') || ''), items: rows.map(({ product, quantity }) => ({ name: product.name, quantity, unit: product.unit, price: livePrice(product) })) };
    state.orders.unshift(order); state.cart = {}; state.role = 'pembeli'; saveState(); render(); showConfirm(code);
  }
  if (form.matches('[data-form="review"]')) {
    event.preventDefault(); const data = new FormData(form); const productId = form.dataset.product; state.reviews.push({ productId, rating: Number(data.get('rating') || 5), comment: String(data.get('comment') || '').trim(), name: 'Pembeli demo' }); saveState(); document.querySelector('#modal-root').innerHTML = ''; render(); toast('Ulasan contoh tersimpan di browser ini.');
  }
});

document.addEventListener('input', (event) => {
  if (event.target.matches('[data-counter]')) { const counter = document.querySelector('#character-count'); if (counter) counter.textContent = `${event.target.value.length}/240 karakter`; }
});
window.addEventListener('popstate', render);
render();
