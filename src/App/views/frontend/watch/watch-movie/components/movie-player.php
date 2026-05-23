<?php
use App\Support\MediaImage;

$sourceUrl = '/api/embed/sources?' . http_build_query([
    'type' => 'movie',
    'tmdbId' => (int) ($item['tmdb_id'] ?? 0),
]);
$playerBackdrop = MediaImage::backdropFromRow($item, 'player');
?>
<div class="watch-layout">
  <div class="watch-main">
    <div class="player-wrap" id="playerWrap" data-player-source-url="<?= escape($sourceUrl) ?>">
      <div
        class="vexio-vidstack-player"
        id="vexioPlayerTarget"
        data-poster="<?= escape((string) ($playerBackdrop['src'] ?? '')) ?>"
        data-title="<?= escape((string) ($item['title'] ?? 'Movie')) ?>"
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
          <span id="vexioUnavailableDetail">This title does not have a browser-ready stream available right now. Please check back later.</span>
        </div>
      </div>
      <img class="vexio-player-watermark" src="/brand/vexio-logo-compact.svg" alt="Vexio" loading="eager">
    </div>
