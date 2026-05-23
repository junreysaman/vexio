<?php
use App\Support\MediaImage;

$showTitle = (string) ($show['title'] ?? 'TV Show');
$currentSeason = (int) ($episode['season_number'] ?? 1);
$currentEpisode = (int) ($episode['episode_number'] ?? 1);
$episodeTitle = (string) (($episode['episode_name'] ?? '') ?: ($episode['title'] ?? 'Episode ' . $currentEpisode));
$playerBackdrop = MediaImage::backdropFromRow(array_merge($show, [
    'backdrop_url' => ($episode['backdrop_url'] ?? '') ?: ($show['backdrop_url'] ?? ''),
]), 'player');
$prevEpisode = null;
$nextEpisode = null;
$episodeRows = $episodes ?? [];
foreach ($episodeRows as $idx => $row) {
    if ((int) ($row['episode_number'] ?? 0) === $currentEpisode && (int) ($row['season_number'] ?? 0) === $currentSeason) {
        $prevEpisode = $episodeRows[$idx - 1] ?? null;
        $nextEpisode = $episodeRows[$idx + 1] ?? null;
        break;
    }
}
$nextUrl = (string) ($nextEpisode['watchUrl'] ?? $nextEpisode['watch_url'] ?? '');
$prevUrl = (string) ($prevEpisode['watchUrl'] ?? $prevEpisode['watch_url'] ?? '');
$nextTitle = (string) (($nextEpisode['episode_name'] ?? '') ?: ($nextEpisode['title'] ?? 'Next episode'));
$sourceUrl = '/api/embed/sources?' . http_build_query([
    'type' => 'tv',
    'tmdbId' => (int) ($show['tmdb_id'] ?? 0),
    'season' => $currentSeason,
    'episode' => $currentEpisode,
]);
?>
<div
  class="player-wrap"
  id="playerWrap"
  data-player-source-url="<?= escape($sourceUrl) ?>"
  data-title="<?= escape($showTitle . ' - S' . $currentSeason . ':E' . $currentEpisode . ' ' . $episodeTitle) ?>"
>
  <div
    class="vexio-vidstack-player"
    id="vexioPlayerTarget"
    data-poster="<?= escape((string) ($playerBackdrop['src'] ?? '')) ?>"
    data-title="<?= escape($showTitle . ' - S' . $currentSeason . ':E' . $currentEpisode . ' ' . $episodeTitle) ?>"
  ></div>
  <div class="vexio-player-loader" id="vexioPlayerLoader" aria-live="polite">
    <div class="vexio-loader-ring"></div>
    <div class="vexio-loader-copy">
      <span class="vexio-loader-title">Preparing stream</span>
      <span class="vexio-loader-status" id="vexioLoaderStatus">Connecting to vexio-main</span>
    </div>
  </div>
  <div class="vexio-player-unavailable" id="vexioPlayerUnavailable" hidden>
    <div class="vexio-unavailable-icon" aria-hidden="true">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"></circle><path d="M9.75 9.75 14.25 14.25M14.25 9.75 9.75 14.25"></path></svg>
    </div>
    <div class="vexio-unavailable-copy">
      <span class="vexio-unavailable-kicker">Stream unavailable</span>
      <strong>No playable source found</strong>
      <span id="vexioUnavailableDetail">This episode does not have a browser-ready stream available right now. Please check back later.</span>
    </div>
  </div>
  <img class="vexio-player-watermark" src="/brand/vexio-logo-compact.svg" alt="Vexio" loading="eager">

  <div class="next-ep-overlay" id="nextEpOverlay" data-has-next="<?= $nextUrl !== '' ? '1' : '0' ?>" data-next-url="<?= escape($nextUrl) ?>">
    <div style="text-align:center;margin-bottom:8px;">
      <div style="font-size:11px;font-weight:700;letter-spacing:2px;color:var(--cyan);text-transform:uppercase;margin-bottom:4px;">Up Next</div>
      <div class="nec-countdown" id="nextCountdown">5</div>
      <div class="nec-countdown-label">seconds until next episode</div>
    </div>
    <div class="next-ep-card">
      <div class="nec-thumb c1">S<?= (int) ($nextEpisode['season_number'] ?? $currentSeason) ?> &middot; E<?= (int) ($nextEpisode['episode_number'] ?? ($currentEpisode + 1)) ?></div>
      <div class="nec-info">
        <div class="nec-label">Season <?= (int) ($nextEpisode['season_number'] ?? $currentSeason) ?>, Episode <?= (int) ($nextEpisode['episode_number'] ?? ($currentEpisode + 1)) ?></div>
        <div class="nec-title"><?= escape($nextTitle) ?></div>
        <div class="nec-meta"><?= escape((string) ($nextEpisode['air_date'] ?? '')) ?></div>
        <div class="nec-buttons">
          <button class="nec-btn-play" onclick="playNextEpisode()">
            <svg viewBox="0 0 24 24" fill="currentColor" width="14" height="14"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
            Play Now
          </button>
          <button class="nec-btn-cancel" onclick="cancelNextEp()">Cancel</button>
        </div>
      </div>
    </div>
  </div>

  <div class="player-ep-nav">
    <?php if ($prevUrl !== ''): ?>
      <a class="ep-nav-btn" href="<?= escape($prevUrl) ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
        Prev EP
      </a>
    <?php endif; ?>
    <?php if ($nextUrl !== ''): ?>
      <a class="ep-nav-btn next-ep-btn" href="<?= escape($nextUrl) ?>">
        Next EP
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
      </a>
    <?php endif; ?>
  </div>
</div>
