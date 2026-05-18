<?php
$showTitle = (string) ($show['title'] ?? 'TV Show');
$currentSeason = (int) ($episode['season_number'] ?? 1);
$currentEpisode = (int) ($episode['episode_number'] ?? 1);
$episodeTitle = (string) (($episode['episode_name'] ?? '') ?: ($episode['title'] ?? 'Episode ' . $currentEpisode));
$backdrop = (string) (($episode['backdrop_image'] ?? '') ?: (($show['backdrop_image'] ?? '') ?: (($show['poster_image'] ?? '') ?: ($show['poster_url'] ?? ''))));
$runtime = (int) ($show['runtime_minutes'] ?? 0);
$runtimeLabel = $runtime > 0 ? $runtime . 'm' : 'Episode';
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
$embedUrl = (string) ($episode['embedUrl'] ?? $episode['embed_url'] ?? '');
?>
<div class="player-wrap" id="playerWrap" data-player-embed-url="<?= escape($embedUrl) ?>">
  <?php if ($embedUrl !== ''): ?>
    <iframe
      class="embedded-player-frame"
      id="embeddedPlayerFrame"
      title="<?= escape($showTitle . ' - ' . $episodeTitle) ?> player"
      loading="lazy"
      allow="autoplay; fullscreen; picture-in-picture; encrypted-media"
      allowfullscreen
      referrerpolicy="origin"
    ></iframe>
  <?php endif; ?>
  <div class="player-bg">
    <div class="player-backdrop" style="background-image:url('<?= escape($backdrop) ?>');background-size:cover;background-position:center;"></div>
    <div class="player-grid-overlay"></div>
    <div class="player-particles" id="particles"></div>
    <div class="player-center">
      <div class="play-ring" onclick="initPlay()">
        <div class="play-ring-inner">
          <svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
        </div>
      </div>
      <div class="player-ep-badge"><div class="ep-dot"></div>S<?= $currentSeason ?> &middot; E<?= $currentEpisode ?></div>
      <div class="player-title-overlay"><?= escape($showTitle) ?></div>
      <div class="player-subtitle"><?= escape($episodeTitle) ?> &middot; <?= escape($runtimeLabel) ?></div>
    </div>
  </div>

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

  <div class="player-top-bar">
    <button class="player-back-btn" onclick="history.back()">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
      Back
    </button>
    <div class="player-top-title"><?= escape($showTitle) ?> - S<?= $currentSeason ?>:E<?= $currentEpisode ?> <?= escape($episodeTitle) ?> [4K HDR]</div>
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

  <div class="player-controls-overlay">
    <div class="progress-bar" id="progressBar" onclick="seekVideo(event)">
      <div class="progress-buffered"></div>
      <div class="chapter-marker" style="left:18%" data-label="Act I"></div>
      <div class="chapter-marker" style="left:42%" data-label="Act II"></div>
      <div class="chapter-marker" style="left:74%" data-label="Act III"></div>
      <div class="progress-fill" id="progressFill"></div>
    </div>
    <div class="controls-row">
      <button class="ctrl-btn" onclick="skipBack()"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="11 17 2 12 11 7 11 17"></polyline><polyline points="22 17 13 12 22 7 22 17"></polyline></svg></button>
      <button class="ctrl-btn play-main" id="playBtn" onclick="togglePlay()"><svg viewBox="0 0 24 24" fill="currentColor" id="playIcon"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg></button>
      <button class="ctrl-btn" onclick="skipFwd()"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="13 17 22 12 13 7 13 17"></polyline><polyline points="2 17 11 12 2 7 2 17"></polyline></svg></button>
      <button class="ctrl-btn skip-ep" onclick="showToast('Intro skipped')">Skip Intro</button>
      <div class="volume-wrap">
        <button class="ctrl-btn" onclick="toggleMute()"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><path d="M15.54 8.46a5 5 0 0 1 0 7.07"></path><path d="M19.07 4.93a10 10 0 0 1 0 14.14"></path></svg></button>
        <div class="volume-slider" onclick="setVolume(event)"><div class="volume-fill" id="volFill"></div></div>
      </div>
      <span class="ctrl-time"><span id="curTime">0:00</span> <span class="ctrl-sep">/</span> <?= escape($runtimeLabel) ?></span>
      <div class="ctrl-right">
        <span class="quality-badge" onclick="showToast('Quality selector opened')">4K</span>
        <button class="ctrl-btn" onclick="showToast('Subtitles: English')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"></rect><path d="M7 9h.01M11 9h.01M15 9h.01M7 13h.01M11 13h.01M15 13h.01"></path></svg></button>
        <button class="ctrl-btn" onclick="showToast('Settings opened')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07M4.93 4.93a10 10 0 0 0 0 14.14M8.46 8.46a5 5 0 0 0 0 7.07"></path></svg></button>
        <button class="ctrl-btn" onclick="toggleFullscreen()"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 3 21 3 21 9"></polyline><polyline points="9 21 3 21 3 15"></polyline><line x1="21" y1="3" x2="14" y2="10"></line><line x1="3" y1="21" x2="10" y2="14"></line></svg></button>
      </div>
    </div>
  </div>
</div>
