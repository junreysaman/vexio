<section id="new-episodes" class="alt">
  <div class="container">
    <div class="sec-head">
      <h2 class="sec-title">
        <div class="sec-title-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M8 21h8"/><path d="M12 17v4"/></svg>
        </div>
        New <span class="accent">Episodes</span>
      </h2>
      <!-- <a href="/archive/browse" class="see-all">Browse Shows <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a> -->
    </div>
    <div class="hrow-wrap">
      <button class="hrow-btn prev" onclick="scrollRow('episodes-row',-1)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 18l-6-6 6-6"/></svg></button>
      <div class="hrow episode-row" id="episodes-row">
      <?php if (!empty($newEpisodes) && is_array($newEpisodes)): ?>
        <?php foreach ($newEpisodes as $episode): ?>
          <?php
            $backdropMedia = is_array($episode['backdrop_media'] ?? null) ? $episode['backdrop_media'] : null;
            $watchUrl = (string) ($episode['watchUrl'] ?? '#');
            $airDate = (string) ($episode['air_date'] ?? '');
            $airLabel = $airDate !== '' ? date('M j, Y', strtotime($airDate) ?: time()) : 'New';
          ?>
          <a class="episode-card-home" href="<?= escape($watchUrl) ?>">
            <div class="episode-card-bg">
              <?php if ($backdropMedia && ($backdropMedia['src'] ?? '') !== ''): ?>
                <?php echo $this->includePartial('/frontend/partials/media-image', [
                  'media' => $backdropMedia,
                  'alt' => (string) ($episode['show_title'] ?? 'Episode') . ' backdrop',
                  'loading' => 'lazy',
                ]); ?>
              <?php else: ?>
                <div class="episode-card-ph"><?= escape((string) ($episode['show_title'] ?? 'TV Show')) ?></div>
              <?php endif; ?>
              <span class="episode-card-label"><?= escape((string) ($episode['label'] ?? 'Episode')) ?></span>
              <span class="episode-card-play"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg></span>
            </div>
            <div class="episode-card-info">
              <div class="episode-card-show"><?= escape((string) ($episode['show_title'] ?? 'TV Show')) ?></div>
              <div class="episode-card-title"><?= escape((string) ($episode['title'] ?? 'Episode')) ?></div>
              <div class="episode-card-meta">
                <span><?= escape($airLabel) ?></span>
                <?php if (($episode['score'] ?? 'N/A') !== 'N/A'): ?><strong><?= escape((string) $episode['score']) ?></strong><?php endif; ?>
              </div>
            </div>
          </a>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="episode-card-home is-empty">
          <div class="episode-card-bg"><div class="episode-card-ph">No episodes</div></div>
          <div class="episode-card-info">
            <div class="episode-card-show">New Episodes</div>
            <div class="episode-card-title">No published episodes yet</div>
            <div class="episode-card-meta"><span>Check back soon</span></div>
          </div>
        </div>
      <?php endif; ?>
      </div>
      <button class="hrow-btn next" onclick="scrollRow('episodes-row',1)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg></button>
    </div>
  </div>
</section>
