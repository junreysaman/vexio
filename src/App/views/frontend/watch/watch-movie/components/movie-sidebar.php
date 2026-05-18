<!-- ══ SIDEBAR ══ -->
    <div class="watch-sidebar">

      <!-- Sidebar Header -->
      <div class="sidebar-head">
        <div class="sidebar-title">UP NEXT</div>
        <div class="sidebar-sort" onclick="showToast('Sort changed')">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
          Autoplay ON
        </div>
      </div>

      <!-- Dummy Ad -->
      <div class="sidebar-ad-block">
        <div style="padding:12px;background:var(--bg3);border:1px dashed var(--border);border-radius:8px;text-align:center;color:var(--muted);font-size:12px;">
          💡 Advertisement Space
        </div>
      </div>

      <!-- Up Next Items - Related Movies -->
      <div style="overflow-y:auto;flex:1;">
        <?php $relatedItems = $related ?? []; ?>
        <?php if (!empty($relatedItems)): ?>
          <?php foreach ($relatedItems as $idx => $rel): ?>
            <?php
            $relPoster = $rel['poster_image'] ?? $rel['poster_url'] ?? '';
            $relTitle = $rel['title'] ?? 'Unknown';
            $relYear = $rel['release_year'] ?? 'N/A';
            $relRating = $rel['tmdb_rating'] ?? 'N/A';
            $relGenres = $rel['genres'] ?? 'Unknown';
            $relWatchUrl = $rel['watchUrl'] ?? $rel['watch_url'] ?? '#';
            ?>
            <a href="<?= escape($relWatchUrl) ?>" class="up-next-card" onclick="return true;">
              <div class="unc-thumb c<?= ($idx % 8) + 1 ?>">
                <?php if ($relPoster): ?>
                  <img src="<?= escape($relPoster) ?>" alt="<?= escape($relTitle) ?>" style="width:100%;height:100%;object-fit:cover;">
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
                <div class="unc-meta"><?= escape(substr($relGenres, 0, 20)) ?> · <?= (int) $relYear ?></div>
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

        <!-- Dummy Ad Section 2 -->
        <div style="padding:12px;margin:16px;background:var(--bg3);border:1px dashed var(--border);border-radius:8px;text-align:center;color:var(--muted);font-size:12px;">
          📢 Advertisement - Premium Content
        </div>
      </div><!-- /overflow-y -->
    </div><!-- /sidebar -->

</div>