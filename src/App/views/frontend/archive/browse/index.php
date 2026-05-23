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
<script>
window.archivePageData = {
    current_page: <?= (int) ($current_page ?? 1) ?>,
    total_pages: <?= (int) ($total_pages ?? 1) ?>,
    page_size: <?= (int) ($page_size ?? 24) ?>
};
</script>
<script src="<?= asset('/assets/frontend/js/archive.js') ?>"></script>
<?= $this->end() ?>

