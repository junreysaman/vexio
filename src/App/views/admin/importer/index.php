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

<div id="dbmovies-dbmvs-application" class="dbmovies-app vexio-dbmvs">
    <header class="dbmvsapp">
        <nav class="left" id="dbmovies-types">
            <ul>
                <li><h3 id="dbmvs-logo-status" class="ready"><a href="/admin/importer">DBMVS</a> <small>3.5.3</small></h3></li>
                <li><a id="dbmvstabapp-movie" href="/admin/importer?tab=movies" class="dbmvs-tab-content button <?= $activeTab === 'movies' ? 'button-primary' : '' ?>" data-type="movies">Movies</a></li>
                <li><a id="dbmvstabapp-tv" href="/admin/importer?tab=tv" class="dbmvs-tab-content button <?= $activeTab === 'tv' ? 'button-primary' : '' ?>" data-type="tv">Shows</a></li>
                <li><span class="dbmvs-counter">Credits: <b><?= escape((string) $stats['credits']) ?></b></span></li>
                <li><span class="dbmvs-counter">Used: <b><?= number_format((int) $stats['used']) ?></b></span></li>
                <li><span class="dbmvs-counter">Requests: <b><?= escape((string) $stats['requests']) ?></b></span></li>
            </ul>
        </nav>
        <nav class="right" id="dbmovies-settings">
            <ul>
                <li class="title">Meta Updater</li>
                <li><a href="/admin/content" class="button button-primary button-small">Start</a></li>
            </ul>
        </nav>
    </header>

    <div class="forms" id="dbmvs-forms-response">
        <form action="/admin/importer" method="GET" id="dbmovies-form-filter" class="importer-filter">
            <fieldset>
                <a class="button button-large dbmovies-log-collapse" href="#" title="Imported data"></a>
            </fieldset>
            <fieldset>
                <input type="hidden" name="tab" value="<?= escape($activeTab) ?>">
                <input type="hidden" name="q" value="<?= escape($query ?? '') ?>">
                <input type="number" id="dbmvs-year" name="year" min="1900" max="<?= date('Y') + 1 ?>" value="<?= escape((string) ($year ?? '')) ?>" placeholder="Year">
            </fieldset>
            <fieldset>
                <input type="number" id="dbmvs-page" min="1" value="1" placeholder="Page" disabled>
            </fieldset>
            <fieldset>
                <select id="dbmvs-popularity" name="sort">
                    <?php foreach ($sortOptions as $value => $label): ?>
                        <option value="<?= escape($value) ?>" <?= ($sort ?? '') === $value ? 'selected' : '' ?>><?= escape($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </fieldset>
            <fieldset>
                <select id="dbmvs-genre" name="genre">
                    <option value="">All genres</option>
                    <?php foreach ($genreOptions as $genreOption): ?>
                        <option value="<?= (int) $genreOption['id'] ?>" <?= (int) ($genre ?? 0) === (int) $genreOption['id'] ? 'selected' : '' ?>>
                            <?= escape((string) $genreOption['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </fieldset>
            <fieldset>
                <select id="dbmvs-language" name="language">
                    <option value="">All languages</option>
                    <?php foreach ($languageOptions as $value => $label): ?>
                        <option value="<?= escape($value) ?>" <?= ($language ?? '') === $value ? 'selected' : '' ?>><?= escape($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </fieldset>
            <fieldset>
                <select id="dbmvs-country" name="country">
                    <option value="">All countries</option>
                    <?php foreach ($countryOptions as $value => $label): ?>
                        <option value="<?= escape($value) ?>" <?= ($country ?? '') === $value ? 'selected' : '' ?>><?= escape($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </fieldset>
            <fieldset>
                <input type="submit" id="dbmvs-btn-filter" class="button button-large" value="Discover">
            </fieldset>
        </form>

        <form action="/admin/importer" method="GET" id="dbmovies-form-search" class="right importer-search">
            <fieldset>
                <input type="hidden" name="tab" value="<?= escape($activeTab) ?>">
                <input type="text" id="dbmvs-search-term" name="q" value="<?= escape($query ?? '') ?>" placeholder="Search..">
            </fieldset>
            <fieldset>
                <input type="submit" id="dbmvs-btn-search" class="button button-large" value="Search">
            </fieldset>
        </form>
    </div>

    <div class="dbmvs-progress-bar"><div class="progressing"></div></div>

    <div class="dbmovies-logs">
        <div id="dbmovies-logs-box" class="box">
            <ul>
                <li><span class="type green">DBMVS</span> Welcome, the service has started successfully</li>
            </ul>
        </div>
        <div class="hidder"><a id="dbmovies-cleanlog" href="#">CLEAN</a></div>
    </div>

    <div class="wrapp">
        <div class="content">
            <div id="importerMeta" class="data_results importer-meta">
                <section><span id="dbmvs-total-results">Loading TMDB results...</span></section>
                <section class="right"><span id="dbmvs-current-page">Page 1</span></section>
            </div>
            <div id="importerGrid" class="items importer-grid">
                <div class="importer-empty">
                    <i class="icon-cloud_download"></i>
                    <strong>Loading importer</strong>
                    <span>Fetching fresh TMDB metadata without reloading the page.</span>
                </div>
            </div>
            <div class="paginator importer-pagination">
                <div id="dbmovies-loadmore-spinner"></div>
                <button class="button" id="importPrev" type="button">Previous</button>
                <button class="button button-primary" id="importNext" type="button">Load More</button>
            </div>
        </div>
    </div>
</div>

<?= $this->end() ?>

<?= $this->start('styles') ?>
<link rel="stylesheet" href="/dooplay/inc/core/dbmvs/assets/dbmovies.min.css">
<style>
body.importer-page .paper-main {
    padding: 0;
}

.vexio-dbmvs {
    min-height: calc(100vh - 64px);
    background: #f1f2f4;
}

.vexio-dbmvs .dbmvsapp {
    min-height: 58px;
    border-top: 2px solid #1d1f24;
    background: #fff;
}

.vexio-dbmvs #dbmvs-logo-status {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin: 0;
    font-size: 14px;
    font-weight: 800;
}

.vexio-dbmvs #dbmvs-logo-status::before {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #28c840;
    content: "";
}

.vexio-dbmvs #dbmvs-logo-status small {
    color: #a6adb7;
    font-size: 11px;
}

.vexio-dbmvs .dbmvs-counter {
    display: inline-flex;
    align-items: center;
    min-height: 28px;
    padding: 0 12px;
    border: 1px solid #dde2e8;
    border-radius: 3px;
    color: #2262b7;
    font-size: 11px;
    font-weight: 700;
}

.vexio-dbmvs .dbmvs-counter b {
    margin-left: 4px;
}

.vexio-dbmvs #dbmvs-forms-response {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    min-height: 56px;
    padding: 10px 16px;
    border-top: 1px solid #edf0f3;
    border-bottom: 1px solid #dfe4ea;
    background: #fbfbfc;
}

.vexio-dbmvs #dbmovies-form-filter,
.vexio-dbmvs #dbmovies-form-search {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0;
}

