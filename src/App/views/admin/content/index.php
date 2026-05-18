<?= $this->start('content') ?>

<?php
$typeLabels = $types ?? [];
$activeType = $activeType ?? 'all';
$activeStatus = $activeStatus ?? 'all';
$query = (string) ($query ?? '');
$meta = $meta ?? ['current_page' => 1, 'last_page' => 1, 'total' => count($items ?? [])];
$statusLabels = ['all' => 'All Statuses'] + ($statuses ?? []);
$typeClass = static fn(string $type): string => str_replace('_', '-', $type);
$queryBase = '/admin/content?type=' . urlencode((string) $activeType)
    . '&status=' . urlencode((string) $activeStatus)
    . '&q=' . urlencode($query);
?>

<div class="admin-page-head content-head">
    <div>
        <span class="paper-pill">Catalogue</span>
        <h1>Content Management</h1>
        <p>Review imported movies, TV shows, seasons, and episode metadata by content type.</p>
    </div>
    <a class="btn btn-primary shadow-sm" href="/admin/importer">
        <i class="icon-cloud_download mr-2"></i>Import Content
    </a>
</div>

<section class="content-type-strip" aria-label="Content type filters">
    <?php foreach ($typeLabels as $type => $label): ?>
        <a class="<?= $activeType === $type ? 'active' : '' ?>" href="/admin/content?type=<?= escape($type) ?>">
            <span><?= escape($label) ?></span>
            <strong><?= number_format((int) ($stats[$type] ?? 0)) ?></strong>
        </a>
    <?php endforeach; ?>
</section>

