function togglePanel(head) {
  head.classList.toggle('open');
  const body = head.nextElementSibling;
  if (body) body.classList.toggle('open');
}

function openFilterDrawer() {
  const drawer = document.getElementById('filter-drawer');
  const sidebar = document.getElementById('sidebar');
  const drawerBody = document.getElementById('drawerFilters');
  if (!drawer || !sidebar || !drawerBody) return;

  drawerBody.innerHTML = '';
  drawerBody.appendChild(sidebar);
  drawer.classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeFilterDrawer() {
  const drawer = document.getElementById('filter-drawer');
  const layout = document.querySelector('.archive-layout');
  const sidebar = document.getElementById('sidebar');
  if (layout && sidebar) layout.insertBefore(sidebar, layout.firstElementChild);
  if (drawer) drawer.classList.remove('open');
  document.body.style.overflow = '';
}

function showToast(message) {
  const toast = document.getElementById('toast');
  if (!toast) return;
  toast.textContent = message;
  toast.classList.add('show');
  clearTimeout(window.__archiveToastTimer);
  window.__archiveToastTimer = setTimeout(() => toast.classList.remove('show'), 2200);
}

function updateRating(input) {
  const value = Number(input.value || 0);
  const pct = (value / 10) * 100;
  input.style.setProperty('--pct', pct + '%');
  const label = document.getElementById('ratingVal');
  if (label) label.textContent = `${value.toFixed(1)}+`;
}

function selectedType() {
  return document.querySelector('input[name="type"]:checked')?.value || 'all';
}

function selectedGenres() {
  return Array.from(document.querySelectorAll('input[name="genre[]"]:checked')).map((input) => input.value);
}

function selectedCountries() {
  return Array.from(document.querySelectorAll('input[name="country[]"]:checked')).map((input) => input.value);
}

function filterState() {
  return {
    type: selectedType(),
    genres: selectedGenres(),
    countries: selectedCountries(),
    rating: Number(document.getElementById('ratingSlider')?.value || 0),
    yearFrom: Number(document.getElementById('yearFrom')?.value || 0),
    yearTo: Number(document.getElementById('yearTo')?.value || 0),
  };
}

function cardMatches(card, state) {
  const type = card.dataset.type || '';
  const genres = (card.dataset.genres || '').split(',').filter(Boolean);
  const countries = (card.dataset.countries || '').split(',').filter(Boolean);
  const year = Number(card.dataset.year || 0);
  const rating = Number(card.dataset.rating || 0);

  if (state.type !== 'all' && type !== state.type) return false;
  if (state.genres.length && !state.genres.some((genre) => genres.includes(genre))) return false;
  if (state.countries.length && !state.countries.some((country) => countries.includes(country))) return false;
  if (state.rating > 0 && rating < state.rating) return false;
  if (state.yearFrom > 0 && year < state.yearFrom) return false;
  if (state.yearTo > 0 && year > state.yearTo) return false;

  return true;
}

let archiveCurrentPage = 1;
const archivePageSize = 24;
let archiveTotalPages = 1;
let archiveIsLoading = false;
let archiveHasMore = true;

function matchingArchiveCards(state = filterState()) {
  return Array.from(document.querySelectorAll('#cardGrid .trend-card, #cardGrid .acard')).filter((card) => cardMatches(card, state));
}

function renderArchiveCards() {
  const cards = Array.from(document.querySelectorAll('#cardGrid .trend-card, #cardGrid .acard'));
  cards.forEach((card) => {
    card.hidden = false;
  });

  updateSentinelVisibility();
  document.getElementById('archiveEmpty').hidden = cards.length !== 0;
}

function updateSentinelVisibility() {
  const sentinel = document.getElementById('archiveInfiniteSentinel');
  if (!sentinel) return;

  // Show sentinel only if we have more pages to load
  sentinel.hidden = !archiveHasMore || archiveIsLoading;

  // If no more items, ensure sentinel is hidden and not part of scroll
  if (!archiveHasMore) {
    sentinel.setAttribute('aria-hidden', 'true');
  }
}

function updateActiveFilters(state) {
  const container = document.getElementById('activeFilters');
  if (!container) return;

  const badges = [];
  if (state.type !== 'all') badges.push({ key: 'type', label: state.type === 'movie' ? 'Movies' : 'TV Shows' });
  state.genres.forEach((slug) => {
    const input = document.querySelector(`input[name="genre[]"][value="${CSS.escape(slug)}"]`);
    badges.push({ key: 'genre', value: slug, label: input?.closest('label')?.querySelector('.fc-label')?.textContent || slug });
  });
  state.countries.forEach((slug) => {
    const input = document.querySelector(`input[name="country[]"][value="${CSS.escape(slug)}"]`);
    badges.push({ key: 'country', value: slug, label: input?.closest('label')?.querySelector('.fc-label')?.textContent || slug });
  });
  if (state.rating > 0) badges.push({ key: 'rating', label: `Rating ${state.rating.toFixed(1)}+` });
  if (state.yearFrom > 0 || state.yearTo > 0) {
    badges.push({ key: 'year', label: `${state.yearFrom || 'Any'} - ${state.yearTo || 'Any'}` });
  }

  container.innerHTML = badges.map((badge) => (
    `<span class="af-badge">${escapeHtml(badge.label)} <button type="button" onclick="removeFilter('${badge.key}','${escapeHtml(badge.value || '')}')">&times;</button></span>`
  )).join('') + (badges.length ? '<button class="af-clear-all" type="button" onclick="clearAllFilters()">Clear All</button>' : '');
}

function updateFilterBadgeCount() {
  const count = document.querySelectorAll('#activeFilters .af-badge').length;
  const badge = document.getElementById('filterBadge');
  if (!badge) return;
  badge.textContent = String(count);
  badge.style.display = count ? '' : 'none';
}

async function updateFilters() {
  if (archiveIsLoading) return; // Prevent concurrent requests

  const state = filterState();
  archiveCurrentPage = 1;
  archiveHasMore = true;
  archiveIsLoading = true;

  const grid = document.getElementById('cardGrid');
  if (grid) {
    grid.innerHTML = '';
  }

  const params = new URLSearchParams({
    page: 1,
    limit: archivePageSize,
    type: state.type,
    rating: state.rating,
    year_from: state.yearFrom,
    year_to: state.yearTo,
  });

  if (state.genres.length > 0) {
    params.set('genres', state.genres.join(','));
  }

  if (state.countries.length > 0) {
    params.set('countries', state.countries.join(','));
  }

  try {
    const response = await fetch(`/api/browse/paginate?${params.toString()}`);
    if (!response.ok) {
      throw new Error(`API error: ${response.status}`);
    }
    const data = await response.json();

    if (data.items && Array.isArray(data.items) && data.items.length > 0) {
      archiveCurrentPage = parseInt(data.page) || 1;
      archiveTotalPages = parseInt(data.total_pages) || 1;

      // Calculate hasMore: we have more if current page is less than total pages
      archiveHasMore = archiveCurrentPage < archiveTotalPages;

      if (grid) {
        data.items.forEach((item) => {
          const card = createArchiveCard(item);
          grid.appendChild(card);
        });
      }
    } else {
      archiveHasMore = false;
      if (grid) grid.innerHTML = '';
    }

    // Update total count
    const resultNum = document.getElementById('resultNum');
    const totalItems = parseInt(data.total) || 0;
    if (resultNum) {
      resultNum.textContent = totalItems.toLocaleString();
    }

    // Update empty state
    const archiveEmpty = document.getElementById('archiveEmpty');
    if (archiveEmpty) {
      archiveEmpty.hidden = totalItems !== 0;
    }

    updateSentinelVisibility();
    updateActiveFilters(state);
    updateFilterBadgeCount();
  } catch (error) {
    console.error('Failed to load filtered items:', error);
    showToast('Failed to apply filters. Please try again.');
    archiveHasMore = false;
  } finally {
    archiveIsLoading = false;
  }
}

function sortCards(value, notify = true) {
  const grid = document.getElementById('cardGrid');
  if (!grid) return;

  const cards = Array.from(grid.querySelectorAll('.trend-card, .acard'));
  const sorted = cards.sort((left, right) => {
    if (value === 'rating') return Number(right.dataset.rating || 0) - Number(left.dataset.rating || 0);
    if (value === 'newest') return String(right.dataset.releaseDate || '').localeCompare(String(left.dataset.releaseDate || ''));
    if (value === 'oldest') return String(left.dataset.releaseDate || '').localeCompare(String(right.dataset.releaseDate || ''));
    if (value === 'az') return String(left.dataset.title || '').localeCompare(String(right.dataset.title || ''));
    if (value === 'za') return String(right.dataset.title || '').localeCompare(String(left.dataset.title || ''));
    return Number(right.dataset.views || 0) - Number(left.dataset.views || 0);
  });

  sorted.forEach((card) => grid.appendChild(card));
  renderArchiveCards();
  if (notify) showToast('Results sorted');
}

async function applyFilters() {
  await updateFilters();
  closeFilterDrawer();
  showToast('Filters applied');
}

async function resetFilters() {
  document.querySelectorAll('input[name="genre[]"]').forEach((input) => input.checked = false);
  document.querySelectorAll('input[name="country[]"]').forEach((input) => input.checked = false);
  document.querySelector('input[name="type"][value="all"]')?.click();
  const slider = document.getElementById('ratingSlider');
  if (slider) {
    slider.value = 0;
    updateRating(slider);
  }
  const from = document.getElementById('yearFrom');
  const to = document.getElementById('yearTo');
  if (from) from.value = '';
  if (to) to.value = '';
  await updateFilters();
  showToast('Filters reset');
}

async function removeFilter(key, value) {
  if (key === 'type') document.querySelector('input[name="type"][value="all"]')?.click();
  if (key === 'genre') {
    const input = document.querySelector(`input[name="genre[]"][value="${CSS.escape(value)}"]`);
    if (input) input.checked = false;
  }
  if (key === 'country') {
    const input = document.querySelector(`input[name="country[]"][value="${CSS.escape(value)}"]`);
    if (input) input.checked = false;
  }
  if (key === 'rating') {
    const slider = document.getElementById('ratingSlider');
    if (slider) {
      slider.value = 0;
      updateRating(slider);
    }
  }
  if (key === 'year') {
    document.getElementById('yearFrom').value = '';
    document.getElementById('yearTo').value = '';
  }
  await updateFilters();
}

async function clearAllFilters() {
  await resetFilters();
}

function filterGenreList(term) {
  const needle = term.trim().toLowerCase();
  document.querySelectorAll('#genreList .fc-item').forEach((item) => {
    const text = item.textContent.toLowerCase();
    item.hidden = needle !== '' && !text.includes(needle);
  });
}

function filterCountryList(term) {
  const needle = term.trim().toLowerCase();
  document.querySelectorAll('#countryList .fc-item').forEach((item) => {
    const text = item.textContent.toLowerCase();
    item.hidden = needle !== '' && !text.includes(needle);
  });
}

function fillSearch(term) {
  const input = document.getElementById('searchInput');
  if (!input) return;
  input.value = term;
  input.focus();
}

function escapeHtml(value) {
  return String(value).replace(/[&<>"']/g, (char) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;',
  })[char]);
}