.vexio-dbmvs #dbmvs-year {
    width: 90px;
}

.vexio-dbmvs #dbmvs-page {
    width: 90px;
}

.vexio-dbmvs #dbmvs-popularity {
    width: 128px;
}

.vexio-dbmvs #dbmvs-genre {
    width: 158px;
}

.vexio-dbmvs #dbmvs-language,
.vexio-dbmvs #dbmvs-country {
    width: 138px;
}

.vexio-dbmvs #dbmvs-search-term {
    width: 112px;
}

.vexio-dbmvs .dbmovies-logs {
    border-bottom: 1px solid #dfe4ea;
    background: #fff;
    box-shadow: 0 4px 10px rgba(31, 38, 46, .12);
}

.vexio-dbmvs #dbmovies-logs-box {
    height: 58px;
    overflow-y: auto;
}

.vexio-dbmvs #dbmovies-cleanlog {
    color: #0d5cc9;
    font-size: 10px;
    font-weight: 800;
}

.vexio-dbmvs .wrapp {
    padding-top: 0;
}

@media (max-width: 900px) {
    .vexio-dbmvs #dbmvs-forms-response,
    .vexio-dbmvs #dbmovies-form-filter,
    .vexio-dbmvs #dbmovies-form-search {
        align-items: stretch;
        flex-wrap: wrap;
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

    const setBusy = (busy) => {
        grid.classList.toggle('is-loading', busy);
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
        document.getElementById('dbmvs-page').value = state.page;
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
                    <select class="custom-select form-control r-0 light s-12" name="status" aria-label="Import status">
                        <option value="draft">Draft</option>
                        <option value="published">Publish</option>
                    </select>
                    <label class="import-featured-toggle">
                        <input type="checkbox" name="featured" value="1">
                        <span>Featured</span>
                    </label>
                    <button class="btn btn-primary btn-sm" type="submit"><i class="icon-cloud_download mr-1"></i>Import</button>
                </form>
            </article>
        `;
    };

    const bindImports = () => {
        grid.querySelectorAll('[data-import-form]').forEach((importForm) => {
            importForm.addEventListener('submit', async (event) => {
                event.preventDefault();
                const button = importForm.querySelector('button');
                const original = button.innerHTML;
                button.disabled = true;
                button.innerHTML = 'Importing...';

                try {
                    const response = await fetch('/admin/importer/import', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json'},
                        body: new URLSearchParams(new FormData(importForm)),
                    });
                    const data = await response.json();
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
                } catch (error) {
                    alert(error.message);
                    button.disabled = false;
                    button.innerHTML = original;
                }
            });
        });
    };

    const load = async () => {
        setBusy(true);
        empty('icon-cloud_download', 'Loading importer', 'Fetching fresh TMDB metadata without reloading the page.');

        try {
            const response = await fetch('/admin/importer/results?' + queryString().toString(), {
                headers: {'Accept': 'application/json'},
            });
            const data = await response.json();

            if (!response.ok || !data.ok) {
                throw new Error(data.error?.message || 'Could not load TMDB results.');
            }

            renderMeta(data);
            grid.innerHTML = data.results.length
                ? data.results.map(card).join('')
                : '<div class="importer-empty"><i class="icon-search"></i><strong>No unimported results found</strong><span>Try a broader title, year, tab, or next page.</span></div>';
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

    document.querySelectorAll('#dbmovies-types a[data-type]').forEach((tab) => {
        tab.addEventListener('click', (event) => {
            event.preventDefault();
            const url = new URL(tab.href);
            state.tab = url.searchParams.get('tab') || 'movies';
            state.page = 1;
            state.genre = '';
            state.language = '';
            state.country = '';
            form.tab.value = state.tab;
            searchForm.tab.value = state.tab;
            form.language.value = '';
            form.country.value = '';
            renderGenres();
            document.querySelectorAll('#dbmovies-types a[data-type]').forEach((link) => {
                link.classList.toggle('button-primary', link === tab);
            });
            history.replaceState(null, '', '/admin/importer?tab=' + encodeURIComponent(state.tab));
            load();
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
