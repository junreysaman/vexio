<?php
use App\Support\MediaImage;

/** @var array<int, array<string, mixed>>|null $featured */
$slides = !empty($featured) ? $featured : [
    [
        'title' => 'No Featured Content',
        'titleHl' => '',
        'badge' => 'Featured',
        'genre' => 'Unavailable',
        'genres' => [['name' => 'Unavailable', 'url' => '/genre/unavailable']],
        'year' => 'N/A',
        'eps' => 'Movie',
        'score' => 'N/A',
        'desc' => 'There are no featured titles available at the moment.',
        'poster' => 'https://picsum.photos/seed/vexio-placeholder-poster/500/750',
        'backdrop' => 'https://picsum.photos/seed/vexio-placeholder-backdrop/1600/900',
        'watchUrl' => '#',
    ],
];
$slideCount = count($slides);
?>

<section id="hero" style="padding:0;">
  <div class="hero-progress" id="heroProgress"></div>
  <div class="hero-counter"><span id="heroNum">01</span> / <span id="heroTotal"><?= str_pad((string) max(1, $slideCount), 2, '0', STR_PAD_LEFT) ?></span></div>

  <div class="slide-track" id="slideTrack">
    <?php foreach ($slides as $index => $item): ?>
      <?php
        $title = (string) ($item['title'] ?? 'Untitled');
        $titleHl = (string) ($item['titleHl'] ?? '');
        $badge = (string) ($item['badge'] ?? 'Featured');
        $genre = (string) ($item['genre'] ?? 'Unknown');
        $genres = !empty($item['genres']) && is_array($item['genres'])
            ? $item['genres']
            : [['name' => $genre, 'url' => '/genre/' . rawurlencode(strtolower(str_replace(' ', '-', $genre)))]];
        $year = (string) ($item['year'] ?? 'N/A');
        $eps = (string) ($item['eps'] ?? 'Movie');
        $score = (string) ($item['score'] ?? 'N/A');
        $desc = (string) ($item['desc'] ?? '');
        $posterMedia = is_array($item['poster_media'] ?? null)
            ? $item['poster_media']
            : MediaImage::fromString((string) ($item['poster'] ?? 'https://picsum.photos/seed/vexio-placeholder-poster/500/750'), 'heroPoster');
        $backdropMedia = is_array($item['backdrop_media'] ?? null)
            ? $item['backdrop_media']
            : MediaImage::fromString((string) ($item['backdrop'] ?? 'https://picsum.photos/seed/vexio-placeholder-backdrop/1600/900'), 'heroBackdrop');
        $watchUrl = (string) ($item['watchUrl'] ?? '#');
      ?>
      <div class="slide<?= $index === 0 ? ' active' : '' ?>" id="slide-<?= $index ?>">
        <div class="slide-bg">
          <div class="slide-bg-inner">
            <div class="slide-bg-color">
              <div class="slide-bg-media">
                <?php echo $this->includePartial('/frontend/partials/media-image', [
                    'media' => $backdropMedia,
                    'alt' => '',
                    'loading' => $index === 0 ? 'eager' : 'lazy',
                ]); ?>
              </div>
            </div>
          </div>
          <div class="slide-noise"></div>
        </div>
        <div class="slide-gradient"></div>
        <div class="slide-poster">
          <?php echo $this->includePartial('/frontend/partials/media-image', [
              'media' => $posterMedia,
              'alt' => $title . ' poster',
              'loading' => $index === 0 ? 'eager' : 'lazy',
              'fetchpriority' => $index === 0 ? 'high' : '',
          ]); ?>
        </div>
        <div class="slide-content">
          <div class="slide-type"><span class="pulse-dot"></span><?= htmlspecialchars($badge, ENT_QUOTES) ?></div>
          <h1 class="slide-title"><?= htmlspecialchars($title, ENT_QUOTES) ?><?php if ($titleHl !== ''): ?><br><span class="hl"><?= htmlspecialchars($titleHl, ENT_QUOTES) ?></span><?php endif; ?></h1>
          <div class="slide-meta">
            <span class="smeta-tag"><?= htmlspecialchars($year, ENT_QUOTES) ?></span>
            <span class="smeta-tag"><?= htmlspecialchars($eps, ENT_QUOTES) ?></span>
            <?php foreach ($genres as $genreItem): ?>
              <?php
                $genreName = (string) ($genreItem['name'] ?? '');
                $genreUrl = (string) ($genreItem['url'] ?? '#');
              ?>
              <?php if ($genreName !== ''): ?>
                <a class="smeta-tag genre-badge" href="<?= htmlspecialchars($genreUrl, ENT_QUOTES) ?>"><?= htmlspecialchars($genreName, ENT_QUOTES) ?></a>
              <?php endif; ?>
            <?php endforeach; ?>
            <span class="smeta-rating"><?= htmlspecialchars($score, ENT_QUOTES) ?></span>
          </div>
          <p class="slide-desc"><?= htmlspecialchars($desc, ENT_QUOTES) ?></p>
          <div class="slide-actions">
            <?php if ($watchUrl !== '' && $watchUrl !== '#'): ?>
              <a href="<?= htmlspecialchars($watchUrl, ENT_QUOTES) ?>" class="s-btn-play"><svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M8 5v14l11-7z"/></svg>Watch Now</a>
            <?php else: ?>
              <a href="#" class="s-btn-play" onclick="event.preventDefault();showToast('Watch unavailable')"><svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M8 5v14l11-7z"/></svg>Watch Now</a>
            <?php endif; ?>
            <a href="#" class="s-btn-info" onclick="event.preventDefault();showToast('Added to Watchlist!')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>My List</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <button class="hero-arrow prev" id="heroPrev" aria-label="Previous"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 18l-6-6 6-6"/></svg></button>
  <button class="hero-arrow next" id="heroNext" aria-label="Next"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg></button>

  <div class="hero-controls" id="heroDots">
    <?php foreach ($slides as $index => $item): ?>
      <button class="hero-dot<?= $index === 0 ? ' active' : '' ?>" aria-label="Slide <?= $index + 1 ?>"></button>
    <?php endforeach; ?>
  </div>
</section>