function initArchivePage() {
  const slider = document.getElementById('ratingSlider');
  if (slider) updateRating(slider);

  // Initialize pagination state from server data
  if (typeof window.archivePageData !== 'undefined' && window.archivePageData) {
    archiveCurrentPage = Math.max(1, parseInt(window.archivePageData.current_page) || 1);
    archiveTotalPages = Math.max(1, parseInt(window.archivePageData.total_pages) || 1);
    archiveHasMore = archiveCurrentPage < archiveTotalPages;
  }

  // Initialize the infinite scroll observer
  initArchiveInfiniteScroll();

  // Render cards with proper visibility
  renderArchiveCards();

  const overlay = document.getElementById('search-overlay');
  ['searchOpen', 'mobileSearchOpen'].forEach((id) => {
    const button = document.getElementById(id);
    if (button) {
      button.addEventListener('click', () => {
        overlay?.classList.add('open');
        setTimeout(() => document.getElementById('searchInput')?.focus(), 150);
      });
    }
  });
  document.getElementById('searchClose')?.addEventListener('click', () => overlay?.classList.remove('open'));
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      overlay?.classList.remove('open');
      closeFilterDrawer();
    }
    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
      event.preventDefault();
      overlay?.classList.add('open');
      setTimeout(() => document.getElementById('searchInput')?.focus(), 150);
    }
  });
}

