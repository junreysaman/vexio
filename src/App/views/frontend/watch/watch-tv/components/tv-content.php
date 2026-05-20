<?php
use App\Support\MediaImage;

$showTitle = (string) ($show['title'] ?? 'TV Show');
$posterMedia = MediaImage::posterFromRow($show, 'detail');
$currentSeason = (int) ($episode['season_number'] ?? 1);
$currentEpisode = (int) ($episode['episode_number'] ?? 1);
$episodeTitle = (string) (($episode['episode_name'] ?? '') ?: ($episode['title'] ?? 'Episode ' . $currentEpisode));
$synopsis = (string) (($episode['synopsis'] ?? '') ?: (($show['synopsis'] ?? '') ?: 'No synopsis available.'));
$genres = (string) ($show['genres'] ?? '');
$genreNames = is_array($show['genre_names'] ?? null)
  ? array_filter(array_map('trim', $show['genre_names']))
  : array_filter(array_map('trim', explode(',', $genres)));
$rating = $show['tmdb_rating'] ?? 'N/A';
$votes = number_format((int) ($show['tmdb_vote_count'] ?? 0));
$views = number_format((int) ($show['views'] ?? 0));
$releaseYear = $show['release_year'] ?? 'N/A';
$releaseDate = $show['release_date'] ?? $releaseYear;
$premiered = ($releaseDate && $releaseDate !== 'N/A' && strtotime((string) $releaseDate) !== false)
  ? date('F j, Y', strtotime((string) $releaseDate))
  : (string) $releaseDate;
