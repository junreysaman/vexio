<?= $this->start('content') ?>

<?php
$statusLabels = ['all' => 'All', 'published' => 'Published', 'hidden' => 'Hidden'];
$page = (int) ($meta['current_page'] ?? 1);
$lastPage = (int) ($meta['last_page'] ?? 1);
$baseQuery = '/admin/comments?status=' . urlencode((string) $activeStatus) . '&q=' . urlencode((string) $query);
?>

<div class="admin-page-head">
    <div>
        <span class="paper-pill">Community</span>
        <h1>Comment Management</h1>
        <p>Review viewer comments and replies. New comments are auto-approved for signed-in users.</p>
    </div>
</div>

<section class="stat-grid user-stat-grid" aria-label="Comment statistics">
    <article class="stat-tile"><span>Total</span><strong><?= number_format((int) ($stats['total'] ?? 0)) ?></strong></article>
    <article class="stat-tile"><span>Published</span><strong><?= number_format((int) ($stats['published'] ?? 0)) ?></strong></article>
    <article class="stat-tile"><span>Hidden</span><strong><?= number_format((int) ($stats['hidden'] ?? 0)) ?></strong></article>
    <article class="stat-tile"><span>Replies</span><strong><?= number_format((int) ($stats['replies'] ?? 0)) ?></strong></article>
</section>

<section class="content-type-strip" aria-label="Comment status filters">
    <?php foreach ($statusLabels as $status => $label): ?>
        <a class="<?= $activeStatus === $status ? 'active' : '' ?>" href="/admin/comments?status=<?= escape($status) ?>&q=<?= urlencode((string) $query) ?>">
            <span><?= escape($label) ?></span>
            <strong><?= $status === 'all' ? number_format((int) ($stats['total'] ?? 0)) : number_format((int) ($stats[$status] ?? 0)) ?></strong>
        </a>
    <?php endforeach; ?>
</section>

<section class="card no-b shadow-sm admin-card">
    <div class="card-body content-toolbar">
        <form action="/admin/comments" method="GET" class="content-filter">
            <input type="hidden" name="status" value="<?= escape((string) $activeStatus) ?>">
            <input type="search" name="q" value="<?= escape((string) $query) ?>" placeholder="Search comments, users, email">
            <button class="btn btn-primary" type="submit">Search</button>
            <a class="btn btn-light" href="/admin/comments">Reset</a>
        </form>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 content-table">
                <thead>
                <tr>
                    <th>Comment</th>
                    <th>Target</th>
                    <th>User</th>
                    <th>Status</th>
                    <th>Posted</th>
                    <th class="text-right">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($comments)): ?>
                    <tr><td colspan="6"><div class="empty-state"><strong>No comments found</strong><span>Try a different filter or search term.</span></div></td></tr>
                <?php endif; ?>
                <?php foreach (($comments ?? []) as $comment): ?>
                    <?php
                    $isReply = !empty($comment['parent_id']);
                    $target = $comment['owner_type'] === 'episode'
                        ? trim((string) ($comment['episode_item_title'] ?? 'TV Show')) . ' S' . (int) ($comment['season_number'] ?? 0) . ' E' . (int) ($comment['episode_number'] ?? 0)
                        : (string) ($comment['item_title'] ?? 'Movie');
                    ?>
                    <tr>
                        <td>
                            <strong><?= $isReply ? 'Reply' : 'Comment' ?> #<?= (int) $comment['id'] ?></strong>
                            <small class="d-block text-muted"><?= escape((string) $comment['body']) ?></small>
                        </td>
                        <td><?= escape($target) ?></td>
                        <td>
                            <strong><?= escape((string) ($comment['display_name'] ?? $comment['username'] ?? 'Viewer')) ?></strong>
                            <small class="d-block text-muted"><?= escape((string) ($comment['email'] ?? '')) ?></small>
                        </td>
                        <td><span class="badge r-3 <?= $comment['status'] === 'published' ? 'badge-info' : 'badge-secondary' ?>"><?= escape((string) $comment['status']) ?></span></td>
                        <td><?= escape(date('M d, Y H:i', strtotime((string) $comment['created_at']))) ?></td>
                        <td class="text-right">
                            <div class="table-actions">
                                <?php if ($comment['status'] !== 'published'): ?>
                                    <form action="/admin/comments/<?= (int) $comment['id'] ?>/publish" method="POST"><input type="hidden" name="token" value="<?= escape($_csrfToken ?? '') ?>"><button class="icon-action" type="submit" title="Publish"><i class="icon-check"></i></button></form>
                                <?php endif; ?>
                                <?php if ($comment['status'] !== 'hidden'): ?>
                                    <form action="/admin/comments/<?= (int) $comment['id'] ?>/hide" method="POST"><input type="hidden" name="token" value="<?= escape($_csrfToken ?? '') ?>"><button class="icon-action" type="submit" title="Hide"><i class="icon-visibility_off"></i></button></form>
                                <?php endif; ?>
                                <form action="/admin/comments/<?= (int) $comment['id'] ?>/delete" method="POST" onsubmit="return confirm('Delete this comment and its replies?');"><input type="hidden" name="token" value="<?= escape($_csrfToken ?? '') ?>"><button class="icon-action danger" type="submit" title="Delete"><i class="icon-trash"></i></button></form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<div class="content-pagination">
    <a class="btn btn-light <?= $page <= 1 ? 'disabled' : '' ?>" href="<?= $baseQuery ?>&page=<?= max(1, $page - 1) ?>">Previous</a>
    <span>Page <?= number_format($page) ?> of <?= number_format($lastPage) ?></span>
    <a class="btn btn-light <?= $page >= $lastPage ? 'disabled' : '' ?>" href="<?= $baseQuery ?>&page=<?= min($lastPage, $page + 1) ?>">Next</a>
</div>

<?= $this->end() ?>
