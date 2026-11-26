/* PanenLokal — script.js */
'use strict';

/* ---------- Navbar toggle (mobile) ---------- */
function toggleNav() {
    const menu = document.getElementById('navbarMenu');
    if (menu) menu.classList.toggle('open');
}

/* ---------- Auto-dismiss alerts ---------- */
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.alert.alert-dismissible').forEach(function (el) {
        setTimeout(function () {
            if (!el.isConnected) return;
            el.style.transition = 'opacity .5s';
            el.style.opacity = '0';
            setTimeout(function () {
                if (el.isConnected) el.remove();
            }, 500);
        }, 4000);
    });
});

/* ---------- Form error helpers ---------- */
function clearErrors(scope) {
    const root = scope || document;
    root.querySelectorAll('.is-invalid').forEach(function (el) {
        el.classList.remove('is-invalid');
    });
    root.querySelectorAll('.form-error').forEach(function (el) {
        el.remove();
    });
}

function showError(el, message) {
    if (!el) return;
    el.classList.add('is-invalid');
    const err = document.createElement('div');
    err.className = 'form-error';
    err.textContent = message;
    const group = el.closest('.form-group');
    if (group) {
        const existing = group.querySelector('.form-error');
        if (existing) existing.remove();
        group.appendChild(err);
    } else {
        el.insertAdjacentElement('afterend', err);
    }
}

/* ---------- Form: Login validation ---------- */
function validateLogin(e) {
    const u = document.getElementById('username');
    const p = document.getElementById('password');
    let ok = true;
    clearErrors();
    if (!u || !u.value.trim()) { showError(u, 'Username tidak boleh kosong'); ok = false; }
    if (!p || !p.value.trim()) { showError(p, 'Password tidak boleh kosong'); ok = false; }
    if (!ok) e.preventDefault();
}

/* ---------- Form: Register validation ---------- */
function validateRegister(e) {
    clearErrors();
    let ok = true;
    const fields = { nama_lengkap: 'Nama lengkap', username: 'Username', password: 'Password', confirm_password: 'Konfirmasi password' };
    for (const [id, label] of Object.entries(fields)) {
        const el = document.getElementById(id);
        if (el && !el.value.trim()) { showError(el, label + ' tidak boleh kosong'); ok = false; }
    }
    const pw  = document.getElementById('password');
    const cpw = document.getElementById('confirm_password');
    if (pw && cpw && pw.value && cpw.value && pw.value !== cpw.value) {
        showError(cpw, 'Password tidak cocok'); ok = false;
    }
    if (pw && pw.value && pw.value.length < 6) {
        showError(pw, 'Password minimal 6 karakter'); ok = false;
    }
    const role = document.getElementById('role');
    if (role && role.value === 'petani') {
        const nu = document.getElementById('nama_usaha');
        if (nu && !nu.value.trim()) { showError(nu, 'Nama usaha tidak boleh kosong'); ok = false; }
    }
    if (!ok) e.preventDefault();
}

/* ---------- Form: Batch / Rescue validation ---------- */
function validateBatch(e) {
    clearErrors();
    let ok = true;
    const hn = document.getElementById('harga_normal');
    const st = document.getElementById('stok_tersedia');
    const ra = document.getElementById('rescue_aktif');
    const hr = document.getElementById('harga_rescue');

    if (hn && parseFloat(hn.value) <= 0) { showError(hn, 'Harga normal harus lebih dari 0'); ok = false; }
    if (st && parseFloat(st.value) < 0)  { showError(st, 'Stok tidak boleh negatif'); ok = false; }
    if (ra && ra.checked && hr) {
        if (!hr.value || parseFloat(hr.value) <= 0) { showError(hr, 'Harga rescue harus lebih dari 0'); ok = false; }
        else if (hn && parseFloat(hr.value) >= parseFloat(hn.value)) {
            showError(hr, 'Harga rescue harus lebih rendah dari harga normal'); ok = false;
        }
    }
    if (!ok) e.preventDefault();
}

