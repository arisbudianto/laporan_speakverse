{{--
    Perlindungan sisi siswa: cegah salin/klik kanan/tempel dan cegah terjemahan otomatis browser.
    Hanya aktif untuk akun ber-role "student", dan tidak aktif di halaman profil
    (supaya siswa tetap bisa menempel kata sandi saat mengganti sandi).
    Ini PENGHALANG, bukan pengaman mutlak (lihat catatan di dokumentasi).
--}}
@php
    $svGuard = auth()->check()
        && (auth()->user()->role ?? null) === 'student'
        && ! request()->routeIs('profile.*');
@endphp
@if ($svGuard)
@verbatim
<style id="sv-guard-css">
    html, body, body * {
        -webkit-user-select: none; -moz-user-select: none; user-select: none;
        -webkit-touch-callout: none;
    }
    input, textarea, [contenteditable="true"], .sv-allow-select, .sv-allow-select * {
        -webkit-user-select: text; -moz-user-select: text; user-select: text;
    }
    img, svg, a { -webkit-user-drag: none; user-drag: none; }
    @media print { html { display: none !important; } }
    #sv-guard-toast {
        position: fixed; left: 50%; bottom: 24px; transform: translateX(-50%);
        background: rgba(15,23,42,.92); color: #fff; padding: 10px 16px; border-radius: 12px;
        font: 500 14px/1.3 Outfit, system-ui, sans-serif; z-index: 2147483000;
        opacity: 0; pointer-events: none; transition: opacity .2s;
        max-width: 90vw; text-align: center;
    }
    #sv-guard-toast.show { opacity: 1; }
    #sv-translate-overlay {
        position: fixed; inset: 0; z-index: 2147483647; display: none;
        align-items: center; justify-content: center; padding: 24px;
        background: rgba(5,8,22,.96); color: #fff; text-align: center;
        font-family: Outfit, system-ui, sans-serif;
    }
    #sv-translate-overlay.show { display: flex; }
    #sv-translate-overlay .box { max-width: 460px; }
    #sv-translate-overlay h2 { font-size: 22px; margin: 0 0 10px; }
    #sv-translate-overlay p { font-size: 15px; line-height: 1.5; margin: 0 0 18px; opacity: .9; }
    #sv-translate-overlay button {
        background: #06b6d4; color: #04222a; border: 0; border-radius: 12px;
        padding: 10px 20px; font-weight: 700; font-size: 15px; cursor: pointer;
    }
</style>

<div id="sv-guard-toast" role="status" aria-live="polite"></div>
<div id="sv-translate-overlay" role="alertdialog" aria-modal="true">
    <div class="box">
        <h2>Terjemahan otomatis terdeteksi</h2>
        <p>SpeakVerse harus dibaca dalam bahasa aslinya supaya latihan dan penilaian berjalan benar.
           Matikan terjemahan pada browser atau ekstensi Anda (misalnya klik ikon terjemahan di bilah alamat,
           lalu pilih &ldquo;Tampilkan bahasa asli&rdquo;), kemudian muat ulang halaman.</p>
        <button type="button" onclick="location.reload()">Muat ulang halaman</button>
    </div>
</div>

<script id="sv-guard-js">
(function () {
    'use strict';
    var toastEl = document.getElementById('sv-guard-toast');
    var toastTimer = null;
    function toast(msg) {
        if (!toastEl) return;
        toastEl.textContent = msg;
        toastEl.classList.add('show');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () { toastEl.classList.remove('show'); }, 1800);
    }
    function isEditable(el) {
        if (!el || !el.tagName) return false;
        var t = el.tagName.toLowerCase();
        return t === 'textarea' || (t === 'input' && el.type !== 'button' && el.type !== 'submit' && el.type !== 'checkbox' && el.type !== 'radio') || el.isContentEditable;
    }

    document.addEventListener('contextmenu', function (e) {
        e.preventDefault(); toast('Klik kanan dinonaktifkan di SpeakVerse.');
    }, true);

    ['copy', 'cut'].forEach(function (ev) {
        document.addEventListener(ev, function (e) {
            if (isEditable(e.target)) return;      // boleh menyalin tulisan sendiri di kotak jawaban
            e.preventDefault();
            try { if (e.clipboardData) e.clipboardData.setData('text/plain', ''); } catch (x) {}
            toast('Menyalin soal tidak diizinkan.');
        }, true);
    });

    document.addEventListener('paste', function (e) {
        var t = e.target;
        if (t && t.closest && t.closest('[data-allow-paste]')) return;
        e.preventDefault(); toast('Menempel teks tidak diizinkan. Ketik jawaban Anda sendiri.');
    }, true);

    document.addEventListener('dragstart', function (e) { e.preventDefault(); }, true);
    document.addEventListener('drop', function (e) { e.preventDefault(); }, true);
    document.addEventListener('selectstart', function (e) {
        if (isEditable(e.target) || (e.target.closest && e.target.closest('.sv-allow-select'))) return;
        e.preventDefault();
    }, true);

    document.addEventListener('keydown', function (e) {
        var k = (e.key || '').toLowerCase();
        var mod = e.ctrlKey || e.metaKey;
        var editable = isEditable(e.target);
        var block = false;
        if (k === 'f12') block = true;
        if (mod && e.shiftKey && (k === 'i' || k === 'j' || k === 'c')) block = true;   // DevTools
        if (mod && (k === 'u' || k === 's' || k === 'p')) block = true;                  // sumber, simpan, cetak
        if (mod && (k === 'a' || k === 'c' || k === 'x') && !editable) block = true;
        if (block) { e.preventDefault(); e.stopPropagation(); toast('Pintasan itu dinonaktifkan di SpeakVerse.'); return false; }
    }, true);

    // ---- Deteksi terjemahan otomatis (Chrome/Edge/ekstensi yang menandai halaman) ----
    var overlay = document.getElementById('sv-translate-overlay');
    var html = document.documentElement;
    var initialLang = (html.getAttribute('lang') || '').toLowerCase();
    function translated() {
        var c = html.className || '';
        if (/translated-(ltr|rtl)/.test(c)) return true;
        var l = (html.getAttribute('lang') || '').toLowerCase();
        if (initialLang && l && l !== initialLang) return true;
        if (document.querySelector('body > .skiptranslate, .goog-te-banner-frame')) return true;
        return false;
    }
    function check() {
        if (!overlay) return;
        if (translated()) overlay.classList.add('show'); else overlay.classList.remove('show');
    }
    try {
        new MutationObserver(check).observe(html, { attributes: true, attributeFilter: ['class', 'lang'] });
        new MutationObserver(function () { if (document.querySelector('body > .skiptranslate')) check(); })
            .observe(document.body, { childList: true });
    } catch (x) {}
    check();
})();
</script>
@endverbatim
@endif
