<?= $this->start('content') ?>

<?php
$tabs = [
    'movies' => ['label' => 'Movies', 'icon' => 'icon-movie'],
    'tv' => ['label' => 'TV Shows', 'icon' => 'icon-live_tv'],
];
$sortOptions = [
    'popularity.desc' => 'Popularity desc',
    'popularity.asc' => 'Popularity asc',
    'vote_average.desc' => 'Rating desc',
    'vote_average.asc' => 'Rating asc',
    'release_date.desc' => 'Newest movies',
    'first_air_date.desc' => 'Newest shows',
];
$genreOptions = $genres ?? [];
$languageOptions = $languageOptions ?? [];
$countryOptions = $countryOptions ?? [];
$stats = $importerStats ?? ['credits' => 'TMDB', 'used' => 0, 'requests' => 'Live'];
?>

<div class="importer-container">
    <div class="importer-tabs">
        <button type="button" id="dbmvstabapp-movie" class="importer-tab <?= $activeTab === 'movies' ? 'active' : '' ?>" data-type="movies">
            <i class="icon-movie"></i> Movies
        </button>
        <button type="button" id="dbmvstabapp-tv" class="importer-tab <?= $activeTab === 'tv' ? 'active' : '' ?>" data-type="tv">
            <i class="icon-live_tv"></i> TV Shows
        </button>
        <div class="importer-stats">
            <span><strong><?= escape((string) $stats['credits']) ?></strong> Credits</span>
            <span><strong><?= number_format((int) $stats['used']) ?></strong> Used</span>
            <span><strong><?= escape((string) $stats['requests']) ?></strong> Requests</span>
        </div>
    </div>

    <div class="importer-toolbar">
        <form action="/admin/importer" method="GET" id="dbmovies-form-filter" class="importer-filter">
            <input type="hidden" name="tab" value="<?= escape($activeTab) ?>">
            <input type="hidden" name="q" value="<?= escape($query ?? '') ?>">
            
            <input type="number" id="dbmvs-year" name="year" min="1900" max="<?= date('Y') + 1 ?>" value="<?= escape((string) ($year ?? '')) ?>" placeholder="Year">
            
            <select id="dbmvs-popularity" name="sort">
                <?php foreach ($sortOptions as $value => $label): ?>
                    <option value="<?= escape($value) ?>" <?= ($sort ?? '') === $value ? 'selected' : '' ?>><?= escape($label) ?></option>
                <?php endforeach; ?>
            </select>
            
            <select id="dbmvs-genre" name="genre">
                <option value="">All genres</option>
                <?php foreach ($genreOptions as $genreOption): ?>
                    <option value="<?= (int) $genreOption['id'] ?>" <?= (int) ($genre ?? 0) === (int) $genreOption['id'] ? 'selected' : '' ?>>
                        <?= escape((string) $genreOption['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <select id="dbmvs-language" name="language">
                <option value="">All languages</option>
                <?php foreach ($languageOptions as $value => $label): ?>
                    <option value="<?= escape($value) ?>" <?= ($language ?? '') === $value ? 'selected' : '' ?>><?= escape($label) ?></option>
                <?php endforeach; ?>
            </select>
            
            <select id="dbmvs-country" name="country">
                <option value="">All countries</option>
                <?php foreach ($countryOptions as $value => $label): ?>
                    <option value="<?= escape($value) ?>" <?= ($country ?? '') === $value ? 'selected' : '' ?>><?= escape($label) ?></option>
                <?php endforeach; ?>
            </select>
            
            <button type="submit" id="dbmvs-btn-filter" class="paper-btn primary">
                <i class="icon-search"></i> Discover
            </button>
        </form>

        <form action="/admin/importer" method="GET" id="dbmovies-form-search" class="importer-search">
            <input type="hidden" name="tab" value="<?= escape($activeTab) ?>">
            <input type="text" id="dbmvs-search-term" name="q" value="<?= escape($query ?? '') ?>" placeholder="Search for content..">
            <button type="submit" id="dbmvs-btn-search" class="paper-btn primary">
                <i class="icon-search"></i>
            </button>
        </form>
    </div>

    <div class="importer-logs">
        <div id="dbmovies-logs-box">
            <span class="log-entry log-success"><i class="icon-check"></i> Welcome, the service has started successfully</span>
        </div>
    </div>

    <div id="importerMeta" class="importer-meta">
        <span id="dbmvs-total-results">Loading TMDB results...</span>
        <span id="dbmvs-current-page">Page 1</span>
    </div>

    <div id="importerGrid" class="importer-grid">
        <div class="importer-empty">
            <i class="icon-cloud_download"></i>
            <strong>Loading importer</strong>
            <span>Fetching fresh TMDB metadata without reloading the page.</span>
        </div>
    </div>

    <div class="importer-pagination">
        <button type="button" id="importPrev" class="paper-btn">
            <i class="icon-chevron_left"></i> Previous
        </button>
        <span class="pagination-info">
            <span id="dbmvs-page-display">Page 1</span>
        </span>
        <button type="button" id="importNext" class="paper-btn primary">
            Load More <i class="icon-chevron_right"></i>
        </button>
    </div>
</div>

<?= $this->end() ?>

<?= $this->start('styles') ?>
<style>
.importer-container {
    display: grid;
    grid-template-columns: 1fr;
    gap: 18px;
}

.importer-tabs {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    border: 1px solid var(--paper-line);
    border-radius: 8px;
    background: var(--paper-surface);
}

.importer-tab {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border: 1px solid transparent;
    border-radius: 6px;
    background: transparent;
    color: var(--paper-muted);
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
}

.importer-tab:hover {
    background: rgba(50, 119, 200, .05);
    color: var(--paper-blue);
}

.importer-tab.active {
    background: rgba(50, 119, 200, .10);
    color: var(--paper-blue);
    border-color: var(--paper-blue);
}

.importer-stats {
    display: flex;
    align-items: center;
    gap: 18px;
    margin-left: auto;
    padding-left: 12px;
    border-left: 1px solid var(--paper-line);
}

.importer-stats span {
    display: flex;
    flex-direction: column;
    align-items: center;
    font-size: 12px;
    color: var(--paper-muted);
    font-weight: 700;
}

.importer-stats strong {
    color: var(--paper-blue);
    font-size: 14px;
    margin-bottom: 2px;
}

.importer-toolbar {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    border: 1px solid var(--paper-line);
    border-radius: 8px;
    background: var(--paper-surface);
    flex-wrap: wrap;
}

.importer-filter {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
    min-width: 300px;
}

.importer-filter input,
.importer-filter select {
    padding: 8px 10px;
    border: 1px solid var(--paper-line);
    border-radius: 6px;
    background: var(--paper-surface);
    color: var(--paper-ink);
    font-size: 13px;
    font-weight: 500;
}

.importer-filter input::placeholder {
    color: var(--paper-muted);
}

.importer-filter input:focus,
.importer-filter select:focus {
    outline: none;
    border-color: var(--paper-blue);
    box-shadow: 0 0 0 2px rgba(50, 119, 200, .1);
}

.importer-search {
    display: flex;
    align-items: center;
    gap: 8px;
}

.importer-search input {
    padding: 8px 10px;
    border: 1px solid var(--paper-line);
    border-radius: 6px;
    background: var(--paper-surface);
    color: var(--paper-ink);
    font-size: 13px;
    min-width: 180px;
}

.importer-logs {
    padding: 12px;
    border: 1px solid var(--paper-line);
    border-radius: 8px;
    background: var(--paper-surface);
    max-height: 80px;
    overflow-y: auto;
}

#dbmovies-logs-box {
    display: grid;
    gap: 6px;
}

.log-entry {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    color: var(--paper-muted);
}

.log-success {
    color: var(--paper-green);
}

.importer-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    border: 1px solid var(--paper-line);
    border-radius: 8px;
    background: rgba(50, 119, 200, .02);
    font-size: 13px;
    font-weight: 700;
    color: var(--paper-blue);
}