async function loadMoreArchiveItems() {
  if (archiveIsLoading || !archiveHasMore) return;

  archiveIsLoading = true;
  const sentinel = document.getElementById('archiveInfiniteSentinel');
  if (sentinel) sentinel.classList.add('loading');

  const state = filterState();
  const nextPage = archiveCurrentPage + 1;

  const params = new URLSearchParams({
    page: nextPage,
    limit: archivePageSize,
    type: state.type,
    rating: state.rating,
    year_from: state.yearFrom,
    year_to: state.yearTo,
  });

  if (state.genres.length > 0) {
    params.set('genres', state.genres.join(','));
  }

  if (state.countries.length > 0) {
    params.set('countries', state.countries.join(','));
  }

  try {
    const response = await fetch(`/api/browse/paginate?${params.toString()}`);
    if (!response.ok) {
      throw new Error(`API error: ${response.status}`);
    }
    const data = await response.json();

    if (data.items && Array.isArray(data.items) && data.items.length > 0) {
      archiveCurrentPage = parseInt(data.page) || nextPage;
      archiveTotalPages = parseInt(data.total_pages) || archiveTotalPages;

      // Calculate hasMore based on pages
      const previousHasMore = archiveHasMore;
      archiveHasMore = archiveCurrentPage < archiveTotalPages;

      const grid = document.getElementById('cardGrid');
      if (grid) {
        data.items.forEach((item) => {
          const card = createArchiveCard(item);
          grid.appendChild(card);
        });
      }

      renderArchiveCards();

      // If this was the last page, hide sentinel
      if (!archiveHasMore) {
        updateSentinelVisibility();
      }
    } else {
      // No items returned, we've reached the end
      archiveHasMore = false;
      updateSentinelVisibility();
    }
  } catch (error) {
    console.error('Failed to load more items:', error);
    // Don't mark as having no more on error - allow retry
    archiveHasMore = archiveCurrentPage < archiveTotalPages;
  } finally {
    archiveIsLoading = false;
    if (sentinel) sentinel.classList.remove('loading');
  }
}

