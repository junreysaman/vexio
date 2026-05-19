<!-- ═══ HERO ══════════════════════════════════════════ -->
<div id="trend-hero">
  <div class="container">
    <div class="trend-hero-inner">
      <div class="arch-breadcrumb">
        <a href="#">Home</a>
        <span>›</span>
        <a href="#">Browse</a>
        <span>›</span>
        <span style="color:var(--muted2);">Trending</span>
      </div>
      <div class="trend-hero-top">
        <div>
          <h1 class="trend-hero-title">
            <span class="accent-fire">🔥 Trending</span><br>
            <span>Right Now</span>
          </h1>
          <p class="trend-hero-sub">Updated every hour — the hottest titles across <strong style="color:var(--text)">12,847 titles</strong>. Ranked by views, ratings, and community buzz.</p>
        </div>
        <div class="live-pill">
          <span class="live-dot"></span>
          Live Updates — 2 min ago
        </div>
      </div>
      <div class="trend-filter-row">
        <div class="trend-filter-pills">
          <button class="tf-pill active" onclick="setFilter(this)">🔥 All Trending</button>
          <button class="tf-pill" onclick="setFilter(this)">📺 Series</button>
          <button class="tf-pill" onclick="setFilter(this)">🎬 Movies</button>
          <button class="tf-pill" onclick="setFilter(this)">⚡ Anime</button>
          <button class="tf-pill" onclick="setFilter(this)">🆕 New Releases</button>
          <button class="tf-pill" onclick="setFilter(this)">🏆 Top Rated</button>
        </div>
        <div class="trend-time-select">
          <button class="tts-btn active" onclick="setTime(this)">Today</button>
          <button class="tts-btn" onclick="setTime(this)">Week</button>
          <button class="tts-btn" onclick="setTime(this)">Month</button>
        </div>
      </div>
    </div>
  </div>
</div>