.importer-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 14px;
}

.importer-grid.is-loading {
    opacity: 0.6;
    pointer-events: none;
}

.import-card {
    display: flex;
    flex-direction: column;
    border: 1px solid var(--paper-line);
    border-radius: 8px;
    background: var(--paper-surface);
    overflow: hidden;
    transition: all 0.2s ease;
}

.import-card:hover {
    border-color: var(--paper-blue);
    box-shadow: var(--paper-shadow);
}

.import-poster {
    position: relative;
    width: 100%;
    aspect-ratio: 2 / 3;
    overflow: hidden;
    background: linear-gradient(135deg, #f0f2f5, #e4e8ed);
    display: grid;
    place-items: center;
}

.import-poster img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.import-poster i {
    font-size: 40px;
    color: var(--paper-blue);
    opacity: 0.5;
}

.rating {
    position: absolute;
    top: 8px;
    right: 8px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 8px;
    border-radius: 4px;
    background: rgba(0, 0, 0, 0.7);
    color: #ffc107;
    font-size: 12px;
    font-weight: 700;
}

.import-card-body {
    flex: 1;
    padding: 12px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.import-card-body strong {
    font-size: 13px;
    font-weight: 700;
    color: var(--paper-ink);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    line-height: 1.3;
}

.import-card-body span {
    font-size: 11px;
    color: var(--paper-muted);
    font-weight: 600;
}

.import-action {
    padding: 8px;
    border-top: 1px solid var(--paper-line);
    display: grid;
    gap: 6px;
}

.custom-select {
    padding: 6px 8px;
    border: 1px solid var(--paper-line);
    border-radius: 4px;
    background: var(--paper-surface);
    color: var(--paper-ink);
    font-size: 12px;
    font-weight: 600;
}

.import-featured-toggle {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 600;
    color: var(--paper-muted);
    cursor: pointer;
}

.import-featured-toggle input[type="checkbox"] {
    cursor: pointer;
}

.importer-empty {
    display: grid;
    place-items: center;
    gap: 8px;
    padding: 60px 20px;
    text-align: center;
    border: 1px dashed var(--paper-line);
    border-radius: 8px;
    background: rgba(50, 119, 200, .02);
    grid-column: 1 / -1;
}

.importer-empty i {
    font-size: 48px;
    color: var(--paper-blue);
    opacity: 0.5;
}

.importer-empty strong {
    font-size: 16px;
    font-weight: 700;
    color: var(--paper-ink);
}

.importer-empty span {
    max-width: 340px;
    font-size: 13px;
    color: var(--paper-muted);
    line-height: 1.5;
}

.importer-pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 16px;
    padding: 16px;
}

.pagination-info {
    font-size: 13px;
    font-weight: 700;
    color: var(--paper-muted);
}

.paper-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 16px;
    border: 1px solid var(--paper-line);
    border-radius: 6px;
    background: var(--paper-surface);
    color: var(--paper-ink);
    font-weight: 700;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.paper-btn:hover:not(:disabled) {
    border-color: var(--paper-blue);
    background: rgba(50, 119, 200, .05);
    color: var(--paper-blue);
}

.paper-btn.primary {
    border-color: var(--paper-blue);
    background: var(--paper-blue);
    color: white;
}

.paper-btn.primary:hover:not(:disabled) {
    background: #2a5fb8;
    border-color: #2a5fb8;
}

.paper-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

@media (max-width: 900px) {
    .importer-filter {
        flex: 1 1 100%;
    }

    .importer-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    }

    .importer-stats {
        margin-left: 0;
        padding-left: 0;
        border-left: none;
        width: 100%;
        padding-top: 12px;
        border-top: 1px solid var(--paper-line);
    }
}