function createArchiveCard(item) {
  if (!item || typeof item !== 'object') return null;

  const type = item.type || 'movie';
  const typeLabel = item.type_label || (type === 'tv_show' ? 'TV Show' : 'Movie');
  const year = item.release_year || '';
  const rating = item.tmdb_rating || null;
  const views = item.views || 0;
  const created = item.created_at || '';
  const releaseDate = item.release_date || '';
  const isFeatured = !!item.is_featured;
  const genres = Array.isArray(item.genres) ? item.genres : [];
  const genreSlugs = genres.map((g) => {
    if (typeof g === 'object' && g !== null && g.url) {
      return g.url.split('/').pop() || '';
    }
    return '';
  }).filter(Boolean);
  const countries = Array.isArray(item.countries) ? item.countries : [];
  const countryCodes = countries.map((c) => {
    if (typeof c === 'object' && c !== null && c.code) {
      return c.code || '';
    }
    return '';
  }).filter(Boolean);

  const dataAttrs = 'data-title="' + escapeHtml((item.title || '').toLowerCase()) + '"'
    + ' data-type="' + escapeHtml(type) + '"'
    + ' data-genres="' + escapeHtml(genreSlugs.join(',')) + '"'
    + ' data-countries="' + escapeHtml(countryCodes.join(',')) + '"'
    + ' data-year="' + escapeHtml(String(year)) + '"'
    + ' data-rating="' + escapeHtml(String(rating || 0)) + '"'
    + ' data-views="' + escapeHtml(String(views)) + '"'
    + ' data-release-date="' + escapeHtml(releaseDate) + '"'
    + ' data-created="' + escapeHtml(created) + '"';

  const card = document.createElement('div');
  card.className = 'trend-card';
  card.setAttribute('data-attrs', dataAttrs);
  card.dataset.title = (item.title || '').toLowerCase();
  card.dataset.type = type;
  card.dataset.genres = genreSlugs.join(',');
  card.dataset.countries = countryCodes.join(',');
  card.dataset.year = year;
  card.dataset.rating = rating || 0;
  card.dataset.views = views;
  card.dataset.releaseDate = releaseDate;
  card.dataset.created = created;

  const poster = item.poster || '';
  const watchUrl = item.watchUrl || item.watch_url || '#';

  card.innerHTML = `
    <a href="${escapeHtml(watchUrl)}" class="card-link">
      <div class="card-poster">
        ${poster ? `<img src="${escapeHtml(poster)}" alt="${escapeHtml(item.title || 'Untitled')}" loading="lazy">` : ''}
        ${isFeatured ? '<span class="card-badge hot">HOT</span>' : ''}
      </div>
      <div class="card-info">
        <h3 class="card-title">${escapeHtml(item.title || 'Untitled')}</h3>
        <div class="card-meta">
          <span class="card-label">${escapeHtml(typeLabel)}</span>
          ${year ? `<span class="card-year">${escapeHtml(String(year))}</span>` : ''}
          ${rating ? `<span class="card-rating">★ ${escapeHtml(String(rating))}</span>` : ''}
        </div>
      </div>
    </a>
  `;

  return card;
}

