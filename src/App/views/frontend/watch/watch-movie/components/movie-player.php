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
      <video
        class="vexio-plyr-video"
        id="vexioPlyrVideo"
        controls
        playsinline
        preload="metadata"
        crossorigin="anonymous"
        poster="<?= escape((string) ($playerBackdrop['src'] ?? '')) ?>"
      ></video>
    </div>
