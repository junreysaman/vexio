<?php
$moreGenres = !empty($more_genres) && is_array($more_genres) ? $more_genres : [];
?>
<?php if ($moreGenres !== []): ?>
<section class="genre-section" data-genre-section="compact">
  <div class="section-header">
    <div class="section-dot section-dot-gold"></div>
    <div class="section-title">More Genres</div>
    <div class="section-line"></div>
  </div>
  <div class="genre-grid-compact" id="compactGrid">
    <?php foreach ($moreGenres as $genre): ?>
      <?php
        $name = (string) ($genre['name'] ?? 'Unknown');
        $backdrop = (string) (($genre['backdrop'] ?? '') ?: ($genre['poster'] ?? ''));
      ?>
      <a class="genre-card-sm" data-genre-card data-section="compact" data-genre="<?= escape(strtolower($name . ' ' . ($genre['slug'] ?? ''))) ?>" href="<?= escape((string) ($genre['url'] ?? '#')) ?>#genre-results">
        <div class="gcs-bg<?= $backdrop === '' ? ' is-empty' : '' ?>" style="<?= $backdrop !== '' ? 'background-image:url(\'' . escape($backdrop) . '\');' : '' ?>"></div>
        <div class="gcs-grad"></div>
        <div class="gcs-accent-line"></div>
        <div class="gcs-content">
          <div class="gcs-name"><?= escape($name) ?></div>
          <div class="gcs-count"><strong><?= number_format((int) ($genre['total'] ?? 0)) ?></strong> titles</div>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>
