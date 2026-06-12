<?= $this->start('styles') ?>
<!-- Video.js offline player -->
<link rel="stylesheet" href="/assets/vendor/videojs/video-js.min.css">
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
  'pageUrl' => isset($show) && is_array($show) ? (string) ($show['watchUrl'] ?? ($show['watch_url'] ?? ($_SERVER['REQUEST_URI'] ?? '/'))) : ($_SERVER['REQUEST_URI'] ?? '/'),
  'pageTitle' => isset($show) && is_array($show) ? (string) ($show['title'] ?? 'Watch TV Show') : 'Watch TV Show',
  'pageImage' => isset($show) && is_array($show) ? (\App\Support\MediaImage::ogImageFromRow($show) ?: '/favicon.png') : '/favicon.png',
]) ?>




<?= $this->end() ?>

<?= $this->start('scripts') ?>
<script src="/assets/vendor/videojs/video.min.js"></script>
<script src="/assets/vendor/hls/hls.min.js"></script>
<script src="/assets/frontend/js/watch-tv.js?v=<?= filemtime(dirname(__DIR__, 6) . '/public/assets/frontend/js/watch-tv.js') ?>"></script>
<script src="/assets/frontend/js/watch-comments.js?v=<?= filemtime(dirname(__DIR__, 6) . '/public/assets/frontend/js/watch-comments.js') ?>"></script>
<?= $this->end() ?>
