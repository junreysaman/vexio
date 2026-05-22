<?= $this->start("styles") ?>
<link rel="stylesheet" href="/assets/frontend/css/watch-movie.css">
<link rel="stylesheet" href="/assets/vendor/plyr/plyr.css">
<?= $this->end() ?>

<?= $this->start('content') ?>

<?= $this->includePartial('/frontend/watch/watch-movie/ad/movie-top-leaderboard-ad') ?>
<?= $this->includePartial('/frontend/watch/watch-movie/components/movie-player') ?>
<?= $this->includePartial('/frontend/watch/watch-movie/components/movie-content-area') ?>

<?= $this->includePartial('/frontend/watch/watch-movie/components/movie-sidebar') ?>

<?= $this->includePartial('/frontend/partials/share-modal', [
  'pageUrl' => $_SERVER['REQUEST_URI'] ?? '/',
  'pageTitle' => $item['title'] ?? 'Watch Movie',
  'pageImage' => \App\Support\MediaImage::ogImageFromRow($item) ?: '/favicon.png',
]) ?>

<?= $this->end() ?>

<?= $this->start('scripts') ?>
<script src="/assets/vendor/hls/hls.min.js"></script>
<script src="/assets/vendor/plyr/plyr.polyfilled.js"></script>
<script src="/assets/frontend/js/watch-movie.js?v=<?= filemtime(dirname(__DIR__, 6) . '/public/assets/frontend/js/watch-movie.js') ?>"></script>
<script src="/assets/frontend/js/watch-comments.js?v=<?= filemtime(dirname(__DIR__, 6) . '/public/assets/frontend/js/watch-comments.js') ?>"></script>
<?= $this->end() ?>
