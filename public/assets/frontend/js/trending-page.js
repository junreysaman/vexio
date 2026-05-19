/* ═══ INTERSTITIAL AD ═══════════════════════════════ */
(function () {
    var el = document.getElementById('ad-interstitial');
    var skipBtn = document.getElementById('skipBtn');
    var skipCount = document.getElementById('skipCount');
    var closeBtn = document.getElementById('interCloseBtn');
    var seconds = 5;

    function dismissInterstitial() {
        el.classList.add('hidden');
    }
    window.dismissInterstitial = dismissInterstitial;

    // Close button
    if (closeBtn) closeBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        dismissInterstitial();
    });

    // Countdown
    var interval = setInterval(function () {
        seconds--;
        if (skipCount) skipCount.textContent = seconds;
        if (seconds <= 0) {
            clearInterval(interval);
            dismissInterstitial();
        }
    }, 1000);
})();

/* ═══ SEARCH OVERLAY ═══════════════════════════════ */
function initSearch() {
    const overlay = document.getElementById('search-overlay');
    ['searchOpen', 'mobileSearchOpen'].forEach(id => {
        const btn = document.getElementById(id);
        if (btn) btn.addEventListener('click', () => {
            overlay.classList.add('open');
            setTimeout(() => document.getElementById('searchInput').focus(), 150);
        });
    });
    document.getElementById('searchClose').addEventListener('click', () => overlay.classList.remove('open'));
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') overlay.classList.remove('open');
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') { e.preventDefault(); overlay.classList.add('open'); setTimeout(() => document.getElementById('searchInput').focus(), 150); }
    });
}
function fillSearch(t) { document.getElementById('searchInput').value = t; document.getElementById('searchInput').focus(); }

/* ═══ NAV SCROLL ════════════════════════════════════ */
window.addEventListener('scroll', () => {
    document.getElementById('topnav').classList.toggle('scrolled', window.scrollY > 20);
}, { passive: true });

/* ═══ TOAST ═════════════════════════════════════════ */
let tt;
function showToast(msg) {
    const t = document.getElementById('toast');
    t.textContent = msg; t.classList.add('show');
    clearTimeout(tt); tt = setTimeout(() => t.classList.remove('show'), 2200);
}

/* ═══ FILTER PILLS ══════════════════════════════════ */
function setFilter(btn) {
    document.querySelectorAll('.tf-pill').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    showToast(`Filter: ${btn.textContent.trim()}`);
}

/* ═══ TIME TOGGLE ═══════════════════════════════════ */
function setTime(btn) {
    document.querySelectorAll('.tts-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    showToast(`Showing: ${btn.textContent.trim()} trending`);
}

/* ═══ INIT ══════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', initSearch);