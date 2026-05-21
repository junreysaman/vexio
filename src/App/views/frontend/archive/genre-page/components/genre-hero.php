<?php
$activeGenre = is_array($active_genre ?? null) ? $active_genre : null;
$taxonomySingular = (string) ($taxonomy_singular ?? 'Genre');
$taxonomyPlural = (string) ($taxonomy_plural ?? 'Genres');
$taxonomyArchiveLabel = (string) ($taxonomy_archive_label ?? 'Genre archive');
$taxonomyListLabel = $taxonomySingular === 'Genre' ? 'All Genres' : 'All ' . $taxonomyPlural;
$genreName = (string) ($activeGenre['name'] ?? $taxonomyPlural);
$genreCount = is_array($genres ?? null) ? count($genres) : 0;
$catalogCount = (int) ($total_catalog_items ?? 0);
?>
<section id="genre-hero" class="vex-page-hero">
  <div class="container genre-shell">
    <div class="vex-page-hero-inner genre-hero-inner">
      <div class="arch-breadcrumb">
        <a href="/">Home</a>
        <span>/</span>
        <a href="/archive/browse">Browse</a>
        <span>/</span>
        <span><?= $activeGenre ? escape($genreName) : escape($taxonomyPlural) ?></span>
      </div>

      <div class="vex-page-hero-top genre-hero-top">
        <div>
          <p class="vex-page-kicker"><?= $activeGenre ? escape($taxonomyArchiveLabel) : escape($taxonomyPlural) ?></p>
          <h1 class="vex-page-title genre-hero-title">
            <?= $activeGenre ? escape($genreName) . ' <span class="accent">Archive</span>' : 'Browse by <span class="accent">' . escape($taxonomySingular) . '</span>' ?>
          </h1>
          <p class="vex-page-sub genre-hero-sub">
            <?= $activeGenre
              ? 'Explore titles tagged under ' . escape($genreName) . ', with matching ' . strtolower(escape($taxonomySingular)) . ' cards ranked by catalogue depth.'
              : 'Explore ' . number_format($catalogCount) . ' tagged titles across ' . number_format($genreCount) . ' ' . strtolower(escape($taxonomyPlural)) . '.' ?>
          </p>
        </div>
        <div class="vex-page-pill genre-count-pill">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
          <?= number_format($genreCount) ?> <?= escape($taxonomyPlural) ?> Available
        </div>
      </div>

      <div class="vex-page-controls genre-search-row">
        <label class="genre-search-input">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
          <input placeholder="Filter <?= strtolower(escape($taxonomyPlural)) ?>..." oninput="filterGenres(this.value)" autocomplete="off">
        </label>
        <div class="genre-filter-pills">
          <button class="gf-pill active" type="button" onclick="setGenreFilter('all', this)">All</button>
          <button class="gf-pill" type="button" onclick="setGenreFilter('featured', this)">Featured</button>
          <button class="gf-pill" type="button" onclick="setGenreFilter('standard', this)"><?= escape($taxonomyListLabel) ?></button>
          <button class="gf-pill" type="button" onclick="setGenreFilter('compact', this)">More <?= escape($taxonomyPlural) ?></button>
        </div>
      </div>
    </div>
  </div>
</section>
