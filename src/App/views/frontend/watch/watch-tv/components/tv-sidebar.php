<?php
$currentSeason = (int) ($episode['season_number'] ?? 1);
$currentEpisode = (int) ($episode['episode_number'] ?? 1);
$rating = $show['tmdb_rating'] ?? 'N/A';
$runtime = (int) ($show['runtime_minutes'] ?? 0);
$runtimeLabel = $runtime > 0 ? $runtime . 'm' : 'Episode';
?>
<div class="watch-sidebar">
  <div class="sidebar-head">
    <div><div class="sidebar-title">EPISODES</div></div>
    <div style="display:flex;align-items:center;gap:8px;">
      <span class="sidebar-season-badge">S<?= $currentSeason ?></span>
    </div>
  </div>

  <div class="continue-banner">
    <div class="continue-banner-icon"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg></div>
    <div class="continue-banner-info">
      <div class="continue-banner-label">Currently Watching</div>
      <div class="continue-banner-text">S<?= $currentSeason ?> E<?= $currentEpisode ?> - 0:00 / <?= escape($runtimeLabel) ?></div>
      <div class="continue-banner-progress-bar"><div class="continue-banner-progress-fill" style="width:12%"></div></div>
    </div>
  </div>

  <?= $this->includePartial('frontend/watch/watch-tv/ad/tv-sidebar-ad1') ?>

  <div class="episode-sidebar-scroll" data-sidebar-episodes data-page-size="12">
    <?php foreach (($episodes ?? []) as $idx => $row): ?>
      <?php
      $rowSeason = (int) ($row['season_number'] ?? $currentSeason);
      $rowEpisode = (int) ($row['episode_number'] ?? ($idx + 1));
      $isCurrent = $rowSeason === $currentSeason && $rowEpisode === $currentEpisode;
      $rowTitle = (string) (($row['episode_name'] ?? '') ?: ($row['title'] ?? 'Episode ' . $rowEpisode));
      $rowPoster = (string) (($row['backdrop_image'] ?? '') ?: (($row['poster_image'] ?? '') ?: ($row['poster_url'] ?? '')));
      $classes = ['ep-sidebar-card'];
      if ($rowEpisode < $currentEpisode) $classes[] = 'watched';
      if ($isCurrent) $classes[] = 'current';
      ?>
      <a class="<?= escape(implode(' ', $classes)) ?>" href="<?= escape((string) ($row['watchUrl'] ?? $row['watch_url'] ?? '#')) ?>" data-sidebar-episode="<?= $idx ?>" <?= $isCurrent ? 'data-current-episode="1"' : '' ?>>
        <div class="esc-thumb c<?= ($idx % 8) + 1 ?>">
          <?php if ($rowPoster !== ''): ?><img src="<?= escape($rowPoster) ?>" alt="" loading="lazy" decoding="async" style="width:100%;height:100%;object-fit:cover;"><?php else: ?><div class="esc-ph">S<?= $rowSeason ?> E<?= $rowEpisode ?></div><?php endif; ?>
          <div class="esc-play-icon"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg></div>
          <span class="esc-duration"><?= escape($runtimeLabel) ?></span>
          <?php if ($isCurrent): ?><div class="esc-ep-progress"><div class="esc-ep-progress-fill" style="width:12%"></div></div><?php endif; ?>
        </div>
        <div class="esc-info">
          <div class="esc-label"><?= $isCurrent ? 'NOW PLAYING' : 'Episode ' . $rowEpisode ?></div>
          <div class="esc-title"><?= escape($rowTitle) ?></div>
          <div class="esc-meta"><?= escape((string) ($row['air_date'] ?? '')) ?></div>
        </div>
        <div class="esc-score"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg><?= escape((string) $rating) ?></div>
      </a>
    <?php endforeach; ?>
    <div class="episode-sidebar-sentinel" data-sidebar-episode-sentinel hidden></div>
    <?php if (empty($episodes)): ?>
      <div style="padding:20px;text-align:center;color:var(--muted);font-size:12px;">No published episodes found</div>
    <?php endif; ?>
  </div>
</div>
