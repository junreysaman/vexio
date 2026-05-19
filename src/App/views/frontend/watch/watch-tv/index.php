<?= $this->start('styles') ?>
<link rel="stylesheet" href="/assets/frontend/css/watch-tv-show.css">
<?= $this->end() ?>

<?= $this->start('content') ?>
<div class="page-wrap">
  <?= $this->includePartial('/frontend/watch/watch-tv/ad/tv-top-ad') ?>

  <div class="watch-layout">
    <div class="watch-main">
      <?= $this->includePartial('/frontend/watch/watch-tv/components/tv-player') ?>
      <?= $this->includePartial('/frontend/watch/watch-tv/components/tv-server-selector') ?>
      <?= $this->includePartial('/frontend/watch/watch-tv/components/quick-episode') ?>
      <?= $this->includePartial('/frontend/watch/watch-tv/components/tv-content') ?>
    </div>

    <?= $this->includePartial('/frontend/watch/watch-tv/components/tv-sidebar') ?>
  </div>
</div>
<?= $this->end() ?>

<?= $this->start('scripts') ?>
<script src="/assets/frontend/js/watch-tv.js?v=<?= filemtime(dirname(__DIR__, 6) . '/public/assets/frontend/js/watch-tv.js') ?>"></script>
<script src="/assets/frontend/js/watch-comments.js?v=<?= filemtime(dirname(__DIR__, 6) . '/public/assets/frontend/js/watch-comments.js') ?>"></script>

<?= $this->end() ?>
