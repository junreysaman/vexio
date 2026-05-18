<?= $this->start("styles") ?>
<link rel="stylesheet" href="/assets/frontend/css/watch-movie.css">
<?= $this->end() ?>

<?= $this->start('content') ?>

<?= $this->includePartial('/frontend/watch/watch-movie/ad/movie-top-leaderboard-ad') ?>
<?= $this->includePartial('/frontend/watch/watch-movie/components/movie-player') ?>
<?= $this->includePartial('/frontend/watch/watch-movie/components/movie-server-selector') ?>
<?= $this->includePartial('/frontend/watch/watch-movie/components/movie-content-area') ?>

<?= $this->includePartial('/frontend/watch/watch-movie/components/movie-sidebar') ?>


<?= $this->end() ?>

<?= $this->start('scripts') ?>
<script src="/assets/frontend/js/watch-movie.js?v=<?= filemtime(dirname(__DIR__, 6) . '/public/assets/frontend/js/watch-movie.js') ?>"></script>
<?= $this->end() ?>
