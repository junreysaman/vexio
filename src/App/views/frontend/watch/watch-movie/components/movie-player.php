<?php
use App\Support\MediaImage;

$tmdbIdVal = (int) ($item['tmdb_id'] ?? 0);
$sourceUrl = (string) ($item['embed_url'] ?? $item['embedUrl'] ?? '');
$playerBackdrop = MediaImage::backdropFromRow($item, 'player');
?>
<div class="watch-layout">
  <div class="watch-main">
    <div class="player-wrap" id="playerWrap" data-player-source-url="<?= escape($sourceUrl) ?>" data-player-embed-url="<?= escape($sourceUrl) ?>">
      <div
        class="vexio-player-target"
        id="vexioPlayerTarget"
        data-poster="<?= escape((string) ($playerBackdrop['src'] ?? '')) ?>"
        data-title="<?= escape((string) ($item['title'] ?? 'Movie')) ?>"
      ></div>
        <div class="vexio-player-backdrop" id="vexioPlayerBackdrop" aria-hidden="true"></div>
      <div class="vexio-player-loader" id="vexioPlayerLoader" aria-live="polite">
        <div class="vexio-loader-film vexio-loader-film-top" aria-hidden="true"></div>
        <div class="vexio-loader-film vexio-loader-film-bottom" aria-hidden="true"></div>
        <div class="vexio-loader-timecode" aria-hidden="true">00:00:00:00</div>
        <div class="vexio-loader-mark" aria-hidden="true">
          <img src="/brand/vexio-player-loading.png" alt="">
        </div>
        <div class="vexio-loader-ring"></div>
        <div class="vexio-loader-copy">
          <span class="vexio-loader-title">Preparing stream</span>
          <span class="vexio-loader-status" id="vexioLoaderStatus">Connecting to VEXIO server</span>
        </div>
        <div class="vexio-loader-progress" aria-hidden="true"><span></span></div>
      </div>

      <div class="vexio-audio-unavailable" id="vexioAudioUnavailable" hidden>
        <div class="vexio-unavailable-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M11 5 6 9H2v6h4l5 4V5Z"/><path d="M15.54 8.46a5 5 0 0 1 0 7.07"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14"/></svg>
        </div>
        <div class="vexio-unavailable-copy">
          <span class="vexio-unavailable-kicker">Audio unavailable</span>
          <strong>This stream has no audio track</strong>
          <span id="vexioAudioUnavailableDetail">You can still use CC/subtitles.</span>
        </div>
      </div>

      <div class="vexio-player-unavailable" id="vexioPlayerUnavailable" hidden>
        <div class="vexio-unavailable-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"></circle><path d="M9.75 9.75 14.25 14.25M14.25 9.75 9.75 14.25"></path></svg>
        </div>
        <div class="vexio-unavailable-copy">
          <span class="vexio-unavailable-kicker">Stream unavailable</span>
          <strong>No playable source found</strong>
          <span id="vexioUnavailableDetail">This server does not have a playable stream right now. Try another server from the server list below the player.</span>
          <span class="vexio-server-hint">Select another server below if this one is unavailable.</span>
        </div>
      </div>
      <img class="vexio-player-watermark" src="/brand/vexio-logo-compact.svg" alt="Vexio" loading="eager">
    </div>