/* ---------- Cart: preview total ---------- */
function updateCartTotal() {
    let total = 0;
    document.querySelectorAll('.cart-row').forEach(function (row) {
        const qty   = parseFloat(row.querySelector('.qty-input')?.value || 0);
        const price = parseFloat(row.dataset.price || 0);
        const sub   = qty * price;
        const subEl = row.querySelector('.subtotal');
        if (subEl) subEl.textContent = formatRupiah(sub);
        total += sub;
    });
    const totEl = document.getElementById('cart-total');
    if (totEl) totEl.textContent = formatRupiah(total);
}

function formatRupiah(n) {
    return 'Rp ' + Math.round(n).toLocaleString('id-ID');
}

/* ---------- Character counter for textarea ---------- */
function attachCharCounter(textareaId, counterId, max) {
    const ta = document.getElementById(textareaId);
    const ct = document.getElementById(counterId);
    if (!ta || !ct) return;
    function update() {
        const len = ta.value.length;
        ct.textContent = len + ' / ' + max;
        ct.style.color = len > max ? 'var(--red)' : 'var(--gray-500)';
    }
    ta.addEventListener('input', update);
    update();
}

/* ---------- Countdown timer (Food Rescue) ---------- */
function startCountdown(elementOrId, deadlineISO) {
    const el = typeof elementOrId === 'string'
        ? document.getElementById(elementOrId)
        : elementOrId;
    if (!el || !deadlineISO) return;
    const normalized = String(deadlineISO).includes('T')
        ? String(deadlineISO)
        : String(deadlineISO).replace(' ', 'T');
    const deadline = new Date(normalized).getTime();
    if (Number.isNaN(deadline)) return;
    function tick() {
        const now  = Date.now();
        const diff = deadline - now;
        if (diff <= 0) { el.innerHTML = '<span class="badge badge-danger">Penawaran Berakhir</span>'; return; }
        const d = Math.floor(diff / 86400000);
        const h = Math.floor((diff % 86400000) / 3600000);
        const m = Math.floor((diff % 3600000) / 60000);
        const s = Math.floor((diff % 60000) / 1000);
        const dayUnit = d > 0 ? '<span class="countdown-unit">' + d + 'hr</span>' : '';
        el.innerHTML = '<span class="countdown">' + dayUnit + '<span class="countdown-unit">' + pad(h) + 'j</span><span class="countdown-unit">' + pad(m) + 'm</span><span class="countdown-unit">' + pad(s) + 'd</span></span>';
        setTimeout(tick, 1000);
    }
    tick();
}
function pad(n) { return n < 10 ? '0' + n : n; }

/* ---------- Confirm delete / action ---------- */
function confirmAction(msg) {
    return confirm(msg || 'Yakin melanjutkan aksi ini?');
}
function confirmDelete(msg) {
    return confirm(msg || 'Yakin ingin menghapus data ini?');
}

/* ---------- Toggle rescue fields ---------- */
function toggleRescueFields() {
    const cb   = document.getElementById('rescue_aktif');
    const wrap = document.getElementById('rescue-fields');
    if (!cb || !wrap) return;
    wrap.style.display = cb.checked ? 'block' : 'none';
}

/* ---------- Toggle petani fields in register ---------- */
function togglePetaniFields() {
    const role = document.getElementById('role');
    const wrap = document.getElementById('petani-fields');
    if (!role || !wrap) return;
    wrap.style.display = role.value === 'petani' ? 'block' : 'none';
}

