<?= $this->start('styles') ?>
<link rel="stylesheet" href="/assets/frontend/css/watch-tv-show.css">
<?= $this->end('styles') ?>

<?= $this->start('content') ?>

<div class="page-wrap">

  <!-- TOP LEADERBOARD AD -->
  <div class="top-ad-bar">
    <div class="ad-box ad-728">
      <span class="ad-label">Advertisement</span>
      <span class="ad-copy">✦ DUMMY LEADERBOARD AD — 728×90 ✦</span>
      <span class="ad-sub">Placeholder / Demo Placement</span>
    </div>
  </div>

  <!-- WATCH LAYOUT -->
  <div class="watch-layout">

    <!-- ══ MAIN COLUMN ══ -->
    <div class="watch-main">

      <!-- VIDEO PLAYER -->
      <div class="player-wrap" id="playerWrap">
        <div class="player-bg">
          <div class="player-backdrop"></div>
          <div class="player-grid-overlay"></div>
          <div class="player-particles" id="particles"><div class="particle" style="width: 4.1542px; height: 4.1542px; left: 18.4411%; background: rgb(255, 94, 125); animation-duration: 13.8634s; animation-delay: 1.08823s;"></div><div class="particle" style="width: 3.48857px; height: 3.48857px; left: 26.814%; background: rgb(0, 200, 240); animation-duration: 6.68475s; animation-delay: 1.04065s;"></div><div class="particle" style="width: 5.84055px; height: 5.84055px; left: 54.1755%; background: rgb(139, 92, 246); animation-duration: 8.75102s; animation-delay: 3.33198s;"></div><div class="particle" style="width: 2.81264px; height: 2.81264px; left: 44.4212%; background: rgb(255, 195, 64); animation-duration: 9.72176s; animation-delay: 7.24921s;"></div><div class="particle" style="width: 3.83844px; height: 3.83844px; left: 32.9346%; background: rgb(255, 195, 64); animation-duration: 12.6799s; animation-delay: 7.23689s;"></div><div class="particle" style="width: 4.32078px; height: 4.32078px; left: 18.7162%; background: rgb(139, 92, 246); animation-duration: 11.3909s; animation-delay: 2.59137s;"></div><div class="particle" style="width: 3.47505px; height: 3.47505px; left: 89.5121%; background: rgb(232, 23, 63); animation-duration: 11.1611s; animation-delay: 1.43656s;"></div><div class="particle" style="width: 3.8007px; height: 3.8007px; left: 36.4319%; background: rgb(232, 23, 63); animation-duration: 7.26149s; animation-delay: 5.75763s;"></div><div class="particle" style="width: 2.2342px; height: 2.2342px; left: 42.8751%; background: rgb(0, 200, 240); animation-duration: 13.5894s; animation-delay: 6.43484s;"></div><div class="particle" style="width: 3.0635px; height: 3.0635px; left: 7.44085%; background: rgb(232, 23, 63); animation-duration: 11.9614s; animation-delay: 5.67166s;"></div><div class="particle" style="width: 2.68596px; height: 2.68596px; left: 28.4168%; background: rgb(139, 92, 246); animation-duration: 13.8452s; animation-delay: 4.69522s;"></div><div class="particle" style="width: 3.06824px; height: 3.06824px; left: 47.654%; background: rgb(0, 200, 240); animation-duration: 6.94683s; animation-delay: 1.56495s;"></div><div class="particle" style="width: 4.85821px; height: 4.85821px; left: 53.7125%; background: rgb(0, 200, 240); animation-duration: 8.54086s; animation-delay: 3.6831s;"></div><div class="particle" style="width: 4.40584px; height: 4.40584px; left: 64.1481%; background: rgb(255, 195, 64); animation-duration: 8.59058s; animation-delay: 0.57196s;"></div><div class="particle" style="width: 4.76881px; height: 4.76881px; left: 10.4725%; background: rgb(255, 195, 64); animation-duration: 6.64769s; animation-delay: 1.66673s;"></div><div class="particle" style="width: 5.80637px; height: 5.80637px; left: 58.8756%; background: rgb(232, 23, 63); animation-duration: 11.1765s; animation-delay: 7.42089s;"></div><div class="particle" style="width: 2.95343px; height: 2.95343px; left: 55.6883%; background: rgb(255, 94, 125); animation-duration: 8.23932s; animation-delay: 5.82217s;"></div><div class="particle" style="width: 5.51734px; height: 5.51734px; left: 35.7717%; background: rgb(255, 195, 64); animation-duration: 10.9281s; animation-delay: 6.82131s;"></div></div>
          <div class="player-center">
            <div class="play-ring" onclick="initPlay()">
              <div class="play-ring-inner">
                <svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
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
                  <svg viewBox="0 0 24 24" fill="currentColor" width="14" height="14"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
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
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
            Back
          </button>
          <div class="player-top-title">Stellar Drift — S2:E7 "The Void Between Stars" [4K HDR]</div>
          <div class="player-ep-nav">
            <button class="ep-nav-btn" onclick="showToast('← Previous: S2 E6 — Fractured Horizon')">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
              Prev EP
            </button>
            <button class="ep-nav-btn next-ep-btn" onclick="triggerNextEp()">
              Next EP
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
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
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="11 17 2 12 11 7 11 17"></polyline><polyline points="22 17 13 12 22 7 22 17"></polyline></svg>
            </button>
            <button class="ctrl-btn play-main" id="playBtn" onclick="togglePlay()">
              <svg viewBox="0 0 24 24" fill="currentColor" id="playIcon"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
            </button>
            <button class="ctrl-btn" onclick="skipFwd()">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="13 17 22 12 13 7 13 17"></polyline><polyline points="2 17 11 12 2 7 2 17"></polyline></svg>
            </button>
            <button class="ctrl-btn skip-ep" onclick="showToast('⏭ Intro skipped!')">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="13 17 22 12 13 7 13 17"></polyline><line x1="2" y1="12" x2="2" y2="12"></line></svg>
              Skip Intro
            </button>
            <div class="volume-wrap">
              <button class="ctrl-btn" onclick="toggleMute()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><path d="M15.54 8.46a5 5 0 0 1 0 7.07"></path><path d="M19.07 4.93a10 10 0 0 1 0 14.14"></path></svg>
              </button>
              <div class="volume-slider" onclick="setVolume(event)">
                <div class="volume-fill" id="volFill"></div>
              </div>
            </div>
            <span class="ctrl-time"><span id="curTime">27:14</span> <span class="ctrl-sep">/</span> 47:03</span>
            <div class="ctrl-right">
              <span class="quality-badge" onclick="showToast('Quality selector opened')">4K</span>
              <button class="ctrl-btn" onclick="showToast('Subtitles: English')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"></rect><path d="M7 9h.01M11 9h.01M15 9h.01M7 13h.01M11 13h.01M15 13h.01"></path></svg>
              </button>
              <button class="ctrl-btn" onclick="showToast('Settings opened')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07M4.93 4.93a10 10 0 0 0 0 14.14M8.46 8.46a5 5 0 0 0 0 7.07"></path></svg>
              </button>
              <button class="ctrl-btn" onclick="toggleFullscreen()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 3 21 3 21 9"></polyline><polyline points="9 21 3 21 3 15"></polyline><line x1="21" y1="3" x2="14" y2="10"></line><line x1="3" y1="21" x2="10" y2="14"></line></svg>
              </button>
            </div>
          </div>
        </div>
      </div><!-- /player-wrap -->

      <!-- SERVER / LANGUAGE SELECTOR -->
      <div class="server-bar">
        <div class="container">
          <div class="server-inner">
            <span class="server-label">Server</span>
            <div class="server-tabs">
              <button class="server-tab active" onclick="selectServer(this,'VX-1')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12H2m20 0-4 4m4-4-4-4M2 12l4 4M2 12l4-4"></path></svg>
                VX-1 <span style="color:var(--green);font-size:9px;">●</span>
              </button>
              <button class="server-tab" onclick="selectServer(this,'VX-2')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12H2m20 0-4 4m4-4-4-4M2 12l4 4M2 12l4-4"></path></svg>
                VX-2
              </button>
              <button class="server-tab" onclick="selectServer(this,'HD-Fast')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12H2m20 0-4 4m4-4-4-4M2 12l4 4M2 12l4-4"></path></svg>
                HD-Fast
              </button>
              <button class="server-tab" onclick="selectServer(this,'Backup')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12H2m20 0-4 4m4-4-4-4M2 12l4 4M2 12l4-4"></path></svg>
                Backup
              </button>
            </div>
            <div class="server-divider"></div>
            <div class="lang-tabs">
              <button class="lang-tab active sub" onclick="selectLang(this,'sub')">SUB</button>
              <button class="lang-tab dub" onclick="selectLang(this,'dub')">DUB</button>
            </div>
          </div>
        </div>
      </div>

      <!-- SEASON / EPISODE QUICK NAV -->
      <div class="season-bar">
        <div class="container">
          <div class="season-inner">
            <span class="season-label">Season</span>
            <div class="season-select-wrap">
              <select class="season-select" onchange="showToast('Switched to ' + this.options[this.selectedIndex].text)">
                <option>Season 1 (12 eps)</option>
                <option selected="">Season 2 (10 eps)</option>
                <option>Season 3 (8 eps) — Ongoing</option>
              </select>
              <span class="season-select-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg></span>
            </div>
            <div class="ep-quick-nav" id="epQuickNav">
              <button class="ep-chip watched" onclick="selectEpChip(this,1)" data-ep="1">E1</button>
              <button class="ep-chip watched" onclick="selectEpChip(this,2)" data-ep="2">E2</button>
              <button class="ep-chip watched" onclick="selectEpChip(this,3)" data-ep="3">E3</button>
              <button class="ep-chip watched" onclick="selectEpChip(this,4)" data-ep="4">E4</button>
              <button class="ep-chip watched" onclick="selectEpChip(this,5)" data-ep="5">E5</button>
              <button class="ep-chip watched" onclick="selectEpChip(this,6)" data-ep="6">E6</button>
              <button class="ep-chip active" onclick="selectEpChip(this,7)" data-ep="7">E7</button>
              <button class="ep-chip" onclick="selectEpChip(this,8)" data-ep="8">E8</button>
              <button class="ep-chip" onclick="selectEpChip(this,9)" data-ep="9">E9</button>
              <button class="ep-chip" onclick="selectEpChip(this,10)" data-ep="10">E10</button>
            </div>
          </div>
        </div>
      </div>

      <!-- CONTENT AREA -->
      <div class="container">
        <div class="content-pad">

          <!-- SHOW HERO INFO -->
          <div class="show-info-wrap">
            <div class="show-poster c1">
              <div class="show-poster-badge">S3 ONGOING</div>
              STELLAR<br>DRIFT
            </div>
            <div class="show-details">
              <div class="show-type-pill">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="15" rx="2"></rect><polyline points="17 2 12 7 7 2"></polyline></svg>
                TV Series · Sci-Fi · 2022 – Present
              </div>
              <div class="show-title">Stellar <span>Drift</span></div>

              <!-- Currently Playing Episode -->
              <div class="ep-now-label">
                <span class="en-badge">NOW PLAYING</span>
                <span class="en-sep">·</span>
                <span class="en-title">S2 E7 — "The Void Between Stars"</span>
                <span class="en-sep">·</span>
                <span class="en-duration">47m</span>
              </div>

              <div class="show-meta-row">
                <span class="mmeta-tag hd">4K HDR</span>
                <span class="mmeta-tag sub">SUB</span>
                <span class="mmeta-tag dub">DUB</span>
                <span class="mmeta-tag ongoing">● ONGOING</span>
                <div class="mmeta-rating">
                  <svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                  9.1
                </div>
                <div class="mmeta-dot"></div>
                <span style="font-size:12px;color:var(--muted2)">3 Seasons</span>
                <div class="mmeta-dot"></div>
                <span style="font-size:12px;color:var(--muted2)">30 Episodes</span>
                <div class="mmeta-dot"></div>
                <span style="font-size:12px;color:var(--muted2)">TV-MA</span>
              </div>
              <div class="show-desc">
                When Earth's last generation-ship loses contact with its destination colony, Commander Yara Nyx must navigate a fractured crew, rogue AI mutinies, and the terrifying silence that suggests they are not alone in the void.
              </div>
              <div class="show-actions">
                <button class="btn-primary" onclick="initPlay()">
                  <svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                  Resume S2 E7
                </button>
                <button class="btn-secondary" onclick="showToast('Added to Watchlist ✓')">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                  Watchlist
                </button>
                <button class="btn-icon liked" id="likeBtn" onclick="toggleLike()">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                </button>
                <button class="btn-icon" onclick="showToast('Link copied to clipboard!')">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>
                </button>
                <button class="btn-icon" onclick="showToast('Download started!')">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                </button>
              </div>
            </div>
          </div>

          <!-- STATS GRID -->
          <div class="details-grid">
            <div class="detail-card">
              <div class="detail-label">IMDb Rating</div>
              <div class="detail-val gold">★ 9.1 / 10</div>
            </div>
            <div class="detail-card">
              <div class="detail-label">Episodes</div>
              <div class="detail-val">30 Total</div>
            </div>
            <div class="detail-card">
              <div class="detail-label">Premiered</div>
              <div class="detail-val cyan">Sep 04, 2022</div>
            </div>
            <div class="detail-card">
              <div class="detail-label">Status</div>
              <div class="detail-val" style="color:var(--gold)">Ongoing ●</div>
            </div>
            <div class="detail-card">
              <div class="detail-label">Studio</div>
              <div class="detail-val">Orbit Films</div>
            </div>
            <div class="detail-card">
              <div class="detail-label">Views</div>
              <div class="detail-val accent">18.7M</div>
            </div>
            <div class="detail-card">
              <div class="detail-label">Quality</div>
              <div class="detail-val">4K · HDR10</div>
            </div>
            <div class="detail-card">
              <div class="detail-label">Network</div>
              <div class="detail-val purple">APEX+</div>
            </div>
          </div>

          <!-- GENRES -->
          <div class="genres-row">
            <span class="genre-pill">🚀 Sci-Fi</span>
            <span class="genre-pill">🌌 Space Opera</span>
            <span class="genre-pill">🤖 AI / Android</span>
            <span class="genre-pill">🎭 Drama</span>
            <span class="genre-pill">🧠 Thriller</span>
            <span class="genre-pill">🌑 Mystery</span>
            <span class="genre-pill">⚡ Action</span>
          </div>

          <!-- NOTIFY BANNER -->
          <div class="notify-banner">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
            <div class="notify-banner-text"><strong>Season 3</strong> is currently airing — new episodes every Friday. <strong>E8 drops in 3 days.</strong></div>
            <button class="notify-btn" onclick="showToast('🔔 Notifications enabled for Stellar Drift!')">Notify Me</button>
          </div>

          <!-- INLINE AD -->
          <div class="inline-ad-wrap">
            <div class="ad-box ad-728">
              <span class="ad-label">Advertisement</span>
              <span class="ad-copy">✦ DUMMY MID-PAGE AD — 728×90 ✦</span>
              <span class="ad-sub">Placeholder / Demo Placement</span>
            </div>
          </div>

          <!-- TABS -->
          <div class="content-tabs">
            <button class="ctab active" onclick="switchTab('episodes',this)">📺 Episodes</button>
            <button class="ctab" onclick="switchTab('cast',this)">🎭 Cast &amp; Crew</button>
            <button class="ctab" onclick="switchTab('details',this)">ℹ️ Details</button>
            <button class="ctab" onclick="switchTab('comments',this)">💬 Comments (2.1k)</button>
          </div>

          <!-- TAB: EPISODES -->
          <div class="tab-panel active" id="tab-episodes">
            <div class="ep-list-controls">
              <button class="ep-sort-btn active" onclick="showToast('Sorted: Oldest First')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="12" x2="15" y2="12"></line><line x1="3" y1="18" x2="9" y2="18"></line></svg>
                Asc
              </button>
              <button class="ep-sort-btn" onclick="showToast('Sorted: Newest First')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="9" y2="6"></line><line x1="3" y1="12" x2="15" y2="12"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                Desc
              </button>
              <input type="text" class="ep-search" placeholder="Search episode…">
              <span class="ep-count-badge">10 episodes · Season 2</span>
            </div>
            <div class="ep-list">

              <!-- E1 - Watched -->
              <div class="ep-row watched" onclick="selectEpisode(1)">
                <div class="ep-thumb c2">
                  <div class="ep-thumb-ph">S2 E1</div>
                  <div class="ep-play-hover"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg></div>
                  <span class="ep-num-badge">E1</span>
                  <span class="ep-duration-badge">44m</span>
                  <div class="ep-thumb-progress"><div class="ep-thumb-progress-fill" style="width:100%"></div></div>
                </div>
                <div class="ep-info">
                  <div class="ep-meta-top"><span class="ep-num-label">Episode 1</span><span class="ep-airdate">Jan 12, 2024</span></div>
                  <div class="ep-title-text">Fractured Horizon</div>
                  <div class="ep-desc-text">After the mutiny, Commander Nyx wakes aboard the Argo's secondary bay to find half the crew missing. With navigation offline, the journey to find them begins.</div>
                </div>
                <div class="ep-row-right">
                  <div class="ep-watched-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></div>
                  <div class="ep-score"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>9.2</div>
                </div>
              </div>

              <!-- E2 -->
              <div class="ep-row watched" onclick="selectEpisode(2)">
                <div class="ep-thumb c3">
                  <div class="ep-thumb-ph">S2 E2</div>
                  <div class="ep-play-hover"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg></div>
                  <span class="ep-num-badge">E2</span>
                  <span class="ep-duration-badge">41m</span>
                  <div class="ep-thumb-progress"><div class="ep-thumb-progress-fill" style="width:100%"></div></div>
                </div>
                <div class="ep-info">
                  <div class="ep-meta-top"><span class="ep-num-label">Episode 2</span><span class="ep-airdate">Jan 19, 2024</span></div>
                  <div class="ep-title-text">The Signal at Perihelion</div>
                  <div class="ep-desc-text">A distress beacon from a derelict vessel forces the crew to choose between rescue and survival, as ARIA reveals a hidden sub-routine she's been running for 40 years.</div>
                </div>
                <div class="ep-row-right">
                  <div class="ep-watched-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></div>
                  <div class="ep-score"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>8.8</div>
                </div>
              </div>

              <!-- E3 -->
              <div class="ep-row watched" onclick="selectEpisode(3)">
                <div class="ep-thumb c4">
                  <div class="ep-thumb-ph">S2 E3</div>
                  <div class="ep-play-hover"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg></div>
                  <span class="ep-num-badge">E3</span>
                  <span class="ep-duration-badge">49m</span>
                  <div class="ep-thumb-progress"><div class="ep-thumb-progress-fill" style="width:100%"></div></div>
                </div>
                <div class="ep-info">
                  <div class="ep-meta-top"><span class="ep-num-label">Episode 3</span><span class="ep-airdate">Jan 26, 2024</span></div>
                  <div class="ep-title-text">Gravity Well</div>
                  <div class="ep-desc-text">Trapped in the gravitational pull of a brown dwarf, the Argo must burn its last reserves — or leave three crew members stranded in the void forever.</div>
                </div>
                <div class="ep-row-right">
                  <div class="ep-watched-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></div>
                  <div class="ep-score"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>9.0</div>
                </div>
              </div>

              <!-- E4 -->
              <div class="ep-row watched" onclick="selectEpisode(4)">
                <div class="ep-thumb c5">
                  <div class="ep-thumb-ph">S2 E4</div>
                  <div class="ep-play-hover"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg></div>
                  <span class="ep-num-badge">E4</span>
                  <span class="ep-duration-badge">52m</span>
                  <div class="ep-thumb-progress"><div class="ep-thumb-progress-fill" style="width:100%"></div></div>
                </div>
                <div class="ep-info">
                  <div class="ep-meta-top"><span class="ep-num-label">Episode 4</span><span class="ep-airdate">Feb 02, 2024</span></div>
                  <div class="ep-title-text">Cold Equations</div>
                  <div class="ep-desc-text">The crew discovers the colony ship was never lost — it was hidden. Someone on board has known the destination all along and has been lying for years.</div>
                </div>
                <div class="ep-row-right">
                  <div class="ep-watched-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></div>
                  <div class="ep-score"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>9.4</div>
                </div>
              </div>

              <!-- E5 -->
              <div class="ep-row watched" onclick="selectEpisode(5)">
                <div class="ep-thumb c6">
                  <div class="ep-thumb-ph">S2 E5</div>
                  <div class="ep-play-hover"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg></div>
                  <span class="ep-num-badge">E5</span>
                  <span class="ep-duration-badge">46m</span>
                  <div class="ep-thumb-progress"><div class="ep-thumb-progress-fill" style="width:100%"></div></div>
                </div>
                <div class="ep-info">
                  <div class="ep-meta-top"><span class="ep-num-label">Episode 5</span><span class="ep-airdate">Feb 09, 2024</span></div>
                  <div class="ep-title-text">Red Dwarf Protocol</div>
                  <div class="ep-desc-text">ARIA crosses the line the crew feared most. Dr. Sarev must choose whether to shut her down or trust her judgment — and what it would mean to do either.</div>
                </div>
                <div class="ep-row-right">
                  <div class="ep-watched-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></div>
                  <div class="ep-score"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>9.1</div>
                </div>
              </div>

              <!-- E6 -->
              <div class="ep-row watched" onclick="selectEpisode(6)">
                <div class="ep-thumb c7">
                  <div class="ep-thumb-ph">S2 E6</div>
                  <div class="ep-play-hover"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg></div>
                  <span class="ep-num-badge">E6</span>
                  <span class="ep-duration-badge">51m</span>
                  <div class="ep-thumb-progress"><div class="ep-thumb-progress-fill" style="width:100%"></div></div>
                </div>
                <div class="ep-info">
                  <div class="ep-meta-top"><span class="ep-num-label">Episode 6</span><span class="ep-airdate">Feb 16, 2024</span></div>
                  <div class="ep-title-text">Phantom Payload</div>
                  <div class="ep-desc-text">A hidden cargo bay — sealed since launch — is finally breached. What's inside will permanently divide the crew and set the Argo on an irreversible course.</div>
                </div>
                <div class="ep-row-right">
                  <div class="ep-watched-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></div>
                  <div class="ep-score"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>9.3</div>
                </div>
              </div>

              <!-- E7 - CURRENT -->
              <div class="ep-row current" onclick="selectEpisode(7)">
                <div class="ep-thumb c1">
                  <div class="ep-thumb-ph">S2 E7</div>
                  <div class="ep-play-hover"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg></div>
                  <span class="ep-num-badge">E7</span>
                  <span class="ep-duration-badge">47m</span>
                  <div class="ep-thumb-progress"><div class="ep-thumb-progress-fill" style="width:58%"></div></div>
                </div>
                <div class="ep-info">
                  <div class="ep-meta-top"><span class="ep-num-label" style="color:var(--accent);">▶ NOW PLAYING</span><span class="ep-airdate">Mar 01, 2024</span></div>
                  <div class="ep-title-text">"The Void Between Stars"</div>
                  <div class="ep-desc-text">Nyx faces a choice no commander should ever have to make. With ARIA silent and the crew fractured, she must confront what they left behind — and whether it ever let them go.</div>
                </div>
                <div class="ep-row-right">
                  <div style="font-size:10px;font-weight:700;color:var(--cyan);">58%</div>
                  <div class="ep-score"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>9.7</div>
                </div>
              </div>

              <!-- E8 -->
              <div class="ep-row" onclick="selectEpisode(8)">
                <div class="ep-thumb c2">
                  <div class="ep-thumb-ph">S2 E8</div>
                  <div class="ep-play-hover"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg></div>
                  <span class="ep-num-badge">E8</span>
                  <span class="ep-duration-badge">44m</span>
                </div>
                <div class="ep-info">
                  <div class="ep-meta-top"><span class="ep-num-label">Episode 8</span><span class="ep-airdate">Mar 08, 2024</span></div>
                  <div class="ep-title-text">Echoes of the Singularity</div>
                  <div class="ep-desc-text">The Argo receives a transmission from the future — or something pretending to be. ARIA's cryptic response leaves the crew questioning everything they thought they knew about time.</div>
                </div>
                <div class="ep-row-right">
                  <div class="ep-score"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>9.0</div>
                </div>
              </div>

              <!-- E9 -->
              <div class="ep-row" onclick="selectEpisode(9)">
                <div class="ep-thumb c8">
                  <div class="ep-thumb-ph">S2 E9</div>
                  <div class="ep-play-hover"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg></div>
                  <span class="ep-num-badge">E9</span>
                  <span class="ep-duration-badge">55m</span>
                </div>
                <div class="ep-info">
                  <div class="ep-meta-top"><span class="ep-num-label">Episode 9</span><span class="ep-airdate">Mar 15, 2024</span></div>
                  <div class="ep-title-text">Last Light at Andromeda</div>
                  <div class="ep-desc-text">The penultimate episode of Season 2 delivers the collision everyone feared — as two crew factions clash in the Argo's corridors, something outside begins to respond.</div>
                </div>
                <div class="ep-row-right">
                  <div class="ep-score"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>9.5</div>
                </div>
              </div>

              <!-- E10 -->
              <div class="ep-row" onclick="selectEpisode(10)">
                <div class="ep-thumb c3">
                  <div class="ep-thumb-ph">S2 E10</div>
                  <div class="ep-play-hover"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg></div>
                  <span class="ep-num-badge">E10</span>
                  <span class="ep-duration-badge">61m</span>
                </div>
                <div class="ep-info">
                  <div class="ep-meta-top"><span class="ep-num-label">Episode 10 — Finale</span><span class="ep-airdate">Mar 22, 2024</span></div>
                  <div class="ep-title-text">Event Horizon</div>
                  <div class="ep-desc-text">The Season 2 finale. Everything converges. No one is safe. The Argo finally arrives — but arrival was never the destination. A jaw-dropping cliffhanger sets up Season 3.</div>
                </div>
                <div class="ep-row-right">
                  <div class="ep-score"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>9.8</div>
                </div>
              </div>

            </div>
          </div><!-- /tab-episodes -->

          <!-- TAB: CAST -->
          <div class="tab-panel" id="tab-cast">
            <div class="sec-mini-head" style="margin-bottom:20px;">
              <div class="sec-mini-title">
                <div class="icon-wrap"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg></div>
                MAIN <span class="accent">CAST</span>
              </div>
              <a href="#" class="see-all" onclick="showToast('Full cast list')">Full Credits <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg></a>
            </div>
            <div class="cast-row" style="margin-bottom:28px;">
              <div class="cast-card" onclick="showToast('Zara Nyx — Commander')">
                <div class="cast-avatar ca1">ZN</div>
                <div class="cast-name">Lyra Voss</div>
                <div class="cast-role">Cmdr. Yara Nyx</div>
              </div>
              <div class="cast-card" onclick="showToast('Dr. Sarev profile')">
                <div class="cast-avatar ca2">DS</div>
                <div class="cast-name">Karan Mehta</div>
                <div class="cast-role">Dr. Sarev</div>
              </div>
              <div class="cast-card" onclick="showToast('ARIA profile')">
                <div class="cast-avatar ca3">AR</div>
                <div class="cast-name">Sena Okafor</div>
                <div class="cast-role">ARIA (Voice)</div>
              </div>
              <div class="cast-card" onclick="showToast('Pilot Dax profile')">
                <div class="cast-avatar ca4">DX</div>
                <div class="cast-name">Tomas Brek</div>
                <div class="cast-role">Pilot Dax</div>
              </div>
              <div class="cast-card" onclick="showToast('Col. Wren profile')">
                <div class="cast-avatar ca5">CW</div>
                <div class="cast-name">Isla Crane</div>
                <div class="cast-role">Col. Wren</div>
              </div>
              <div class="cast-card" onclick="showToast('Engineer Rho profile')">
                <div class="cast-avatar ca6">ER</div>
                <div class="cast-name">Finn Aldric</div>
                <div class="cast-role">Engineer Rho</div>
              </div>
              <div class="cast-card" onclick="showToast('Medic Sola profile')">
                <div class="cast-avatar ca2">MS</div>
                <div class="cast-name">Anya Petrov</div>
                <div class="cast-role">Medic Sola</div>
              </div>
            </div>
            <div class="sec-mini-head" style="margin-bottom:16px;"><div class="sec-mini-title"><div class="icon-wrap"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polygon points="10 8 16 12 10 16 10 8"></polygon></svg></div>CREW</div></div>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;margin-bottom:28px;">
              <div class="detail-card"><div class="detail-label">Creator</div><div class="detail-val" style="font-size:13px;">Marcus Zel</div></div>
              <div class="detail-card"><div class="detail-label">Showrunner</div><div class="detail-val" style="font-size:13px;">Ana Fontaine</div></div>
              <div class="detail-card"><div class="detail-label">Director (S2)</div><div class="detail-val" style="font-size:13px;">Hiroshi Naka</div></div>
              <div class="detail-card"><div class="detail-label">Music</div><div class="detail-val" style="font-size:13px;">Elara Quinn</div></div>
              <div class="detail-card"><div class="detail-label">VFX Supervisor</div><div class="detail-val" style="font-size:13px;">Devon Cole</div></div>
              <div class="detail-card"><div class="detail-label">Cinematography</div><div class="detail-val" style="font-size:13px;">Yuki Hara</div></div>
            </div>
          </div>

          <!-- TAB: DETAILS -->
          <div class="tab-panel" id="tab-details">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px;">
              <div>
                <div style="font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--muted);margin-bottom:8px;">Episode Synopsis</div>
                <div style="font-size:13px;color:var(--muted2);line-height:1.7;">
                  Commander Nyx, alone on the observation deck, confronts the transmission she has been hiding from the crew for three episodes. Meanwhile, ARIA's silence is broken by a single line of code — one that Dr. Sarev recognizes as his own handwriting from 22 years ago. The episode explores the nature of consciousness, memory, and what it means to carry the weight of humanity's last hope across the infinite dark.
                </div>
              </div>
              <div>
                <div style="font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--muted);margin-bottom:8px;">Production Notes</div>
                <div style="font-size:13px;color:var(--muted2);line-height:1.7;">
                  Shot over 18 days on a practical set in Reykjavik, this episode features the longest unbroken take in the series — a 9-minute walk-and-talk across the Argo's bridge. Director Hiroshi Naka described it as "a letter to silence." The score, by Elara Quinn, uses a modified theremin recorded inside a decompression chamber.
                </div>
              </div>
            </div>
            <div style="font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--muted);margin-bottom:12px;">Content Warnings</div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:24px;">
              <span class="genre-pill">⚠️ Themes of loss</span>
              <span class="genre-pill">🧠 Psychological tension</span>
              <span class="genre-pill">🌑 Depiction of isolation</span>
            </div>
          </div>

          <!-- TAB: COMMENTS -->
          <div class="tab-panel" id="tab-comments">
            <div class="comment-input-row">
              <div class="ci-avatar">V</div>
              <textarea class="ci-box" placeholder="Share your thoughts on this episode… (No spoilers please!)" rows="1"></textarea>
            </div>
            <div class="comment-list">
              <div class="comment">
                <div class="c-avatar ca1">SK</div>
                <div class="c-body">
                  <div class="c-header">
                    <span class="c-name">StarKid_99</span>
                    <span class="c-time">2 hours ago</span>
                    <span class="c-badge">TOP FAN</span>
                    <span class="ep-spoiler-tag">SPOILER</span>
                  </div>
                  <div class="c-text">The 9-minute continuous shot on the bridge was absolutely breathtaking. When Nyx finally reads the transmission out loud — I had to pause the episode. That single moment recontextualizes everything from Season 1.</div>
                  <div class="c-actions">
                    <span class="c-action liked"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>2.4k</span>
                    <span class="c-action"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>Reply</span>
                    <span class="c-action"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>Share</span>
                  </div>
                </div>
              </div>
              <div class="comment">
                <div class="c-avatar ca3">NR</div>
                <div class="c-body">
                  <div class="c-header"><span class="c-name">NebulaRider</span><span class="c-time">5 hours ago</span></div>
                  <div class="c-text">ARIA's single line of code reveal has to be the best mystery setup in TV this year. The implications alone kept me up at 2AM writing theories. Show of the decade, full stop.</div>
                  <div class="c-actions">
                    <span class="c-action"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>891</span>
                    <span class="c-action"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>Reply</span>
                  </div>
                </div>
              </div>
              <div class="comment">
                <div class="c-avatar ca5">VX</div>
                <div class="c-body">
                  <div class="c-header"><span class="c-name">VoidExplorer</span><span class="c-time">8 hours ago</span></div>
                  <div class="c-text">The score in this episode deserves its own award. Using a theremin inside a decompression chamber — you can literally feel the pressure drop every time a scene cuts to the exterior. Goosebumps.</div>
                  <div class="c-actions">
                    <span class="c-action"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>567</span>
                    <span class="c-action"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>Reply</span>
                  </div>
                </div>
              </div>
              <div class="comment">
                <div class="c-avatar ca2">DR</div>
                <div class="c-body">
                  <div class="c-header"><span class="c-name">DriftReviews</span><span class="c-time">1 day ago</span><span class="c-badge">CRITIC</span></div>
                  <div class="c-text">Hiroshi Naka directs this one with the confidence of someone who knows exactly what they have. Every frame feels deliberate. Easily the standout episode of the entire series so far. 10/10.</div>
                  <div class="c-actions">
                    <span class="c-action"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>1.1k</span>
                    <span class="c-action"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>Reply</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- RATING WIDGET -->
          <div class="rating-widget" style="margin-top:28px;">
            <div class="rating-score-big">
              <div class="rsb-num">9.7</div>
              <div class="rsb-stars">
                <svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                <svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                <svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                <svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                <svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
              </div>
              <div class="rsb-count">S2 E7 · 31,284 ratings</div>
            </div>
            <div class="rating-bars">
              <div class="rbar-row"><span class="rbar-label">5★</span><div class="rbar-track"><div class="rbar-fill" style="width:82%;"></div></div><span class="rbar-count">25.7k</span></div>
              <div class="rbar-row"><span class="rbar-label">4★</span><div class="rbar-track"><div class="rbar-fill" style="width:12%;"></div></div><span class="rbar-count">3.7k</span></div>
              <div class="rbar-row"><span class="rbar-label">3★</span><div class="rbar-track"><div class="rbar-fill" style="width:4%;"></div></div><span class="rbar-count">1.2k</span></div>
              <div class="rbar-row"><span class="rbar-label">2★</span><div class="rbar-track"><div class="rbar-fill" style="width:1%;"></div></div><span class="rbar-count">0.4k</span></div>
              <div class="rbar-row"><span class="rbar-label">1★</span><div class="rbar-track"><div class="rbar-fill" style="width:0.5%;"></div></div><span class="rbar-count">0.2k</span></div>
            </div>
            <div class="rating-user-wrap">
              <div class="rating-user-label">Rate this episode</div>
              <div class="user-stars">
                <div class="user-star" onclick="rateEp(1)"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg></div>
                <div class="user-star" onclick="rateEp(2)"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg></div>
                <div class="user-star" onclick="rateEp(3)"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg></div>
                <div class="user-star" onclick="rateEp(4)"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg></div>
                <div class="user-star" onclick="rateEp(5)"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg></div>
              </div>
            </div>
          </div>

          <!-- RELATED SHOWS -->
          <div class="related-section">
            <div class="sec-mini-head">
              <div class="sec-mini-title">
                <div class="icon-wrap"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="15" rx="2"></rect><polyline points="17 2 12 7 7 2"></polyline></svg></div>
                YOU MAY ALSO <span class="accent">LIKE</span>
              </div>
              <a href="#" class="see-all" onclick="showToast('Browse all shows')">Browse All <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg></a>
            </div>
            <div class="related-grid">
              <div class="rcard" onclick="showToast('Opening: Arc of Silence')">
                <div class="rcard-thumb c2">
                  <div class="rcard-ph">ARC OF SILENCE</div>
                  <div class="rcard-overlay"><div class="rcard-play-btn"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg></div></div>
                  <span class="rcard-badge badge-ongoing">Ongoing</span>
                  <span class="rcard-score"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>8.9</span>
                </div>
                <div class="rcard-title">Arc of Silence</div>
                <div class="rcard-meta"><span>2023</span><div class="rcard-dot"></div><span>Sci-Fi</span></div>
              </div>
              <div class="rcard" onclick="showToast('Opening: Pulsar Protocol')">
                <div class="rcard-thumb c3">
                  <div class="rcard-ph">PULSAR PROTOCOL</div>
                  <div class="rcard-overlay"><div class="rcard-play-btn"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg></div></div>
                  <span class="rcard-badge badge-hd">HD</span>
                  <span class="rcard-score"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>8.5</span>
                </div>
                <div class="rcard-title">Pulsar Protocol</div>
                <div class="rcard-meta"><span>2021</span><div class="rcard-dot"></div><span>Thriller</span></div>
              </div>
              <div class="rcard" onclick="showToast('Opening: Erebus Station')">
                <div class="rcard-thumb c4">
                  <div class="rcard-ph">EREBUS STATION</div>
                  <div class="rcard-overlay"><div class="rcard-play-btn"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg></div></div>
                  <span class="rcard-badge badge-new">NEW</span>
                  <span class="rcard-score"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>9.0</span>
                </div>
                <div class="rcard-title">Erebus Station</div>
                <div class="rcard-meta"><span>2024</span><div class="rcard-dot"></div><span>Horror</span></div>
              </div>
              <div class="rcard" onclick="showToast('Opening: Dark Matter Relay')">
                <div class="rcard-thumb c5">
                  <div class="rcard-ph">DARK MATTER RELAY</div>
                  <div class="rcard-overlay"><div class="rcard-play-btn"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg></div></div>
                  <span class="rcard-badge badge-hd">4K</span>
                  <span class="rcard-score"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>8.7</span>
                </div>
                <div class="rcard-title">Dark Matter Relay</div>
                <div class="rcard-meta"><span>2022</span><div class="rcard-dot"></div><span>Drama</span></div>
              </div>
              <div class="rcard" onclick="showToast('Opening: Lightspeed Accord')">
                <div class="rcard-thumb c7">
                  <div class="rcard-ph">LIGHTSPEED ACCORD</div>
                  <div class="rcard-overlay"><div class="rcard-play-btn"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg></div></div>
                  <span class="rcard-badge badge-ongoing">Ongoing</span>
                  <span class="rcard-score"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>8.3</span>
                </div>
                <div class="rcard-title">Lightspeed Accord</div>
                <div class="rcard-meta"><span>2023</span><div class="rcard-dot"></div><span>Sci-Fi</span></div>
              </div>
              <div class="rcard" onclick="showToast('Opening: The Kepler Divide')">
                <div class="rcard-thumb c8">
                  <div class="rcard-ph">THE KEPLER DIVIDE</div>
                  <div class="rcard-overlay"><div class="rcard-play-btn"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg></div></div>
                  <span class="rcard-badge badge-hd">HD</span>
                  <span class="rcard-score"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>8.8</span>
                </div>
                <div class="rcard-title">The Kepler Divide</div>
                <div class="rcard-meta"><span>2021</span><div class="rcard-dot"></div><span>Mystery</span></div>
              </div>
            </div>
          </div>

        </div>
      </div><!-- /container -->
    </div><!-- /watch-main -->

    <!-- ══ SIDEBAR ══ -->
    <div class="watch-sidebar">

      <!-- Sidebar Header -->
      <div class="sidebar-head">
        <div>
          <div class="sidebar-title">EPISODES</div>
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
          <span class="sidebar-season-badge">S2</span>
          <button class="sidebar-sort" onclick="showToast('Episodes list reversed')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="9" y2="6"></line><line x1="3" y1="12" x2="15" y2="12"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
            Sort
          </button>
        </div>
      </div>

      <!-- Continue Watching Banner -->
      <div class="continue-banner">
        <div class="continue-banner-icon">
          <svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
        </div>
        <div class="continue-banner-info">
          <div class="continue-banner-label">▶ Currently Watching</div>
          <div class="continue-banner-text">S2 E7 — 27:14 / 47:03</div>
          <div class="continue-banner-progress-bar">
            <div class="continue-banner-progress-fill"></div>
          </div>
        </div>
      </div>

      <!-- Sidebar Ad -->
      <div class="sidebar-ad-slot">
        <div class="ad-box ad-300">
          <span class="ad-label">Advertisement</span>
          <span class="ad-copy">✦ DUMMY SIDEBAR AD — 300×250 ✦</span>
          <span class="ad-sub">Placeholder / Demo Placement</span>
        </div>
      </div>

      <!-- Episode List in Sidebar -->
      <!-- E1 Watched -->
      <div class="ep-sidebar-card watched" onclick="selectEpisode(1)">
        <div class="esc-thumb c2">
          <div class="esc-ph">S2 E1</div>
          <div class="esc-play-icon"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg></div>
          <span class="esc-duration">44m</span>
          <div class="esc-ep-progress"><div class="esc-ep-progress-fill" style="width:100%"></div></div>
        </div>
        <div class="esc-info">
          <div class="esc-label">Episode 1 <span class="esc-watched-check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></span></div>
          <div class="esc-title">Fractured Horizon</div>
          <div class="esc-meta">Jan 12, 2024</div>
        </div>
        <div class="esc-score"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>9.2</div>
      </div>

      <div class="ep-sidebar-card watched" onclick="selectEpisode(2)">
        <div class="esc-thumb c3"><div class="esc-ph">S2 E2</div><div class="esc-play-icon"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg></div><span class="esc-duration">41m</span><div class="esc-ep-progress"><div class="esc-ep-progress-fill" style="width:100%"></div></div></div>
        <div class="esc-info"><div class="esc-label">Episode 2 <span class="esc-watched-check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></span></div><div class="esc-title">The Signal at Perihelion</div><div class="esc-meta">Jan 19, 2024</div></div>
        <div class="esc-score"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>8.8</div>
      </div>

      <div class="ep-sidebar-card watched" onclick="selectEpisode(3)">
        <div class="esc-thumb c4"><div class="esc-ph">S2 E3</div><div class="esc-play-icon"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg></div><span class="esc-duration">49m</span><div class="esc-ep-progress"><div class="esc-ep-progress-fill" style="width:100%"></div></div></div>
        <div class="esc-info"><div class="esc-label">Episode 3 <span class="esc-watched-check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></span></div><div class="esc-title">Gravity Well</div><div class="esc-meta">Jan 26, 2024</div></div>
        <div class="esc-score"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>9.0</div>
      </div>

      <div class="ep-sidebar-card watched" onclick="selectEpisode(4)">
        <div class="esc-thumb c5"><div class="esc-ph">S2 E4</div><div class="esc-play-icon"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg></div><span class="esc-duration">52m</span><div class="esc-ep-progress"><div class="esc-ep-progress-fill" style="width:100%"></div></div></div>
        <div class="esc-info"><div class="esc-label">Episode 4 <span class="esc-watched-check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></span></div><div class="esc-title">Cold Equations</div><div class="esc-meta">Feb 02, 2024</div></div>
        <div class="esc-score"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>9.4</div>
      </div>

      <div class="ep-sidebar-card watched" onclick="selectEpisode(5)">
        <div class="esc-thumb c6"><div class="esc-ph">S2 E5</div><div class="esc-play-icon"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg></div><span class="esc-duration">46m</span><div class="esc-ep-progress"><div class="esc-ep-progress-fill" style="width:100%"></div></div></div>
        <div class="esc-info"><div class="esc-label">Episode 5 <span class="esc-watched-check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></span></div><div class="esc-title">Red Dwarf Protocol</div><div class="esc-meta">Feb 09, 2024</div></div>
        <div class="esc-score"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>9.1</div>
      </div>

      <div class="ep-sidebar-card watched" onclick="selectEpisode(6)">
        <div class="esc-thumb c7"><div class="esc-ph">S2 E6</div><div class="esc-play-icon"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg></div><span class="esc-duration">51m</span><div class="esc-ep-progress"><div class="esc-ep-progress-fill" style="width:100%"></div></div></div>
        <div class="esc-info"><div class="esc-label">Episode 6 <span class="esc-watched-check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></span></div><div class="esc-title">Phantom Payload</div><div class="esc-meta">Feb 16, 2024</div></div>
        <div class="esc-score"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>9.3</div>
      </div>

      <!-- E7 CURRENT -->
      <div class="ep-sidebar-card current" onclick="selectEpisode(7)">
        <div class="esc-thumb c1"><div class="esc-ph">S2 E7</div><div class="esc-play-icon"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg></div><span class="esc-duration">47m</span><div class="esc-ep-progress"><div class="esc-ep-progress-fill" style="width:58%"></div></div></div>
        <div class="esc-info"><div class="esc-label" style="color:var(--cyan);">▶ NOW PLAYING</div><div class="esc-title">"The Void Between Stars"</div><div class="esc-meta">Mar 01, 2024 · 27:14 left</div></div>
        <div class="esc-score"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>9.7</div>
      </div>

      <div class="ep-sidebar-card" onclick="selectEpisode(8)">
        <div class="esc-thumb c2"><div class="esc-ph">S2 E8</div><div class="esc-play-icon"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg></div><span class="esc-duration">44m</span></div>
        <div class="esc-info"><div class="esc-label">Episode 8</div><div class="esc-title">Echoes of the Singularity</div><div class="esc-meta">Mar 08, 2024</div></div>
        <div class="esc-score"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>9.0</div>
      </div>

      <div class="ep-sidebar-card" onclick="selectEpisode(9)">
        <div class="esc-thumb c8"><div class="esc-ph">S2 E9</div><div class="esc-play-icon"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg></div><span class="esc-duration">55m</span></div>
        <div class="esc-info"><div class="esc-label">Episode 9</div><div class="esc-title">Last Light at Andromeda</div><div class="esc-meta">Mar 15, 2024</div></div>
        <div class="esc-score"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>9.5</div>
      </div>

      <div class="ep-sidebar-card" onclick="selectEpisode(10)">
        <div class="esc-thumb c3"><div class="esc-ph">S2 E10</div><div class="esc-play-icon"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg></div><span class="esc-duration">61m</span></div>
        <div class="esc-info"><div class="esc-label">Episode 10 — Finale</div><div class="esc-title">Event Horizon</div><div class="esc-meta">Mar 22, 2024</div></div>
        <div class="esc-score"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>9.8</div>
      </div>

    </div><!-- /watch-sidebar -->

  </div><!-- /watch-layout -->
</div>

<?= $this->end() ?>
