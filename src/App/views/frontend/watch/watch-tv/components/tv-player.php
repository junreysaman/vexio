      <!-- VIDEO PLAYER -->
      <div class="player-wrap" id="playerWrap">
        <div class="player-bg">
          <div class="player-backdrop"></div>
          <div class="player-grid-overlay"></div>
          <div class="player-particles" id="particles"></div>
          <div class="player-center">
            <div class="play-ring" onclick="initPlay()">
              <div class="play-ring-inner">
                <svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
              </div>
            </div>
            <div class="player-ep-badge">
              <div class="ep-dot"></div>
              S2 · E7
            </div>
            <div class="player-title-overlay">STELLAR <span style="color:var(--cyan)">DRIFT</span></div>
            <div class="player-subtitle">"The Void Between Stars" · 47m</div>
          </div>
        </div>

        <!-- Next Episode Overlay -->
        <div class="next-ep-overlay" id="nextEpOverlay">
          <div style="text-align:center;margin-bottom:8px;">
            <div style="font-size:11px;font-weight:700;letter-spacing:2px;color:var(--cyan);text-transform:uppercase;margin-bottom:4px;">Up Next</div>
            <div class="nec-countdown" id="nextCountdown">5</div>
            <div class="nec-countdown-label">seconds until next episode</div>
          </div>
          <div class="next-ep-card">
            <div class="nec-thumb c1">S2 · E8</div>
            <div class="nec-info">
              <div class="nec-label">Season 2, Episode 8</div>
              <div class="nec-title">"Echoes of the Singularity"</div>
              <div class="nec-meta">44m · Airdate: Mar 22, 2024</div>
              <div class="nec-buttons">
                <button class="nec-btn-play" onclick="playNextEpisode()">
                  <svg viewBox="0 0 24 24" fill="currentColor" width="14" height="14"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                  Play Now
                </button>
                <button class="nec-btn-cancel" onclick="cancelNextEp()">Cancel</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Top Bar -->
        <div class="player-top-bar">
          <button class="player-back-btn" onclick="showToast('← Back to Stellar Drift')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            Back
          </button>
          <div class="player-top-title">Stellar Drift — S2:E7 "The Void Between Stars" [4K HDR]</div>
          <div class="player-ep-nav">
            <button class="ep-nav-btn" onclick="showToast('← Previous: S2 E6 — Fractured Horizon')">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
              Prev EP
            </button>
            <button class="ep-nav-btn next-ep-btn" onclick="triggerNextEp()">
              Next EP
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
          </div>
        </div>

        <!-- Controls -->
        <div class="player-controls-overlay">
          <div class="progress-bar" id="progressBar" onclick="seekVideo(event)">
            <div class="progress-buffered"></div>
            <!-- Chapter markers -->
            <div class="chapter-marker" style="left:18%" data-label="Act I"></div>
            <div class="chapter-marker" style="left:42%" data-label="Act II"></div>
            <div class="chapter-marker" style="left:74%" data-label="Act III"></div>
            <div class="progress-fill" id="progressFill"></div>
          </div>
          <div class="controls-row">
            <button class="ctrl-btn" onclick="skipBack()">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="11 17 2 12 11 7 11 17"/><polyline points="22 17 13 12 22 7 22 17"/></svg>
            </button>
            <button class="ctrl-btn play-main" id="playBtn" onclick="togglePlay()">
              <svg viewBox="0 0 24 24" fill="currentColor" id="playIcon"><polygon points="5 3 19 12 5 21 5 3"/></svg>
            </button>
            <button class="ctrl-btn" onclick="skipFwd()">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="13 17 22 12 13 7 13 17"/><polyline points="2 17 11 12 2 7 2 17"/></svg>
            </button>
            <button class="ctrl-btn skip-ep" onclick="showToast('⏭ Intro skipped!')">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="13 17 22 12 13 7 13 17"/><line x1="2" y1="12" x2="2" y2="12"/></svg>
              Skip Intro
            </button>
            <div class="volume-wrap">
              <button class="ctrl-btn" onclick="toggleMute()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M15.54 8.46a5 5 0 0 1 0 7.07"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14"/></svg>
              </button>
              <div class="volume-slider" onclick="setVolume(event)">
                <div class="volume-fill" id="volFill"></div>
              </div>
            </div>
            <span class="ctrl-time"><span id="curTime">27:14</span> <span class="ctrl-sep">/</span> 47:03</span>
            <div class="ctrl-right">
              <span class="quality-badge" onclick="showToast('Quality selector opened')">4K</span>
              <button class="ctrl-btn" onclick="showToast('Subtitles: English')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M7 9h.01M11 9h.01M15 9h.01M7 13h.01M11 13h.01M15 13h.01"/></svg>
              </button>
              <button class="ctrl-btn" onclick="showToast('Settings opened')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07M4.93 4.93a10 10 0 0 0 0 14.14M8.46 8.46a5 5 0 0 0 0 7.07"/></svg>
              </button>
              <button class="ctrl-btn" onclick="toggleFullscreen()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 3 21 3 21 9"/><polyline points="9 21 3 21 3 15"/><line x1="21" y1="3" x2="14" y2="10"/><line x1="3" y1="21" x2="10" y2="14"/></svg>
              </button>
            </div>
          </div>
        </div>
      </div><!-- /player-wrap -->