/* ---------- Stats Count-Up Animation ---------- */
function animateCountUp(el, target, duration) {
    if (!el) return;
    if (target <= 0) {
        el.textContent = '0';
        return;
    }
    const start = performance.now();
    function frame(now) {
        const progress = Math.min((now - start) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        const current = Math.round(target * eased);
        el.textContent = current.toLocaleString('id-ID');
        if (progress < 1) {
            requestAnimationFrame(frame);
        } else {
            el.textContent = target.toLocaleString('id-ID');
        }
    }
    requestAnimationFrame(frame);
}

function initCountUp() {
    const counters = document.querySelectorAll('[data-countup]');
    if (!counters.length) return;

    const reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduceMotion) {
        counters.forEach(function (el) {
            const target = parseInt(el.getAttribute('data-countup'), 10);
            if (!Number.isNaN(target)) el.textContent = target.toLocaleString('id-ID');
        });
        return;
    }

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(function (entries, obs) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                const el = entry.target;
                if (el.dataset.counted === '1') return;
                el.dataset.counted = '1';
                const target = parseInt(el.getAttribute('data-countup'), 10);
                if (!Number.isNaN(target)) {
                    animateCountUp(el, target, 1200);
                }
                obs.unobserve(el);
            });
        }, { threshold: 0.25 });

        counters.forEach(function (el) {
            observer.observe(el);
        });
    } else {
        counters.forEach(function (el) {
            const target = parseInt(el.getAttribute('data-countup'), 10);
            if (!Number.isNaN(target)) animateCountUp(el, target, 1200);
        });
    }
}

/* ---------- Attach all handlers on ready ---------- */
document.addEventListener('DOMContentLoaded', function () {
    // Login form
    const loginForm = document.getElementById('login-form');
    if (loginForm) loginForm.addEventListener('submit', validateLogin);

    // Toggle show/hide password on login
    const togglePwBtn = document.getElementById('toggle-password');
    const pwInput = document.getElementById('password');
    if (togglePwBtn && pwInput) {
        togglePwBtn.addEventListener('click', function () {
            const show = pwInput.type === 'password';
            pwInput.type = show ? 'text' : 'password';
            togglePwBtn.classList.toggle('is-visible', show);
            togglePwBtn.setAttribute('aria-pressed', show ? 'true' : 'false');
            togglePwBtn.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
        });
    }

    // Register form
    const regForm = document.getElementById('register-form');
    if (regForm) {
        regForm.addEventListener('submit', validateRegister);
        const roleSelect = document.getElementById('role');
        if (roleSelect) { roleSelect.addEventListener('change', togglePetaniFields); togglePetaniFields(); }
    }

    // Batch form
    const batchForm = document.getElementById('batch-form');
    if (batchForm) {
        batchForm.addEventListener('submit', validateBatch);
        const rescueCb = document.getElementById('rescue_aktif');
        if (rescueCb) { rescueCb.addEventListener('change', toggleRescueFields); toggleRescueFields(); }
    }

    // Cart
    document.querySelectorAll('.qty-input').forEach(function (el) {
        el.addEventListener('input', updateCartTotal);
    });
    updateCartTotal();

    // Char counter for review
    attachCharCounter('komentar', 'komentar-counter', 500);

    // Countdown timers (Food Rescue)
    document.querySelectorAll('[data-countdown]').forEach(function (el) {
        const deadline = el.getAttribute('data-countdown');
        if (deadline) {
            startCountdown(el, deadline);
        }
    });

    // Stats count-up animation
    initCountUp();

    // Upload area click & payment form validation
    const uploadArea = document.getElementById('upload-area');
    const paymentForm = document.getElementById('payment-form');
    if (uploadArea) {
        const fileInput = document.getElementById('bukti_pembayaran');
        uploadArea.addEventListener('click', function (e) {
            if (fileInput && e.target !== fileInput) {
                fileInput.click();
            }
        });
        if (fileInput) {
            fileInput.addEventListener('change', function () {
                const name = this.files[0]?.name || 'Belum dipilih';
                const info = document.getElementById('upload-info');
                if (info) info.textContent = name;
                if (this.files && this.files.length > 0) {
                    uploadArea.classList.remove('is-invalid');
                    clearErrors(paymentForm || uploadArea.closest('form') || document);
                }
            });
        }
        if (paymentForm && fileInput) {
            paymentForm.addEventListener('submit', function (e) {
                clearErrors(paymentForm);
                uploadArea.classList.remove('is-invalid');
                if (!fileInput.files || fileInput.files.length === 0) {
                    uploadArea.classList.add('is-invalid');
                    showError(fileInput, 'Silakan pilih berkas bukti pembayaran terlebih dahulu.');
                    e.preventDefault();
                    return;
                }
                if (!confirmAction('Kirim bukti pembayaran ini?')) {
                    e.preventDefault();
                }
            });
        }
    }
});
