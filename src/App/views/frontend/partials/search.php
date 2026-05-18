<!-- ═══════ SEARCH OVERLAY ═══════ -->
<div id="search-overlay">
  <button class="so-close" id="searchClose">✕</button>
  <div class="so-inner">
    <div class="so-box">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
      <input type="text" placeholder="Search TV shows, movies, genres…" id="searchInput" autocomplete="off"/>
    </div>
    <div class="so-results" id="searchResults" aria-live="polite"></div>
    <p class="so-state" id="searchState">Type at least 2 characters</p>
    <div class="so-tags">
      <span class="so-tag" onclick="fillSearch('Attack on Titan')">Attack on Titan</span>
      <span class="so-tag" onclick="fillSearch('Action')">Action</span>
      <span class="so-tag" onclick="fillSearch('Romance')">Romance</span>
      <span class="so-tag" onclick="fillSearch('Fantasy')">Fantasy</span>
      <span class="so-tag" onclick="fillSearch('Isekai')">Isekai</span>
      <span class="so-tag" onclick="fillSearch('Movies')">Movies</span>
      <span class="so-tag" onclick="fillSearch('Shonen')">Shonen</span>
      <span class="so-tag" onclick="fillSearch('Horror')">Horror</span>
    </div>
  </div>
</div>
