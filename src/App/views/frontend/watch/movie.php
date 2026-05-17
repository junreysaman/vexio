<?= $this->start('styles') ?>
<style>
.watch-shell{background:#070a12;color:#f6f8ff;min-height:100vh;padding:84px 0 48px}.watch-layout{display:grid;grid-template-columns:minmax(0,1fr) 340px;gap:22px;max-width:1480px;margin:0 auto;padding:0 18px}.watch-player{position:relative;aspect-ratio:16/9;background:#05070d;border:1px solid rgba(255,255,255,.1);overflow:hidden}.watch-backdrop{position:absolute;inset:0;background-size:cover;background-position:center;filter:brightness(.42);transform:scale(1.04)}.watch-player-center{position:absolute;inset:0;display:grid;place-content:center;text-align:center;gap:14px}.watch-play{width:86px;height:86px;border-radius:50%;border:1px solid rgba(255,255,255,.35);background:rgba(0,200,240,.16);color:white;display:grid;place-items:center;margin:auto}.watch-play svg{width:34px}.watch-title-overlay{font-family:'Bebas Neue',sans-serif;font-size:44px;letter-spacing:0}.watch-subtitle{color:#b7c0d5;font-weight:800}.watch-controls{position:absolute;left:0;right:0;bottom:0;padding:16px;background:linear-gradient(transparent,rgba(0,0,0,.82));display:flex;align-items:center;gap:12px}.watch-progress{height:5px;background:#e8173f;border-radius:999px;flex:1}.watch-main-card,.watch-side-card{background:#0d121f;border:1px solid rgba(255,255,255,.09);padding:18px}.watch-info{display:flex;gap:18px;margin-top:18px}.watch-poster{width:128px;aspect-ratio:2/3;background:#111827;flex:0 0 auto;overflow:hidden}.watch-poster img,.related-poster img{width:100%;height:100%;object-fit:cover}.watch-kicker{display:inline-flex;gap:8px;align-items:center;color:#00c8f0;font-size:12px;font-weight:900;text-transform:uppercase}.watch-heading{font-family:'Bebas Neue',sans-serif;font-size:46px;margin:8px 0 6px;letter-spacing:0}.watch-meta{display:flex;gap:8px;flex-wrap:wrap;color:#cbd5e1;font-size:12px;font-weight:800}.watch-pill{border:1px solid rgba(255,255,255,.13);padding:5px 8px;background:#111827}.watch-desc{color:#aeb8cc;line-height:1.65;margin-top:14px}.watch-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:16px}.watch-btn{border:0;background:#e8173f;color:white;padding:11px 14px;font-weight:900}.watch-btn.secondary{background:#172033;color:#dbe7ff}.related-list{display:grid;gap:12px}.related-card{display:flex;gap:10px;color:inherit;text-decoration:none}.related-poster{width:58px;aspect-ratio:2/3;background:#162033;flex:0 0 auto}.related-card strong{display:block;font-size:13px}.related-card span{color:#92a0b8;font-size:12px}@media(max-width:980px){.watch-layout{grid-template-columns:1fr}.watch-info{display:grid}.watch-poster{width:150px}.watch-heading{font-size:38px}}
</style>
<?= $this->end() ?>

<?= $this->start('content') ?>
<?php
$poster = ($item['poster_image'] ?? '') ?: ($item['poster_url'] ?? '');
$backdrop = ($item['backdrop_image'] ?? '') ?: $poster;
$rating = $item['tmdb_rating'] ?? null;
?>
<main class="watch-shell">
  <div class="watch-layout">
    <section class="watch-main">
      <div class="watch-player">
        <div class="watch-backdrop" style="background-image:url('<?= escape($backdrop) ?>')"></div>
        <div class="watch-player-center">
          <button class="watch-play" type="button" onclick="showToast('Starting <?= escape($item['title']) ?>')">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
          </button>
          <div class="watch-title-overlay"><?= escape($item['title']) ?></div>
          <div class="watch-subtitle"><?= escape((string) ($item['release_year'] ?: 'Movie')) ?> · Full Movie</div>
        </div>
        <div class="watch-controls"><span>00:00</span><div class="watch-progress"></div><span>HD</span></div>
      </div>

      <div class="watch-main-card">
        <div class="watch-info">
          <div class="watch-poster"><?php if ($poster): ?><img src="<?= escape($poster) ?>" alt=""><?php endif; ?></div>
          <div>
            <div class="watch-kicker">Movie · TMDB #<?= (int) $item['tmdb_id'] ?></div>
            <h1 class="watch-heading"><?= escape($item['title']) ?></h1>
            <div class="watch-meta">
              <span class="watch-pill">4K HDR</span>
              <span class="watch-pill">Rating <?= escape((string) ($rating ?: 'N/A')) ?></span>
              <span class="watch-pill"><?= number_format((int) ($item['tmdb_vote_count'] ?? 0)) ?> votes</span>
              <span class="watch-pill"><?= number_format((int) ($item['views'] ?? 0)) ?> views</span>
            </div>
            <p class="watch-desc"><?= escape($item['synopsis'] ?: 'No synopsis available.') ?></p>
            <div class="watch-actions">
              <button class="watch-btn" type="button">Watch Now</button>
              <button class="watch-btn secondary" type="button">Watchlist</button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <aside class="watch-side-card">
      <h2 class="watch-kicker">Related Movies</h2>
      <div class="related-list">
        <?php foreach (($related ?? []) as $relatedItem): ?>
          <?php $relatedPoster = ($relatedItem['poster_image'] ?? '') ?: ($relatedItem['poster_url'] ?? ''); ?>
          <a class="related-card" href="/movie/<?= (int) $relatedItem['tmdb_id'] ?>">
            <span class="related-poster"><?php if ($relatedPoster): ?><img src="<?= escape($relatedPoster) ?>" alt=""><?php endif; ?></span>
            <span><strong><?= escape($relatedItem['title']) ?></strong><span><?= escape((string) ($relatedItem['release_year'] ?: 'Movie')) ?> · ★ <?= escape((string) ($relatedItem['tmdb_rating'] ?? 'N/A')) ?></span></span>
          </a>
        <?php endforeach; ?>
      </div>
    </aside>
  </div>
</main>
<?= $this->end() ?>
