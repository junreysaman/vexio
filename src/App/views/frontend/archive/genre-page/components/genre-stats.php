<?php
$genreCount = is_array($genres ?? null) ? count($genres) : 0;
$catalogCount = (int) ($total_catalog_items ?? 0);
$featuredCount = is_array($featured_genres ?? null) ? count($featured_genres) : 0;
$activeGenre = is_array($active_genre ?? null) ? $active_genre : null;
$taxonomySingular = (string) ($taxonomy_singular ?? 'Genre');
$taxonomyPlural = (string) ($taxonomy_plural ?? 'Genres');
?>
<div class="stats-banner">
  <div class="stat-item">
    <div class="stat-number"><?= number_format($catalogCount) ?></div>
    <div class="stat-label"><?= escape($taxonomySingular) ?> Tags</div>
  </div>
  <div class="stat-item">
    <div class="stat-number"><?= number_format($genreCount) ?></div>
    <div class="stat-label"><?= escape($taxonomyPlural) ?></div>
  </div>
  <div class="stat-item">
    <div class="stat-number stat-number-cyan"><?= number_format($featuredCount) ?></div>
    <div class="stat-label">Featured</div>
  </div>
  <div class="stat-item">
    <div class="stat-number stat-number-gold"><?= number_format((int) ($total_items ?? 0)) ?></div>
    <div class="stat-label"><?= $activeGenre ? 'Current Titles' : 'Selected Titles' ?></div>
  </div>
</div>
