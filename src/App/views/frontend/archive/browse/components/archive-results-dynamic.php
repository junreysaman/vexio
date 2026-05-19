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

  <div class="trend-grid" id="cardGrid">
    <?php foreach ($items as $item): ?>
      <?php
        $type       = (string) ($item['type'] ?? 'movie');
        $typeLabel  = (string) ($item['type_label'] ?? ($type === 'tv_show' ? 'TV Show' : 'Movie'));
        $year       = (string) ($item['release_year'] ?? '');
        $rating     = $item['tmdb_rating'] ?? null;
        $views      = (int) ($item['views'] ?? 0);
        $created    = (string) ($item['created_at'] ?? '');
        $isFeatured = !empty($item['is_featured']);
        $genres     = !empty($item['genres']) && is_array($item['genres']) ? $item['genres'] : [];
        $genreSlugs = array_map(fn(array $g): string => basename((string) ($g['url'] ?? '')), $genres);

        $dataAttrs = 'data-title="' . escape(strtolower((string) ($item['title'] ?? ''))) . '"'
          . ' data-type="' . escape($type) . '"'
          . ' data-genres="' . escape(implode(',', $genreSlugs)) . '"'
          . ' data-year="' . escape($year) . '"'
          . ' data-rating="' . escape((string) ($rating ?? 0)) . '"'
          . ' data-views="' . escape((string) $views) . '"'
          . ' data-created="' . escape($created) . '"';

        echo $this->includePartial('/frontend/partials/card', [
          'cardTitle'      => (string) ($item['title'] ?? 'Untitled'),
          'cardPoster'     => (string) ($item['poster'] ?? ''),
          'cardWatchUrl'   => (string) ($item['watchUrl'] ?? '#'),
          'cardLabel'      => $typeLabel,
          'cardBadge'      => $isFeatured ? 'HOT' : '',
          'cardBadgeClass' => 'hot',
          'cardRating'     => $rating,
          'cardYear'       => $year,
          'cardDataAttrs'  => $dataAttrs,
        ]);
      ?>
    <?php endforeach; ?>
  </div>

  <div class="archive-empty" id="archiveEmpty" hidden>
    <strong>No titles match these filters</strong>
    <span>Try a broader type, genre, rating, or year range.</span>
  </div>
</div>
