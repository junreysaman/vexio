<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/favicon.png" type="image/x-icon">
    <title><?= escape($title ?? 'Dashboard') ?> | <?= escape($project ?? 'Paper-PHPFramework') ?></title>
    <link rel="stylesheet" href="/assets/admin/css/app.css">
    <link rel="stylesheet" href="/assets/backend/css/paper.css">
    <?= $this->section('styles') ?>
</head>

<body class="<?= escape($body_class ?? 'paper-backend') ?>">
    <div id="loader" class="paper-loader" aria-hidden="true"></div>
    <div class="paper-back-layout">
        <aside class="paper-back-sidebar" aria-label="Backend navigation">
            <a class="paper-back-brand" href="/">
                <span class="paper-brand-mark"><img src="/favicon.png" alt=""></span>
                <span><?= escape($project ?? 'Paper-PHPFramework') ?></span>
            </a>
            <nav class="paper-back-nav">
                <a class="<?= isActive('/admin/dashboard') ?>" href="/admin/dashboard"><i class="icon-dashboard"></i><span>Dashboard</span></a>
                <a class="<?= str_starts_with(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/admin/content') ? 'active' : '' ?>" href="/admin/content"><i class="icon-video_library"></i><span>Content</span></a>
                <a class="<?= str_starts_with(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/admin/importer') ? 'active' : '' ?>" href="/admin/importer"><i class="icon-cloud_download"></i><span>Importer</span></a>
                <a class="<?= str_starts_with(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/admin/users') ? 'active' : '' ?>" href="/admin/users"><i class="icon-account_box"></i><span>Users</span></a>
            </nav>
        </aside>

        <main class="paper-back-main">
            <header class="paper-back-topbar">
                <div>
                    <strong><?= escape($title ?? 'Dashboard') ?></strong>
                    <span><?= escape(date('M d, Y')) ?></span>
                </div>
                <?php if (!empty($currentUser)): ?>
                    <form action="/logout" method="POST">
                        <input type="hidden" name="token" value="<?= escape($_csrfToken ?? '') ?>">
                        <button class="paper-icon-btn" type="submit" title="Sign out" aria-label="Sign out">
                            <i class="icon-exit_to_app"></i>
                        </button>
                    </form>
                <?php endif; ?>
            </header>

            <section class="paper-back-content">
                <?php foreach (($flashes ?? []) as $flash): ?>
                    <div class="paper-alert <?= escape($flash['type'] ?? 'success') ?>">
                        <?= escape($flash['message'] ?? '') ?>
                    </div>
                <?php endforeach; ?>
                <?= $this->section('content') ?>
            </section>
        </main>
    </div>

    <script src="/assets/backend/js/app.js"></script>
    <?= $this->section('scripts') ?>
</body>

</html>
