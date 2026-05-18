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
    <div class="title-card-grid">
      <?php foreach ($items as $item): ?>
        <?php
          $title = (string) ($item['title'] ?? 'Untitled');
          $poster = (string) ($item['poster'] ?? '');
          $watchUrl = (string) ($item['watchUrl'] ?? '#');
          $typeLabel = (string) ($item['type_label'] ?? 'Title');
          $year = (string) ($item['release_year'] ?? 'N/A');
          $score = $item['tmdb_rating'] ?? null;
          $initials = strtoupper(substr(preg_replace('/[^a-z0-9]+/i', '', $title) ?: 'VX', 0, 3));
        ?>
        <article class="acard">
          <a class="archive-card-link" href="<?= escape($watchUrl !== '' ? $watchUrl : '#') ?>"<?= $watchUrl === '' || $watchUrl === '#' ? ' onclick="event.preventDefault();showToast(\'Watch unavailable\')"' : '' ?>>
            <div class="acard-thumb">
              <?php if ($poster !== ''): ?>
                <img src="<?= escape($poster) ?>" alt="<?= escape($title) ?> poster" loading="lazy">
              <?php else: ?>
                <div class="acard-ph c1"><?= escape($initials) ?></div>
              <?php endif; ?>
              <div class="acard-badge badge-ep"><?= escape($typeLabel) ?></div>
              <?php if ($score !== null && $score !== ''): ?>
                <div class="acard-score"><svg viewBox="0 0 24 24" fill="var(--gold)" stroke="none" width="12" height="12"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg><?= escape(number_format((float) $score, 1)) ?></div>
              <?php endif; ?>
              <div class="acard-overlay">
                <div class="acard-play">
                  <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                </div>
              </div>
            </div>
            <div class="acard-title"><?= escape($title) ?></div>
            <div class="acard-meta">
              <span><?= escape((string) ($activeGenre['name'] ?? 'Genre')) ?></span>
              <span><?= escape($year) ?></span>
            </div>
          </a>
        </article>
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
