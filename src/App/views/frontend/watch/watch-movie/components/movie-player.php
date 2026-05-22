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
      <div class="vexio-server-selector" aria-label="Server">
        <button type="button" class="vexio-server-pill active" data-server-id="vexio-main">vexio-main</button>
      </div>
    </div>
