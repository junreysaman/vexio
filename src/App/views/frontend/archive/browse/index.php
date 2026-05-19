<?= $this->start('styles') ?>
<?= $this->includePartial('/frontend/archive/browse/components/styles') ?>
<?= $this->end() ?>

<?= $this->start('content') ?>

<?= $this->includePartial('/frontend/archive/browse/components/archive-hero') ?>

<main id="archive-main">
    <section class="container archive-layout">
        <?= $this->includePartial('/frontend/archive/browse/components/archive-sidebar-dynamic') ?>
        <?= $this->includePartial('/frontend/archive/browse/components/archive-results-dynamic') ?>
    </section>

    <?= $this->includePartial('/frontend/archive/browse/components/archive-bottom') ?>
</main>

<?= $this->end() ?>

<?= $this->start('scripts') ?>
<?= $this->includePartial('/frontend/archive/browse/components/scripts') ?>
<?= $this->end() ?>
