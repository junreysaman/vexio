<?= $this->start('content') ?>

<main class="paper-page">
    <section class="paper-page-header">
        <div class="paper-kicker"><i class="icon-alert-triangle"></i> 404</div>
        <h1><?= escape($title ?? 'Not Found') ?></h1>
        <p><?= escape($message ?? 'The page you requested could not be found.') ?></p>
        <div class="paper-actions">
            <a class="paper-btn primary" href="/"><i class="icon-home"></i> Home</a>
        </div>
    </section>

    <section class="paper-panel">
        <div class="paper-panel-head">
            <strong>Route Missing</strong>
            <span class="paper-pill">Handled</span>
        </div>
        <div class="paper-panel-body">
            <span>The starter route error handler is wired through <strong>AppController::notFound()</strong>.</span>
        </div>
    </section>
</main>

<?= $this->end() ?>