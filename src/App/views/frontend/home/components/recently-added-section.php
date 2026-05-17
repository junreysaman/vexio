<section id="now-airing">
  <div class="container">
    <div class="sec-head">
      <h2 class="sec-title">
        <div class="sec-title-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="5 3 19 12 5 21 5 3"/></svg>
        </div>
        Recently <span class="accent">Added</span>
      </h2>
      <a href="#" class="see-all">All Titles <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
    </div>
    <div class="hrow-wrap">
      <button class="hrow-btn prev" onclick="scrollRow('airing-row',-1)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 18l-6-6 6-6"/></svg></button>
      <div class="hrow" id="airing-row">
      <?php if (!empty($recentlyAdded) && is_array($recentlyAdded)): ?>
        <?php foreach ($recentlyAdded as $item): ?>
          <?php
            $title = htmlspecialchars((string) ($item['title'] ?? 'Untitled'), ENT_QUOTES);
            $poster = htmlspecialchars((string) ($item['poster'] ?? 'https://picsum.photos/seed/vexio-placeholder-poster/400/600'), ENT_QUOTES);
            $genres = !empty($item['genres']) && is_array($item['genres'])
              ? $item['genres']
              : [['name' => (string) ($item['genre'] ?? 'Unknown'), 'url' => '/genre/' . rawurlencode(strtolower(str_replace(' ', '-', (string) ($item['genre'] ?? 'unknown'))))]];
            $year = htmlspecialchars((string) ($item['year'] ?? 'N/A'), ENT_QUOTES);
            $score = (string) ($item['score'] ?? 'N/A');
            $watchUrl = htmlspecialchars((string) ($item['watchUrl'] ?? '#'), ENT_QUOTES);
            $badgeText = !empty($item['is_featured']) ? 'Hot' : 'New';
            $badgeClass = !empty($item['is_featured']) ? 'badge-hot' : 'badge-new';
            $primaryGenre = $genres[0] ?? ['name' => 'Unknown', 'url' => '#'];
            $genreName = (string) ($primaryGenre['name'] ?? 'Unknown');
            $genreUrl = (string) ($primaryGenre['url'] ?? '#');
          ?>
          <div class="acard">
            <a class="archive-card-link" href="<?= $watchUrl ?>"<?= $watchUrl === '#' ? ' onclick="event.preventDefault();showToast(\'Watch unavailable\')"' : '' ?>>
              <div class="acard-thumb">
                <img src="<?= $poster ?>" alt="<?= $title ?> poster" loading="lazy">
                <div class="acard-badge <?= $badgeClass ?>"><?= $badgeText ?></div>
                <?php if ($score !== '' && $score !== 'N/A'): ?>
                  <div class="acard-score"><svg viewBox="0 0 24 24" fill="var(--gold)" stroke="none" width="12" height="12"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg><?= htmlspecialchars($score, ENT_QUOTES) ?></div>
                <?php endif; ?>
                <div class="acard-overlay">
                  <div class="acard-play"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg></div>
                </div>
              </div>
              <div class="acard-title"><?= $title ?></div>
            </a>
            <div class="acard-meta">
              <a class="card-genre-link" href="<?= htmlspecialchars($genreUrl, ENT_QUOTES) ?>"><?= htmlspecialchars($genreName, ENT_QUOTES) ?></a>
              <span class="acard-dot"></span>
              <span class="acard-year"><?= $year ?></span>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="acard">
          <div class="acard-thumb">
            <img src="https://picsum.photos/seed/vexio-placeholder/400/600" alt="No recently added titles" loading="lazy">
            <div class="acard-overlay"><div class="acard-play"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg></div></div>
          </div>
          <div class="acard-title">No recently added titles</div>
          <div class="acard-meta"><span>Try again later</span></div>
        </div>
      <?php endif; ?>
      </div>
      <button class="hrow-btn next" onclick="scrollRow('airing-row',1)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg></button>
    </div>
  </div>
</section>
