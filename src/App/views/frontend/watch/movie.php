<?= $this->start('content') ?>
<?php
$poster = ($item['poster_image'] ?? '') ?: ($item['poster_url'] ?? '');
$backdrop = ($item['backdrop_image'] ?? '') ?: $poster;
$rating = $item['tmdb_rating'] ?? null;
$genreLinks = is_array($item['genre_links'] ?? null) ? $item['genre_links'] : [];
?>
<main class="watch-shell">
  <div class="watch-layout">
    <section class="watch-main">
      <div class="watch-player">
        <div class="watch-backdrop" style="background-image:url('<?= escape($backdrop) ?>')"></div>
        <div class="watch-player-center">
          <button class="watch-play" type="button" onclick="showToast('Starting <?= escape($item['title']) ?>')">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
          </button>
          <div class="watch-title-overlay"><?= escape($item['title']) ?></div>
          <div class="watch-subtitle"><?= escape((string) ($item['release_year'] ?: 'Movie')) ?> · Full Movie</div>
        </div>
        <div class="watch-controls"><span>00:00</span><div class="watch-progress"></div><span>HD</span></div>
      </div>

      <div class="watch-main-card">
        <div class="watch-info">
          <div class="watch-poster"><?php if ($poster): ?><img src="<?= escape($poster) ?>" alt=""><?php endif; ?></div>
          <div>
            <div class="watch-kicker">Movie · TMDB #<?= (int) $item['tmdb_id'] ?></div>
            <h1 class="watch-heading"><?= escape($item['title']) ?></h1>
            <div class="watch-meta">
              <span class="watch-pill">4K HDR</span>
              <span class="watch-pill">Rating <?= escape((string) ($rating ?: 'N/A')) ?></span>
              <span class="watch-pill"><?= number_format((int) ($item['tmdb_vote_count'] ?? 0)) ?> votes</span>
              <span class="watch-pill"><?= number_format((int) ($item['views'] ?? 0)) ?> views</span>
              <?php foreach (array_slice($genreLinks, 0, 4) as $genre): ?>
                <a class="watch-pill" href="<?= escape((string) ($genre['url'] ?? '#')) ?>"><?= escape((string) ($genre['name'] ?? 'Genre')) ?></a>
              <?php endforeach; ?>
            </div>
            <p class="watch-desc"><?= escape($item['synopsis'] ?: 'No synopsis available.') ?></p>
            <div class="watch-actions">
              <button class="watch-btn" type="button">Watch Now</button>
              <button class="watch-btn secondary" type="button">Watchlist</button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <aside class="watch-side-card">
      <h2 class="watch-kicker">Related Movies</h2>
      <div class="related-list">
        <?php foreach (($related ?? []) as $relatedItem): ?>
          <?php
          $relatedPoster = ($relatedItem['poster_image'] ?? '') ?: ($relatedItem['poster_url'] ?? '');
          $relatedWatchUrl = (string) ($relatedItem['watchUrl'] ?? '#');
          ?>
          <a class="related-card" href="<?= escape($relatedWatchUrl !== '' ? $relatedWatchUrl : '#') ?>">
            <span class="related-poster"><?php if ($relatedPoster): ?><img src="<?= escape($relatedPoster) ?>" alt=""><?php endif; ?></span>
            <span><strong><?= escape($relatedItem['title']) ?></strong><span><?= escape((string) ($relatedItem['release_year'] ?: 'Movie')) ?> · ★ <?= escape((string) ($relatedItem['tmdb_rating'] ?? 'N/A')) ?></span></span>
          </a>
        <?php endforeach; ?>
      </div>
    </aside>
  </div>
</main>
<?= $this->end() ?>