@media (max-width: 600px) {
    .importer-grid {
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    }

    .importer-pagination {
        flex-wrap: wrap;
    }

    .paper-btn {
        font-size: 12px;
        padding: 8px 12px;
    }
}
</style>
<?= $this->end() ?>

<?= $this->start('scripts') ?>
<script>
(() => {
    const state = {
        tab: <?= json_encode($activeTab) ?>,
        page: 1,
        q: <?= json_encode((string) ($query ?? '')) ?>,
        year: <?= json_encode((string) ($year ?? '')) ?>,
        sort: <?= json_encode((string) ($sort ?? 'popularity.desc')) ?>,
        genre: <?= json_encode((string) ($genre ?? '')) ?>,
        language: <?= json_encode((string) ($language ?? '')) ?>,
        country: <?= json_encode((string) ($country ?? '')) ?>,
        totalPages: 1,
        token: <?= json_encode((string) ($_csrfToken ?? '')) ?>,
    };
    const genresByTab = <?= json_encode($genresByTab ?? ['movies' => [], 'tv' => []]) ?>;

    const grid = document.getElementById('importerGrid');
    const meta = document.getElementById('importerMeta');
    const form = document.querySelector('.importer-filter');
    const searchForm = document.querySelector('.importer-search');
    const prev = document.getElementById('importPrev');
    const next = document.getElementById('importNext');
    const genreField = document.getElementById('dbmvs-genre');

    const esc = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    }[char]));

    const empty = (icon, title, message) => {
        grid.innerHTML = `<div class="importer-empty"><i class="${icon}"></i><strong>${esc(title)}</strong><span>${esc(message)}</span></div>`;
    };

    const queryString = () => new URLSearchParams({
        tab: state.tab,
        page: String(state.page),
        q: state.q,
        year: state.year,
        sort: state.sort,
        genre: state.genre,
        language: state.language,
        country: state.country,
    });

    const skeletonGrid = () => {
        const count = 12;
        grid.innerHTML = Array.from({length: count}, () =>
            `<div class="sk-card"></div>`
        ).join('');
        grid.className = 'importer-grid sk-grid';
    };

    const setBusy = (busy) => {
        if (busy) {
            skeletonGrid();
        } else {
            grid.className = 'importer-grid';
        }
        form.querySelectorAll('button, input, select').forEach((field) => field.disabled = busy);
        prev.disabled = busy || state.page <= 1;
        next.disabled = busy || state.page >= state.totalPages;
    };

    const renderMeta = (data) => {
        const total = Number(data.meta?.total_results ?? 0).toLocaleString();
        const visible = Number(data.meta?.visible_results ?? 0).toLocaleString();
        state.totalPages = Number(data.meta?.total_pages ?? 1);
        state.page = Number(data.meta?.page ?? state.page);
        meta.innerHTML = `<span>${visible} visible from ${total} TMDB results</span><span>Page ${state.page.toLocaleString()} of ${state.totalPages.toLocaleString()}</span>`;
        document.getElementById('dbmvs-page-display').textContent = `Page ${state.page}`;
        prev.disabled = state.page <= 1;
        next.disabled = state.page >= state.totalPages;
    };

    const renderGenres = () => {
        const current = state.genre;
        const items = genresByTab[state.tab] || [];
        genreField.innerHTML = '<option value="">All genres</option>' + items.map((item) => {
            const id = String(item.id || '');
            return `<option value="${esc(id)}" ${id === current ? 'selected' : ''}>${esc(item.name || '')}</option>`;
        }).join('');

        if (!items.some((item) => String(item.id || '') === current)) {
            state.genre = '';
            genreField.value = '';
        }
    };

    const isReleasedByToday = (item) => {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        // Get the release date - the API returns it as 'date' field
        const releaseDate = item.date;
        
        // If no date available, return false
        if (!releaseDate) {
            return false;
        }
        
        const itemDate = new Date(releaseDate);
        itemDate.setHours(0, 0, 0, 0);
        
        // Check if date is valid and not in the future
        if (isNaN(itemDate.getTime())) {
            return false;
        }
        
        return itemDate <= today;
    };

    const card = (item) => {
        return `
            <article class="import-card" data-tmdb-id="${item.id}">
                <div class="import-poster">
                    ${item.poster_url ? `<img src="${esc(item.poster_url)}" alt="">` : '<i class="icon-image"></i>'}
                    <span class="rating"><i class="icon-star"></i>${esc(item.vote_average)}</span>
                </div>
                <div class="import-card-body">
                    <strong title="${esc(item.title)}">${esc(item.title)}</strong>
                    <span>${esc(item.year)} &middot; ${esc(item.language || 'N/A')}</span>
                </div>
                <form class="import-action" data-import-form>
                    <input type="hidden" name="token" value="${esc(state.token)}">
                    <input type="hidden" name="tab" value="${esc(state.tab)}">
                    <input type="hidden" name="tmdb_id" value="${item.id}">
                    <select class="custom-select" name="status" aria-label="Import status">
                        <option value="draft">Draft</option>
                        <option value="published">Publish</option>
                    </select>
                    <label class="import-featured-toggle">
                        <input type="checkbox" name="featured" value="1">
                        <span>Featured</span>
                    </label>
                    <button class="paper-btn primary" type="submit" style="width: 100%; justify-content: center;"><i class="icon-cloud_download"></i>Import</button>
                </form>
            </article>
        `;
    };

    // Serial import queue — ensures each import uses the fresh CSRF token
    // returned by the previous one, preventing concurrent token mismatch (419).
    let importQueue = Promise.resolve();

    const runImport = (importForm) => {
        const button = importForm.querySelector('button');
        const original = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="icon-hourglass_top"></i>Importing...';

        // Sync the hidden token field with the latest known token before sending.
        const tokenField = importForm.querySelector('[name="token"]');
        if (tokenField) tokenField.value = state.token;

        return fetch('/admin/importer/import', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json'},
            body: new URLSearchParams(new FormData(importForm)),
        })
        .then((response) => response.json().then((data) => ({ response, data })))
        .then(({ response, data }) => {
            const freshToken = data.csrf_token || data.error?.csrf_token;
            if (freshToken) {
                state.token = freshToken;
                document.querySelectorAll('[name="token"]').forEach((field) => {
                    field.value = freshToken;
                });
            }

            if (!response.ok || !data.ok) {
                throw new Error(data.error?.message || 'Import failed.');
            }

            importForm.closest('.import-card')?.remove();
            if (!grid.querySelector('.import-card')) {
                empty('icon-check', 'Imported all visible items', 'Fetch the next page or adjust your filters.');
            }
        })
        .catch((error) => {
            alert(error.message);
            button.disabled = false;
            button.innerHTML = original;
        });
    };

    const bindImports = () => {
        grid.querySelectorAll('[data-import-form]').forEach((importForm) => {
            importForm.addEventListener('submit', (event) => {
                event.preventDefault();
                // Chain onto the queue so imports run one at a time.
                importQueue = importQueue.then(() => runImport(importForm));
            });
        });
    };

    const load = async () => {
        setBusy(true);

        try {
            const response = await fetch('/admin/importer/results?' + queryString().toString(), {
                headers: {'Accept': 'application/json'},
            });
            const data = await response.json();

            if (!response.ok || !data.ok) {
                throw new Error(data.error?.message || 'Could not load TMDB results.');
            }

            renderMeta(data);
            const releasedItems = data.results.filter(isReleasedByToday);
            
            // Sort by release date descending (newest first, going backwards)
            releasedItems.sort((a, b) => {
                const dateA = a.date;
                const dateB = b.date;
                return new Date(dateB) - new Date(dateA);
            });
            
            grid.innerHTML = releasedItems.length
                ? releasedItems.map(card).join('')
                : '<div class="importer-empty"><i class="icon-search"></i><strong>No released content found</strong><span>Items displayed are only those released today or earlier. Try a different page or adjust your filters.</span></div>';
            grid.className = 'importer-grid';
            bindImports();
        } catch (error) {
            meta.innerHTML = '<span>TMDB connection issue</span><span>Page ' + state.page + '</span>';
            empty('icon-cloud_off', 'TMDB is not connected yet', error.message);
        } finally {
            setBusy(false);
        }
    };

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        state.page = 1;
        state.q = form.q.value.trim();
        state.year = form.year.value.trim();
        state.sort = form.sort.value;
        state.genre = form.genre.value;
        state.language = form.language.value;
        state.country = form.country.value;
        searchForm.q.value = '';
        state.q = '';
        load();
    });

    searchForm.addEventListener('submit', (event) => {
        event.preventDefault();
        state.page = 1;
        state.q = searchForm.q.value.trim();
        state.year = form.year.value.trim();
        state.sort = form.sort.value;
        state.genre = form.genre.value;
        state.language = form.language.value;
        state.country = form.country.value;
        form.q.value = state.q;
        load();
    });

    document.querySelectorAll('.importer-tab').forEach((tab) => {
        tab.addEventListener('click', (event) => {
            const newTab = tab.getAttribute('data-type');
            if (newTab && newTab !== state.tab) {
                state.tab = newTab;
                state.page = 1;
                state.genre = '';
                state.language = '';
                state.country = '';
                form.tab.value = state.tab;
                searchForm.tab.value = state.tab;
                form.language.value = '';
                form.country.value = '';
                renderGenres();
                document.querySelectorAll('.importer-tab').forEach((link) => {
                    link.classList.toggle('active', link === tab);
                });
                history.replaceState(null, '', '/admin/importer?tab=' + encodeURIComponent(state.tab));
                load();
            }
        });
    });

    prev.addEventListener('click', () => {
        if (state.page > 1) {
            state.page -= 1;
            load();
        }
    });

    next.addEventListener('click', () => {
        if (state.page < state.totalPages) {
            state.page += 1;
            load();
        }
    });

    renderGenres();
    load();
})();
</script>
<?= $this->end() ?>
