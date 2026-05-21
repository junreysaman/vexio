<aside id="sidebar">
  <?php
  $types = $types ?? [];
  $genres = $genres ?? [];
  $countries = $countries ?? [];
  ?>
  <div class="filter-panel">
    <div class="fp-head open" onclick="togglePanel(this)">
      <div class="fp-head-left">
        <div class="fp-icon" style="background:rgba(232,23,63,.15);">
          <svg viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
        </div>
        <span class="fp-title">Type</span>
      </div>
      <svg class="fp-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
    </div>
    <div class="fp-body open">
      <div class="fr-list">
        <?php foreach ($types as $type): ?>
          <label class="fr-item">
            <input type="radio" name="type" value="<?= escape($type['value'] ?? '') ?>" onchange="updateFilters()" <?= ($type['value'] ?? '') === 'all' ? 'checked' : '' ?>>
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
        <div class="fp-icon" style="background:rgba(0,200,240,.1);">
          <svg viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15 15 0 0 1 0 20"/><path d="M12 2a15 15 0 0 0 0 20"/></svg>
        </div>
        <span class="fp-title">Country</span>
      </div>
      <svg class="fp-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
    </div>
    <div class="fp-body">
      <div class="filter-search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input placeholder="Search countries..." oninput="filterCountryList(this.value)">
      </div>
      <div class="fc-list" id="countryList">
        <?php foreach ($countries as $country): ?>
          <label class="fc-item">
            <input type="checkbox" name="country[]" value="<?= escape($country['slug'] ?? '') ?>" onchange="updateFilters()">
            <div class="fc-check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></div>
            <span class="fc-label"><?= escape($country['name'] ?? 'Unknown') ?></span>
          </label>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

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
        <input placeholder="Search genres..." oninput="filterGenreList(this.value)">
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
        <input type="range" min="0" max="10" step="0.5" value="0" id="ratingSlider" oninput="updateRating(this);updateFilters()">
        <span class="rating-val" id="ratingVal">0.0+</span>
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
        <input class="year-input" id="yearFrom" type="number" placeholder="From" min="1900" max="2100" oninput="updateFilters()">
        <span class="year-sep">-</span>
        <input class="year-input" id="yearTo" type="number" placeholder="To" min="1900" max="2100" oninput="updateFilters()">
      </div>
    </div>
  </div>

  <div class="filter-actions">
    <button class="fa-apply" onclick="applyFilters()">Apply Filters</button>
    <button class="fa-reset" onclick="resetFilters()">Reset</button>
  </div>
</aside>
