<script>
function filterGenres(term) {
  const query = String(term || '').toLowerCase().trim();
  document.querySelectorAll('[data-genre-card]').forEach((card) => {
    const haystack = card.getAttribute('data-genre') || '';
    card.hidden = query !== '' && !haystack.includes(query);
  });
}

function setGenreFilter(type, button) {
  document.querySelectorAll('.gf-pill').forEach((pill) => pill.classList.remove('active'));
  button.classList.add('active');

  document.querySelectorAll('[data-genre-section]').forEach((section) => {
    const sectionType = section.getAttribute('data-genre-section');
    section.hidden = type !== 'all' && sectionType !== type;
  });
}

window.filterGenres = filterGenres;
window.setGenreFilter = setGenreFilter;

function scrollToGenreResults() {
  if (window.location.hash !== '#genre-results') {
    return;
  }

  const results = document.getElementById('genre-results');

  if (!results) {
    return;
  }

  const navHeight = Number.parseFloat(getComputedStyle(document.documentElement).getPropertyValue('--nav-h')) || 68;
  const top = results.getBoundingClientRect().top + window.pageYOffset - navHeight - 24;
  window.scrollTo({ top: Math.max(0, top), behavior: 'auto' });
}

document.addEventListener('DOMContentLoaded', () => {
  setTimeout(scrollToGenreResults, 60);
});
setTimeout(scrollToGenreResults, 160);
</script>
