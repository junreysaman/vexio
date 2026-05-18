 <!-- SEASON / EPISODE QUICK NAV -->
      <div class="season-bar">
        <div class="container">
          <div class="season-inner">
            <span class="season-label">Season</span>
            <div class="season-select-wrap">
              <select class="season-select" onchange="showToast('Switched to ' + this.options[this.selectedIndex].text)">
                <option>Season 1 (12 eps)</option>
                <option selected>Season 2 (10 eps)</option>
                <option>Season 3 (8 eps) — Ongoing</option>
              </select>
              <span class="season-select-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></span>
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