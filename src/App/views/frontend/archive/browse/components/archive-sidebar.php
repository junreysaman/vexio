<aside id="sidebar">
  <?php
  $types = $types ?? [];
  ?>
  <div class="filter-panel">
    <div class="fp-head" onclick="togglePanel(this)">
      <div class="fp-head-left">
        <div class="fp-icon" style="background:rgba(232,23,63,.15);">
          <svg viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
        </div>
        <span class="fp-title">Type</span>
      </div>
      <svg class="fp-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
    </div>
    <div class="fp-body">
      <div class="fr-list">
        <?php foreach ($types as $type): ?>
          <label class="fr-item">
            <input type="radio" name="type" value="<?= escape($type['value'] ?? '') ?>" <?= ($type['value'] === 'all' ? 'checked' : '') ?>>
            <div class="fr-dot"></div>
            <span class="fr-label"><?= escape($type['label'] ?? 'All Types') ?></span>
          </label>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="filter-panel">
    <div class="fp-head" onclick="togglePanel(this)">
      <div class="fp-head-left">
        <div class="fp-icon" style="background:rgba(0,200,240,.12);">
          <svg viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>
        </div>
        <span class="fp-title">Audio</span>
      </div>
      <svg class="fp-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
    </div>
    <div class="fp-body">
      <div class="fc-list">
        <label class="fc-item">
          <input type="checkbox" checked onchange="updateFilters()">
          <div class="fc-check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></div>
          <span class="fc-label">Subbed (SUB)</span>
          <span class="fc-count">9.2k</span>
        </label>
        <label class="fc-item">
          <input type="checkbox" onchange="updateFilters()">
          <div class="fc-check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></div>
          <span class="fc-label">Dubbed (DUB)</span>
          <span class="fc-count">4.8k</span>
        </label>
        <label class="fc-item">
          <input type="checkbox" onchange="updateFilters()">
          <div class="fc-check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></div>
          <span class="fc-label">Both Available</span>
          <span class="fc-count">3.4k</span>
        </label>
      </div>
    </div>
  </div>

  <?php
  $genres = $genres ?? [];
  ?>
  <div class="filter-panel">
    <div class="fp-head" onclick="togglePanel(this)">
      <div class="fp-head-left">
        <div class="fp-icon" style="background:rgba(139,92,246,.15);">
          <svg viewBox="0 0 24 24" fill="none" stroke="var(--purple)" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
        </div>
        <span class="fp-title">Genre</span>
      </div>
      <svg class="fp-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
    </div>
    <div class="fp-body">
      <div class="filter-search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input placeholder="Search genres…" oninput="filterGenreList(this.value)">
      </div>
      <div class="fc-list" id="genreList">
        <?php foreach ($genres as $genre): ?>
          <label class="fc-item">
            <input type="checkbox" name="genre[]" value="<?= escape($genre['slug'] ?? '') ?>" onchange="updateFilters()">
            <div class="fc-check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></div>
            <span class="fc-label"><?= escape($genre['name'] ?? 'Unknown') ?></span>
          </label>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <?php
  $statuses = $statuses ?? [];
  ?>
  <div class="filter-panel">
    <div class="fp-head" onclick="togglePanel(this)">
      <div class="fp-head-left">
        <div class="fp-icon" style="background:rgba(255,195,64,.1);">
          <svg viewBox="0 0 24 24" fill="none" stroke="var(--gold)" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <span class="fp-title">Status</span>
      </div>
      <svg class="fp-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
    </div>
    <div class="fp-body">
      <div class="fr-list">
        <?php foreach ($statuses as $status): ?>
          <label class="fr-item">
            <input type="radio" name="status" value="<?= escape($status['value'] ?? '') ?>" <?= ($status['value'] === 'all' ? 'checked' : '') ?>><div class="fr-dot"></div><span class="fr-label"><?= escape($status['label'] ?? 'All') ?></span>
          </label>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="filter-panel">
    <div class="fp-head" onclick="togglePanel(this)">
      <div class="fp-head-left">
        <div class="fp-icon" style="background:rgba(255,195,64,.1);">
          <svg viewBox="0 0 24 24" fill="var(--gold)" stroke="var(--gold)" stroke-width="1"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        </div>
        <span class="fp-title">Min. Rating</span>
      </div>
      <svg class="fp-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
    </div>
    <div class="fp-body">
      <div class="rating-range">
        <input type="range" min="0" max="10" step="0.5" value="7" id="ratingSlider" oninput="updateRating(this)">
        <span class="rating-val" id="ratingVal">⭐ 7.0+</span>
      </div>
    </div>
  </div>

  <div class="filter-panel">
    <div class="fp-head" onclick="togglePanel(this)">
      <div class="fp-head-left">
        <div class="fp-icon" style="background:rgba(0,200,240,.1);">
          <svg viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </div>
        <span class="fp-title">Release Year</span>
      </div>
      <svg class="fp-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
    </div>
    <div class="fp-body">
      <div class="year-range">
        <input class="year-input" type="number" placeholder="From" min="1960" max="2025" value="2000">
        <span class="year-sep">—</span>
        <input class="year-input" type="number" placeholder="To" min="1960" max="2025" value="2025">
      </div>
      <div style="margin-top:12px;">
        <div style="font-size:10px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--muted);margin-bottom:8px;">Season</div>
        <div class="ep-chips">
          <button class="ep-chip active" onclick="toggleChip(this)">All</button>
          <button class="ep-chip" onclick="toggleChip(this)">Winter</button>
          <button class="ep-chip" onclick="toggleChip(this)">Spring</button>
          <button class="ep-chip" onclick="toggleChip(this)">Summer</button>
          <button class="ep-chip" onclick="toggleChip(this)">Fall</button>
        </div>
      </div>
    </div>
  </div>

  <div class="filter-panel">
    <div class="fp-head" onclick="togglePanel(this)">
      <div class="fp-head-left">
        <div class="fp-icon" style="background:rgba(232,23,63,.12);">
          <svg viewBox="0 0 24 24" fill="none" stroke="var(--accent2)" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
        </div>
        <span class="fp-title">Episode Count</span>
      </div>
      <svg class="fp-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
    </div>
    <div class="fp-body">
      <div class="ep-chips">
        <button class="ep-chip active" onclick="toggleChip(this)">Any</button>
        <button class="ep-chip" onclick="toggleChip(this)">1–12</button>
        <button class="ep-chip" onclick="toggleChip(this)">13–24</button>
        <button class="ep-chip" onclick="toggleChip(this)">25–50</button>
        <button class="ep-chip" onclick="toggleChip(this)">51–100</button>
        <button class="ep-chip" onclick="toggleChip(this)">100+</button>
      </div>
    </div>
  </div>

  <div class="filter-panel">
    <div class="fp-head" onclick="togglePanel(this)">
      <div class="fp-head-left">
        <div class="fp-icon" style="background:rgba(139,92,246,.12);">
          <svg viewBox="0 0 24 24" fill="none" stroke="var(--purple)" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
        </div>
        <span class="fp-title">Tags</span>
      </div>
      <svg class="fp-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
    </div>
    <div class="fp-body">
      <div class="tag-cloud" id="tagCloud">
        <button class="tc-tag" onclick="this.classList.toggle('active');updateFilters()">Overpowered MC</button>
        <button class="tc-tag" onclick="this.classList.toggle('active');updateFilters()">Time Travel</button>
        <button class="tc-tag" onclick="this.classList.toggle('active');updateFilters()">Magic</button>
        <button class="tc-tag" onclick="this.classList.toggle('active');updateFilters()">School Life</button>
        <button class="tc-tag" onclick="this.classList.toggle('active');updateFilters()">Military</button>
        <button class="tc-tag" onclick="this.classList.toggle('active');updateFilters()">Harem</button>
        <button class="tc-tag" onclick="this.classList.toggle('active');updateFilters()">Reincarnation</button>
        <button class="tc-tag" onclick="this.classList.toggle('active');updateFilters()">Demons</button>
        <button class="tc-tag" onclick="this.classList.toggle('active');updateFilters()">Vampires</button>
        <button class="tc-tag" onclick="this.classList.toggle('active');updateFilters()">Martial Arts</button>
        <button class="tc-tag" onclick="this.classList.toggle('active');updateFilters()">Game World</button>
        <button class="tc-tag" onclick="this.classList.toggle('active');updateFilters()">Historical</button>
        <button class="tc-tag" onclick="this.classList.toggle('active');updateFilters()">Music</button>
        <button class="tc-tag" onclick="this.classList.toggle('active');updateFilters()">Cooking</button>
        <button class="tc-tag" onclick="this.classList.toggle('active');updateFilters()">Ecchi</button>
        <button class="tc-tag" onclick="this.classList.toggle('active');updateFilters()">Yaoi</button>
        <button class="tc-tag" onclick="this.classList.toggle('active');updateFilters()">Yuri</button>
        <button class="tc-tag" onclick="this.classList.toggle('active');updateFilters()">Shounen</button>
        <button class="tc-tag" onclick="this.classList.toggle('active');updateFilters()">Seinen</button>
        <button class="tc-tag" onclick="this.classList.toggle('active');updateFilters()">Josei</button>
        <button class="tc-tag" onclick="this.classList.toggle('active');updateFilters()">Shoujo</button>
        <button class="tc-tag" onclick="this.classList.toggle('active');updateFilters()">Kids</button>
      </div>
    </div>
  </div>

  <div class="filter-actions">
    <button class="fa-apply" onclick="applyFilters()">Apply Filters</button>
    <button class="fa-reset" onclick="resetFilters()">Reset</button>
  </div>

  <div class="sidebar-ad">
    <div class="ad-box ad-300">
      <div class="ad-label">Advertisement</div>
      <div class="ad-copy">300 × 250 — Medium Rectangle</div>
      <div class="ad-sub">Your ad here</div>
    </div>
  </div>

  <div class="sidebar-ad" style="margin-top:6px;">
    <div class="ad-box ad-300-600">
      <div class="ad-label">Advertisement</div>
      <div class="ad-copy">300 × 600 — Half Page</div>
      <div class="ad-sub">Premium placement</div>
    </div>
  </div>
</aside>
