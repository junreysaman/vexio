<?= $this->start('styles') ?>
<?= $this->includePartial('/frontend/archive/genre-page/components/styles') ?>
<?= $this->end() ?>

<?= $this->start('content') ?>

<main id="genre-page">
    <?= $this->includePartial('/frontend/archive/genre-page/components/genre-hero') ?>
    <section id="genre-main">
        <div class="genre-shell">
            <?php if (!empty($active_genre) && is_array($active_genre)): ?>
                <?= $this->includePartial('/frontend/archive/genre-page/components/genre-content') ?>
            <?php endif; ?>
            <?= $this->includePartial('/frontend/archive/genre-page/components/genre-stats') ?>
            <?= $this->includePartial('/frontend/archive/genre-page/components/featured-genres') ?>
            <?= $this->includePartial('/frontend/archive/genre-page/components/all-genres') ?>
            <?= $this->includePartial('/frontend/archive/genre-page/components/more-genres') ?>
            <?php if (empty($active_genre) || !is_array($active_genre)): ?>
                <?= $this->includePartial('/frontend/archive/genre-page/components/genre-content') ?>
            <?php endif; ?>
        </div>
    </section>
</main>

<?= $this->end() ?>

<?= $this->start('scripts') ?>
<?= $this->includePartial('/frontend/archive/genre-page/components/scripts') ?>
<?= $this->end() ?>
