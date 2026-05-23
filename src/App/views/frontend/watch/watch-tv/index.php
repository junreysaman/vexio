<?= $this->start('styles') ?>
<link rel="stylesheet" href="https://cdn.vidstack.io/player/theme.css">
<link rel="stylesheet" href="https://cdn.vidstack.io/player/video.css">
<link rel="stylesheet" href="/assets/frontend/css/watch-tv-show.css">
<?= $this->end() ?>

<?= $this->start('content') ?>
<div class="page-wrap">
  <?= $this->includePartial('/frontend/watch/watch-tv/ad/tv-top-ad') ?>

  <div class="watch-layout">
    <div class="watch-main">
      <?= $this->includePartial('/frontend/watch/watch-tv/components/tv-player') ?>
      <?= $this->includePartial('/frontend/watch/watch-tv/components/server-selector') ?>
      <?= $this->includePartial('/frontend/watch/watch-tv/components/quick-episode') ?>
      <?= $this->includePartial('/frontend/watch/watch-tv/components/tv-content') ?>
    </div>

    <?= $this->includePartial('/frontend/watch/watch-tv/components/tv-sidebar') ?>
  </div>
</div>

<?= $this->includePartial('/frontend/partials/share-modal', [
  'pageUrl' => $_SERVER['REQUEST_URI'] ?? '/',
  'pageTitle' => (isset($episode) && is_array($episode) ? (string) ($episode['title'] ?? 'Watch TV Show') : ((isset($show) && is_array($show) ? ($show['title'] ?? 'Watch TV Show') : 'Watch TV Show'))),
  'pageImage' => (isset($episode) && is_array($episode) ? \App\Support\MediaImage::ogImageFromRow($episode) : null) ?: (isset($show) && is_array($show) ? \App\Support\MediaImage::ogImageFromRow($show) : null) ?: '/favicon.png',
]) ?>




<?= $this->end() ?>

<?= $this->start('scripts') ?>
<script src="/assets/frontend/js/watch-movie.js?v=<?= filemtime(dirname(__DIR__, 6) . '/public/assets/frontend/js/watch-movie.js') ?>"></script>
<script src="/assets/frontend/js/watch-comments.js?v=<?= filemtime(dirname(__DIR__, 6) . '/public/assets/frontend/js/watch-comments.js') ?>"></script>


<?= $this->end() ?>
