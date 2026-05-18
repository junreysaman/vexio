function initEpisodeList() {
    const panel = document.querySelector('[data-episode-list]');
    if (!panel) return;

    const search = panel.querySelector('[data-episode-search]');
    const list = panel.querySelector('.ep-list');
    const rows = Array.from(panel.querySelectorAll('.ep-row'));
    const loadMore = panel.querySelector('[data-episode-load-more]');
    const loadMoreWrap = loadMore?.closest('.ep-load-more-wrap');
    const count = panel.querySelector('[data-episode-visible-count]');
    const pageSize = parseInt(panel.dataset.pageSize || '10', 10);
    let visibleLimit = pageSize;

    const render = () => {
        const term = (search?.value || '').trim().toLowerCase();
        const isNumericTerm = /^(episode|ep|e)?\s*\d+$/i.test(term) || /^s\d+\s*e\d+$/i.test(term);
        const normalizedNumberTerm = term.match(/\d+/)?.[0] || '';
        const matches = rows.filter(row => {
            if (!term) return true;

            if (isNumericTerm && normalizedNumberTerm) {
                return (` ${row.dataset.numberSearch || ''} `).includes(` ${normalizedNumberTerm} `)
                    || (` ${row.dataset.numberSearch || ''} `).includes(` 0${normalizedNumberTerm} `)
                    || (` ${row.dataset.numberSearch || ''} `).includes(` episode ${normalizedNumberTerm} `)
                    || (` ${row.dataset.numberSearch || ''} `).includes(` e${normalizedNumberTerm} `);
            }

            return (row.dataset.search || '').includes(term)
                || (row.dataset.numberSearch || '').includes(term);
        });

        rows.forEach(row => {
            row.hidden = true;
        });

        matches.slice(0, visibleLimit).forEach(row => {
            row.hidden = false;
        });

        if (loadMore) {
            loadMore.hidden = matches.length <= visibleLimit;
        }
        if (loadMoreWrap) {
            loadMoreWrap.hidden = matches.length <= visibleLimit;
        }

        if (count) {
            count.textContent = `${Math.min(matches.length, visibleLimit).toLocaleString()} of ${matches.length.toLocaleString()} episodes`;
        }

        list?.classList.toggle('is-empty', matches.length === 0);
        panel.querySelector('.ep-empty')?.toggleAttribute('hidden', matches.length !== 0);
    };

    search?.addEventListener('input', () => {
        visibleLimit = pageSize;
        render();
    });

    loadMore?.addEventListener('click', () => {
        visibleLimit += pageSize;
        render();
    });

    render();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initEpisodeList);
} else {
    initEpisodeList();
}
