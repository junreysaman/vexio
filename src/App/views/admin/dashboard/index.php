<?= $this->start('content') ?>

<div class="dashboard-hero">
    <div>
        <span class="paper-pill">Trending now</span>
        <h1>Top movies and TV episodes by views</h1>
        <p>Use this first dashboard view to spot the licensed content people are watching most.</p>
    </div>
    <div class="dashboard-hero-actions">
        <form action="/admin/content/reset-views" method="POST" onsubmit="return confirm('Reset all title and episode view counters to 0? This cannot be undone.');">
            <input type="hidden" name="token" value="<?= escape($_csrfToken ?? '') ?>">
            <button class="dashboard-visit-btn" type="submit">
                <i class="icon-refresh"></i>
                Reset Views
            </button>
        </form>
        <a class="dashboard-visit-btn" href="/" target="_blank" rel="noopener">
            <i class="icon-open_in_new"></i>
            Visit website
        </a>
    </div>
</div>

<section class="stat-grid" aria-label="Content statistics">
    <article class="stat-tile">
        <span>Published</span>
        <strong><?= number_format((int) ($stats['published'] ?? 0)) ?></strong>
    </article>
    <article class="stat-tile">
        <span>Movies</span>
        <strong><?= number_format((int) ($stats['movies'] ?? 0)) ?></strong>
    </article>
    <article class="stat-tile">
        <span>TV Episodes</span>
        <strong><?= number_format((int) ($stats['tvEpisodes'] ?? 0)) ?></strong>
    </article>
    <article class="stat-tile">
        <span>Total Views</span>
        <strong><?= number_format((int) ($stats['totalViews'] ?? 0)) ?></strong>
    </article>
</section>

<section class="paper-panel trending-panel">
    <div class="paper-panel-head">
        <strong>Most Viewed</strong>
        <span class="paper-pill">Live ranking</span>
    </div>

    <div class="trending-list">
        <?php if (empty($trending)): ?>
            <div class="empty-state">
                <strong>No published media yet</strong>
                <span>Add licensed movies or TV episodes to start building this ranking.</span>
            </div>
        <?php endif; ?>

        <?php foreach (($trending ?? []) as $index => $item): ?>
            <article class="trending-row">
                <div class="rank"><?= (int) $index + 1 ?></div>
                <div class="poster" aria-hidden="true">
                    <?php $poster = \App\Support\MediaImage::adminPosterSrc($item); ?>
                    <?php if (!empty($poster)): ?>
                        <img src="<?= escape($poster) ?>" alt="">
                    <?php else: ?>
                        <i class="<?= $item['type'] === 'movie' ? 'icon-movie' : 'icon-play_circle_outline' ?>"></i>
                    <?php endif; ?>
                </div>
                <div class="media-meta">
                    <strong><?= escape($item['title']) ?></strong>
                    <span>
                        <?= escape(ucfirst(str_replace('_', ' ', (string) $item['type']))) ?>
                        <?php if (!empty($item['release_year'])): ?>
                            <?= escape((string) $item['release_year']) ?>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="view-count">
                    <strong><?= number_format((int) $item['views']) ?></strong>
                    <span>views</span>
                </div>
                <div class="dashboard-row-actions">
                    <?php if (!empty($item['watchUrl'])): ?>
                        <a class="icon-action" href="<?= escape((string) $item['watchUrl']) ?>" target="_blank" rel="noopener" title="Open watch page" aria-label="Open watch page">
                            <i class="icon-play_arrow"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<?= $this->end() ?>
