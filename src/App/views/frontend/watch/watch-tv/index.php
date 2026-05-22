<?= $this->start('styles') ?>
<link rel="stylesheet" href="/assets/frontend/css/watch-tv-show.css">
<link rel="stylesheet" href="/assets/vendor/plyr/plyr.css">
<?= $this->end() ?>

<?= $this->start('content') ?>
<div class="page-wrap">
  <?= $this->includePartial('/frontend/watch/watch-tv/ad/tv-top-ad') ?>

  <div class="watch-layout">
    <div class="watch-main">
      <?= $this->includePartial('/frontend/watch/watch-tv/components/tv-player') ?>
      <?= $this->includePartial('/frontend/watch/watch-tv/components/quick-episode') ?>
      <?= $this->includePartial('/frontend/watch/watch-tv/components/tv-content') ?>
    </div>

    <?= $this->includePartial('/frontend/watch/watch-tv/components/tv-sidebar') ?>
  </div>
</div>

<?= $this->includePartial('/frontend/partials/share-modal', [
  'pageUrl' => $_SERVER['REQUEST_URI'] ?? '/',
  'pageTitle' => $show['title'] ?? 'Watch TV Show',
  'pageImage' => \App\Support\MediaImage::ogImageFromRow($show) ?: '/favicon.png',
]) ?>

<?= $this->end() ?>

<?= $this->start('scripts') ?>
<script src="/assets/vendor/hls/hls.min.js"></script>
<script src="/assets/vendor/plyr/plyr.polyfilled.js"></script>
<script src="/assets/frontend/js/watch-tv.js?v=<?= filemtime(dirname(__DIR__, 6) . '/public/assets/frontend/js/watch-tv.js') ?>"></script>
<script src="/assets/frontend/js/watch-comments.js?v=<?= filemtime(dirname(__DIR__, 6) . '/public/assets/frontend/js/watch-comments.js') ?>"></script>

<?= $this->end() ?>
