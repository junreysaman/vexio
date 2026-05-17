<?php
$activeGenre = is_array($active_genre ?? null) ? $active_genre : null;
$genreName = (string) ($activeGenre['name'] ?? 'Genres');
$genreCount = is_array($genres ?? null) ? count($genres) : 0;
$catalogCount = (int) ($total_catalog_items ?? 0);
?>
<section id="genre-hero">
  <div class="genre-shell">
    <div class="genre-hero-inner">
      <div class="arch-breadcrumb">
        <a href="/">Home</a>
        <span>/</span>
        <a href="/archive/browse">Browse</a>
        <span>/</span>
        <span><?= $activeGenre ? escape($genreName) : 'Genres' ?></span>
      </div>

      <div class="genre-hero-top">
        <div>
          <h1 class="genre-hero-title">
            <?= $activeGenre ? escape($genreName) . ' <span class="accent">Archive</span>' : 'Browse by <span class="accent">Genre</span>' ?>
          </h1>
          <p class="genre-hero-sub">
            <?= $activeGenre
              ? 'Explore titles tagged under ' . escape($genreName) . ', with matching genre cards ranked by catalogue depth.'
              : 'Explore ' . number_format($catalogCount) . ' genre-tagged titles across ' . number_format($genreCount) . ' categories.' ?>
          </p>
        </div>
        <div class="genre-count-pill">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
          <?= number_format($genreCount) ?> Genres Available
        </div>
      </div>

      <div class="genre-search-row">
        <label class="genre-search-input">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
          <input placeholder="Filter genres..." oninput="filterGenres(this.value)" autocomplete="off">
        </label>
        <div class="genre-filter-pills">
          <button class="gf-pill active" type="button" onclick="setGenreFilter('all', this)">All</button>
          <button class="gf-pill" type="button" onclick="setGenreFilter('featured', this)">Featured</button>
          <button class="gf-pill" type="button" onclick="setGenreFilter('standard', this)">All Genres</button>
          <button class="gf-pill" type="button" onclick="setGenreFilter('compact', this)">More Genres</button>
        </div>
      </div>
    </div>
  </div>
</section>
