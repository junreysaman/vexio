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
            $type      = (string) ($item['type'] ?? 'movie');
            $typeLabel = $type === 'tv_show' ? 'TV Show' : 'Movie';
            $score     = (string) ($item['score'] ?? '');
            $rating    = $score !== '' && $score !== 'N/A' ? (float) $score : null;
            $badgeText  = !empty($item['is_featured']) ? 'HOT' : 'NEW';
            $badgeClass = !empty($item['is_featured']) ? 'hot' : 'new';

            echo $this->includePartial('/frontend/partials/card', [
              'cardTitle'      => (string) ($item['title'] ?? 'Untitled'),
              'cardPoster'     => (string) ($item['poster'] ?? ''),
              'cardWatchUrl'   => (string) ($item['watchUrl'] ?? '#'),
              'cardLabel'      => $typeLabel,
              'cardBadge'      => $badgeText,
              'cardBadgeClass' => $badgeClass,
              'cardRating'     => $rating,
              'cardYear'       => (string) ($item['year'] ?? ''),
            ]);
          ?>
        <?php endforeach; ?>
      <?php else: ?>
        <?php echo $this->includePartial('/frontend/partials/card', [
          'cardTitle'    => 'No recently added titles',
          'cardPoster'   => '',
          'cardWatchUrl' => '#',
          'cardLabel'    => '',
          'cardBadge'    => '',
          'cardYear'     => '',
        ]); ?>
      <?php endif; ?>
      </div>
      <button class="hrow-btn next" onclick="scrollRow('airing-row',1)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg></button>
    </div>
  </div>
</section>
