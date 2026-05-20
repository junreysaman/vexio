<?php use App\Support\MediaImage; ?>
<section id="genres-sec">
  <div class="container">
    <div class="sec-head">
      <h2 class="sec-title">
        <div class="sec-title-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
        </div>
        Browse by <span class="accent">Genre</span>
      </h2>
    </div>
    <div class="hrow-wrap">
      <button class="hrow-btn prev" onclick="scrollRow('genre-row',-1)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 18l-6-6 6-6"/></svg></button>
      <div class="hrow" id="genre-row">
        <?php
          $genreList = !empty($genres) && is_array($genres) ? $genres : [];
        ?>
        <?php if ($genreList !== []): ?>
          <?php foreach ($genreList as $genre): ?>
            <?php
              $name = htmlspecialchars((string) ($genre['name'] ?? 'Unknown'), ENT_QUOTES);
              $url = htmlspecialchars((string) ($genre['url'] ?? '#'), ENT_QUOTES);
              $imageMedia = is_array($genre['image_media'] ?? null)
                  ? $genre['image_media']
                  : MediaImage::fromString((string) ($genre['image'] ?? 'https://picsum.photos/seed/vexio-genre-empty/420/260'), 'genre');
              $icon = strtoupper(substr((string) ($genre['name'] ?? ''), 0, 3));
            ?>
            <a class="genre-card" href="<?= $url ?>">
              <div class="genre-card-media">
                <?php echo $this->includePartial('/frontend/partials/media-image', [
                    'media' => $imageMedia,
                    'alt' => (string) ($genre['name'] ?? 'Genre') . ' backdrop',
                    'loading' => 'lazy',
                ]); ?>
              </div>
              <div class="gc-overlay">
                <div class="gc-icon"><?= $icon ?></div>
                <div class="gc-name"><?= $name ?></div>
                <div class="gc-count">Explore</div>
              </div>
            </a>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="genre-card">
            <div class="genre-card-media">
              <?php echo $this->includePartial('/frontend/partials/media-image', [
                  'media' => MediaImage::fromString('https://picsum.photos/seed/vexio-genre-empty/420/260', 'genre'),
                  'alt' => 'No genres available',
                  'loading' => 'lazy',
              ]); ?>
            </div>
            <div class="gc-overlay">
              <div class="gc-icon">N/A</div>
              <div class="gc-name">No Genres</div>
              <div class="gc-count">Check back soon</div>
            </div>
          </div>
        <?php endif; ?>
      </div>
      <button class="hrow-btn next" onclick="scrollRow('genre-row',1)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg></button>
    </div>
  </div>
</section>
