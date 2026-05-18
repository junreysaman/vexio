<!-- SIDEBAR -->
    <div class="watch-sidebar">

      <div class="sidebar-head">
        <div class="sidebar-title">UP NEXT</div>
        <div class="sidebar-sort" onclick="showToast('Sort changed')">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
          Autoplay ON
        </div>
      </div>

      <?= $this->includePartial('/frontend/watch/watch-movie/ad/sidebar-ad-1') ?>

      <div style="overflow-y:auto;flex:1;">
        <?php $relatedItems = $related ?? []; ?>
        <?php if (!empty($relatedItems)): ?>
          <?php foreach ($relatedItems as $idx => $rel): ?>
            <?php
            $relBackdrop = $rel['backdrop_image'] ?? '';
            $relTitle = $rel['title'] ?? 'Unknown';
            $relYear = $rel['release_year'] ?? 'N/A';
            $relRating = $rel['tmdb_rating'] ?? 'N/A';
            $relGenres = $rel['genres'] ?? '';
            $relWatchUrl = $rel['watchUrl'] ?? $rel['watch_url'] ?? '#';
            ?>
            <a href="<?= escape($relWatchUrl) ?>" class="up-next-card" onclick="return true;">
              <div class="unc-thumb c<?= ($idx % 8) + 1 ?>">
                <?php if ($relBackdrop): ?>
                  <img src="<?= escape($relBackdrop) ?>" alt="<?= escape($relTitle) ?>" style="width:100%;height:100%;object-fit:cover;">
                <?php else: ?>
                  <div class="unc-ph"><?= strtoupper(substr($relTitle, 0, 12)) ?></div>
                <?php endif; ?>
                <div class="unc-play-icon">
                  <svg viewBox="0 0 24 24" fill="white"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                </div>
              </div>
              <div class="unc-info">
                <div class="unc-label">Recommended</div>
                <div class="unc-title"><?= escape($relTitle) ?></div>
                <div class="unc-meta"><?= escape(substr($relGenres !== '' ? $relGenres : 'Unknown', 0, 20)) ?> &middot; <?= (int) $relYear ?></div>
              </div>
              <div class="unc-score">
                <svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                <?= escape((string) $relRating) ?>
              </div>
            </a>
          <?php endforeach; ?>
        <?php else: ?>
          <div style="padding:20px;text-align:center;color:var(--muted);font-size:12px;">
            No related movies found
          </div>
        <?php endif; ?>

        <?= $this->includePartial('/frontend/watch/watch-movie/ad/sidebar-ad-2') ?>
      </div>
    </div>

</div>
