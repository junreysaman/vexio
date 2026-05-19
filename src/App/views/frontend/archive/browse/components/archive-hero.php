<?php
$heroTitle = $title ?? 'Browse';
$total = (int) ($total_items ?? count($items ?? []));
$heroGenres = array_slice($genres ?? [], 0, 12);
?>

<div id="archive-hero" class="vex-page-hero">
  <div class="container">
    <div class="vex-page-hero-inner">
      <div class="arch-breadcrumb">
        <a href="/">Home</a>
        <span>/</span>
        <a href="/archive/browse">Browse</a>
        <span>/</span>
        <span style="color:var(--muted2);">All Titles</span>
      </div>
      <div class="vex-page-hero-top">
        <div>
          <p class="vex-page-kicker">Archive</p>
          <h1 class="vex-page-title arch-hero-title"><?= escape($heroTitle) ?> <span class="accent">Archive</span></h1>
          <p class="vex-page-sub">Explore the Vexio catalogue with filters for type, quality, year, region, and title search.</p>
        </div>
        <div class="vex-page-pill">
          <span id="totalCount"><?= escape((string) $total) ?></span> titles
        </div>
      </div>
      <div class="vex-page-controls arch-hero-meta">
        <span>Showing <span class="count"><?= escape((string) $total) ?></span> titles</span>
        <span class="pipe">|</span>
        <span>Updated daily</span>
        <span class="pipe">|</span>
        <span>SUB & DUB Available</span>
      </div>
      <div class="arch-quick-genres">
        <?php foreach ($heroGenres as $genre): ?>
          <?php if (!empty($genre['name']) && !empty($genre['url'])): ?>
            <a class="qg-pill" href="<?= escape((string) $genre['url']) ?>"><?= escape((string) $genre['name']) ?></a>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
