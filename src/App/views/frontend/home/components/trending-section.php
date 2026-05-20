<?php use App\Support\MediaImage; ?>
<section id="trending">
  <div class="container">
    <div class="sec-head">
      <h2 class="sec-title">
        <div class="sec-title-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>
        </div>
        Trending <span class="accent">Now</span>
      </h2>
      <a href="#" class="see-all">View All <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
    </div>
    <?php
      $trendingItems = !empty($trending) && is_array($trending) ? $trending : [];
      $featured = $trendingItems[0] ?? null;
      $listItems = $featured ? array_slice($trendingItems, 1) : [];
      $typeLabel = function (string $type): string {
          return match ($type) {
              'movie' => 'Movie',
              'tv_show' => 'TV Show',
              default => ucfirst(str_replace('_', ' ', $type)),
          };
      };
      $itemFormat = function (string $type): string {
          return $type === 'tv_show' ? 'Series' : 'Movie';
      };
      $tagNames = function (string $genre): array {
          return array_filter(array_map('trim', explode('/', $genre)));
      };
    ?>
    <div class="trending-layout">
      <?php
        $featuredWatchUrlRaw = (string) ($featured['watchUrl'] ?? '#');
        $featuredWatchUrl = htmlspecialchars($featuredWatchUrlRaw !== '' ? $featuredWatchUrlRaw : '#', ENT_QUOTES);
      ?>
      <a class="trend-feature" id="trendFeature" href="<?= $featuredWatchUrl ?>"<?= $featuredWatchUrlRaw === '' || $featuredWatchUrlRaw === '#' ? ' onclick="event.preventDefault();showToast(\'Watch unavailable\')"' : '' ?>>
        <div class="trend-feature-bg">
          <?php
            $featureBackdrop = is_array($featured['backdrop_media'] ?? null)
                ? $featured['backdrop_media']
                : MediaImage::fromString((string) ($featured['backdrop'] ?? 'https://picsum.photos/seed/vexio-trending-feature/1200/700'), 'spotlight');
            echo $this->includePartial('/frontend/partials/media-image', [
                'media' => $featureBackdrop,
                'alt' => '',
                'loading' => 'eager',
            ]);
          ?>
        </div>
        <div class="trend-feature-gradient"></div>
        <span class="tf-rank">01</span>
        <div class="trend-feature-body">
          <div class="tf-tags">
            <?php if ($featured): ?>
              <?php foreach (array_slice($tagNames((string) ($featured['genre'] ?? '')), 0, 2) as $tag): ?>
                <span class="tf-tag"><?= htmlspecialchars($tag, ENT_QUOTES) ?></span>
              <?php endforeach; ?>
              <span class="tf-tag" style="color:var(--cyan);border:1px solid rgba(0,200,240,.3);background:rgba(0,200,240,.08);"><?= htmlspecialchars($typeLabel((string) ($featured['type'] ?? '')), ENT_QUOTES) ?></span>
            <?php else: ?>
              <span class="tf-tag">No trending items</span>
            <?php endif; ?>
          </div>
          <div class="tf-title"><?= htmlspecialchars($featured['title'] ?? 'No Trending Title', ENT_QUOTES) ?></div>
          <div class="tf-meta">
            <span class="tf-rating"><?= htmlspecialchars((string) ($featured['score'] ?? 'N/A'), ENT_QUOTES) ?></span>
            <span><?= htmlspecialchars($itemFormat((string) ($featured['type'] ?? 'movie')), ENT_QUOTES) ?></span>
            <?php if (!empty($featured['genre'])): ?>
              <span style="background:rgba(0,200,240,.1);color:var(--cyan);border:1px solid rgba(0,200,240,.2);padding:2px 7px;border-radius:4px;font-size:10px;font-weight:700;">
                <?= htmlspecialchars((string) ($featured['genre'] ?? ''), ENT_QUOTES) ?>
              </span>
            <?php endif; ?>
          </div>
          <div class="tf-actions">
            <span class="tf-play"><svg viewBox="0 0 24 24" fill="currentColor" width="14" height="14"><path d="M8 5v14l11-7z"/></svg>Watch Now</span>
            <button type="button" class="tf-add" onclick="event.stopPropagation();event.preventDefault();showToast('Added to watchlist!')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>Add to List</button>
          </div>
        </div>
      </a>
      <div>
        <div class="trend-tabs">
          <button class="trend-tab active" data-filter="all">All</button>
          <button class="trend-tab" data-filter="tv_show">TV Shows</button>
          <button class="trend-tab" data-filter="movie">Movies</button>
        </div>
        <div class="trend-list-title">This Week's Top Picks</div>
        <div class="trend-list" id="trendList">
          <?php if ($listItems !== []): ?>
            <?php foreach ($listItems as $index => $item): ?>
              <?php
                $title = (string) ($item['title'] ?? 'Untitled');
                $posterMedia = is_array($item['poster_media'] ?? null)
                    ? $item['poster_media']
                    : MediaImage::fromString((string) ($item['poster'] ?? 'https://picsum.photos/seed/vexio-trending/180/240'), 'thumb');
                $watchUrlRaw = (string) ($item['watchUrl'] ?? '#');
                $watchUrl = htmlspecialchars($watchUrlRaw !== '' ? $watchUrlRaw : '#', ENT_QUOTES);
                $itemType = (string) ($item['type'] ?? 'unknown');
                $metaText = htmlspecialchars($item['genre'] . ' / ' . $itemFormat($itemType), ENT_QUOTES);
              ?>
              <a class="tl-item" href="<?= $watchUrl ?>" data-type="<?= htmlspecialchars($itemType, ENT_QUOTES) ?>"<?= $watchUrlRaw === '' || $watchUrlRaw === '#' ? ' onclick="event.preventDefault();showToast(\'Watch unavailable\')"' : '' ?>>
                <span class="tl-rank <?= $index < 3 ? 'r' . ($index + 2) : 'rn' ?>"><?= $index + 2 ?></span>
                <div class="tl-thumb">
                  <?php echo $this->includePartial('/frontend/partials/media-image', [
                      'media' => $posterMedia,
                      'alt' => $title . ' poster',
                      'loading' => 'lazy',
                  ]); ?>
                </div>
                <div class="tl-info">
                  <div class="tl-title"><?= htmlspecialchars($title, ENT_QUOTES) ?></div>
                  <div class="tl-sub"><span><?= $metaText ?></span></div>
                </div>
                <div class="tl-score"><?= htmlspecialchars((string) ($item['score'] ?? 'N/A'), ENT_QUOTES) ?></div>
              </a>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="tl-item">
              <div class="tl-thumb"><img src="https://picsum.photos/seed/vexio-trending-empty/180/240" alt="No trending titles" loading="lazy"></div>
              <div class="tl-info">
                <div class="tl-title">No trending titles available</div>
                <div class="tl-sub"><span>Check back soon</span></div>
              </div>
              <div class="tl-score">—</div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>
