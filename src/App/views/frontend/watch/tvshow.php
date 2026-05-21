<?= $this->start('content') ?>
<?php
use App\Support\MediaImage;

$poster = (string) ($show['poster_url'] ?? '');
$backdrop = ($episode['backdrop_url'] ?? '') ?: ($show['backdrop_url'] ?? '') ?: $poster;
$posterMedia = MediaImage::fromString($poster, 'detail');
$backdropMedia = MediaImage::fromString($backdrop, 'player');
$poster = MediaImage::srcOnly($posterMedia);
$backdrop = MediaImage::srcOnly($backdropMedia);
$currentSeason = (int) ($episode['season_number'] ?? 1);
$currentEpisode = (int) ($episode['episode_number'] ?? 1);
$genreLinks = is_array($show['genre_links'] ?? null) ? $show['genre_links'] : [];
?>
<main class="watch-shell">
  <div class="watch-layout">
    <section class="watch-main">
      <div class="watch-player">
        <div class="watch-backdrop" style="background-image:url('<?= escape($backdrop) ?>')"></div>
        <div class="watch-player-center">
          <button class="watch-play" type="button" onclick="showToast('Starting S<?= $currentSeason ?> E<?= $currentEpisode ?>')">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
          </button>
          <div class="watch-title-overlay"><?= escape($show['title']) ?></div>
          <div class="watch-subtitle">Season <?= $currentSeason ?> · Episode <?= $currentEpisode ?></div>
        </div>
        <div class="watch-controls"><span>00:00</span><div class="watch-progress"></div><span>HD</span></div>
      </div>

      <div class="watch-main-card">
        <div class="watch-info">
          <div class="watch-poster"><?php if ($poster): ?><img src="<?= escape($poster) ?>" srcset="<?= escape($posterMedia['srcset'] ?? '') ?>" sizes="<?= escape($posterMedia['sizes'] ?? '') ?>" width="<?= (int) ($posterMedia['width'] ?? 0) ?>" height="<?= (int) ($posterMedia['height'] ?? 0) ?>" alt="<?= escape((string) ($show['title'] ?? 'TV show poster')) ?>" loading="eager" decoding="async"><?php endif; ?></div>
          <div>
            <div class="watch-kicker">TV Show · TMDB #<?= (int) $show['tmdb_id'] ?></div>
            <h1 class="watch-heading"><?= escape($show['title']) ?></h1>
            <div class="watch-meta">
              <span class="watch-pill">S<?= $currentSeason ?> E<?= $currentEpisode ?></span>
              <span class="watch-pill">★ <?= escape((string) ($show['tmdb_rating'] ?? 'N/A')) ?></span>
              <span class="watch-pill"><?= number_format(count($episodes ?? [])) ?> episodes in season</span>
              <span class="watch-pill"><?= number_format((int) ($episode['views'] ?? 0)) ?> views</span>
              <?php foreach (array_slice($genreLinks, 0, 4) as $genre): ?>
                <a class="watch-pill" href="<?= escape((string) ($genre['url'] ?? '#')) ?>"><?= escape((string) ($genre['name'] ?? 'Genre')) ?></a>
              <?php endforeach; ?>
            </div>
            <p class="watch-desc"><strong><?= escape($episode['title']) ?></strong><br><?= escape($episode['synopsis'] ?: ($show['synopsis'] ?: 'No synopsis available.')) ?></p>
          </div>
        </div>
      </div>
    </section>

    <aside class="watch-side-card">
      <h2 class="watch-kicker">Episodes</h2>
      <div class="season-row">
        <?php foreach (($seasons ?? []) as $season): ?>
          <?php $seasonNo = (int) $season['season_number']; ?>
          <a class="season-chip <?= $seasonNo === $currentSeason ? 'active' : '' ?>" href="<?= escape((string) ($season['watchUrl'] ?? '#')) ?>">S<?= $seasonNo ?></a>
        <?php endforeach; ?>
      </div>
      <div class="episode-list">
        <?php foreach (($episodes ?? []) as $row): ?>
          <?php
          $rowSeason = (int) $row['season_number'];
          $rowEpisode = (int) $row['episode_number'];
          $rowPosterMedia = MediaImage::fromString((string) (($row['backdrop_url'] ?? '') ?: ($row['poster_url'] ?? '') ?: $poster), 'thumb');
          $rowPoster = MediaImage::srcOnly($rowPosterMedia);
          ?>
          <a class="episode-card <?= $rowEpisode === $currentEpisode ? 'current' : '' ?>" href="<?= escape((string) ($row['watchUrl'] ?? '#')) ?>">
            <span class="episode-thumb"><?php if ($rowPoster): ?><img src="<?= escape($rowPoster) ?>" srcset="<?= escape($rowPosterMedia['srcset'] ?? '') ?>" sizes="<?= escape($rowPosterMedia['sizes'] ?? '') ?>" width="<?= (int) ($rowPosterMedia['width'] ?? 0) ?>" height="<?= (int) ($rowPosterMedia['height'] ?? 0) ?>" alt="<?= escape((string) ($row['title'] ?? 'Episode thumbnail')) ?>" loading="lazy" decoding="async"><?php endif; ?></span>
            <span><strong>Episode <?= $rowEpisode ?></strong><span><?= escape($row['title']) ?></span></span>
          </a>
        <?php endforeach; ?>
      </div>
    </aside>
  </div>
</main>
<?= $this->end() ?>
