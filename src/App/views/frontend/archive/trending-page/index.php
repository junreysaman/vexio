<?= $this->start("styles") ?>
<link rel="stylesheet" href="/assets/frontend/css/trending-page.css">
<?= $this->end() ?>

<?= $this->start('content') ?>

<?= $this->includePartial('frontend/archive/trending-page/ad/trending-interstitial') ?>
<?= $this->includePartial('frontend/archive/trending-page/ad/trending-mobile-sticky') ?>

<?= $this->includePartial('frontend/archive/trending-page/components/trending-hero') ?>

<!-- ═══ MAIN CONTENT ══════════════════════════════════ -->
<section id="trend-main">
  <div class="container">

    <!-- Stats -->
    <div class="stats-banner">
      <div class="stat-item">
        <div class="stat-number">2.4M</div>
        <div class="stat-label">Views Today</div>
        <div class="stat-change up">▲ 18% vs yesterday</div>
      </div>
      <div class="stat-item">
        <div class="stat-number" style="background:linear-gradient(135deg,var(--cyan),var(--purple));-webkit-background-clip:text;-webkit-text-fill-color:transparent;">348</div>
        <div class="stat-label">Rising Titles</div>
        <div class="stat-change up">▲ 24 new today</div>
      </div>
      <div class="stat-item">
        <div class="stat-number" style="background:linear-gradient(135deg,var(--green),#0ea5e9);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">9.1</div>
        <div class="stat-label">Avg. Rating</div>
        <div class="stat-change up">▲ 0.2 this week</div>
      </div>
      <div class="stat-item">
        <div class="stat-number" style="background:linear-gradient(135deg,var(--accent2),var(--purple));-webkit-background-clip:text;-webkit-text-fill-color:transparent;">67K</div>
        <div class="stat-label">Active Watchers</div>
        <div class="stat-change up">▲ All-time high</div>
      </div>
    </div>

    <!-- ══ AD-1 · LEADERBOARD 728×90 ════════════════════
         Placement: Above the fold, below stats, max visibility
         IAB Standard: Leaderboard / Responsive Banner
    ════════════════════════════════════════════════════ -->
    <div class="ad-unit ad-leaderboard">
      <span class="ad-label">Advertisement</span>
      <div class="ad-creative" onclick="showToast('Opening Funimation…')">
        <button class="ad-close" onclick="event.stopPropagation();this.closest('.ad-leaderboard').style.display='none'">×</button>
        <div class="ad-lb-img"></div>
        <div class="ad-lb-divider"></div>
        <div class="ad-lb-logo">FUNI</div>
        <div class="ad-lb-copy">
          <div class="ad-lb-headline">Funimation — Stream Every Dub Ever Made</div>
          <div class="ad-lb-sub">New simulcasts weekly · <em>1 month FREE</em> with code VEXIO · No credit card needed</div>
        </div>
        <button class="ad-lb-cta">Claim Offer →</button>
      </div>
    </div>

    <!-- ══ #1 SPOTLIGHT ══════════════════════════════ -->
    <div class="trend-section">
      <div class="section-header">
        <div class="section-dot"></div>
        <div class="section-title">🏆 #1 Spotlight — Most Watched</div>
        <div class="section-line"></div>
      </div>

      <div class="spotlight-wrap">
        <!-- Main spotlight card -->
        <div class="spotlight-card" onclick="showToast('Opening Demon Slayer…')">
          <div class="sp-bg" style="background-image:url('https://images.unsplash.com/photo-1578632767115-351597cf2477?w=1200&q=80');"></div>
          <div class="sp-gradient"></div>
          <div class="sp-rank">#1</div>
          <div class="sp-content">
            <div class="sp-badges">
              <span class="sp-badge fire">🔥 #1 Today</span>
              <span class="sp-badge rank">⚔️ Action</span>
              <span class="sp-badge new">New Ep ✦ S4E12</span>
            </div>
            <div class="sp-title">Demon Slayer</div>
            <div class="sp-meta">
              <span class="hi">★ 9.8</span>
              <span class="dot">•</span>
              <span>2024</span>
              <span class="dot">•</span>
              <span>4 Seasons</span>
              <span class="dot">•</span>
              <span>SUB + DUB</span>
              <span class="dot">•</span>
              <span class="hi">1.2M views today</span>
            </div>
            <p class="sp-desc">A young boy becomes a demon slayer to avenge his family and cure his sister. Breathtaking animation meets emotional storytelling in one of anime's greatest epics.</p>
            <div class="sp-actions">
              <button class="sp-btn-play" onclick="event.stopPropagation();showToast('Playing now…')">
                <svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                Play Now
              </button>
              <button class="sp-btn-add" onclick="event.stopPropagation();showToast('Added to Watchlist!')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
                Watchlist
              </button>
            </div>
          </div>
        </div>

        <!-- Sidebar #2–5 -->
        <div class="spotlight-sidebar">
          <div class="sidebar-card" onclick="showToast('Opening Attack on Titan…')">
            <div class="sc-bg" style="background-image:url('https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=600&q=80');"></div>
            <div class="sc-grad"></div>
            <div class="sc-accent"></div>
            <div class="sc-rank-num">#2</div>
            <div class="sc-content">
              <div class="sc-title">Attack on Titan</div>
              <div class="sc-meta"><strong>★ 9.7</strong> &nbsp;•&nbsp; 847K views</div>
            </div>
          </div>
          <div class="sidebar-card" onclick="showToast('Opening One Piece…')">
            <div class="sc-bg" style="background-image:url('https://images.unsplash.com/photo-1534796636912-3b95b3ab5986?w=600&q=80');"></div>
            <div class="sc-grad"></div>
            <div class="sc-accent"></div>
            <div class="sc-rank-num">#3</div>
            <div class="sc-content">
              <div class="sc-title">One Piece</div>
              <div class="sc-meta"><strong>★ 9.5</strong> &nbsp;•&nbsp; 712K views</div>
            </div>
          </div>
          <div class="sidebar-card" onclick="showToast('Opening Jujutsu Kaisen…')">
            <div class="sc-bg" style="background-image:url('https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=600&q=80');"></div>
            <div class="sc-grad"></div>
            <div class="sc-accent"></div>
            <div class="sc-rank-num">#4</div>
            <div class="sc-content">
              <div class="sc-title">Jujutsu Kaisen</div>
              <div class="sc-meta"><strong>★ 9.4</strong> &nbsp;•&nbsp; 605K views</div>
            </div>
          </div>
          <div class="sidebar-card" onclick="showToast('Opening Vinland Saga…')">
            <div class="sc-bg" style="background-image:url('https://images.unsplash.com/photo-1592478411213-6153e4ebc07d?w=600&q=80');"></div>
            <div class="sc-grad"></div>
            <div class="sc-accent"></div>
            <div class="sc-rank-num">#5</div>
            <div class="sc-content">
              <div class="sc-title">Vinland Saga</div>
              <div class="sc-meta"><strong>★ 9.3</strong> &nbsp;•&nbsp; 498K views</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ══ TRENDING THIS WEEK — GRID ═════════════════ -->
    <div class="trend-section">
      <div class="section-header">
        <div class="section-dot red"></div>
        <div class="section-title">📈 Trending This Week</div>
        <div class="section-line"></div>
        <div class="section-see-all" onclick="showToast('Loading all trending…')">See All →</div>
      </div>

      <div class="trend-grid" id="trendGrid">

        <div class="trend-card" onclick="showToast('Opening Frieren…')">
          <div class="tc-bg" style="background-image:url('https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=600&q=80');"></div>
          <div class="tc-gradient"></div>
          <div class="tc-rank">#1</div>
          <div class="tc-badge hot">🔥 HOT</div>
          <div class="tc-overlay"><div class="tc-play-ring"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg></div></div>
          <div class="tc-content">
            <div class="tc-title">Frieren</div>
            <div class="tc-meta">
              <strong>★ 9.4</strong><span class="dot">•</span>
              <span class="tc-trend-arrow up">▲ 412%</span>
            </div>
          </div>
          <div class="tc-hover-strip"></div>
        </div>

        <div class="trend-card" onclick="showToast('Opening Bleach: TYBW…')">
          <div class="tc-bg" style="background-image:url('https://images.unsplash.com/photo-1578632767115-351597cf2477?w=600&q=80');"></div>
          <div class="tc-gradient"></div>
          <div class="tc-rank">#2</div>
          <div class="tc-badge new">NEW EP</div>
          <div class="tc-overlay"><div class="tc-play-ring"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg></div></div>
          <div class="tc-content">
            <div class="tc-title">Bleach: TYBW</div>
            <div class="tc-meta">
              <strong>★ 9.6</strong><span class="dot">•</span>
              <span class="tc-trend-arrow up">▲ 389%</span>
            </div>
          </div>
          <div class="tc-hover-strip"></div>
        </div>

        <div class="trend-card" onclick="showToast('Opening Solo Leveling…')">
          <div class="tc-bg" style="background-image:url('https://images.unsplash.com/photo-1534796636912-3b95b3ab5986?w=600&q=80');"></div>
          <div class="tc-gradient"></div>
          <div class="tc-rank">#3</div>
          <div class="tc-badge rise">📈 RISING</div>
          <div class="tc-overlay"><div class="tc-play-ring"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg></div></div>
          <div class="tc-content">
            <div class="tc-title">Solo Leveling</div>
            <div class="tc-meta">
              <strong>★ 9.1</strong><span class="dot">•</span>
              <span class="tc-trend-arrow up">▲ 277%</span>
            </div>
          </div>
          <div class="tc-hover-strip"></div>
        </div>

        <div class="trend-card" onclick="showToast('Opening Mushoku Tensei…')">
          <div class="tc-bg" style="background-image:url('https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=600&q=80');"></div>
          <div class="tc-gradient"></div>
          <div class="tc-rank">#4</div>
          <div class="tc-badge top">★ TOP</div>
          <div class="tc-overlay"><div class="tc-play-ring"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg></div></div>
          <div class="tc-content">
            <div class="tc-title">Mushoku Tensei</div>
            <div class="tc-meta">
              <strong>★ 9.0</strong><span class="dot">•</span>
              <span class="tc-trend-arrow up">▲ 201%</span>
            </div>
          </div>
          <div class="tc-hover-strip"></div>
        </div>

        <div class="trend-card" onclick="showToast('Opening Dungeon Meshi…')">
          <div class="tc-bg" style="background-image:url('https://images.unsplash.com/photo-1592478411213-6153e4ebc07d?w=600&q=80');"></div>
          <div class="tc-gradient"></div>
          <div class="tc-rank">#5</div>
          <div class="tc-badge hot">🔥 HOT</div>
          <div class="tc-overlay"><div class="tc-play-ring"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg></div></div>
          <div class="tc-content">
            <div class="tc-title">Dungeon Meshi</div>
            <div class="tc-meta">
              <strong>★ 9.2</strong><span class="dot">•</span>
              <span class="tc-trend-arrow up">▲ 198%</span>
            </div>
          </div>
          <div class="tc-hover-strip"></div>
        </div>

        <div class="trend-card" onclick="showToast('Opening Re:Zero…')">
          <div class="tc-bg" style="background-image:url('https://images.unsplash.com/photo-1502209524164-acea936639a2?w=600&q=80');"></div>
          <div class="tc-gradient"></div>
          <div class="tc-rank">#6</div>
          <div class="tc-badge new">NEW EP</div>
          <div class="tc-overlay"><div class="tc-play-ring"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg></div></div>
          <div class="tc-content">
            <div class="tc-title">Re:Zero</div>
            <div class="tc-meta">
              <strong>★ 8.9</strong><span class="dot">•</span>
              <span class="tc-trend-arrow up">▲ 176%</span>
            </div>
          </div>
          <div class="tc-hover-strip"></div>
        </div>

        <div class="trend-card" onclick="showToast('Opening Fullmetal Alchemist…')">
          <div class="tc-bg" style="background-image:url('https://images.unsplash.com/photo-1461896836934-ffe607ba8211?w=600&q=80');"></div>
          <div class="tc-gradient"></div>
          <div class="tc-rank">#7</div>
          <div class="tc-badge top">★ TOP</div>
          <div class="tc-overlay"><div class="tc-play-ring"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg></div></div>
          <div class="tc-content">
            <div class="tc-title">FMA: Brotherhood</div>
            <div class="tc-meta">
              <strong>★ 9.9</strong><span class="dot">•</span>
              <span class="tc-trend-arrow same">— Stable</span>
            </div>
          </div>
          <div class="tc-hover-strip"></div>
        </div>

        <div class="trend-card" onclick="showToast('Opening Spy x Family…')">
          <div class="tc-bg" style="background-image:url('https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=600&q=80');"></div>
          <div class="tc-gradient"></div>
          <div class="tc-rank">#8</div>
          <div class="tc-badge rise">📈 RISING</div>
          <div class="tc-overlay"><div class="tc-play-ring"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg></div></div>
          <div class="tc-content">
            <div class="tc-title">Spy x Family</div>
            <div class="tc-meta">
              <strong>★ 8.8</strong><span class="dot">•</span>
              <span class="tc-trend-arrow up">▲ 143%</span>
            </div>
          </div>
          <div class="tc-hover-strip"></div>
        </div>

      </div>
    </div>

    <!-- ══ TOP 10 + WATCHED TODAY — TWO COLUMNS ══════ -->
    <div class="trend-section two-col">

      <!-- Top 10 Rank Table -->
      <div>
        <div class="section-header">
          <div class="section-dot red"></div>
          <div class="section-title">🏅 Top 10 Chart</div>
          <div class="section-line"></div>
          <div class="section-see-all" onclick="showToast('Loading full chart…')">Full Chart →</div>
        </div>
        <div class="rank-table">

          <div class="rank-row" onclick="showToast('Opening Demon Slayer…')">
            <div class="rr-num top3">1</div>
            <div class="rr-thumb" style="background-image:url('https://images.unsplash.com/photo-1578632767115-351597cf2477?w=200&q=80');"></div>
            <div class="rr-info">
              <div class="rr-title">Demon Slayer</div>
              <div class="rr-sub"><span class="hl">Action</span><span class="dot">•</span><span>4 Seasons</span></div>
            </div>
            <div class="rr-right">
              <div class="rr-views">1.2M views</div>
              <div class="rr-change up">▲ 18%</div>
            </div>
          </div>

          <div class="rank-row" onclick="showToast('Opening Attack on Titan…')">
            <div class="rr-num top3">2</div>
            <div class="rr-thumb" style="background-image:url('https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=200&q=80');"></div>
            <div class="rr-info">
              <div class="rr-title">Attack on Titan</div>
              <div class="rr-sub"><span class="hl">Action</span><span class="dot">•</span><span>4 Seasons</span></div>
            </div>
            <div class="rr-right">
              <div class="rr-views">847K views</div>
              <div class="rr-change up">▲ 12%</div>
            </div>
          </div>

          <div class="rank-row" onclick="showToast('Opening One Piece…')">
            <div class="rr-num top3">3</div>
            <div class="rr-thumb" style="background-image:url('https://images.unsplash.com/photo-1534796636912-3b95b3ab5986?w=200&q=80');"></div>
            <div class="rr-info">
              <div class="rr-title">One Piece</div>
              <div class="rr-sub"><span class="hl">Adventure</span><span class="dot">•</span><span>Ongoing</span></div>
            </div>
            <div class="rr-right">
              <div class="rr-views">712K views</div>
              <div class="rr-change up">▲ 9%</div>
            </div>
          </div>

          <div class="rank-row" onclick="showToast('Opening Jujutsu Kaisen…')">
            <div class="rr-num">4</div>
            <div class="rr-thumb" style="background-image:url('https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=200&q=80');"></div>
            <div class="rr-info">
              <div class="rr-title">Jujutsu Kaisen</div>
              <div class="rr-sub"><span class="hl">Action</span><span class="dot">•</span><span>3 Seasons</span></div>
            </div>
            <div class="rr-right">
              <div class="rr-views">605K views</div>
              <div class="rr-change up">▲ 7%</div>
            </div>
          </div>

          <div class="rank-row" onclick="showToast('Opening Vinland Saga…')">
            <div class="rr-num">5</div>
            <div class="rr-thumb" style="background-image:url('https://images.unsplash.com/photo-1592478411213-6153e4ebc07d?w=200&q=80');"></div>
            <div class="rr-info">
              <div class="rr-title">Vinland Saga</div>
              <div class="rr-sub"><span class="hl">Historical</span><span class="dot">•</span><span>2 Seasons</span></div>
            </div>
            <div class="rr-right">
              <div class="rr-views">498K views</div>
              <div class="rr-change new-entry">★ New High</div>
            </div>
          </div>

          <div class="rank-row" onclick="showToast('Opening Frieren…')">
            <div class="rr-num">6</div>
            <div class="rr-thumb" style="background-image:url('https://images.unsplash.com/photo-1502209524164-acea936639a2?w=200&q=80');"></div>
            <div class="rr-info">
              <div class="rr-title">Frieren: Beyond</div>
              <div class="rr-sub"><span class="hl">Fantasy</span><span class="dot">•</span><span>1 Season</span></div>
            </div>
            <div class="rr-right">
              <div class="rr-views">441K views</div>
              <div class="rr-change up">▲ 33%</div>
            </div>
          </div>

          <div class="rank-row" onclick="showToast('Opening Bleach TYBW…')">
            <div class="rr-num">7</div>
            <div class="rr-thumb" style="background-image:url('https://images.unsplash.com/photo-1461896836934-ffe607ba8211?w=200&q=80');"></div>
            <div class="rr-info">
              <div class="rr-title">Bleach: TYBW</div>
              <div class="rr-sub"><span class="hl">Action</span><span class="dot">•</span><span>Final Arc</span></div>
            </div>
            <div class="rr-right">
              <div class="rr-views">388K views</div>
              <div class="rr-change up">▲ 21%</div>
            </div>
          </div>

          <div class="rank-row" onclick="showToast('Opening Solo Leveling…')">
            <div class="rr-num">8</div>
            <div class="rr-thumb" style="background-image:url('https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200&q=80');"></div>
            <div class="rr-info">
              <div class="rr-title">Solo Leveling</div>
              <div class="rr-sub"><span class="hl">Action</span><span class="dot">•</span><span>2 Seasons</span></div>
            </div>
            <div class="rr-right">
              <div class="rr-views">347K views</div>
              <div class="rr-change down">▼ 3%</div>
            </div>
          </div>

          <div class="rank-row" onclick="showToast('Opening Chainsaw Man…')">
            <div class="rr-num">9</div>
            <div class="rr-thumb" style="background-image:url('https://images.unsplash.com/photo-1582771083-3dfd4a1a6d97?w=200&q=80');"></div>
            <div class="rr-info">
              <div class="rr-title">Chainsaw Man</div>
              <div class="rr-sub"><span class="hl">Horror</span><span class="dot">•</span><span>2 Seasons</span></div>
            </div>
            <div class="rr-right">
              <div class="rr-views">291K views</div>
              <div class="rr-change up">▲ 5%</div>
            </div>
          </div>

          <div class="rank-row" onclick="showToast('Opening FMA: Brotherhood…')">
            <div class="rr-num">10</div>
            <div class="rr-thumb" style="background-image:url('https://images.unsplash.com/photo-1596727362302-b8d891c42ab8?w=200&q=80');"></div>
            <div class="rr-info">
              <div class="rr-title">FMA: Brotherhood</div>
              <div class="rr-sub"><span class="hl">Action</span><span class="dot">•</span><span>Complete</span></div>
            </div>
            <div class="rr-right">
              <div class="rr-views">254K views</div>
              <div class="rr-change new-entry">★ Classic Pick</div>
            </div>
          </div>

        </div>
      </div>

      <!-- Most Watched Today — Compact scroll -->
      <div>
        <div class="section-header">
          <div class="section-dot cyan"></div>
          <div class="section-title">⏱️ Most Watched Today</div>
          <div class="section-line"></div>
        </div>
        <div class="hscroll-wrap">
          <div class="hscroll-row">

            <div class="hscroll-card" onclick="showToast('Opening Demon Slayer…')">
              <div class="hc-bg" style="background-image:url('https://images.unsplash.com/photo-1578632767115-351597cf2477?w=400&q=80');"></div>
              <div class="hc-grad"></div>
              <div class="hc-content">
                <div class="hc-rank-tag">#1 TODAY</div>
                <div class="hc-title">Demon Slayer</div>
                <div class="hc-meta"><strong>1.2M</strong> views</div>
              </div>
              <div class="hc-strip"></div>
            </div>

            <div class="hscroll-card" onclick="showToast('Opening Attack on Titan…')">
              <div class="hc-bg" style="background-image:url('https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=400&q=80');"></div>
              <div class="hc-grad"></div>
              <div class="hc-content">
                <div class="hc-rank-tag">#2 TODAY</div>
                <div class="hc-title">Attack on Titan</div>
                <div class="hc-meta"><strong>847K</strong> views</div>
              </div>
              <div class="hc-strip"></div>
            </div>

            <div class="hscroll-card" onclick="showToast('Opening One Piece…')">
              <div class="hc-bg" style="background-image:url('https://images.unsplash.com/photo-1534796636912-3b95b3ab5986?w=400&q=80');"></div>
              <div class="hc-grad"></div>
              <div class="hc-content">
                <div class="hc-rank-tag">#3 TODAY</div>
                <div class="hc-title">One Piece</div>
                <div class="hc-meta"><strong>712K</strong> views</div>
              </div>
              <div class="hc-strip"></div>
            </div>

            <div class="hscroll-card" onclick="showToast('Opening Jujutsu Kaisen…')">
              <div class="hc-bg" style="background-image:url('https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=400&q=80');"></div>
              <div class="hc-grad"></div>
              <div class="hc-content">
                <div class="hc-rank-tag">#4 TODAY</div>
                <div class="hc-title">Jujutsu Kaisen</div>
                <div class="hc-meta"><strong>605K</strong> views</div>
              </div>
              <div class="hc-strip"></div>
            </div>

            <div class="hscroll-card" onclick="showToast('Opening Frieren…')">
              <div class="hc-bg" style="background-image:url('https://images.unsplash.com/photo-1592478411213-6153e4ebc07d?w=400&q=80');"></div>
              <div class="hc-grad"></div>
              <div class="hc-content">
                <div class="hc-rank-tag">#5 TODAY</div>
                <div class="hc-title">Frieren</div>
                <div class="hc-meta"><strong>441K</strong> views</div>
              </div>
              <div class="hc-strip"></div>
            </div>

            <div class="hscroll-card" onclick="showToast('Opening Dungeon Meshi…')">
              <div class="hc-bg" style="background-image:url('https://images.unsplash.com/photo-1502209524164-acea936639a2?w=400&q=80');"></div>
              <div class="hc-grad"></div>
              <div class="hc-content">
                <div class="hc-rank-tag">#6 TODAY</div>
                <div class="hc-title">Dungeon Meshi</div>
                <div class="hc-meta"><strong>388K</strong> views</div>
              </div>
              <div class="hc-strip"></div>
            </div>

          </div>
        </div>
      </div>
    </div>

    <!-- ══ TRENDING BY REGION ═════════════════════════ -->
    <div class="trend-section">
      <div class="section-header">
        <div class="section-dot purple"></div>
        <div class="section-title">🌍 Trending by Region</div>
        <div class="section-line"></div>
      </div>
      <div class="region-grid">

        <div class="region-card" onclick="showToast('Japan trending…')">
          <div class="rc-bg" style="background-image:url('https://images.unsplash.com/photo-1578632767115-351597cf2477?w=400&q=80');"></div>
          <div class="rc-grad"></div>
          <div class="rc-accent"></div>
          <div class="rc-content">
            <div class="rc-flag">🇯🇵</div>
            <div class="rc-info">
              <div class="rc-name">Japan</div>
              <div class="rc-count"><strong>4.2K</strong> titles</div>
            </div>
          </div>
        </div>

        <div class="region-card" onclick="showToast('USA trending…')">
          <div class="rc-bg" style="background-image:url('https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=400&q=80');"></div>
          <div class="rc-grad"></div>
          <div class="rc-accent"></div>
          <div class="rc-content">
            <div class="rc-flag">🇺🇸</div>
            <div class="rc-info">
              <div class="rc-name">USA</div>
              <div class="rc-count"><strong>2.8K</strong> titles</div>
            </div>
          </div>
        </div>

        <div class="region-card" onclick="showToast('South Korea trending…')">
          <div class="rc-bg" style="background-image:url('https://images.unsplash.com/photo-1534796636912-3b95b3ab5986?w=400&q=80');"></div>
          <div class="rc-grad"></div>
          <div class="rc-accent"></div>
          <div class="rc-content">
            <div class="rc-flag">🇰🇷</div>
            <div class="rc-info">
              <div class="rc-name">South Korea</div>
              <div class="rc-count"><strong>1.9K</strong> titles</div>
            </div>
          </div>
        </div>

        <div class="region-card" onclick="showToast('China trending…')">
          <div class="rc-bg" style="background-image:url('https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=400&q=80');"></div>
          <div class="rc-grad"></div>
          <div class="rc-accent"></div>
          <div class="rc-content">
            <div class="rc-flag">🇨🇳</div>
            <div class="rc-info">
              <div class="rc-name">China</div>
              <div class="rc-count"><strong>1.6K</strong> titles</div>
            </div>
          </div>
        </div>

        <div class="region-card" onclick="showToast('Philippines trending…')">
          <div class="rc-bg" style="background-image:url('https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&q=80');"></div>
          <div class="rc-grad"></div>
          <div class="rc-accent"></div>
          <div class="rc-content">
            <div class="rc-flag">🇵🇭</div>
            <div class="rc-info">
              <div class="rc-name">Philippines</div>
              <div class="rc-count"><strong>843</strong> titles</div>
            </div>
          </div>
        </div>

        <div class="region-card" onclick="showToast('Global trending…')">
          <div class="rc-bg" style="background-image:url('https://images.unsplash.com/photo-1592478411213-6153e4ebc07d?w=400&q=80');"></div>
          <div class="rc-grad"></div>
          <div class="rc-accent"></div>
          <div class="rc-content">
            <div class="rc-flag">🌐</div>
            <div class="rc-info">
              <div class="rc-name">Global</div>
              <div class="rc-count"><strong>12.8K</strong> titles</div>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- ══ TRENDING GENRES ════════════════════════════ -->
    <div class="trend-section">
      <div class="section-header">
        <div class="section-dot"></div>
        <div class="section-title">📊 Trending Genres</div>
        <div class="section-line"></div>
        <div class="section-see-all" onclick="showToast('Loading all genres…')">All Genres →</div>
      </div>
      <div class="genre-burst-grid">

        <div class="gb-card" data-color="red" onclick="showToast('Browsing Action…')">
          <span class="gb-emoji">⚔️</span>
          <div class="gb-name">Action</div>
          <div class="gb-trend hot">🔥 +412% this week</div>
          <div class="gb-bar"><div class="gb-fill" style="width:94%;background:linear-gradient(to right,var(--accent),var(--accent2));"></div></div>
        </div>

        <div class="gb-card" data-color="purple" onclick="showToast('Browsing Fantasy…')">
          <span class="gb-emoji">🌟</span>
          <div class="gb-name">Fantasy</div>
          <div class="gb-trend up">▲ +287% this week</div>
          <div class="gb-bar"><div class="gb-fill" style="width:80%;background:linear-gradient(to right,var(--purple),#b78bff);"></div></div>
        </div>

        <div class="gb-card" data-color="cyan" onclick="showToast('Browsing Sci-Fi…')">
          <span class="gb-emoji">🚀</span>
          <div class="gb-name">Sci-Fi</div>
          <div class="gb-trend up">▲ +203% this week</div>
          <div class="gb-bar"><div class="gb-fill" style="width:72%;background:linear-gradient(to right,var(--cyan),#0ea5e9);"></div></div>
        </div>

        <div class="gb-card" data-color="red" onclick="showToast('Browsing Horror…')">
          <span class="gb-emoji">🩸</span>
          <div class="gb-name">Horror</div>
          <div class="gb-trend hot">🔥 +188% this week</div>
          <div class="gb-bar"><div class="gb-fill" style="width:68%;background:linear-gradient(to right,#7f1d1d,var(--accent));"></div></div>
        </div>

        <div class="gb-card" data-color="gold" onclick="showToast('Browsing Romance…')">
          <span class="gb-emoji">💕</span>
          <div class="gb-name">Romance</div>
          <div class="gb-trend up">▲ +165% this week</div>
          <div class="gb-bar"><div class="gb-fill" style="width:62%;background:linear-gradient(to right,#db2777,var(--accent2));"></div></div>
        </div>

        <div class="gb-card" data-color="green" onclick="showToast('Browsing Sports…')">
          <span class="gb-emoji">⚽</span>
          <div class="gb-name">Sports</div>
          <div class="gb-trend up">▲ +141% this week</div>
          <div class="gb-bar"><div class="gb-fill" style="width:56%;background:linear-gradient(to right,var(--green),#16a34a);"></div></div>
        </div>

        <div class="gb-card" data-color="purple" onclick="showToast('Browsing Isekai…')">
          <span class="gb-emoji">🌀</span>
          <div class="gb-name">Isekai</div>
          <div class="gb-trend hot">🔥 +133% this week</div>
          <div class="gb-bar"><div class="gb-fill" style="width:52%;background:linear-gradient(to right,#7c3aed,var(--purple));"></div></div>
        </div>

        <div class="gb-card" data-color="cyan" onclick="showToast('Browsing Thriller…')">
          <span class="gb-emoji">😰</span>
          <div class="gb-name">Thriller</div>
          <div class="gb-trend up">▲ +119% this week</div>
          <div class="gb-bar"><div class="gb-fill" style="width:47%;background:linear-gradient(to right,#0369a1,var(--cyan));"></div></div>
        </div>

      </div>
    </div>

  </div>
</section>


<?= $this->end() ?>

<?= $this->start('scripts') ?>
<script src="/assets/frontend/js/trending-page.js"></script>
<?= $this->end() ?>