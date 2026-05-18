 <!-- CONTENT AREA -->
      <div class="container">
        <div class="content-pad">

          <!-- BREADCRUMB -->
          <div class="breadcrumb">
            <a href="#">Home</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            <a href="#">TV Shows</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            <a href="#">Sci-Fi</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            <a href="#">Stellar Drift</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            <span>S2 E7</span>
          </div>

          <!-- SHOW HERO INFO -->
          <div class="show-info-wrap">
            <div class="show-poster c1">
              <div class="show-poster-badge">S3 ONGOING</div>
              STELLAR<br/>DRIFT
            </div>
            <div class="show-details">
              <div class="show-type-pill">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="15" rx="2"/><polyline points="17 2 12 7 7 2"/></svg>
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
                  <svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
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
                  <svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                  Resume S2 E7
                </button>
                <button class="btn-secondary" onclick="showToast('Added to Watchlist ✓')">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                  Watchlist
                </button>
                <button class="btn-icon liked" id="likeBtn" onclick="toggleLike()">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                </button>
                <button class="btn-icon" onclick="showToast('Link copied to clipboard!')">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                </button>
                <button class="btn-icon" onclick="showToast('Download started!')">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
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
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
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
            <button class="ctab" onclick="switchTab('cast',this)">🎭 Cast & Crew</button>
            <button class="ctab" onclick="switchTab('details',this)">ℹ️ Details</button>
            <button class="ctab" onclick="switchTab('comments',this)">💬 Comments (2.1k)</button>
          </div>

          <!-- TAB: EPISODES -->
          <div class="tab-panel active" id="tab-episodes">
            <div class="ep-list-controls">
              <button class="ep-sort-btn active" onclick="showToast('Sorted: Oldest First')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="15" y2="12"/><line x1="3" y1="18" x2="9" y2="18"/></svg>
                Asc
              </button>
              <button class="ep-sort-btn" onclick="showToast('Sorted: Newest First')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="9" y2="6"/><line x1="3" y1="12" x2="15" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                Desc
              </button>
              <input type="text" class="ep-search" placeholder="Search episode…"/>
              <span class="ep-count-badge">10 episodes · Season 2</span>
            </div>
            <div class="ep-list">

              <!-- E1 - Watched -->
              <div class="ep-row watched" onclick="selectEpisode(1)">
                <div class="ep-thumb c2">
                  <div class="ep-thumb-ph">S2 E1</div>
                  <div class="ep-play-hover"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg></div>
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
                  <div class="ep-watched-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></div>
                  <div class="ep-score"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>9.2</div>
                </div>
              </div>

              <!-- E2 -->
              <div class="ep-row watched" onclick="selectEpisode(2)">
                <div class="ep-thumb c3">
                  <div class="ep-thumb-ph">S2 E2</div>
                  <div class="ep-play-hover"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg></div>
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
                  <div class="ep-watched-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></div>
                  <div class="ep-score"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>8.8</div>
                </div>
              </div>

              <!-- E3 -->
              <div class="ep-row watched" onclick="selectEpisode(3)">
                <div class="ep-thumb c4">
                  <div class="ep-thumb-ph">S2 E3</div>
                  <div class="ep-play-hover"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg></div>
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
                  <div class="ep-watched-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></div>
                  <div class="ep-score"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>9.0</div>
                </div>
              </div>

              <!-- E4 -->
              <div class="ep-row watched" onclick="selectEpisode(4)">
                <div class="ep-thumb c5">
                  <div class="ep-thumb-ph">S2 E4</div>
                  <div class="ep-play-hover"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg></div>
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
                  <div class="ep-watched-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></div>
                  <div class="ep-score"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>9.4</div>
                </div>
              </div>

              <!-- E5 -->
              <div class="ep-row watched" onclick="selectEpisode(5)">
                <div class="ep-thumb c6">
                  <div class="ep-thumb-ph">S2 E5</div>
                  <div class="ep-play-hover"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg></div>
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
                  <div class="ep-watched-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></div>
                  <div class="ep-score"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>9.1</div>
                </div>
              </div>

              <!-- E6 -->
              <div class="ep-row watched" onclick="selectEpisode(6)">
                <div class="ep-thumb c7">
                  <div class="ep-thumb-ph">S2 E6</div>
                  <div class="ep-play-hover"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg></div>
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
                  <div class="ep-watched-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></div>
                  <div class="ep-score"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>9.3</div>
                </div>
              </div>

              <!-- E7 - CURRENT -->
              <div class="ep-row current" onclick="selectEpisode(7)">
                <div class="ep-thumb c1">
                  <div class="ep-thumb-ph">S2 E7</div>
                  <div class="ep-play-hover"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg></div>
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
                  <div class="ep-score"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>9.7</div>
                </div>
              </div>

              <!-- E8 -->
              <div class="ep-row" onclick="selectEpisode(8)">
                <div class="ep-thumb c2">
                  <div class="ep-thumb-ph">S2 E8</div>
                  <div class="ep-play-hover"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg></div>
                  <span class="ep-num-badge">E8</span>
                  <span class="ep-duration-badge">44m</span>
                </div>
                <div class="ep-info">
                  <div class="ep-meta-top"><span class="ep-num-label">Episode 8</span><span class="ep-airdate">Mar 08, 2024</span></div>
                  <div class="ep-title-text">Echoes of the Singularity</div>
                  <div class="ep-desc-text">The Argo receives a transmission from the future — or something pretending to be. ARIA's cryptic response leaves the crew questioning everything they thought they knew about time.</div>
                </div>
                <div class="ep-row-right">
                  <div class="ep-score"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>9.0</div>
                </div>
              </div>

              <!-- E9 -->
              <div class="ep-row" onclick="selectEpisode(9)">
                <div class="ep-thumb c8">
                  <div class="ep-thumb-ph">S2 E9</div>
                  <div class="ep-play-hover"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg></div>
                  <span class="ep-num-badge">E9</span>
                  <span class="ep-duration-badge">55m</span>
                </div>
                <div class="ep-info">
                  <div class="ep-meta-top"><span class="ep-num-label">Episode 9</span><span class="ep-airdate">Mar 15, 2024</span></div>
                  <div class="ep-title-text">Last Light at Andromeda</div>
                  <div class="ep-desc-text">The penultimate episode of Season 2 delivers the collision everyone feared — as two crew factions clash in the Argo's corridors, something outside begins to respond.</div>
                </div>
                <div class="ep-row-right">
                  <div class="ep-score"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>9.5</div>
                </div>
              </div>

              <!-- E10 -->
              <div class="ep-row" onclick="selectEpisode(10)">
                <div class="ep-thumb c3">
                  <div class="ep-thumb-ph">S2 E10</div>
                  <div class="ep-play-hover"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg></div>
                  <span class="ep-num-badge">E10</span>
                  <span class="ep-duration-badge">61m</span>
                </div>
                <div class="ep-info">
                  <div class="ep-meta-top"><span class="ep-num-label">Episode 10 — Finale</span><span class="ep-airdate">Mar 22, 2024</span></div>
                  <div class="ep-title-text">Event Horizon</div>
                  <div class="ep-desc-text">The Season 2 finale. Everything converges. No one is safe. The Argo finally arrives — but arrival was never the destination. A jaw-dropping cliffhanger sets up Season 3.</div>
                </div>
                <div class="ep-row-right">
                  <div class="ep-score"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>9.8</div>
                </div>
              </div>

            </div>
          </div><!-- /tab-episodes -->

          <!-- TAB: CAST -->
          <div class="tab-panel" id="tab-cast">
            <div class="sec-mini-head" style="margin-bottom:20px;">
              <div class="sec-mini-title">
                <div class="icon-wrap"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
                MAIN <span class="accent">CAST</span>
              </div>
              <a href="#" class="see-all" onclick="showToast('Full cast list')">Full Credits <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg></a>
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
            <div class="sec-mini-head" style="margin-bottom:16px;"><div class="sec-mini-title"><div class="icon-wrap"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8"/></svg></div>CREW</div></div>
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
                    <span class="c-action liked"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>2.4k</span>
                    <span class="c-action"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>Reply</span>
                    <span class="c-action"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>Share</span>
                  </div>
                </div>
              </div>
              <div class="comment">
                <div class="c-avatar ca3">NR</div>
                <div class="c-body">
                  <div class="c-header"><span class="c-name">NebulaRider</span><span class="c-time">5 hours ago</span></div>
                  <div class="c-text">ARIA's single line of code reveal has to be the best mystery setup in TV this year. The implications alone kept me up at 2AM writing theories. Show of the decade, full stop.</div>
                  <div class="c-actions">
                    <span class="c-action"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>891</span>
                    <span class="c-action"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>Reply</span>
                  </div>
                </div>
              </div>
              <div class="comment">
                <div class="c-avatar ca5">VX</div>
                <div class="c-body">
                  <div class="c-header"><span class="c-name">VoidExplorer</span><span class="c-time">8 hours ago</span></div>
                  <div class="c-text">The score in this episode deserves its own award. Using a theremin inside a decompression chamber — you can literally feel the pressure drop every time a scene cuts to the exterior. Goosebumps.</div>
                  <div class="c-actions">
                    <span class="c-action"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>567</span>
                    <span class="c-action"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>Reply</span>
                  </div>
                </div>
              </div>
              <div class="comment">
                <div class="c-avatar ca2">DR</div>
                <div class="c-body">
                  <div class="c-header"><span class="c-name">DriftReviews</span><span class="c-time">1 day ago</span><span class="c-badge">CRITIC</span></div>
                  <div class="c-text">Hiroshi Naka directs this one with the confidence of someone who knows exactly what they have. Every frame feels deliberate. Easily the standout episode of the entire series so far. 10/10.</div>
                  <div class="c-actions">
                    <span class="c-action"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>1.1k</span>
                    <span class="c-action"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>Reply</span>
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
                <svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                <svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                <svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                <svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                <svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
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
                <div class="user-star" onclick="rateEp(1)"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></div>
                <div class="user-star" onclick="rateEp(2)"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></div>
                <div class="user-star" onclick="rateEp(3)"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></div>
                <div class="user-star" onclick="rateEp(4)"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></div>
                <div class="user-star" onclick="rateEp(5)"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></div>
              </div>
            </div>
          </div>

          <!-- RELATED SHOWS -->
          <div class="related-section">
            <div class="sec-mini-head">
              <div class="sec-mini-title">
                <div class="icon-wrap"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="15" rx="2"/><polyline points="17 2 12 7 7 2"/></svg></div>
                YOU MAY ALSO <span class="accent">LIKE</span>
              </div>
              <a href="#" class="see-all" onclick="showToast('Browse all shows')">Browse All <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg></a>
            </div>
            <div class="related-grid">
              <div class="rcard" onclick="showToast('Opening: Arc of Silence')">
                <div class="rcard-thumb c2">
                  <div class="rcard-ph">ARC OF SILENCE</div>
                  <div class="rcard-overlay"><div class="rcard-play-btn"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg></div></div>
                  <span class="rcard-badge badge-ongoing">Ongoing</span>
                  <span class="rcard-score"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>8.9</span>
                </div>
                <div class="rcard-title">Arc of Silence</div>
                <div class="rcard-meta"><span>2023</span><div class="rcard-dot"></div><span>Sci-Fi</span></div>
              </div>
              <div class="rcard" onclick="showToast('Opening: Pulsar Protocol')">
                <div class="rcard-thumb c3">
                  <div class="rcard-ph">PULSAR PROTOCOL</div>
                  <div class="rcard-overlay"><div class="rcard-play-btn"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg></div></div>
                  <span class="rcard-badge badge-hd">HD</span>
                  <span class="rcard-score"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>8.5</span>
                </div>
                <div class="rcard-title">Pulsar Protocol</div>
                <div class="rcard-meta"><span>2021</span><div class="rcard-dot"></div><span>Thriller</span></div>
              </div>
              <div class="rcard" onclick="showToast('Opening: Erebus Station')">
                <div class="rcard-thumb c4">
                  <div class="rcard-ph">EREBUS STATION</div>
                  <div class="rcard-overlay"><div class="rcard-play-btn"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg></div></div>
                  <span class="rcard-badge badge-new">NEW</span>
                  <span class="rcard-score"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>9.0</span>
                </div>
                <div class="rcard-title">Erebus Station</div>
                <div class="rcard-meta"><span>2024</span><div class="rcard-dot"></div><span>Horror</span></div>
              </div>
              <div class="rcard" onclick="showToast('Opening: Dark Matter Relay')">
                <div class="rcard-thumb c5">
                  <div class="rcard-ph">DARK MATTER RELAY</div>
                  <div class="rcard-overlay"><div class="rcard-play-btn"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg></div></div>
                  <span class="rcard-badge badge-hd">4K</span>
                  <span class="rcard-score"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>8.7</span>
                </div>
                <div class="rcard-title">Dark Matter Relay</div>
                <div class="rcard-meta"><span>2022</span><div class="rcard-dot"></div><span>Drama</span></div>
              </div>
              <div class="rcard" onclick="showToast('Opening: Lightspeed Accord')">
                <div class="rcard-thumb c7">
                  <div class="rcard-ph">LIGHTSPEED ACCORD</div>
                  <div class="rcard-overlay"><div class="rcard-play-btn"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg></div></div>
                  <span class="rcard-badge badge-ongoing">Ongoing</span>
                  <span class="rcard-score"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>8.3</span>
                </div>
                <div class="rcard-title">Lightspeed Accord</div>
                <div class="rcard-meta"><span>2023</span><div class="rcard-dot"></div><span>Sci-Fi</span></div>
              </div>
              <div class="rcard" onclick="showToast('Opening: The Kepler Divide')">
                <div class="rcard-thumb c8">
                  <div class="rcard-ph">THE KEPLER DIVIDE</div>
                  <div class="rcard-overlay"><div class="rcard-play-btn"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg></div></div>
                  <span class="rcard-badge badge-hd">HD</span>
                  <span class="rcard-score"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>8.8</span>
                </div>
                <div class="rcard-title">The Kepler Divide</div>
                <div class="rcard-meta"><span>2021</span><div class="rcard-dot"></div><span>Mystery</span></div>
              </div>
            </div>
          </div>
