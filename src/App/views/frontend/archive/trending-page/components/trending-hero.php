<?php
$filters = is_array($filters ?? null) ? $filters : [];
$totalItems = (int) ($total_items ?? 0);
?>

<div id="trend-hero" class="vex-page-hero">
  <div class="container">
    <div class="vex-page-hero-inner trend-hero-inner">
      <div class="arch-breadcrumb">
        <a href="/">Home</a>
        <span>/</span>
        <a href="/archive/browse">Browse</a>
        <span>/</span>
        <span style="color:var(--muted2);">Trending</span>
      </div>
      <div class="vex-page-hero-top trend-hero-top">
        <div>
          <p class="vex-page-kicker">Live catalogue</p>
          <h1 class="vex-page-title trend-hero-title">
            <span class="accent-fire">Trending</span><br>
            <span>Right Now</span>
          </h1>
          <p class="vex-page-sub trend-hero-sub">Updated from the Vexio catalogue. Ranked by views, ratings, freshness, and community momentum across <strong><?= number_format($totalItems) ?> titles</strong>.</p>
        </div>
        <div class="vex-page-pill live-pill">
          <span class="live-dot"></span>
          Live catalogue
        </div>
      </div>
      <div class="vex-page-controls trend-filter-row" data-trending-controls>
        <div class="trend-filter-pills" role="tablist" aria-label="Trending filters">
          <?php foreach ($filters as $index => $filter): ?>
            <button class="tf-pill <?= $index === 0 ? 'active' : '' ?>" type="button" data-filter="<?= escape((string) ($filter['value'] ?? 'all')) ?>">
              <?= escape((string) ($filter['label'] ?? 'Filter')) ?>
            </button>
          <?php endforeach; ?>
        </div>
        <div class="trend-time-select" aria-label="Trending time range">
          <button class="tts-btn active" type="button" data-time="day">Today</button>
          <button class="tts-btn" type="button" data-time="week">Week</button>
          <button class="tts-btn" type="button" data-time="month">Month</button>
        </div>
      </div>
    </div>
  </div>
</div>
