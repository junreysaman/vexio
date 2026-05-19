<?php
$activeGenre = is_array($active_genre ?? null) ? $active_genre : null;
$items = !empty($items) && is_array($items) ? $items : [];
?>
<?php if ($activeGenre): ?>
<section class="genre-section active-genre-results" id="genre-results">
  <div class="section-header">
    <div class="section-dot"></div>
    <div class="section-title"><?= escape((string) ($activeGenre['name'] ?? 'Genre')) ?> Titles</div>
    <div class="section-line"></div>
  </div>

  <?php if ($items !== []): ?>
    <div class="trend-grid">
      <?php foreach ($items as $item): ?>
        <?php
          $type      = (string) ($item['type'] ?? 'movie');
          $typeLabel = (string) ($item['type_label'] ?? ($type === 'tv_show' ? 'TV Show' : 'Movie'));

          echo $this->includePartial('/frontend/partials/card', [
            'cardTitle'    => (string) ($item['title'] ?? 'Untitled'),
            'cardPoster'   => (string) ($item['poster'] ?? ''),
            'cardWatchUrl' => (string) ($item['watchUrl'] ?? '#'),
            'cardLabel'    => $typeLabel,
            'cardBadge'    => '',
            'cardRating'   => $item['tmdb_rating'] ?? null,
            'cardYear'     => (string) ($item['release_year'] ?? ''),
          ]);
        ?>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="genre-empty">
      <strong>No titles in this genre yet</strong>
      <span>Import or tag content and it will appear here.</span>
    </div>
  <?php endif; ?>
</section>
<?php endif; ?>
