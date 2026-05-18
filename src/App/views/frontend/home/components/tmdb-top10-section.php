<section id="top10">
  <div class="container">
    <div class="sec-head">
      <h2 class="sec-title">
        <div class="sec-title-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m12 2 3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        </div> TMDB
        Top <span class="accent">Rank</span>
      </h2>
      <!-- <a href="#" class="see-all">Full Rankings <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a> -->
    </div>
    <?php
      $topByTmdbItems = !empty($topByTmdb) && is_array($topByTmdb) ? $topByTmdb : [];
    ?>
    <div class="lb-grid" id="lbGrid">
      <?php if ($topByTmdbItems !== []): ?>
        <?php foreach ($topByTmdbItems as $index => $item): ?>
          <?php
            $rank = $index + 1;
            $rankClass = $rank <= 3 ? 'r' . $rank : 'rn';
            $title = htmlspecialchars((string) ($item['title'] ?? 'Untitled'), ENT_QUOTES);
            $poster = htmlspecialchars((string) ($item['poster'] ?? 'https://picsum.photos/seed/vexio-top-' . $rank . '/160/220'), ENT_QUOTES);
            $genre = htmlspecialchars((string) ($item['genre'] ?? 'Unknown Genre'), ENT_QUOTES);
            $duration = htmlspecialchars((string) ($item['type'] ?? 'movie') === 'movie' ? 'Movie' : 'Series', ENT_QUOTES);
            $score = htmlspecialchars((string) ($item['score'] ?? 'N/A'), ENT_QUOTES);
            $watchUrlRaw = (string) ($item['watchUrl'] ?? '#');
            $watchUrl = htmlspecialchars($watchUrlRaw !== '' ? $watchUrlRaw : '#', ENT_QUOTES);
          ?>
          <a class="lb-item" href="<?= $watchUrl ?>"<?= $watchUrlRaw === '' || $watchUrlRaw === '#' ? ' onclick="event.preventDefault();showToast(\'Watch unavailable\')"' : '' ?>>
            <span class="lb-rank <?= $rankClass ?>"><?= $rank ?></span>
            <div class="lb-thumb"><img src="<?= $poster ?>" alt="<?= $title ?> poster" loading="lazy"></div>
            <div class="lb-info">
              <div class="lb-title"><?= $title ?></div>
              <div class="lb-sub"><span><?= $genre ?></span><span><?= $duration ?></span></div>
            </div>
            <div class="lb-score"><?= $score ?></div>
          </a>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="lb-item">
          <span class="lb-rank r1">1</span>
          <div class="lb-thumb"><img src="https://picsum.photos/seed/vexio-top-empty/160/220" alt="No items available" loading="lazy"></div>
          <div class="lb-info">
            <div class="lb-title">No top TMDb titles available</div>
            <div class="lb-sub"><span>Check back soon</span><span>—</span></div>
          </div>
          <div class="lb-score">—</div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>