$status = !empty($show['in_production']) ? 'Ongoing' : (($show['tmdb_status'] ?? '') ?: 'Published');
$seasonCount = (int) (($show['number_of_seasons'] ?? 0) ?: count($seasons ?? []));
$episodeCount = (int) (($show['number_of_episodes'] ?? 0) ?: count($episodes ?? []));
$runtime = (int) ($show['runtime_minutes'] ?? 0);
$runtimeLabel = $runtime > 0 ? $runtime . 'm' : 'Episode';
?>
<div class="container">
  <div class="content-pad">

    <div class="show-info-wrap">
      <div class="show-poster c1">
        <div class="show-poster-badge">S<?= $seasonCount ?: $currentSeason ?> <?= escape(strtoupper($status)) ?></div>
        <?php if (MediaImage::srcOnly($posterMedia) !== ''): ?>
          <?php echo $this->includePartial('/frontend/partials/media-image', [
              'media' => $posterMedia,
              'alt' => $showTitle . ' poster',
              'loading' => 'eager',
              'class' => 'show-poster-img',
          ]); ?>
        <?php else: ?>
          <?= escape(strtoupper(substr($showTitle, 0, 20))) ?>
        <?php endif; ?>
      </div>
      <div class="show-details">
        <div class="show-type-pill">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="15" rx="2"></rect><polyline points="17 2 12 7 7 2"></polyline></svg>
          TV Series &middot; <?= escape($genres !== '' ? $genres : 'Uncategorized') ?> &middot; <?= escape((string) $releaseYear) ?>
        </div>
        <div class="show-title"><?= escape($showTitle) ?></div>

        <div class="ep-now-label">
          <span class="en-badge">NOW PLAYING</span>
          <span class="en-sep">&middot;</span>
          <span class="en-title">S<?= $currentSeason ?> E<?= $currentEpisode ?> - <?= escape($episodeTitle) ?></span>
          <span class="en-sep">&middot;</span>
          <span class="en-duration"><?= escape($runtimeLabel) ?></span>
        </div>

        <div class="show-meta-row">
          <span class="mmeta-tag hd">4K HDR</span>
          <span class="mmeta-tag sub">SUB</span>
          <span class="mmeta-tag dub">DUB</span>
          <span class="mmeta-tag ongoing"><?= escape(strtoupper($status)) ?></span>
          <div class="mmeta-rating">
            <svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
            <?= escape((string) $rating) ?>
          </div>
          <div class="mmeta-dot"></div>
          <span style="font-size:12px;color:var(--muted2)"><?= number_format($seasonCount) ?> Seasons</span>
          <div class="mmeta-dot"></div>
          <span style="font-size:12px;color:var(--muted2)"><?= number_format($episodeCount) ?> Episodes</span>
          <div class="mmeta-dot"></div>
          <span style="font-size:12px;color:var(--muted2)"><?= escape((string) ($show['rated'] ?? 'NR')) ?></span>
        </div>
        <div class="show-desc"><?= escape($synopsis) ?></div>
        <div class="show-actions">
          <button class="btn-primary" onclick="initPlay()">
            <svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
            Resume S<?= $currentSeason ?> E<?= $currentEpisode ?>
          </button>
          <button class="btn-secondary" onclick="showToast('Added to Watchlist')">Watchlist</button>
          <button class="btn-icon liked" id="likeBtn" onclick="toggleLike()"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg></button>
          <button class="btn-icon" onclick="openShareModal('<?= escape(url($_SERVER['REQUEST_URI'] ?? '/')) ?>', '<?= escape($showTitle) ?>', '<?= escape($poster) ?>')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg></button>
        </div>
      </div>
    </div>

    <div class="details-grid">
      <div class="detail-card"><div class="detail-label">Rating</div><div class="detail-val gold">* <?= escape((string) $rating) ?> / 10</div></div>
      <div class="detail-card"><div class="detail-label">Episodes</div><div class="detail-val"><?= number_format($episodeCount) ?> Total</div></div>
      <div class="detail-card"><div class="detail-label">Premiered</div><div class="detail-val"><?= escape($premiered) ?></div></div>
      <div class="detail-card"><div class="detail-label">Status</div><div class="detail-val" style="color:var(--gold)"><?= escape($status) ?></div></div>
      <div class="detail-card"><div class="detail-label">Language</div><div class="detail-val"><?= escape(strtoupper((string) ($show['original_language'] ?? 'N/A'))) ?></div></div>
      <div class="detail-card"><div class="detail-label">Views</div><div class="detail-val accent"><?= $views ?></div></div>
      <div class="detail-card"><div class="detail-label">Quality</div><div class="detail-val">4K &middot; HDR10</div></div>
      <div class="detail-card"><div class="detail-label">Votes</div><div class="detail-val"><?= $votes ?></div></div>
    </div>

    <div class="genres-row">
      <?php foreach (array_slice($genreNames, 0, 7) as $genre): ?>
        <span class="genre-pill"><?= escape($genre) ?></span>
      <?php endforeach; ?>
      <?php if ($genreNames === []): ?><span class="genre-pill">No genres</span><?php endif; ?>
    </div>

        <?= $this->includePartial('frontend/watch/watch-tv/ad/tv-midpage-ad') ?>

    <div class="content-tabs">
      <button class="ctab active" onclick="switchTab('episodes',this)">Episodes</button>
      <button class="ctab" onclick="switchTab('details',this)">Details</button>
      <button class="ctab" onclick="switchTab('comments',this)">Comments</button>
    </div>

    <div class="tab-panel active" id="tab-episodes" data-episode-list data-page-size="10">
      <div class="ep-list-controls">
        <input type="search" class="ep-search" placeholder="Search episodes..." data-episode-search autocomplete="off">
        <span class="ep-count-badge"><span data-episode-visible-count><?= number_format(min(10, count($episodes ?? []))) ?> of <?= number_format(count($episodes ?? [])) ?> episodes</span> &middot; Season <?= $currentSeason ?></span>
      </div>
      <div class="ep-list">
        <?php foreach (($episodes ?? []) as $idx => $row): ?>
          <?php
          $rowSeason = (int) ($row['season_number'] ?? $currentSeason);
          $rowEpisode = (int) ($row['episode_number'] ?? ($idx + 1));
          $isCurrent = $rowSeason === $currentSeason && $rowEpisode === $currentEpisode;
          $rowTitle = (string) (($row['episode_name'] ?? '') ?: ($row['title'] ?? 'Episode ' . $rowEpisode));
          $rowPosterMedia = MediaImage::posterFromRow($row, 'thumb');
          if (MediaImage::srcOnly($rowPosterMedia) === '') {
              $rowPosterMedia = MediaImage::backdropFromRow($row, 'thumb');
          }
          $rowUrl = (string) ($row['watchUrl'] ?? $row['watch_url'] ?? '#');
          $rowSynopsis = (string) (($row['synopsis'] ?? '') ?: ($show['synopsis'] ?? ''));
          $rowSearch = strtolower($rowTitle . ' ' . $rowSynopsis);
          $rowNumberSearch = implode(' ', [
            (string) $rowEpisode,
            str_pad((string) $rowEpisode, 2, '0', STR_PAD_LEFT),
            'e' . $rowEpisode,
            'ep' . $rowEpisode,
            'episode ' . $rowEpisode,
            's' . $rowSeason . 'e' . $rowEpisode,
            's' . $rowSeason . ' e' . $rowEpisode,
          ]);
          $classes = ['ep-row'];
          if ($rowEpisode < $currentEpisode) $classes[] = 'watched';
          if ($isCurrent) $classes[] = 'current';
          ?>
          <a class="<?= escape(implode(' ', $classes)) ?>" href="<?= escape($rowUrl) ?>" data-search="<?= escape($rowSearch) ?>" data-number-search="<?= escape($rowNumberSearch) ?>"<?= $idx >= 10 ? ' hidden' : '' ?>>
            <div class="ep-thumb c<?= ($idx % 8) + 1 ?>">
              <?php if (MediaImage::srcOnly($rowPosterMedia) !== ''): ?>
                <?php echo $this->includePartial('/frontend/partials/media-image', [
                    'media' => $rowPosterMedia,
                    'alt' => $rowTitle,
                    'loading' => 'lazy',
                    'class' => 'ep-thumb-img',
                ]); ?>
              <?php else: ?><div class="ep-thumb-ph">S<?= $rowSeason ?> E<?= $rowEpisode ?></div><?php endif; ?>
              <div class="ep-play-hover"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg></div>
              <span class="ep-num-badge">E<?= $rowEpisode ?></span>
              <span class="ep-duration-badge"><?= escape($runtimeLabel) ?></span>
              <?php if ($isCurrent): ?><div class="ep-thumb-progress"><div class="ep-thumb-progress-fill" style="width:12%"></div></div><?php endif; ?>
            </div>
            <div class="ep-info">
              <div class="ep-meta-top"><span class="ep-num-label"><?= $isCurrent ? 'NOW PLAYING' : 'Episode ' . $rowEpisode ?></span><span class="ep-airdate"><?= escape((string) ($row['air_date'] ?? '')) ?></span></div>
              <div class="ep-title-text"><?= escape($rowTitle) ?></div>
              <div class="ep-desc-text"><?= escape((string) (($row['synopsis'] ?? '') ?: $show['synopsis'] ?? 'No synopsis available.')) ?></div>
            </div>
            <div class="ep-row-right">
              <?php if ($rowEpisode < $currentEpisode): ?><div class="ep-watched-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></div><?php endif; ?>
              <div class="ep-score"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg><?= escape((string) $rating) ?></div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
      <div class="ep-empty" hidden>No episodes match your search.</div>
      <div class="ep-load-more-wrap">
        <button class="btn-secondary ep-load-more" type="button" data-episode-load-more>Load More Episodes</button>
      </div>
    </div>

    <div class="tab-panel" id="tab-details">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px;">
        <div><div style="font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--muted);margin-bottom:8px;">Episode Synopsis</div><div style="font-size:13px;color:var(--muted2);line-height:1.7;"><?= escape($synopsis) ?></div></div>
        <div><div style="font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--muted);margin-bottom:8px;">Show Details</div><div style="font-size:13px;color:var(--muted2);line-height:1.7;"><?= escape((string) (($show['tagline'] ?? '') ?: ($show['synopsis'] ?? 'No additional details available.'))) ?></div></div>
      </div>
    </div>

    <div class="tab-panel" id="tab-comments">
      <?= $this->includePartial('/frontend/watch/components/comments', [
        'commentOwnerType' => 'episode',
        'commentOwnerId' => (int) ($episode['id'] ?? 0),
        'comments' => $comments ?? [],
        'commentCount' => $commentCount ?? 0,
        'commentPlaceholder' => 'Share your thoughts on this episode...',
      ]) ?>
    </div>

    <div class="rating-widget" style="margin-top:28px;">
      <div class="rating-score-big"><div class="rsb-num"><?= escape((string) $rating) ?></div><div class="rsb-count">S<?= $currentSeason ?> E<?= $currentEpisode ?> &middot; <?= $votes ?> ratings</div></div>
      <div class="rating-user-wrap"><div class="rating-user-label">Rate this episode</div><div class="user-stars"><?php for ($i = 1; $i <= 5; $i++): ?><div class="user-star" onclick="rateEp(<?= $i ?>)"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg></div><?php endfor; ?></div></div>
    </div>

    <div class="related-section">
      <div class="sec-mini-head"><div class="sec-mini-title"><div class="icon-wrap"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="15" rx="2"></rect><polyline points="17 2 12 7 7 2"></polyline></svg></div>YOU MAY ALSO <span class="accent">LIKE</span></div></div>
      <div class="trend-grid">
        <?php foreach (array_slice(($related ?? []), 0, 6) as $item): ?>
          <?php
            $rType = (string) ($item['type'] ?? 'tv_show');
            echo $this->includePartial('/frontend/partials/card', [
              'cardTitle'    => (string) ($item['title'] ?? 'Untitled'),
              'cardPosterMedia' => MediaImage::posterFromRow($item, 'card'),
              'cardPoster'   => (string) (($item['poster_image'] ?? '') ?: ($item['poster_url'] ?? '')),
              'cardWatchUrl' => (string) ($item['watchUrl'] ?? $item['watch_url'] ?? '#'),
              'cardLabel'    => $rType === 'tv_show' ? 'TV Show' : 'Movie',
              'cardBadge'    => '',
              'cardRating'   => is_numeric($item['tmdb_rating'] ?? null) ? (float) $item['tmdb_rating'] : null,
              'cardYear'     => (string) ($item['release_year'] ?? ''),
            ]);
          ?>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?= $this->includePartial('/frontend/watch/watch-movie/ad/movie-footer-ad') ?>
</div>
