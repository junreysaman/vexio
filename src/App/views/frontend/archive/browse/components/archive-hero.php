<?php
$heroTitle = $title ?? 'Browse';
$total = (int) ($total_items ?? count($items ?? []));
$heroGenres = array_slice($genres ?? [], 0, 12);
?>

<div class="ad-leaderboard" style="margin-top:var(--nav-h)">
  <div class="ad-box ad-728">
    <div class="ad-label">Advertisement</div>
    <div class="ad-copy">728 × 90 — Leaderboard</div>
    <div class="ad-sub">Your ad could be here</div>
  </div>
</div>

<div id="archive-hero" style="margin-bottom: 1cap;">
  <div class="container">
    <div class="arch-breadcrumb">
      <a href="/">Home</a>
      <span>›</span>
      <a href="/archive/browse">Browse</a>
      <span>›</span>
      <span style="color:var(--muted2);">All Titles</span>
    </div>
    <h1 class="arch-hero-title"><?= escape($heroTitle) ?> <span class="accent">Archive</span></h1>
    <div class="arch-hero-meta">
      <span>Showing <span class="count" id="totalCount"><?= escape((string) $total) ?></span> titles</span>
      <span class="pipe">|</span>
      <span>Updated daily</span>
      <span class="pipe">|</span>
      <span>SUB & DUB Available</span>
    </div>
    <div class="arch-quick-genres">
      <!-- <button class="qg-pill active" onclick="quickGenre(this,'All')">✦ All</button> -->
    </div>
  </div>
</div>
