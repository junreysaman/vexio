/* Search */
function initSearch() {
    const openBtns = [document.getElementById('searchOpen'), document.getElementById('mobileSearchOpen')];
    const overlay = document.getElementById('search-overlay');
    const input = document.getElementById('searchInput');
    const close = document.getElementById('searchClose');
    const results = document.getElementById('searchResults');
    const state = document.getElementById('searchState');
    let timer = null;
    let controller = null;

    if (!overlay || !input || !close || !results || !state) return;

    openBtns.forEach(btn => {
        if (!btn) return;

        btn.addEventListener('click', () => {
            overlay.classList.add('open');
            setTimeout(() => input.focus(), 150);
        });
    });

    close.addEventListener('click', () => overlay.classList.remove('open'));

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') overlay.classList.remove('open');

        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            overlay.classList.add('open');
            setTimeout(() => input.focus(), 150);
        }
    });

    input.addEventListener('input', () => {
        clearTimeout(timer);
        const query = input.value.trim();

        if (controller) controller.abort();

        if (query.length < 2) {
            renderSearchResults([]);
            state.textContent = 'Type at least 2 characters';
            return;
        }

        state.textContent = 'Searching...';
        timer = setTimeout(() => runSearch(query), 180);
    });

    function runSearch(query) {
        controller = new AbortController();

        fetch(`/api/search?q=${encodeURIComponent(query)}`, {
            headers: {Accept: 'application/json'},
            signal: controller.signal,
        })
            .then(response => response.ok ? response.json() : Promise.reject(response))
            .then(data => {
                const items = Array.isArray(data.results) ? data.results : [];
                renderSearchResults(items);
                state.textContent = items.length ? '' : 'No results found';
            })
            .catch(error => {
                if (error.name === 'AbortError') return;

                renderSearchResults([]);
                state.textContent = 'Search is unavailable right now';
            });
    }

    function renderSearchResults(items) {
        results.innerHTML = items.map(item => {
            const title = escapeHtml(item.title || 'Untitled');
            const year = escapeHtml(item.year || '');
            const image = escapeHtml(item.image || '');
            const url = escapeHtml(item.watchUrl || '#');
            const media = image
                ? `<img src="${image}" alt="">`
                : '<span class="so-result-fallback"></span>';

            return `<a class="so-result" href="${url}">
                <span class="so-result-media">${media}</span>
                <span class="so-result-copy">
                    <strong>${title}</strong>
                    <span>${year}</span>
                </span>
            </a>`;
        }).join('');
    }

    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, char => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;',
        })[char]);
    }
}

function fillSearch(t) {
    const input = document.getElementById('searchInput');
    const overlay = document.getElementById('search-overlay');

    if (!input) return;

    if (overlay) overlay.classList.add('open');

    input.value = t;
    input.focus();
    input.dispatchEvent(new Event('input'));
}
