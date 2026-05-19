<?php
$items = !empty($items) && is_array($items) ? $items : [];
$totalItems = (int) ($total_items ?? count($items));
?>
<div id="results-area">
  <div class="active-filters" id="activeFilters"></div>

  <div class="results-toolbar">
    <button class="mobile-filter-btn" onclick="openFilterDrawer()">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
      Filters
      <span class="badge" id="filterBadge">0</span>
    </button>
    <div class="results-count">
      <strong id="resultNum"><?= number_format($totalItems) ?></strong> results found
    </div>
    <div class="toolbar-right">
      <div class="sort-select">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
        <select id="sortSelect" onchange="sortCards(this.value)">
          <option value="popular">Most Popular</option>
          <option value="rating">Highest Rated</option>
          <option value="newest">Newest First</option>
          <option value="oldest">Oldest First</option>
          <option value="az">A - Z</option>
          <option value="za">Z - A</option>
        </select>
      </div>
    </div>
  </div>

  <div class="card-grid" id="cardGrid">
    <?php foreach ($items as $item): ?>
      <?php
        $title = (string) ($item['title'] ?? 'Untitled');
        $poster = (string) ($item['poster'] ?? '');
        $watchUrl = (string) ($item['watchUrl'] ?? '#');
        $type = (string) ($item['type'] ?? 'unknown');
        $typeLabel = (string) ($item['type_label'] ?? ucfirst(str_replace('_', ' ', $type)));
        $year = (string) ($item['release_year'] ?? 'N/A');
        $rating = $item['tmdb_rating'] ?? null;
        $views = (int) ($item['views'] ?? 0);
        $created = (string) ($item['created_at'] ?? '');
        $genres = !empty($item['genres']) && is_array($item['genres']) ? $item['genres'] : [];
        $genreNames = array_map(fn(array $genre): string => (string) ($genre['name'] ?? ''), $genres);
        $genreSlugs = array_map(fn(array $genre): string => basename((string) ($genre['url'] ?? '')), $genres);
        $genreLabel = (string) ($genreNames[0] ?? ($item['genre_label'] ?? 'Unknown'));
        $badgeText = !empty($item['is_featured']) ? 'HOT' : $typeLabel;
        $badgeClass = !empty($item['is_featured']) ? 'badge-hot' : 'badge-ep';
        $initials = strtoupper(substr(preg_replace('/[^a-z0-9]+/i', '', $title) ?: 'VX', 0, 3));
      ?>
      <article
        class="acard"
        data-title="<?= escape(strtolower($title)) ?>"
        data-type="<?= escape($type) ?>"
        data-genres="<?= escape(implode(',', $genreSlugs)) ?>"
        data-year="<?= escape($year) ?>"
        data-rating="<?= escape((string) ($rating ?? 0)) ?>"
        data-views="<?= escape((string) $views) ?>"
        data-created="<?= escape($created) ?>"
      >
        <a class="archive-card-link" href="<?= escape($watchUrl !== '' ? $watchUrl : '#') ?>"<?= $watchUrl === '' || $watchUrl === '#' ? ' onclick="event.preventDefault();showToast(\'Watch unavailable\')"' : '' ?>>
          <div class="acard-thumb">
            <?php if ($poster !== ''): ?>
              <img src="<?= escape($poster) ?>" alt="<?= escape($title) ?> poster" loading="lazy">
            <?php else: ?>
              <div class="acard-ph c1"><?= escape($initials) ?></div>
            <?php endif; ?>
            <div class="acard-badge <?= escape($badgeClass) ?>"><?= escape($badgeText) ?></div>
            <?php if ($rating !== null && $rating !== ''): ?>
              <div class="acard-score"><svg viewBox="0 0 24 24" fill="var(--gold)" stroke="none" width="12" height="12"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg><?= escape(number_format((float) $rating, 1)) ?></div>
            <?php endif; ?>
            <div class="acard-overlay">
              <div class="acard-play">
                <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><polygon points="5 3 19 12 5 21 5 3"/></svg>
              </div>
            </div>
          </div>
          <div class="acard-title"><?= escape($title) ?></div>
          <div class="acard-meta">
            <span><?= escape($genreLabel) ?></span>
            <span class="acard-dot"></span>
            <span><?= escape($year) ?></span>
          </div>
          <p class="acard-desc"><?= escape((string) ($item['synopsis'] ?? '')) ?></p>
        </a>
      </article>
    <?php endforeach; ?>
  </div>

  <div class="archive-empty" id="archiveEmpty" hidden>
    <strong>No titles match these filters</strong>
    <span>Try a broader type, genre, rating, or year range.</span>
  </div>
</div>