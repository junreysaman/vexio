(function () {
    const state = {
        filter: 'all',
        time: 'day',
    };

    function initTrendingPage() {
        const controls = document.querySelector('[data-trending-controls]');
        if (!controls) return;

        bindFilters();
        bindTimeButtons();
        bindSectionShortcuts();
        bindAdCloseButtons();
        applyTrendingState();
    }

    function bindFilters() {
        document.querySelectorAll('.tf-pill').forEach(button => {
            button.addEventListener('click', () => {
                state.filter = button.dataset.filter || 'all';
                document.querySelectorAll('.tf-pill').forEach(item => item.classList.toggle('active', item === button));
                applyTrendingState();
                showTrendingToast('Filter: ' + button.textContent.trim());
            });
        });
    }

    function bindTimeButtons() {
        document.querySelectorAll('.tts-btn').forEach(button => {
            button.addEventListener('click', () => {
                state.time = button.dataset.time || 'day';
                document.querySelectorAll('.tts-btn').forEach(item => item.classList.toggle('active', item === button));
                applyTrendingState();
                showTrendingToast('Showing: ' + button.textContent.trim());
            });
        });
    }

    function bindSectionShortcuts() {
        document.querySelectorAll('[data-filter-target]').forEach(button => {
            button.addEventListener('click', () => {
                const filter = button.dataset.filterTarget || 'all';
                const target = document.querySelector('.tf-pill[data-filter="' + filter + '"]');
                if (target) target.click();
            });
        });
    }

    function bindAdCloseButtons() {
        document.querySelectorAll('.ad-close').forEach(button => {
            button.addEventListener('click', event => {
                event.preventDefault();
                event.stopPropagation();
                button.closest('.ad-unit')?.setAttribute('hidden', '');
            });
        });
    }

    function applyTrendingState() {
        const items = Array.from(document.querySelectorAll('[data-trending-item]'));
        if (!items.length) return;

        items.forEach(item => {
            item.hidden = !matchesFilter(item, state.filter);
        });

        sortContainer(document.getElementById('trendGrid'), '.trend-card');
        sortContainer(document.getElementById('trendRankTable'), '.rank-row');
        sortContainer(document.getElementById('trendTodayRow'), '.hscroll-card');
        updateEmptyState();
    }

    function matchesFilter(item, filter) {
        if (filter === 'all') return true;

        const type = item.dataset.type || '';
        const category = item.dataset.category || '';
        const secondary = item.dataset.secondary || '';

        return filter === type || filter === category || filter === secondary;
    }

    function sortContainer(container, selector) {
        if (!container) return;

        Array.from(container.querySelectorAll(selector))
            .sort((a, b) => scoreFor(b) - scoreFor(a))
            .forEach(item => container.appendChild(item));
    }

    function scoreFor(item) {
        const key = state.time.charAt(0).toUpperCase() + state.time.slice(1);
        return Number(item.dataset[state.time + 'Score'] || item.getAttribute('data-' + state.time + '-score') || item.dataset['score' + key] || 0);
    }

    function updateEmptyState() {
        const grid = document.getElementById('trendGrid');
        const empty = document.getElementById('trendFilterEmpty');
        if (!grid || !empty) return;

        const visibleCards = Array.from(grid.querySelectorAll('.trend-card')).filter(item => !item.hidden);
        empty.hidden = visibleCards.length > 0;
    }

    function showTrendingToast(message) {
        if (typeof window.showToast === 'function') {
            window.showToast(message);
        }
    }

    document.addEventListener('DOMContentLoaded', initTrendingPage);
})();
