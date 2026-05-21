<section id="now-airing" class="alt">
  <div class="container">
    <div class="sec-head">
      <h2 class="sec-title">
        <div class="sec-title-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
        </div>
        Now <span class="accent">Airing</span>
      </h2>
      <a href="/archive/browse" class="see-all">Browse Shows <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
    </div>
    <div class="hrow-wrap">
      <button class="hrow-btn prev" onclick="scrollRow('now-airing-row',-1)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 18l-6-6 6-6"/></svg></button>
      <div class="hrow" id="now-airing-row">
      <?php if (!empty($nowAiring) && is_array($nowAiring)): ?>
        <?php foreach ($nowAiring as $item): ?>
          <?php
            $score = (string) ($item['score'] ?? '');
            $rating = $score !== '' && $score !== 'N/A' ? (float) $score : null;
            $releaseDate = (string) ($item['release_date'] ?? '');
            $releaseYear = (string) ($item['year'] ?? '');
            $releaseTimestamp = $releaseDate !== '' ? strtotime($releaseDate) : false;
            $badgeText = $releaseTimestamp !== false ? date('M j', $releaseTimestamp) : 'AIRING';

            echo $this->includePartial('/frontend/partials/card', [
              'cardTitle'      => (string) ($item['title'] ?? 'Untitled'),
              'cardPosterMedia'=> is_array($item['poster_media'] ?? null) ? $item['poster_media'] : null,
              'cardPoster'     => (string) ($item['poster'] ?? ''),
              'cardWatchUrl'   => (string) ($item['watchUrl'] ?? '#'),
              'cardLabel'      => 'TV Show',
              'cardBadge'      => $badgeText,
              'cardBadgeClass' => 'new',
              'cardRating'     => $rating,
              'cardYear'       => $releaseYear,
            ]);
          ?>
        <?php endforeach; ?>
      <?php else: ?>
        <?php echo $this->includePartial('/frontend/partials/card', [
          'cardTitle'    => 'No airing shows yet',
          'cardPoster'   => '',
          'cardWatchUrl' => '#',
          'cardLabel'    => '',
          'cardBadge'    => '',
          'cardYear'     => '',
        ]); ?>
      <?php endif; ?>
      </div>
      <button class="hrow-btn next" onclick="scrollRow('now-airing-row',1)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg></button>
    </div>
  </div>
</section>
