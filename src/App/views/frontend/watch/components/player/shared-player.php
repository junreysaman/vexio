<?php
use App\Support\MediaImage;

/**
 * Shared Vexio player wrapper (movie + tv)
 *
 * Expected variables:
 * - $sourceType: 'movie'|'tv'
 * - $sourceId: tmdb id (int)
 * - $title: string
 * - $posterRow: media row array for poster/backdrop
 * - $season: (int|null) for tv
 * - $episode: (int|null) for tv
 * - $showTitle: string|null (used for title formatting)
 *
$sourceType = (string) ($sourceType ?? 'movie');
$tmdbId = (int) ($sourceId ?? 0);
$title = (string) ($title ?? 'Vexio');
$posterMedia = $posterRow ?? [];
$season = $season ?? null;
$episode = $episode ?? null;

// Build a primary embed URL from known VX providers (do not call external embed API)
$primaryEmbed = '';
if ($tmdbId > 0) {
  if ($sourceType === 'tv' && $season !== null && $episode !== null) {
    $primaryEmbed = 'https://vidfast.pro/tv/' . $tmdbId . '/' . $season . '/' . $episode;
  } else {
    $primaryEmbed = 'https://vidfast.pro/movie/' . $tmdbId;
  }
}

$sourceUrl = $primaryEmbed;

$params = [
  'type' => $sourceType,
  'tmdbId' => $tmdbId,
  'season' => $season,
  'episode' => $episode,
];

// If a global page uses a different key naming convention (tv uses season/episode),
// keeping these names aligned avoids having to duplicate player JS/logic.


foreach ($params as $k => $v) {
  if ($v === null || $v === '') {
    unset($params[$k]);
  }
}

$playerBackdrop = MediaImage::backdropFromRow($posterMedia, 'player');
?>

<div class="player-wrap" id="playerWrap" data-player-source-url="<?= escape($sourceUrl) ?>" data-player-embed-url="<?= escape($sourceUrl) ?>" data-title="<?= escape($title) ?>">
  <div
    class="vexio-player-target"
    id="vexioPlayerTarget"
    data-poster="<?= escape((string) ($playerBackdrop['src'] ?? '')) ?>"
    data-title="<?= escape($title) ?>"
  ></div>
  <div class="vexio-player-backdrop" id="vexioPlayerBackdrop" aria-hidden="true"></div>

  <div class="vexio-player-loader" id="vexioPlayerLoader" aria-live="polite">
    <div class="vexio-loader-ring"></div>
    <div class="vexio-loader-copy">
      <span class="vexio-loader-title">Preparing stream</span>
      <span class="vexio-loader-status" id="vexioLoaderStatus">Connecting to vexio-main</span>
    </div>
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
      <span id="vexioUnavailableDetail">This title does not have a browser-ready stream available right now. Please check back later.</span>
    </div>
  </div>

  <!-- Provider / quality switcher (populated from /api/embed/sources) -->
  <div class="vexio-provider-switch" id="vexioProviderSwitch" hidden>
    <div class="vexio-provider-switch-label">Source</div>
    <div class="vexio-provider-switch-value" id="vexioProviderSwitchValue">Auto</div>
    <button type="button" class="vexio-provider-switch-btn" id="vexioProviderSwitchToggle" aria-label="Select provider">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
    </button>
    <div class="vexio-provider-switch-menu" id="vexioProviderSwitchMenu" hidden></div>
  </div>

  <img class="vexio-player-watermark" src="/brand/vexio-logo-compact.svg" alt="Vexio" loading="eager">
</div>

