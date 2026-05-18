<?= $this->start('styles') ?>
<style>
.watch-shell{background:#070a12;color:#f6f8ff;min-height:100vh;padding:84px 0 48px}.watch-layout{display:grid;grid-template-columns:minmax(0,1fr) 360px;gap:22px;max-width:1480px;margin:0 auto;padding:0 18px}.watch-player{position:relative;aspect-ratio:16/9;background:#05070d;border:1px solid rgba(255,255,255,.1);overflow:hidden}.watch-backdrop{position:absolute;inset:0;background-size:cover;background-position:center;filter:brightness(.42);transform:scale(1.04)}.watch-player-center{position:absolute;inset:0;display:grid;place-content:center;text-align:center;gap:14px}.watch-play{width:86px;height:86px;border-radius:50%;border:1px solid rgba(255,255,255,.35);background:rgba(0,200,240,.16);color:white;display:grid;place-items:center;margin:auto}.watch-play svg{width:34px}.watch-title-overlay{font-family:'Bebas Neue',sans-serif;font-size:42px;letter-spacing:0}.watch-subtitle{color:#b7c0d5;font-weight:800}.watch-controls{position:absolute;left:0;right:0;bottom:0;padding:16px;background:linear-gradient(transparent,rgba(0,0,0,.82));display:flex;align-items:center;gap:12px}.watch-progress{height:5px;background:#e8173f;border-radius:999px;flex:1}.watch-main-card,.watch-side-card{background:#0d121f;border:1px solid rgba(255,255,255,.09);padding:18px}.watch-info{display:flex;gap:18px;margin-top:18px}.watch-poster{width:128px;aspect-ratio:2/3;background:#111827;flex:0 0 auto;overflow:hidden}.watch-poster img,.episode-thumb img{width:100%;height:100%;object-fit:cover}.watch-kicker{display:inline-flex;gap:8px;align-items:center;color:#00c8f0;font-size:12px;font-weight:900;text-transform:uppercase}.watch-heading{font-family:'Bebas Neue',sans-serif;font-size:46px;margin:8px 0 6px;letter-spacing:0}.watch-meta{display:flex;gap:8px;flex-wrap:wrap;color:#cbd5e1;font-size:12px;font-weight:800}.watch-pill{border:1px solid rgba(255,255,255,.13);padding:5px 8px;background:#111827}.watch-desc{color:#aeb8cc;line-height:1.65;margin-top:14px}.episode-list{display:grid;gap:10px;max-height:760px;overflow:auto}.episode-card{display:flex;gap:10px;color:inherit;text-decoration:none;padding:10px;border:1px solid rgba(255,255,255,.08);background:#101827}.episode-card.current{border-color:#e8173f;background:#1d1520}.episode-thumb{width:86px;aspect-ratio:16/9;background:#162033;flex:0 0 auto}.episode-card strong{display:block;font-size:13px}.episode-card span{color:#92a0b8;font-size:12px}.season-row{display:flex;gap:8px;flex-wrap:wrap;margin:12px 0 16px}.season-chip{color:#dbe7ff;text-decoration:none;background:#111827;border:1px solid rgba(255,255,255,.1);padding:7px 10px;font-weight:900;font-size:12px}.season-chip.active{background:#e8173f;color:#fff}@media(max-width:980px){.watch-layout{grid-template-columns:1fr}.watch-info{display:grid}.watch-poster{width:150px}.watch-heading{font-size:38px}}
</style>
<?= $this->end() ?>

<?= $this->start('content') ?>
<?php
$poster = ($show['poster_image'] ?? '') ?: ($show['poster_url'] ?? '');
$backdrop = ($episode['backdrop_image'] ?? '') ?: (($show['backdrop_image'] ?? '') ?: $poster);
$currentSeason = (int) ($episode['season_number'] ?? 1);
$currentEpisode = (int) ($episode['episode_number'] ?? 1);
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
          <div class="watch-poster"><?php if ($poster): ?><img src="<?= escape($poster) ?>" alt=""><?php endif; ?></div>
          <div>
            <div class="watch-kicker">TV Show · TMDB #<?= (int) $show['tmdb_id'] ?></div>
            <h1 class="watch-heading"><?= escape($show['title']) ?></h1>
            <div class="watch-meta">
              <span class="watch-pill">S<?= $currentSeason ?> E<?= $currentEpisode ?></span>
              <span class="watch-pill">★ <?= escape((string) ($show['tmdb_rating'] ?? 'N/A')) ?></span>
              <span class="watch-pill"><?= number_format(count($episodes ?? [])) ?> episodes in season</span>
              <span class="watch-pill"><?= number_format((int) ($episode['views'] ?? 0)) ?> views</span>
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
          $rowPoster = ($row['backdrop_image'] ?? '') ?: (($row['poster_image'] ?? '') ?: $poster);
          ?>
          <a class="episode-card <?= $rowEpisode === $currentEpisode ? 'current' : '' ?>" href="<?= escape((string) ($row['watchUrl'] ?? '#')) ?>">
            <span class="episode-thumb"><?php if ($rowPoster): ?><img src="<?= escape($rowPoster) ?>" alt=""><?php endif; ?></span>
            <span><strong>Episode <?= $rowEpisode ?></strong><span><?= escape($row['title']) ?></span></span>
          </a>
        <?php endforeach; ?>
      </div>
    </aside>
  </div>
</main>
<?= $this->end() ?>
