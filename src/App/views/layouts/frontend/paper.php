<?php
use App\Support\Seo;

$pageLoaderEnabled = filter_var($_ENV['PAGE_LOADER_ENABLED'] ?? false, FILTER_VALIDATE_BOOLEAN);
$siteName = (string) ($project ?? 'Vexio HD');
$pageTitle = trim((string) ($title ?? 'Welcome'));
$fullTitle = $pageTitle !== '' ? $pageTitle . ' | ' . $siteName : $siteName;
$seoDescription = Seo::description((string) ($meta_description ?? Seo::DEFAULT_DESCRIPTION));
$seoKeywords = trim((string) ($meta_keywords ?? 'movies, tv shows, anime, streaming, watch online, VEXIO'));
$seoCanonical = Seo::canonicalUrl((string) ($canonical_url ?? Seo::currentPath()));
$seoImage = Seo::absoluteUrl((string) ($meta_image ?? Seo::DEFAULT_IMAGE));
$seoImageAlt = trim((string) ($meta_image_alt ?? $fullTitle));
$seoType = trim((string) ($og_type ?? 'website'));
$seoRobots = trim((string) ($robots ?? 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1'));
$structuredData = $structured_data ?? [];
if (!is_array($structuredData)) {
    $structuredData = [];
}
$structuredData = array_values(array_filter([
    Seo::website($siteName),
    Seo::organization($siteName, '/brand/vexio-logo-primary-1600x480.png'),
    ...$structuredData,
]));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="<?= escape($seoRobots) ?>">
    <meta name="description" content="<?= escape($seoDescription) ?>">
    <meta name="keywords" content="<?= escape($seoKeywords) ?>">
    <meta name="author" content="<?= escape($siteName) ?>">
    <meta name="theme-color" content="#08090d">
    <link rel="canonical" href="<?= escape($seoCanonical) ?>">
    <link rel="alternate" href="<?= escape($seoCanonical) ?>" hreflang="x-default">
    <meta property="og:site_name" content="<?= escape($siteName) ?>">
    <meta property="og:locale" content="en_US">
    <meta property="og:title" content="<?= escape($fullTitle) ?>">
    <meta property="og:description" content="<?= escape($seoDescription) ?>">
    <meta property="og:type" content="<?= escape($seoType !== '' ? $seoType : 'website') ?>">
    <meta property="og:url" content="<?= escape($seoCanonical) ?>">
    <meta property="og:image" content="<?= escape($seoImage) ?>">
    <meta property="og:image:alt" content="<?= escape($seoImageAlt) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= escape($fullTitle) ?>">
    <meta name="twitter:description" content="<?= escape($seoDescription) ?>">
    <meta name="twitter:image" content="<?= escape($seoImage) ?>">
    <link rel="icon" href="/favicon.png" type="image/x-icon">
    <link rel="apple-touch-icon" href="/brand/vexio-app-icon-192x192.png">
    <link rel="preconnect" href="https://image.tmdb.org" crossorigin>
    <link rel="dns-prefetch" href="//image.tmdb.org">


    <!-- Google tag (gtag.js) --> <script async src="https://www.googletagmanager.com/gtag/js?id=G-KR80HH59JY"></script> <script> window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments);} gtag('js', new Date()); gtag('config', 'G-KR80HH59JY'); </script>
    <!-- Yandex.Metrika counter -->
    <script type="text/javascript">
        (function(m,e,t,r,i,k,a){
            m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
            m[i].l=1*new Date();
            for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
            k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)
        })(window, document,'script','https://mc.yandex.ru/metrika/tag.js?id=109402090', 'ym');

        ym(109402090, 'init', {ssr:true, webvisor:true, clickmap:true, ecommerce:"dataLayer", referrer: document.referrer, url: location.href, accurateTrackBounce:true, trackLinks:true});
    </script>
    <noscript><div><img src="https://mc.yandex.ru/watch/109402090" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
    <!-- /Yandex.Metrika counter -->
    <title><?= escape($fullTitle) ?></title>
    <?php foreach ($structuredData as $jsonLd): ?>
    <?php if (is_array($jsonLd) && $jsonLd !== []): ?>
    <script type="application/ld+json"><?= Seo::jsonLd($jsonLd) ?></script>
    <?php endif; ?>
    <?php endforeach; ?>
    <link rel="stylesheet" href="/assets/admin/css/app.css">
    <link rel="stylesheet" href="/assets/frontend/css/paper.css">
    <?php if ($pageLoaderEnabled): ?>
    <link rel="stylesheet" href="/assets/frontend/css/page-loader.css">
    <?php endif; ?>
    <link rel="stylesheet" href="/assets/frontend/css/global-views.css?v=<?= filemtime(dirname(__DIR__, 5) . '/public/assets/frontend/css/global-views.css') ?>">
    <?= $this->section('styles') ?>
</head>

<body class="<?= escape(trim(($body_class ?? 'paper-frontend') . ($pageLoaderEnabled ? '' : ' loaded'))) ?>">

    <!-- $this->includePartial('frontend/partials/page-loader')
    <div id="app" class="vexio-app-shell"> -->
    <?= $this->includePartial('frontend/partials/navbar') ?>
    <?= $this->includePartial('frontend/partials/search') ?>
     <!-- $this->includePartial('frontend/archive/trending-page/ad/trending-interstitial')
    $this->includePartial('frontend/archive/trending-page/ad/trending-mobile-sticky')  -->
    
        <?= $this->section('content') ?>

        <?= $this->includePartial('frontend/partials/footer') ?>
    </div>
    <script src="/assets/frontend/js/app.js?v=<?= filemtime(dirname(__DIR__, 5) . '/public/assets/frontend/js/app.js') ?>"></script>
    <script src="/assets/frontend/js/search.js?v=<?= filemtime(dirname(__DIR__, 5) . '/public/assets/frontend/js/search.js') ?>"></script>
    <?php if ($pageLoaderEnabled): ?>
    <script src="/assets/frontend/js/page-loader.js?v=<?= filemtime(dirname(__DIR__, 5) . '/public/assets/frontend/js/page-loader.js') ?>"></script>
    <?php endif; ?>
    <?= $this->section('scripts') ?>
</body>

</html>
