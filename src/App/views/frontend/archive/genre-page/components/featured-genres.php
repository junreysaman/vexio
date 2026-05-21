<?php
$featuredGenres = !empty($featured_genres) && is_array($featured_genres) ? $featured_genres : [];
$taxonomyPlural = (string) ($taxonomy_plural ?? 'Genres');
?>
<?php if ($featuredGenres !== []): ?>
<section class="genre-section" data-genre-section="featured">
  <div class="section-header">
    <div class="section-dot"></div>
    <div class="section-title">Featured <?= escape($taxonomyPlural) ?></div>
    <div class="section-line"></div>
  </div>
  <div class="genre-grid-featured">
    <?php foreach ($featuredGenres as $index => $genre): ?>
      <?php
        $name = (string) ($genre['name'] ?? 'Unknown');
        $backdrop = (string) (($genre['backdrop'] ?? '') ?: ($genre['poster'] ?? ''));
        $logoUrl = (string) ($genre['logo_url'] ?? '');
        $modelTitle = (string) ($genre['model_title'] ?? '');
        $badge = $index === 0 ? '#1 Most Content' : 'High Volume';
      ?>
      <a class="genre-card-large" data-genre-card data-section="featured" data-genre="<?= escape(strtolower($name . ' ' . ($genre['slug'] ?? ''))) ?>" href="<?= escape((string) ($genre['url'] ?? '#')) ?>#genre-results">
        <div class="gcl-corner"></div>
        <div class="gcl-corner-label">TOP</div>
        <div class="gcl-bg<?= $backdrop === '' ? ' is-empty' : '' ?>" style="<?= $backdrop !== '' ? 'background-image:url(\'' . escape($backdrop) . '\');' : '' ?>"></div>
        <div class="gcl-gradient"></div>
        <div class="gcl-content">
          <div class="gcl-kicker"><?= escape($badge) ?></div>
          <div class="gcl-name">
            <?php if ($logoUrl !== ''): ?>
              <img class="network-logo" src="<?= escape($logoUrl) ?>" alt="<?= escape($name) ?>" loading="lazy">
            <?php else: ?>
              <?= escape($name) ?>
            <?php endif; ?>
          </div>
          <div class="gcl-meta">
            <span class="count"><?= number_format((int) ($genre['total'] ?? 0)) ?></span> titles
            <?php if ($modelTitle !== ''): ?>
              <span class="gcl-model">Modeled on <?= escape($modelTitle) ?></span>
            <?php endif; ?>
          </div>
          <div class="gcl-tags">
            <span class="gcl-tag">Movies</span>
            <span class="gcl-tag">TV Shows</span>
          </div>
        </div>
        <span class="gcl-btn" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
        </span>
        <div class="gc-badge hot"><?= escape($badge) ?></div>
      </a>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>