<section class="card no-b shadow-sm admin-card">
    <div class="card-body content-toolbar">
        <form action="/admin/content" method="GET" class="content-filter">
            <input type="hidden" name="type" value="<?= escape($activeType) ?>">
            <div class="filter-field is-wide">
                <label for="q">Search</label>
                <input class="form-control r-0 light s-12" id="q" name="q" type="search" value="<?= escape($query) ?>" placeholder="Title, synopsis, or TMDB ID">
            </div>
            <div class="filter-field">
                <label for="status">Status</label>
                <select class="custom-select form-control r-0 light s-12" id="status" name="status">
                    <?php foreach ($statusLabels as $value => $label): ?>
                        <option value="<?= escape($value) ?>" <?= $activeStatus === $value ? 'selected' : '' ?>>
                            <?= escape($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button class="btn btn-primary" type="submit"><i class="icon-search mr-2"></i>Filter</button>
            <a class="btn btn-light" href="/admin/content?type=<?= escape($activeType) ?>">Reset</a>
        </form>
    </div>

    <form action="/admin/content/bulk-delete" method="POST" onsubmit="return confirm('Delete selected content? Related seasons and episodes will also be removed.');">
        <input type="hidden" name="token" value="<?= escape($_csrfToken ?? '') ?>">
        <div class="content-bulkbar">
            <span><?= number_format((int) ($meta['total'] ?? 0)) ?> total records</span>
            <button class="btn btn-outline-danger btn-sm" type="submit">
                <i class="icon-trash mr-1"></i>Bulk Delete
            </button>
        </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 content-table">
                <thead>
                <tr>
                    <th class="select-col">
                        <input type="checkbox" data-check-all aria-label="Select all content rows">
                    </th>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Hero</th>
                    <th>TMDB</th>
                    <th>Children</th>
                    <th>Views</th>
                    <th class="text-right">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($items)): ?>
                    <tr>
                        <td colspan="9">
                            <div class="empty-state">
                                <strong>No content found</strong>
                                <span>Import a title or loosen the current filters.</span>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach (($items ?? []) as $item): ?>
                    <?php
                    $type = (string) ($item['type'] ?? 'movie');
                    $poster = $item['poster_image'] ?: $item['poster_url'] ?: null;
                    ?>
                    <tr>
                        <td class="select-col">
                            <input type="checkbox" name="ids[]" value="<?= (int) $item['id'] ?>" aria-label="Select <?= escape($item['title'] ?? 'content') ?>">
                        </td>
                        <td>
                            <div class="content-title-cell">
                                <span class="content-thumb">
                                    <?php if ($poster): ?>
                                        <img src="<?= escape($poster) ?>" alt="">
                                    <?php else: ?>
                                        <i class="icon-image"></i>
                                    <?php endif; ?>
                                </span>
                                <span>
                                    <strong><?= escape($item['title'] ?? 'Untitled') ?></strong>
                                    <small>
                                        <?= escape((string) ($item['release_year'] ?: 'No year')) ?>
                                        <?= !empty($item['stream_link']) ? ' / Stream ready' : '' ?>
                                    </small>
                                </span>
                            </div>
                        </td>
                        <td><span class="content-type-badge <?= escape($typeClass($type)) ?>"><?= escape($typeLabels[$type] ?? $type) ?></span></td>
                        <td>
                            <span class="badge r-3 <?= ($item['status'] ?? '') === 'published' ? 'badge-success' : 'badge-secondary' ?>">
                                <?= escape(ucfirst((string) ($item['status'] ?? 'draft'))) ?>
                            </span>
                        </td>
                        <td><?= !empty($item['is_featured']) ? '<span class="badge badge-info r-3">Featured</span>' : '<span class="text-muted">No</span>' ?></td>
                        <td>
                            <strong><?= $item['tmdb_id'] ? '#' . (int) $item['tmdb_id'] : 'Manual' ?></strong>
                            <small class="d-block text-muted">Rating <?= escape((string) ($item['tmdb_rating'] ?? 'N/A')) ?> / <?= number_format((int) ($item['tmdb_vote_count'] ?? 0)) ?> votes</small>
                        </td>
                        <td>
                            <span class="content-child-count"><?= number_format((int) ($item['seasons_count'] ?? 0)) ?> seasons</span>
                            <span class="content-child-count"><?= number_format((int) ($item['episodes_count'] ?? 0)) ?> episodes</span>
                        </td>
                        <td><?= number_format((int) ($item['views'] ?? 0)) ?></td>
                        <td class="text-right">
                            <div class="table-actions">
                                <?php if (($item['type'] ?? '') === 'tv_show' && !empty($item['tmdb_id'])): ?>
                                    <button class="icon-action" type="submit" form="import-seasons-<?= (int) $item['id'] ?>" title="Import seasons" aria-label="Import seasons">
                                        <i class="icon-playlist_add"></i>
                                    </button>
                                <?php endif; ?>
                                <a class="icon-action" href="/admin/content/<?= (int) $item['id'] ?>/edit" title="Edit content" aria-label="Edit content">
                                    <i class="icon-pencil"></i>
                                </a>
                                <?php if (!empty($item['watchUrl'])): ?>
                                    <a class="icon-action" href="<?= escape((string) $item['watchUrl']) ?>" title="Open watch page" aria-label="Open watch page">
                                        <i class="icon-play_arrow"></i>
                                    </a>
                                <?php endif; ?>
                                <button class="icon-action danger" type="submit" form="delete-content-<?= (int) $item['id'] ?>" title="Delete content" aria-label="Delete content">
                                    <i class="icon-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    </form>

    <?php foreach (($items ?? []) as $item): ?>
        <?php if (($item['type'] ?? '') === 'tv_show' && !empty($item['tmdb_id'])): ?>
            <form id="import-seasons-<?= (int) $item['id'] ?>" action="/admin/content/<?= (int) $item['id'] ?>/generate-seasons" method="POST" onsubmit="return confirm('Import seasons for this TV show from TMDB? Episodes will not be imported yet.');">
                <input type="hidden" name="token" value="<?= escape($_csrfToken ?? '') ?>">
                <input type="hidden" name="status" value="<?= escape((string) ($item['status'] ?? 'draft')) ?>">
            </form>
        <?php endif; ?>
        <form id="delete-content-<?= (int) $item['id'] ?>" action="/admin/content/<?= (int) $item['id'] ?>/delete" method="POST" onsubmit="return confirm('Delete this content item? Related seasons and episodes will also be removed.');">
            <input type="hidden" name="token" value="<?= escape($_csrfToken ?? '') ?>">
        </form>
    <?php endforeach; ?>

    <div class="content-pagination">
        <a class="btn btn-light <?= (int) ($meta['current_page'] ?? 1) <= 1 ? 'disabled' : '' ?>" href="<?= escape($queryBase . '&page=' . max(1, (int) ($meta['current_page'] ?? 1) - 1)) ?>">Previous</a>
        <span>Page <?= number_format((int) ($meta['current_page'] ?? 1)) ?> of <?= number_format((int) ($meta['last_page'] ?? 1)) ?></span>
        <a class="btn btn-light <?= (int) ($meta['current_page'] ?? 1) >= (int) ($meta['last_page'] ?? 1) ? 'disabled' : '' ?>" href="<?= escape($queryBase . '&page=' . min((int) ($meta['last_page'] ?? 1), (int) ($meta['current_page'] ?? 1) + 1)) ?>">Next</a>
    </div>
</section>

<?= $this->end() ?>

<?= $this->start('scripts') ?>
<script>
document.querySelector('[data-check-all]')?.addEventListener('change', (event) => {
    document.querySelectorAll('.content-table input[name="ids[]"]').forEach((input) => {
        input.checked = event.target.checked;
    });
});
</script>
<?= $this->end() ?>
