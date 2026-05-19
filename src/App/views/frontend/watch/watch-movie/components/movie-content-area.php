<?php
// Extract data from $item
$poster = $item['poster_image'] ?? $item['poster_url'] ?? '';
$backdrop = $item['backdrop_image'] ?? $item['backdrop_url'] ?? $poster;
$title = $item['title'] ?? 'Unknown Title';
$genres = $item['genres'] ?? '';
$genreNames = is_array($item['genre_names'] ?? null)
  ? array_filter(array_map('trim', $item['genre_names']))
  : array_filter(array_map('trim', explode(',', (string) $genres)));
$year = $item['release_year'] ?? 'N/A';
$rating = $item['tmdb_rating'] ?? 'N/A';
$votes = number_format((int) ($item['tmdb_vote_count'] ?? 0));
$views = number_format((int) ($item['views'] ?? 0));
$runtime = $item['runtime_minutes'] ?? null;
$releaseDateRaw = $item['release_date'] ?? $item['release_year'] ?? '';
$releaseDate = 'N/A';
if ($releaseDateRaw !== '') {
  $releaseTimestamp = strtotime((string) $releaseDateRaw);
  $releaseDate = $releaseTimestamp ? date('F j, Y', $releaseTimestamp) : (string) $releaseDateRaw;
}
$language = $item['original_language'] ?? 'EN';
$country = $item['country'] ?? $item['origin_country'] ?? 'N/A';
$rated = $item['rated'] ?? 'PG-13';
$synopsis = $item['synopsis'] ?? 'No synopsis available.';
$tagline = $item['tagline'] ?? '';
$imdbId = $item['imdb_id'] ?? '';
$budget = $item['budget'] ?? null;
$revenue = $item['revenue'] ?? null;
?>
<!-- CONTENT AREA -->
      <div class="container">
        <div class="content-pad">

          <!-- MOVIE INFO -->
          <div class="movie-info-wrap">
            <div class="movie-poster c1">
              <div class="movie-poster-badge">4K HDR</div>
              <?php if ($poster): ?>
                <img src="<?= escape($poster) ?>" alt="<?= escape($title) ?>" style="width:100%;height:100%;object-fit:cover;">
              <?php else: ?>
                <?= strtoupper(substr($title, 0, 20)) ?>
              <?php endif; ?>
            </div>
            <div class="movie-details">
              <div class="movie-type-pill">
                <svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                Movie · <?= escape((string) $genres) ?> · <?= (int) $year ?>
              </div>
              <div class="movie-title"><?= escape($title) ?></div>
              <div class="movie-meta-row">
                <span class="mmeta-tag hd">4K HDR</span>
                <span class="mmeta-tag sub">SUB</span>
                <span class="mmeta-tag dub">DUB</span>
                <div class="mmeta-rating">
                  <svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                  <?= escape((string) $rating) ?>
                </div>
                <div class="mmeta-dot"></div>
                <span style="font-size:12px;color:var(--muted2)"><?php if ($runtime): ?><?= floor($runtime / 60) ?>h <?= $runtime % 60 ?>m<?php else: ?>N/A<?php endif; ?></span>
                <div class="mmeta-dot"></div>
                <span style="font-size:12px;color:var(--muted2)"><?= (int) $year ?></span>
                <div class="mmeta-dot"></div>
                <span style="font-size:12px;color:var(--muted2)"><?= escape($rated) ?></span>
              </div>
              <div class="movie-desc">
                <?= escape($synopsis) ?>
              </div>
              <?php if ($tagline): ?>
                <div style="font-size:13px;color:var(--muted);margin-bottom:8px;font-style:italic;">
                  "<?= escape($tagline) ?>"
                </div>
              <?php endif; ?>
              <div class="movie-actions">
                <button class="btn-primary" onclick="initPlay()">
                  <svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                  Watch Now
                </button>
                <button class="btn-secondary" onclick="showToast('Added to Watchlist ✓')">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                  Watchlist
                </button>
                <button class="btn-icon liked" id="likeBtn" onclick="toggleLike()">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                </button>
                <button class="btn-icon" onclick="showToast('Link copied to clipboard!')">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>
                </button>
                <button class="btn-icon" onclick="showToast('Download started!')">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                </button>
              </div>
            </div>
          </div>

          <!-- STATS GRID -->
          <div class="details-grid">
            <div class="detail-card">
              <div class="detail-label">Rating</div>
              <div class="detail-val gold">★ <?= escape((string) $rating) ?> / 10</div>
            </div>
            <div class="detail-card">
              <div class="detail-label">Runtime</div>
              <div class="detail-val"><?php if ($runtime): ?><?= floor($runtime / 60) ?>h <?= $runtime % 60 ?>m<?php else: ?>N/A<?php endif; ?></div>
            </div>
            <div class="detail-card">
              <div class="detail-label">Release</div>
              <div class="detail-val"><?= escape((string) $releaseDate) ?></div>
            </div>
            <div class="detail-card">
              <div class="detail-label">Language</div>
              <div class="detail-val"><?= strtoupper(escape($language)) ?></div>
            </div>
            <div class="detail-card">
              <div class="detail-label">Country</div>
              <div class="detail-val"><?= escape((string) $country) ?></div>
            </div>
            <div class="detail-card">
              <div class="detail-label">Views</div>
              <div class="detail-val accent"><?= $views ?></div>
            </div>
            <div class="detail-card">
              <div class="detail-label">Quality</div>
              <div class="detail-val">4K · HDR10</div>
            </div>
            <div class="detail-card">
              <div class="detail-label">Votes</div>
              <div class="detail-val" style="color:var(--green)"><?= $votes ?></div>
            </div>
          </div>

          <!-- GENRES -->
          <div class="genres-row">
            <?php if ($genreNames !== []): ?>
              <?php foreach (array_slice($genreNames, 0, 6) as $g): ?>
                <span class="genre-pill"><?= escape($g) ?></span>
              <?php endforeach; ?>
            <?php else: ?>
              <span class="genre-pill">No genres</span>
            <?php endif; ?>
          </div>

          <?= $this->includePartial('/frontend/watch/watch-movie/ad/movie-midpage-ad') ?>

          <!-- RATING WIDGET -->
          <div class="rating-widget">
            <div class="rating-score-big">
              <div class="rsb-num">8.7</div>
              <div class="rsb-stars">
                <svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                <svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                <svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                <svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                <svg viewBox="0 0 24 24" fill="currentColor" class="empty" style="color:var(--gold);opacity:.4"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
              </div>
              <div class="rsb-count">24,891 ratings</div>
            </div>
            <div class="rating-bars">
              <div class="rbar-row"><span class="rbar-label">5★</span><div class="rbar-track"><div class="rbar-fill" style="width:68%;"></div></div><span class="rbar-count">16.9k</span></div>
              <div class="rbar-row"><span class="rbar-label">4★</span><div class="rbar-track"><div class="rbar-fill" style="width:20%;"></div></div><span class="rbar-count">5.0k</span></div>
              <div class="rbar-row"><span class="rbar-label">3★</span><div class="rbar-track"><div class="rbar-fill" style="width:8%;"></div></div><span class="rbar-count">2.0k</span></div>
              <div class="rbar-row"><span class="rbar-label">2★</span><div class="rbar-track"><div class="rbar-fill" style="width:3%;"></div></div><span class="rbar-count">0.7k</span></div>
              <div class="rbar-row"><span class="rbar-label">1★</span><div class="rbar-track"><div class="rbar-fill" style="width:1%;"></div></div><span class="rbar-count">0.2k</span></div>
            </div>
            <div class="rating-user-wrap">
              <div class="rating-user-label">Rate this movie</div>
              <div class="user-stars" id="userStars">
                <div class="user-star" onclick="rateMovie(1)"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg></div>
                <div class="user-star" onclick="rateMovie(2)"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg></div>
                <div class="user-star" onclick="rateMovie(3)"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg></div>
                <div class="user-star" onclick="rateMovie(4)"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg></div>
                <div class="user-star" onclick="rateMovie(5)"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg></div>
              </div>
            </div>
          </div>

          <!-- TABS -->
          <div class="content-tabs">
            <button class="ctab active" onclick="switchTab('comments',this)">💬 Comments <span style="font-size:11px;opacity:.5;">(347)</span></button>
            <button class="ctab" onclick="switchTab('related',this)">🎬 More Like This</button>
            <button class="ctab" onclick="switchTab('details',this)">📋 Full Details</button>
          </div>

          <!-- Comments -->
          <div class="tab-panel active" id="tab-comments">
            <?= $this->includePartial('/frontend/watch/components/comments', [
              'commentOwnerType' => 'item',
              'commentOwnerId' => (int) ($item['id'] ?? 0),
              'comments' => $comments ?? [],
              'commentCount' => $commentCount ?? 0,
              'commentPlaceholder' => 'Share your thoughts on ' . $title . '...',
            ]) ?>
          </div>

          <!-- RELATED TAB -->
          <div class="tab-panel" id="tab-related">
            <div class="trend-grid">
              <?php foreach (array_slice(($related ?? []), 0, 6) as $relatedItem): ?>
                <?php
                  $rType = (string) ($relatedItem['type'] ?? 'movie');
                  echo $this->includePartial('/frontend/partials/card', [
                    'cardTitle'    => (string) ($relatedItem['title'] ?? 'Untitled'),
                    'cardPoster'   => (string) (($relatedItem['poster_image'] ?? '') ?: ($relatedItem['poster_url'] ?? '')),
                    'cardWatchUrl' => (string) ($relatedItem['watchUrl'] ?? $relatedItem['watch_url'] ?? '#'),
                    'cardLabel'    => $rType === 'tv_show' ? 'TV Show' : 'Movie',
                    'cardBadge'    => '',
                    'cardRating'   => is_numeric($relatedItem['tmdb_rating'] ?? null) ? (float) $relatedItem['tmdb_rating'] : null,
                    'cardYear'     => (string) ($relatedItem['release_year'] ?? ''),
                  ]);
                ?>
              <?php endforeach; ?>
              <?php if (empty($related)): ?>
                <p style="color:var(--muted2);font-size:13px;grid-column:1/-1;">No related titles found.</p>
              <?php endif; ?>
            </div>
          </div>

          <!-- DETAILS TAB -->
          <div class="tab-panel" id="tab-details">
            <div style="display:grid;gap:14px;">
              <div class="detail-card" style="padding:20px;">
                <div class="detail-label" style="margin-bottom:10px;">Synopsis</div>
                <div style="font-size:13px;color:var(--muted2);line-height:1.8;">
                  <?= nl2br(escape($synopsis)) ?>

                </div>
              </div>
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="detail-card">
                  <div class="detail-label">IMDb</div>
                  <div class="detail-val"><?= escape((string) ($imdbId ?: 'N/A')) ?></div>
                </div>
                <div class="detail-card">
                  <div class="detail-label">Budget</div>
                  <div class="detail-val"><?= $budget ? '$' . number_format((float) $budget) : 'N/A' ?></div>
                </div>
                <div class="detail-card">
                  <div class="detail-label">Box Office</div>
                  <div class="detail-val"><?= $revenue ? '$' . number_format((float) $revenue) : 'N/A' ?></div>
                </div>
              </div>
            </div>
          </div>

        </div><!-- /content-pad -->
      </div><!-- /container -->

      <?= $this->includePartial('/frontend/watch/watch-movie/ad/movie-footer-ad') ?>

    </div><!-- /watch-main -->


