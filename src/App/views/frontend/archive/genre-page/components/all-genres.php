<?php
$mainGenres = !empty($main_genres) && is_array($main_genres) ? $main_genres : [];
$taxonomyListLabel = ($taxonomy_singular ?? 'Genre') === 'Genre' ? 'All Genres' : 'All ' . (string) ($taxonomy_plural ?? 'Networks');
?>
<?php if ($mainGenres !== []): ?>
<section class="genre-section" data-genre-section="standard">
  <div class="section-header">
    <div class="section-dot section-dot-cyan"></div>
    <div class="section-title"><?= escape($taxonomyListLabel) ?></div>
    <div class="section-line"></div>
  </div>
  <div class="genre-grid" id="genreGrid">
    <?php foreach ($mainGenres as $index => $genre): ?>
      <?php
        $name = (string) ($genre['name'] ?? 'Unknown');
        $backdrop = (string) (($genre['backdrop'] ?? '') ?: ($genre['poster'] ?? ''));
        $logoUrl = (string) ($genre['logo_url'] ?? '');
        $badgeClass = $index < 2 ? 'top' : ($index < 4 ? 'new' : '');
        $badgeText = $index < 2 ? 'Top' : ($index < 4 ? 'Rising' : '');
      ?>
      <a class="genre-card" data-genre-card data-section="standard" data-genre="<?= escape(strtolower($name . ' ' . ($genre['slug'] ?? ''))) ?>" href="<?= escape((string) ($genre['url'] ?? '#')) ?>#genre-results">
        <div class="gc-bg<?= $backdrop === '' ? ' is-empty' : '' ?>" style="<?= $backdrop !== '' ? 'background-image:url(\'' . escape($backdrop) . '\');' : '' ?>"></div>
        <div class="gc-gradient"></div>
        <div class="gc-noise"></div>
        <div class="gc-overlay-play">
          <div class="gc-play-ring"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg></div>
        </div>
        <?php if ($badgeText !== ''): ?>
          <div class="gc-badge <?= escape($badgeClass) ?>"><?= escape($badgeText) ?></div>
        <?php endif; ?>
        <div class="gc-content">
          <div class="gc-name">
            <?php if ($logoUrl !== ''): ?>
              <img class="network-logo" src="<?= escape($logoUrl) ?>" alt="<?= escape($name) ?>" loading="lazy">
            <?php else: ?>
              <?= escape($name) ?>
            <?php endif; ?>
          </div>
          <div class="gc-count"><strong><?= number_format((int) ($genre['total'] ?? 0)) ?></strong> titles</div>
        </div>
        <div class="gc-hover-strip"></div>
      </a>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>
