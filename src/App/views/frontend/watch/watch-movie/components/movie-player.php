<?php
use App\Support\MediaImage;

$sourceUrl = '/api/embed/sources?' . http_build_query([
    'type' => 'movie',
    'tmdbId' => (int) ($item['tmdb_id'] ?? 0),
]);
$playerBackdrop = MediaImage::backdropFromRow($item, 'player');
?>
<div class="watch-layout">

    <!-- ══ MAIN COLUMN ══ -->
    <div class="watch-main">

      <!-- VIDEO PLAYER -->
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
        <!-- Cinematic backdrop -->
        <div class="player-bg">
          <div class="player-backdrop">
            <?php echo $this->includePartial('/frontend/partials/media-image', [
                'media' => $playerBackdrop,
                'alt' => '',
                'loading' => 'lazy',
                'class' => 'player-backdrop-img',
            ]); ?>
          </div>
          <div class="player-grid-overlay"></div>
          <!-- Particles -->
          <div class="player-particles" id="particles"><div class="particle" style="width: 5.5723px; height: 5.5723px; left: 14.8501%; background: rgb(0, 200, 240); animation-duration: 8.63237s; animation-delay: 7.61883s;"></div><div class="particle" style="width: 3.03513px; height: 3.03513px; left: 73.7852%; background: rgb(255, 94, 125); animation-duration: 6.64631s; animation-delay: 1.84265s;"></div><div class="particle" style="width: 5.45042px; height: 5.45042px; left: 65.1689%; background: rgb(232, 23, 63); animation-duration: 9.39261s; animation-delay: 4.32718s;"></div><div class="particle" style="width: 3.81881px; height: 3.81881px; left: 68.0895%; background: rgb(255, 94, 125); animation-duration: 12.9492s; animation-delay: 2.72327s;"></div><div class="particle" style="width: 2.11868px; height: 2.11868px; left: 16.5147%; background: rgb(0, 200, 240); animation-duration: 13.1395s; animation-delay: 0.179745s;"></div><div class="particle" style="width: 4.56133px; height: 4.56133px; left: 34.4875%; background: rgb(139, 92, 246); animation-duration: 8.5024s; animation-delay: 7.97128s;"></div><div class="particle" style="width: 2.43879px; height: 2.43879px; left: 72.8038%; background: rgb(255, 195, 64); animation-duration: 6.67894s; animation-delay: 5.24125s;"></div><div class="particle" style="width: 4.56448px; height: 4.56448px; left: 87.8389%; background: rgb(0, 200, 240); animation-duration: 10.189s; animation-delay: 7.00574s;"></div><div class="particle" style="width: 2.44655px; height: 2.44655px; left: 68.0368%; background: rgb(0, 200, 240); animation-duration: 6.93743s; animation-delay: 7.38654s;"></div><div class="particle" style="width: 4.8532px; height: 4.8532px; left: 68.9734%; background: rgb(139, 92, 246); animation-duration: 13.1416s; animation-delay: 1.5841s;"></div><div class="particle" style="width: 3.29007px; height: 3.29007px; left: 64.851%; background: rgb(232, 23, 63); animation-duration: 9.8563s; animation-delay: 6.96086s;"></div><div class="particle" style="width: 5.7733px; height: 5.7733px; left: 60.7566%; background: rgb(139, 92, 246); animation-duration: 6.3656s; animation-delay: 0.821237s;"></div><div class="particle" style="width: 5.82568px; height: 5.82568px; left: 35.9143%; background: rgb(0, 200, 240); animation-duration: 7.42511s; animation-delay: 7.62529s;"></div><div class="particle" style="width: 3.80056px; height: 3.80056px; left: 55.4831%; background: rgb(0, 200, 240); animation-duration: 12.9746s; animation-delay: 7.63898s;"></div><div class="particle" style="width: 5.79754px; height: 5.79754px; left: 39.2939%; background: rgb(0, 200, 240); animation-duration: 8.09983s; animation-delay: 5.94949s;"></div><div class="particle" style="width: 3.35007px; height: 3.35007px; left: 78.4985%; background: rgb(139, 92, 246); animation-duration: 6.49753s; animation-delay: 5.30367s;"></div><div class="particle" style="width: 2.11289px; height: 2.11289px; left: 4.5601%; background: rgb(0, 200, 240); animation-duration: 8.51985s; animation-delay: 6.52259s;"></div><div class="particle" style="width: 2.00967px; height: 2.00967px; left: 15.7872%; background: rgb(232, 23, 63); animation-duration: 7.40946s; animation-delay: 5.94391s;"></div></div>
          <!-- Center content -->
          <div class="player-center">
            <button class="play-ring" type="button" aria-label="Play movie" onclick="initPlay()">
              <div class="play-ring-inner">
                <svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
              </div>
            </button>
            <div class="player-title-overlay"><?= escape($item['title'] ?? 'Movie') ?></div>
            <div class="player-subtitle">Click to play · <?= (int) ($item['release_year'] ?? 'N/A') ?> · <?php $rt = ($item['runtime_minutes'] ?? 0); if($rt) echo floor($rt/60) . 'h ' . ($rt%60) . 'm'; else echo 'N/A'; ?></div>
          </div>
        </div>

        <!-- Top bar -->
        <div class="player-top-bar">
          <button class="player-back-btn" onclick="showToast('← Back to Browse')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
            Back
          </button>
          <div class="player-top-title"><?= escape($item['title']) ?> (<?= (int) ($item['release_year'] ?? 'N/A') ?>) — Full Movie [4K]</div>
          <div class="live-badge" style="display:none" id="liveBadge">
            <div class="live-badge-dot"></div>
            LIVE
          </div>
        </div>

        <!-- Controls overlay -->
        <div class="player-controls-overlay">
          <div class="progress-bar" id="progressBar" onclick="seekVideo(event)">
            <div class="progress-buffered"></div>
            <div class="progress-fill" id="progressFill"></div>
          </div>
          <div class="controls-row">
            <button class="ctrl-btn" onclick="skipBack()">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="11 17 2 12 11 7 11 17"></polyline><polyline points="22 17 13 12 22 7 22 17"></polyline></svg>
            </button>
            <button class="ctrl-btn play-main" id="playBtn" onclick="togglePlay()">
              <svg viewBox="0 0 24 24" fill="currentColor" id="playIcon"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
            </button>
            <button class="ctrl-btn" onclick="skipFwd()">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="13 17 22 12 13 7 13 17"></polyline><polyline points="2 17 11 12 2 7 2 17"></polyline></svg>
            </button>
            <div class="volume-wrap">
              <button class="ctrl-btn" onclick="toggleMute()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" id="volIcon"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><path d="M15.54 8.46a5 5 0 0 1 0 7.07"></path><path d="M19.07 4.93a10 10 0 0 1 0 14.14"></path></svg>
              </button>
              <div class="volume-slider" onclick="setVolume(event)">
                <div class="volume-fill" id="volFill"></div>
              </div>
            </div>
            <span class="ctrl-time"><span id="curTime">47:22</span> <span class="ctrl-sep">/</span> 2:18:04</span>
            <div class="ctrl-right">
              <span class="quality-badge" onclick="showToast('Quality selector opened')">4K</span>
              <button class="ctrl-btn" onclick="showToast('Subtitles: English')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"></rect><path d="M7 9h.01M11 9h.01M15 9h.01M7 13h.01M11 13h.01M15 13h.01"></path></svg>
              </button>
              <button class="ctrl-btn" onclick="showToast('Settings opened')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07M4.93 4.93a10 10 0 0 0 0 14.14M8.46 8.46a5 5 0 0 0 0 7.07"></path></svg>
              </button>
              <button class="ctrl-btn" onclick="toggleFullscreen()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" id="fsIcon"><polyline points="15 3 21 3 21 9"></polyline><polyline points="9 21 3 21 3 15"></polyline><line x1="21" y1="3" x2="14" y2="10"></line><line x1="3" y1="21" x2="10" y2="14"></line></svg>
              </button>
            </div>
          </div>
        </div>
      </div>

      
      
