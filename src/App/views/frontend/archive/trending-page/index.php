<?= $this->start("styles") ?>
<link rel="stylesheet" href="/assets/frontend/css/trending-page.css">
<?= $this->end() ?>

<?php
use App\Support\MediaImage;

$items = is_array($items ?? null) ? $items : [];
$spotlight = is_array($spotlight ?? null) ? $spotlight : null;
$spotlightSidebar = is_array($spotlight_sidebar ?? null) ? $spotlight_sidebar : [];
$topChart = is_array($top_chart ?? null) ? $top_chart : [];
$watchedToday = is_array($watched_today ?? null) ? $watched_today : [];
$stats = is_array($stats ?? null) ? $stats : [];
$regions = is_array($regions ?? null) ? $regions : [];
$genres = is_array($genres ?? null) ? $genres : [];
?>

<?= $this->start('content') ?>

<?= $this->includePartial('frontend/archive/trending-page/components/trending-hero', [
  'total_items' => $total_items ?? count($items),
  'filters' => $filters ?? [],
]) ?>

<section id="trend-main">
  <div class="container">

    <div class="stats-banner">
      <?php foreach ($stats as $stat): ?>
        <div class="stat-item">
          <div class="stat-number stat-tone-<?= escape((string) ($stat['tone'] ?? 'gold')) ?>"><?= escape((string) ($stat['value'] ?? '0')) ?></div>
          <div class="stat-label"><?= escape((string) ($stat['label'] ?? 'Trending')) ?></div>
          <div class="stat-change up"><?= escape((string) ($stat['change'] ?? 'Live')) ?></div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="ad-unit ad-leaderboard">
      <span class="ad-label">Advertisement</span>
      <div class="ad-creative" data-ad-action="leaderboard">
        <button class="ad-close" type="button" aria-label="Close advertisement">x</button>
        <div class="ad-lb-img"></div>
        <div class="ad-lb-divider"></div>
        <div class="ad-lb-logo">VEX</div>
        <div class="ad-lb-copy">
          <div class="ad-lb-headline">Premium Streaming Picks</div>
          <div class="ad-lb-sub">Fresh simulcasts weekly - <em>featured offers</em> for Vexio viewers</div>
        </div>
        <button class="ad-lb-cta" type="button">View Offer</button>
      </div>
    </div>

    <?php if ($spotlight === null): ?>
      <div class="trend-empty">
        <h2>No trending titles yet</h2>
        <p>Publish movies or series in the catalogue and this page will build the charts automatically.</p>
        <a href="/archive/browse">Browse catalogue</a>
      </div>
    <?php else: ?>
      <div class="trend-section">
        <div class="section-header">
          <div class="section-dot"></div>
          <div class="section-title">#1 Spotlight - Most Watched</div>
          <div class="section-line"></div>
        </div>

        <div class="spotlight-wrap">
          <a class="spotlight-card trend-link-card" href="<?= escape((string) $spotlight['watch_url']) ?>" data-trending-item data-type="<?= escape((string) $spotlight['type']) ?>" data-category="<?= escape((string) $spotlight['primary_category']) ?>" data-secondary="<?= escape((string) $spotlight['secondary_category']) ?>" data-day-score="<?= (int) $spotlight['scores']['day'] ?>" data-week-score="<?= (int) $spotlight['scores']['week'] ?>" data-month-score="<?= (int) $spotlight['scores']['month'] ?>">
            <div class="sp-bg">
              <?php
                $spBackdrop = is_array($spotlight['backdrop_media'] ?? null)
                    ? $spotlight['backdrop_media']
                    : MediaImage::fromString((string) ($spotlight['backdrop'] ?? ''), 'spotlight');
                echo $this->includePartial('/frontend/partials/media-image', ['media' => $spBackdrop, 'alt' => '', 'loading' => 'eager']);
              ?>
            </div>
            <div class="sp-gradient"></div>
            <div class="sp-rank">#1</div>
            <div class="sp-content">
              <div class="sp-badges">
                <span class="sp-badge fire">#1 Today</span>
                <span class="sp-badge rank"><?= escape((string) $spotlight['genre_label']) ?></span>
                <span class="sp-badge new"><?= escape((string) $spotlight['type_label']) ?></span>
              </div>
              <div class="sp-title"><?= escape((string) $spotlight['title']) ?></div>
              <div class="sp-meta">
                <span class="hi">Rating <?= escape((string) $spotlight['rating_label']) ?></span>
                <span class="dot">-</span>
                <span><?= escape((string) $spotlight['year']) ?></span>
                <span class="dot">-</span>
                <span><?= escape((string) $spotlight['type_label']) ?></span>
                <span class="dot">-</span>
                <span class="hi"><?= escape((string) $spotlight['views_label']) ?></span>
              </div>
              <p class="sp-desc"><?= escape((string) $spotlight['synopsis']) ?></p>
              <div class="sp-actions">
                <span class="sp-btn-play">
                  <svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                  Watch Now
                </span>
                <span class="sp-btn-add">Rank #<?= (int) $spotlight['rank'] ?></span>
              </div>
            </div>
          </a>

          <div class="spotlight-sidebar">
            <?php foreach ($spotlightSidebar as $item): ?>
              <a class="sidebar-card trend-link-card" href="<?= escape((string) $item['watch_url']) ?>" data-trending-item data-type="<?= escape((string) $item['type']) ?>" data-category="<?= escape((string) $item['primary_category']) ?>" data-secondary="<?= escape((string) $item['secondary_category']) ?>" data-day-score="<?= (int) $item['scores']['day'] ?>" data-week-score="<?= (int) $item['scores']['week'] ?>" data-month-score="<?= (int) $item['scores']['month'] ?>">
                <div class="sc-bg">
                  <?php
                    $scBackdrop = is_array($item['backdrop_media'] ?? null)
                        ? $item['backdrop_media']
                        : MediaImage::fromString((string) ($item['backdrop'] ?? ''), 'spotlight');
                    echo $this->includePartial('/frontend/partials/media-image', ['media' => $scBackdrop, 'alt' => '', 'loading' => 'lazy']);
                  ?>
                </div>
                <div class="sc-grad"></div>
                <div class="sc-accent"></div>
                <div class="sc-rank-num">#<?= (int) $item['rank'] ?></div>
                <div class="sc-content">
                  <div class="sc-title"><?= escape((string) $item['title']) ?></div>
                  <div class="sc-meta"><strong><?= escape((string) $item['rating_label']) ?></strong> - <?= escape((string) $item['views_label']) ?></div>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <div class="trend-section">
        <div class="section-header">
          <div class="section-dot red"></div>
          <div class="section-title">Trending This Week</div>
          <div class="section-line"></div>
          <button class="section-see-all" type="button" data-filter-target="all">See All</button>
        </div>

        <div class="trend-grid" id="trendGrid">
          <?php foreach ($items as $item): ?>
            <a class="trend-card trend-link-card" href="<?= escape((string) $item['watch_url']) ?>" data-trending-item data-type="<?= escape((string) $item['type']) ?>" data-category="<?= escape((string) $item['primary_category']) ?>" data-secondary="<?= escape((string) $item['secondary_category']) ?>" data-day-score="<?= (int) $item['scores']['day'] ?>" data-week-score="<?= (int) $item['scores']['week'] ?>" data-month-score="<?= (int) $item['scores']['month'] ?>">
              <div class="tc-media">
                <?php
                  $tcPoster = is_array($item['poster_media'] ?? null)
                      ? $item['poster_media']
                      : MediaImage::fromString((string) ($item['poster'] ?? ''), 'card');
                  echo $this->includePartial('/frontend/partials/media-image', [
                      'media' => $tcPoster,
                      'alt' => (string) ($item['title'] ?? 'Untitled') . ' poster',
                      'loading' => 'lazy',
                  ]);
                ?>
              </div>
              <div class="tc-gradient"></div>
              <div class="tc-rank">#<?= (int) $item['rank'] ?></div>
              <div class="tc-badge <?= escape((string) ($item['secondary_category'] === 'new' ? 'new' : ($item['secondary_category'] === 'top' ? 'top' : 'hot'))) ?>"><?= escape((string) ($item['secondary_category'] === 'new' ? 'NEW' : ($item['secondary_category'] === 'top' ? 'TOP' : 'HOT'))) ?></div>
              <div class="tc-overlay"><div class="tc-play-ring"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg></div></div>
              <div class="tc-content">
                <div class="tc-title"><?= escape((string) $item['title']) ?></div>
                <div class="tc-meta">
                  <strong><?= escape((string) $item['rating_label']) ?></strong><span class="dot">-</span>
                  <span class="tc-trend-arrow up"><?= escape((string) $item['views_label']) ?></span>
                </div>
              </div>
              <div class="tc-hover-strip"></div>
            </a>
          <?php endforeach; ?>
        </div>
        <div class="trend-empty trend-filter-empty" id="trendFilterEmpty" hidden>No titles match this filter.</div>
      </div>

      <div class="trend-section two-col">
        <div>
          <div class="section-header">
            <div class="section-dot red"></div>
            <div class="section-title">Top 10 Chart</div>
            <div class="section-line"></div>
            <button class="section-see-all" type="button" data-filter-target="top">Top Rated</button>
          </div>
          <div class="rank-table" id="trendRankTable">
            <?php foreach ($topChart as $index => $item): ?>
              <a class="rank-row trend-link-card" href="<?= escape((string) $item['watch_url']) ?>" data-trending-item data-type="<?= escape((string) $item['type']) ?>" data-category="<?= escape((string) $item['primary_category']) ?>" data-secondary="<?= escape((string) $item['secondary_category']) ?>" data-day-score="<?= (int) $item['scores']['day'] ?>" data-week-score="<?= (int) $item['scores']['week'] ?>" data-month-score="<?= (int) $item['scores']['month'] ?>">
                <div class="rr-num <?= $index < 3 ? 'top3' : '' ?>"><?= $index + 1 ?></div>
                <div class="rr-thumb">
                  <?php
                    $rrPoster = is_array($item['poster_media'] ?? null)
                        ? $item['poster_media']
                        : MediaImage::fromString((string) ($item['poster'] ?? ''), 'thumb');
                    echo $this->includePartial('/frontend/partials/media-image', [
                        'media' => $rrPoster,
                        'alt' => (string) ($item['title'] ?? 'Untitled') . ' poster',
                        'loading' => 'lazy',
                    ]);
                  ?>
                </div>
                <div class="rr-info">
                  <div class="rr-title"><?= escape((string) $item['title']) ?></div>
                  <div class="rr-sub">
                    <span class="rr-type"><?= escape((string) $item['type_label']) ?></span>
                    <span><?= escape((string) $item['year']) ?></span>
                  </div>
                </div>
                <div class="rr-right">
                  <div class="rr-views"><?= escape((string) $item['views_label']) ?></div>
                  <div class="rr-change up"><?= escape((string) $item['rating_label']) ?> rating</div>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        </div>

        <div>
          <div class="section-header">
            <div class="section-dot cyan"></div>
            <div class="section-title">Most Watched Today</div>
            <div class="section-line"></div>
          </div>
          <div class="hscroll-wrap">
            <div class="hscroll-row" id="trendTodayRow">
              <?php foreach ($watchedToday as $index => $item): ?>
                <a class="hscroll-card trend-link-card" href="<?= escape((string) $item['watch_url']) ?>" data-trending-item data-type="<?= escape((string) $item['type']) ?>" data-category="<?= escape((string) $item['primary_category']) ?>" data-secondary="<?= escape((string) $item['secondary_category']) ?>" data-day-score="<?= (int) $item['scores']['day'] ?>" data-week-score="<?= (int) $item['scores']['week'] ?>" data-month-score="<?= (int) $item['scores']['month'] ?>">
                  <div class="hc-bg">
                    <?php
                      $hcBackdrop = is_array($item['backdrop_media'] ?? null)
                          ? $item['backdrop_media']
                          : MediaImage::fromString((string) ($item['backdrop'] ?? ''), 'spotlight');
                      echo $this->includePartial('/frontend/partials/media-image', ['media' => $hcBackdrop, 'alt' => '', 'loading' => 'lazy']);
                    ?>
                  </div>
                  <div class="hc-grad"></div>
                  <div class="hc-content">
                    <div class="hc-rank-tag">#<?= $index + 1 ?> TODAY</div>
                    <div class="hc-title"><?= escape((string) $item['title']) ?></div>
                    <div class="hc-meta"><strong><?= escape((string) $item['views_label']) ?></strong></div>
                  </div>
                  <div class="hc-strip"></div>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>

      <div class="trend-section">
        <div class="section-header">
          <div class="section-dot purple"></div>
          <div class="section-title">Trending by Region</div>
          <div class="section-line"></div>
        </div>
        <div class="region-grid">
          <?php foreach ($regions as $region): ?>
            <div class="region-card" data-region="<?= escape((string) $region['name']) ?>">
              <div class="rc-bg" style="background-image:url('<?= escape((string) $region['image']) ?>');"></div>
              <div class="rc-grad"></div>
              <div class="rc-accent"></div>
              <div class="rc-content">
                <div class="rc-flag"><?= escape((string) $region['flag']) ?></div>
                <div class="rc-info">
                  <div class="rc-name"><?= escape((string) $region['name']) ?></div>
                  <div class="rc-count"><strong><?= escape((string) $region['count']) ?></strong> views</div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="trend-section">
        <div class="section-header">
          <div class="section-dot"></div>
          <div class="section-title">Trending Genres</div>
          <div class="section-line"></div>
          <a class="section-see-all" href="/genres">All Genres</a>
        </div>
        <div class="genre-burst-grid">
          <?php foreach ($genres as $genre): ?>
            <a class="gb-card" href="<?= escape((string) $genre['url']) ?>" data-color="gold">
              <span class="gb-emoji">#</span>
              <div class="gb-name"><?= escape((string) $genre['name']) ?></div>
              <div class="gb-trend hot"><?= escape((string) $genre['trend']) ?></div>
              <div class="gb-bar"><div class="gb-fill" style="width:<?= (int) $genre['width'] ?>%;"></div></div>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

  </div>
</section>

<?= $this->end() ?>

<?= $this->start('scripts') ?>
<script src="/assets/frontend/js/trending-page.js?v=<?= filemtime(dirname(__DIR__, 6) . '/public/assets/frontend/js/trending-page.js') ?>"></script>
<?= $this->end() ?>