<div class="ad-leaderboard" style="margin-top: 2cap;">
  <div class="ad-box ad-728">
    <div class="ad-label">Advertisement</div>
    <div class="ad-copy">728 × 90 — Leaderboard</div>
    <div class="ad-sub">Bottom of page placement</div>
  </div>
</div>

<nav id="botnav">
  <a href="/" class="bot-nav-item" data-nav="home">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
    Home
  </a>
  <a href="/archive/browse" class="bot-nav-item active" data-nav="browse">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
    Browse
  </a>
  <a href="/" class="bot-nav-item home-btn" data-nav="main">
    <div class="home-icon-wrap">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
    </div>
    Watch
  </a>
  <a href="/" class="bot-nav-item" data-nav="watchlist">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
    My List
  </a>
  <a href="/" class="bot-nav-item" data-nav="profile">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
    Profile
  </a>
</nav>

<div id="filter-drawer">
  <div class="fd-backdrop" onclick="closeFilterDrawer()"></div>
  <div class="fd-panel">
    <div class="fd-head">
      <h3>Filters</h3>
      <button class="fd-close" onclick="closeFilterDrawer()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div id="drawerFilters"></div>
    <div class="filter-actions" style="margin-top:auto;">
      <button class="fa-apply" onclick="closeFilterDrawer();applyFilters();">Apply Filters</button>
      <button class="fa-reset" onclick="resetFilters()">Reset</button>
    </div>
  </div>
</div>

<div id="toast"></div>
