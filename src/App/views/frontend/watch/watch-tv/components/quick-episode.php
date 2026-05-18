<?php
$currentSeason = (int) ($episode['season_number'] ?? 1);
$currentEpisode = (int) ($episode['episode_number'] ?? 1);
?>
<div class="season-bar">
  <div class="container">
    <div class="season-inner">
      <span class="season-label">Season</span>
      <div class="season-select-wrap">
        <select class="season-select" onchange="if (this.value) window.location.href = this.value">
          <?php foreach (($seasons ?? []) as $season): ?>
            <?php $seasonNo = (int) ($season['season_number'] ?? 1); ?>
            <option value="<?= escape((string) ($season['watchUrl'] ?? $season['watch_url'] ?? '')) ?>" <?= $seasonNo === $currentSeason ? 'selected' : '' ?>>
              Season <?= $seasonNo ?>
            </option>
          <?php endforeach; ?>
          <?php if (empty($seasons)): ?>
            <option selected>Season <?= $currentSeason ?></option>
          <?php endif; ?>
        </select>
        <span class="season-select-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg></span>
      </div>
      <div class="ep-quick-nav" id="epQuickNav">
        <?php foreach (($episodes ?? []) as $row): ?>
          <?php
          $epNo = (int) ($row['episode_number'] ?? 1);
          $classes = ['ep-chip'];
          if ($epNo < $currentEpisode) {
              $classes[] = 'watched';
          }
          if ($epNo === $currentEpisode) {
              $classes[] = 'active';
          }
          ?>
          <a class="<?= escape(implode(' ', $classes)) ?>" href="<?= escape((string) ($row['watchUrl'] ?? $row['watch_url'] ?? '#')) ?>" data-ep="<?= $epNo ?>">E<?= $epNo ?></a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
