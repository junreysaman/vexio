<!DOCTYPE html>
<html lang="en">

<head>
<!-- Google tag (gtag.js) --> <script async src="https://www.googletagmanager.com/gtag/js?id=G-KR80HH59JY"></script> <script> window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments);} gtag('js', new Date()); gtag('config', 'G-KR80HH59JY'); </script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="index, follow">
    <meta name="description" content="<?= escape($meta_description ?? 'Stream free movies, TV shows, and anime in one place. Discover the latest releases, trending series, and curated anime collections without signup or subscription.') ?>">
    <meta name="keywords" content="<?= escape($meta_keywords ?? 'free movies, free tv shows, free anime, movie streaming, anime streaming, watch online, no subscription, streaming site') ?>">
    <meta name="author" content="<?= escape($project ?? 'Vexio HD') ?>">
    <meta property="og:title" content="<?= escape($title ?? 'Welcome') ?> | <?= escape($project ?? 'Vexio HD') ?>">
    <meta property="og:description" content="<?= escape($meta_description ?? 'Stream free movies, TV shows, and anime in one place. Discover the latest releases, trending series, and curated anime collections without signup or subscription.') ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= escape(url(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/')) ?>">
    <meta property="og:image" content="<?= escape($meta_image ?? '/favicon.png') ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= escape($title ?? 'Welcome') ?> | <?= escape($project ?? 'Vexio HD') ?>">
    <meta name="twitter:description" content="<?= escape($meta_description ?? 'Stream free movies, TV shows, and anime in one place. Discover the latest releases, trending series, and curated anime collections without signup or subscription.') ?>">
    <link rel="icon" href="/favicon.png" type="image/x-icon">
    <link rel="preconnect" href="https://image.tmdb.org" crossorigin>
    <link rel="dns-prefetch" href="//image.tmdb.org">
    <title><?= escape($title ?? 'Welcome') ?> | <?= escape($project ?? 'Vexio HD') ?></title>
    <link rel="stylesheet" href="/assets/admin/css/app.css">
    <link rel="stylesheet" href="/assets/frontend/css/paper.css">
    <link rel="stylesheet" href="/assets/frontend/css/page-loader.css">
    <link rel="stylesheet" href="/assets/frontend/css/global-views.css?v=<?= filemtime(dirname(__DIR__, 5) . '/public/assets/frontend/css/global-views.css') ?>">
    <?= $this->section('styles') ?>
</head>

<body class="<?= escape($body_class ?? 'paper-frontend') ?>">

    <?= $this->includePartial('frontend/partials/page-loader') ?>
    <div id="app" class="vexio-app-shell">
    <?= $this->includePartial('frontend/partials/navbar') ?>
    <?= $this->includePartial('frontend/partials/search') ?>
     <!-- $this->includePartial('frontend/archive/trending-page/ad/trending-interstitial')
    $this->includePartial('frontend/archive/trending-page/ad/trending-mobile-sticky')  -->
    
        <?= $this->section('content') ?>

        <?= $this->includePartial('frontend/partials/footer') ?>
    </div>
    <script src="/assets/frontend/js/app.js?v=<?= filemtime(dirname(__DIR__, 5) . '/public/assets/frontend/js/app.js') ?>"></script>
    <script src="/assets/frontend/js/search.js?v=<?= filemtime(dirname(__DIR__, 5) . '/public/assets/frontend/js/search.js') ?>"></script>
    <script src="/assets/frontend/js/page-loader.js?v=<?= filemtime(dirname(__DIR__, 5) . '/public/assets/frontend/js/page-loader.js') ?>"></script>
    <?= $this->section('scripts') ?>
</body>

</html>
