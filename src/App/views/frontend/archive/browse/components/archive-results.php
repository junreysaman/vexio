<div id="results-area">
  <div class="active-filters" id="activeFilters">
    <span class="af-badge">Anime <button onclick="removeBadge(this)">×</button></span>
    <span class="af-badge">Action <button onclick="removeBadge(this)">×</button></span>
    <span class="af-badge">Rating 7.0+ <button onclick="removeBadge(this)">×</button></span>
    <button class="af-clear-all" onclick="clearAllFilters()">Clear All</button>
  </div>

  <div class="results-toolbar">
    <button class="mobile-filter-btn" onclick="openFilterDrawer()">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
      Filters
      <span class="badge" id="filterBadge">3</span>
    </button>
    <div class="results-count">
      <strong id="resultNum">1,284</strong> results found
    </div>
    <div class="toolbar-right">
      <div class="sort-select">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
        <select onchange="sortCards(this.value)">
          <option value="popular">Most Popular</option>
          <option value="rating">Highest Rated</option>
          <option value="newest">Newest First</option>
          <option value="oldest">Oldest First</option>
          <option value="az">A – Z</option>
          <option value="za">Z – A</option>
          <option value="eps">Most Episodes</option>
        </select>
      </div>
      <div class="view-toggle">
        <button class="vt-btn active" id="gridViewBtn" onclick="setView('grid')" title="Grid view">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        </button>
        <button class="vt-btn" id="listViewBtn" onclick="setView('list')" title="List view">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
        </button>
      </div>
    </div>
  </div>

  <div class="card-grid" id="cardGrid">
    <div class="acard" onclick="showToast('Opening Stellar Genesis…')">
      <div class="acard-thumb">
        <div class="acard-ph c1">STE</div>
        <div class="acard-badge badge-hot">HOT</div>
        <div class="acard-score"><svg viewBox="0 0 24 24" fill="var(--gold)" stroke="none" width="12" height="12"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg> 9.2</div>
        <button class="acard-watchlist" onclick="event.stopPropagation();showToast('Added to watchlist!')">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
        </button>
        <div class="acard-overlay">
          <div class="acard-play">
            <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><polygon points="5 3 19 12 5 21 5 3"/></svg>
          </div>
          <div class="acard-overlay-tags">
            <span class="tag-s">SUB</span>
            <span class="tag-d">DUB</span>
          </div>
        </div>
      </div>
      <div class="acard-title">Stellar Genesis</div>
      <div class="acard-meta"><span>Action</span><span class="acard-dot"></span><span>2024</span></div>
    </div>
    <div class="acard" onclick="showToast('Opening Moonlit Covenant…')">
      <div class="acard-thumb">
        <div class="acard-ph c2">MOO</div>
        <div class="acard-badge badge-new">NEW</div>
        <div class="acard-score"><svg viewBox="0 0 24 24" fill="var(--gold)" stroke="none" width="12" height="12"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg> 8.7</div>
        <button class="acard-watchlist" onclick="event.stopPropagation();showToast('Added to watchlist!')">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
        </button>
        <div class="acard-overlay">
          <div class="acard-play">
            <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><polygon points="5 3 19 12 5 21 5 3"/></svg>
          </div>
          <div class="acard-overlay-tags">
            <span class="tag-s">SUB</span>
          </div>
        </div>
      </div>
      <div class="acard-title">Moonlit Covenant</div>
      <div class="acard-meta"><span>Romance</span><span class="acard-dot"></span><span>2024</span></div>
    </div>
    <div class="acard" onclick="showToast('Opening Iron Dominion…')">
      <div class="acard-thumb">
        <div class="acard-ph c3">IRO</div>
        <div class="acard-badge badge-ep">EP 36</div>
        <div class="acard-score"><svg viewBox="0 0 24 24" fill="var(--gold)" stroke="none" width="12" height="12"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg> 8.4</div>
        <button class="acard-watchlist" onclick="event.stopPropagation();showToast('Added to watchlist!')">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
        </button>
        <div class="acard-overlay">
          <div class="acard-play">
            <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><polygon points="5 3 19 12 5 21 5 3"/></svg>
          </div>
          <div class="acard-overlay-tags">
            <span class="tag-s">SUB</span>
            <span class="tag-d">DUB</span>
          </div>
        </div>
      </div>
      <div class="acard-title">Iron Dominion</div>
      <div class="acard-meta"><span>Mecha</span><span class="acard-dot"></span><span>2023</span></div>
    </div>
    <div class="acard" onclick="showToast('Opening Sakura Chronicles…')">
      <div class="acard-thumb">
        <div class="acard-ph c4">SAK</div>
        <div class="acard-badge badge-new">NEW</div>
        <div class="acard-score"><svg viewBox="0 0 24 24" fill="var(--gold)" stroke="none" width="12" height="12"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg> 8.9</div>
        <button class="acard-watchlist" onclick="event.stopPropagation();showToast('Added to watchlist!')">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
        </button>
        <div class="acard-overlay">
          <div class="acard-play">
            <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><polygon points="5 3 19 12 5 21 5 3"/></svg>
          </div>
          <div class="acard-overlay-tags">
            <span class="tag-s">SUB</span>
          </div>
        </div>
      </div>
      <div class="acard-title">Sakura Chronicles</div>
      <div class="acard-meta"><span>Slice of Life</span><span class="acard-dot"></span><span>2024</span></div>
    </div>
    <div class="acard" onclick="showToast('Opening Abyss Walker…')">
      <div class="acard-thumb">
        <div class="acard-ph c5">ABY</div>
        <div class="acard-badge badge-hot">HOT</div>
        <div class="acard-score"><svg viewBox="0 0 24 24" fill="var(--gold)" stroke="none" width="12" height="12"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg> 9.0</div>
        <button class="acard-watchlist" onclick="event.stopPropagation();showToast('Added to watchlist!')">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
        </button>
        <div class="acard-overlay">
          <div class="acard-play">
            <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><polygon points="5 3 19 12 5 21 5 3"/></svg>
          </div>
          <div class="acard-overlay-tags">
            <span class="tag-s">SUB</span>
            <span class="tag-d">DUB</span>
          </div>
        </div>
      </div>
      <div class="acard-title">Abyss Walker</div>
      <div class="acard-meta"><span>Action</span><span class="acard-dot"></span><span>2023</span></div>
    </div>
    <div class="acard" onclick="showToast('Opening Crimson Throne…')">
      <div class="acard-thumb">
        <div class="acard-ph c6">CRI</div>
        <div class="acard-badge badge-ep">EP 48</div>
        <div class="acard-score"><svg viewBox="0 0 24 24" fill="var(--gold)" stroke="none" width="12" height="12"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg> 8.6</div>
        <button class="acard-watchlist" onclick="event.stopPropagation();showToast('Added to watchlist!')">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
        </button>
        <div class="acard-overlay">
          <div class="acard-play">
            <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><polygon points="5 3 19 12 5 21 5 3"/></svg>
          </div>
          <div class="acard-overlay-tags">
            <span class="tag-s">SUB</span>
            <span class="tag-d">DUB</span>
          </div>
        </div>
      </div>
      <div class="acard-title">Crimson Throne</div>
      <div class="acard-meta"><span>Fantasy</span><span class="acard-dot"></span><span>2022</span></div>
    </div>
    <div class="acard" onclick="showToast('Opening The Phantom Code…')">
      <div class="acard-thumb">
        <div class="acard-ph c7">PHA</div>
        <div class="acard-badge badge-new">NEW</div>
        <div class="acard-score"><svg viewBox="0 0 24 24" fill="var(--gold)" stroke="none" width="12" height="12"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg> 9.1</div>
        <button class="acard-watchlist" onclick="event.stopPropagation();showToast('Added to watchlist!')">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
        </button>
        <div class="acard-overlay">
          <div class="acard-play">
            <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><polygon points="5 3 19 12 5 21 5 3"/></svg>
          </div>
          <div class="acard-overlay-tags">
            <span class="tag-s">SUB</span>
          </div>
        </div>
      </div>
      <div class="acard-title">The Phantom Code</div>
      <div class="acard-meta"><span>Mystery</span><span class="acard-dot"></span><span>2024</span></div>
    </div>
    <div class="acard" onclick="showToast('Opening Dragon Sovereign…')">
      <div class="acard-thumb">
        <div class="acard-ph c8">DRA</div>
        <div class="acard-badge badge-hot">HOT</div>
        <div class="acard-score"><svg viewBox="0 0 24 24" fill="var(--gold)" stroke="none" width="12" height="12"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg> 8.8</div>
        <button class="acard-watchlist" onclick="event.stopPropagation();showToast('Added to watchlist!')">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
        </button>
        <div class="acard-overlay">
          <div class="acard-play">
            <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><polygon points="5 3 19 12 5 21 5 3"/></svg>
          </div>
          <div class="acard-overlay-tags">
            <span class="tag-s">SUB</span>
            <span class="tag-d">DUB</span>
          </div>
        </div>
      </div>
      <div class="acard-title">Dragon Sovereign</div>
      <div class="acard-meta"><span>Action</span><span class="acard-dot"></span><span>2023</span></div>
    </div>
  </div>

  <div class="ad-inline" id="midGridAd" style="margin:24px 0;">
    <div class="ad-box ad-native" style="max-width:680px;">
      <div class="ad-native-thumb">🎮</div>
      <div class="ad-native-body">
        <div class="ad-native-ttl">Native Ad Placement — 680 × 120</div>
        <div class="ad-native-desc">Sponsored content area for native advertising. Blend seamlessly with the content flow.</div>
      </div>
      <div style="position:relative;z-index:1;"><button class="ad-native-cta">Learn More</button></div>
    </div>
  </div>

  <div class="card-grid" id="cardGrid2">
    <div class="acard" onclick="showToast('Opening Void Protocol…')">
      <div class="acard-thumb">
        <div class="acard-ph c1">VOI</div>
        <div class="acard-badge badge-ep">EP 24</div>
        <div class="acard-score"><svg viewBox="0 0 24 24" fill="var(--gold)" stroke="none" width="12" height="12"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg> 8.3</div>
        <button class="acard-watchlist" onclick="event.stopPropagation();showToast('Added to watchlist!')">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
        </button>
        <div class="acard-overlay">
          <div class="acard-play">
            <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><polygon points="5 3 19 12 5 21 5 3"/></svg>
          </div>
          <div class="acard-overlay-tags">
            <span class="tag-s">SUB</span>
          </div>
        </div>
      </div>
      <div class="acard-title">Void Protocol</div>
      <div class="acard-meta"><span>Sci-Fi</span><span class="acard-dot"></span><span>2023</span></div>
    </div>
    <div class="acard" onclick="showToast('Opening Ember Rain…')">
      <div class="acard-thumb">
        <div class="acard-ph c2">EMB</div>
        <div class="acard-badge badge-ep">Movie</div>
        <div class="acard-score"><svg viewBox="0 0 24 24" fill="var(--gold)" stroke="none" width="12" height="12"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg> 8.5</div>
        <button class="acard-watchlist" onclick="event.stopPropagation();showToast('Added to watchlist!')">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
        </button>
        <div class="acard-overlay">
          <div class="acard-play">
            <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><polygon points="5 3 19 12 5 21 5 3"/></svg>
          </div>
          <div class="acard-overlay-tags">
            <span class="tag-s">SUB</span>
            <span class="tag-d">DUB</span>
          </div>
        </div>
      </div>
      <div class="acard-title">Ember Rain</div>
      <div class="acard-meta"><span>Romance</span><span class="acard-dot"></span><span>2024</span></div>
    </div>
    <div class="acard" onclick="showToast('Opening Neon Requiem…')">
      <div class="acard-thumb">
        <div class="acard-ph c3">NEO</div>
        <div class="acard-badge badge-new">NEW</div>
        <div class="acard-score"><svg viewBox="0 0 24 24" fill="var(--gold)" stroke="none" width="12" height="12"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg> 9.3</div>
        <button class="acard-watchlist" onclick="event.stopPropagation();showToast('Added to watchlist!')">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
        </button>
        <div class="acard-overlay">
          <div class="acard-play">
            <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><polygon points="5 3 19 12 5 21 5 3"/></svg>
          </div>
          <div class="acard-overlay-tags">
            <span class="tag-s">SUB</span>
          </div>
        </div>
      </div>
      <div class="acard-title">Neon Requiem</div>
      <div class="acard-meta"><span>Supernatural</span><span class="acard-dot"></span><span>2024</span></div>
    </div>
    <div class="acard" onclick="showToast('Opening Battle Oracle…')">
      <div class="acard-thumb">
        <div class="acard-ph c4">BAT</div>
        <div class="acard-badge badge-hot">HOT</div>
        <div class="acard-score"><svg viewBox="0 0 24 24" fill="var(--gold)" stroke="none" width="12" height="12"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg> 8.7</div>
        <button class="acard-watchlist" onclick="event.stopPropagation();showToast('Added to watchlist!')">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
        </button>
        <div class="acard-overlay">
          <div class="acard-play">
            <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><polygon points="5 3 19 12 5 21 5 3"/></svg>
          </div>
          <div class="acard-overlay-tags">
            <span class="tag-s">SUB</span>
            <span class="tag-d">DUB</span>
          </div>
        </div>
      </div>
      <div class="acard-title">Battle Oracle</div>
      <div class="acard-meta"><span>Sports</span><span class="acard-dot"></span><span>2022</span></div>
    </div>
  </div>

  <div class="pagination" id="pagination">
    <button class="pg-btn disabled">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
    </button>
    <button class="pg-btn active">1</button>
    <button class="pg-btn" onclick="showToast('Loading page 2…')">2</button>
    <button class="pg-btn" onclick="showToast('Loading page 3…')">3</button>
    <button class="pg-btn" onclick="showToast('Loading page 4…')">4</button>
    <button class="pg-btn" onclick="showToast('Loading page 5…')">5</button>
    <span class="pg-dots">···</span>
    <button class="pg-btn" onclick="showToast('Loading page 86…')">86</button>
    <button class="pg-btn">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
    </button>
    <div class="pg-jump">
      <span>Go to</span>
      <input type="number" min="1" max="86" placeholder="—" id="pageJump">
      <button class="pg-jump-go" onclick="jumpPage()">Go</button>
    </div>
  </div>
</div>