function initArchiveInfiniteScroll() {
  const sentinel = document.getElementById('archiveInfiniteSentinel');
  if (!sentinel) return;

  // Set initial visibility
  updateSentinelVisibility();

  const loadMore = () => {
    loadMoreArchiveItems();
  };

  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => {
      // Only trigger if sentinel is visible and we have more items
      if (entries.some((entry) => entry.isIntersecting && !sentinel.hidden)) {
        loadMore();
      }
    }, {
      rootMargin: '360px 0px'
    });
    observer.observe(sentinel);
  } else {
    // Fallback for browsers without IntersectionObserver
    window.addEventListener('scroll', () => {
      if (sentinel.hidden) return;

      const sentinelRect = sentinel.getBoundingClientRect();
      const isVisible = sentinelRect.top <= window.innerHeight + 360;

      if (isVisible) {
        loadMore();
      }
    }, { passive: true });
  }
}

// Expose functions to global scope for inline event handlers
window.togglePanel = togglePanel;
window.openFilterDrawer = openFilterDrawer;
window.closeFilterDrawer = closeFilterDrawer;
window.updateRating = updateRating;
window.applyFilters = applyFilters;
window.resetFilters = resetFilters;
window.removeFilter = removeFilter;
window.clearAllFilters = clearAllFilters;
window.filterGenreList = filterGenreList;
window.filterCountryList = filterCountryList;
window.fillSearch = fillSearch;
window.sortCards = sortCards;

document.addEventListener('DOMContentLoaded', initArchivePage);
