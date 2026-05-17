/* ─── SEARCH ─────────────────────────────────────────── */
function initSearch() {
    const openBtns = [document.getElementById('searchOpen'), document.getElementById('mobileSearchOpen')];
    const overlay = document.getElementById('search-overlay');
    openBtns.forEach(btn => {
        if (btn) btn.addEventListener('click', () => {
            overlay.classList.add('open');
            setTimeout(() => document.getElementById('searchInput').focus(), 150);
        });
    });
    document.getElementById('searchClose').addEventListener('click', () => overlay.classList.remove('open'));
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') overlay.classList.remove('open');
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            overlay.classList.add('open');
            setTimeout(() => document.getElementById('searchInput').focus(), 150);
        }
    });
}
function fillSearch(t) {
    document.getElementById('searchInput').value = t;
    document.getElementById('searchInput').focus();
}