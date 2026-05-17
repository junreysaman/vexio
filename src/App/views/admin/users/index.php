<?= $this->start('content') ?>

<div class="admin-page-head">
    <div>
        <span class="paper-pill">Admin</span>
        <h1>User Management</h1>
        <p>Manage admins and standard viewer accounts from one controlled workspace.</p>
    </div>
    <a class="btn btn-primary shadow-sm" href="/admin/users/create">
        <i class="icon-add mr-2"></i>Add User
    </a>
</div>

<section class="stat-grid user-stat-grid" aria-label="User statistics">
    <article class="stat-tile">
        <span>Total Users</span>
        <strong><?= number_format((int) ($stats['total'] ?? 0)) ?></strong>
    </article>
    <article class="stat-tile">
        <span>Active</span>
        <strong><?= number_format((int) ($stats['active'] ?? 0)) ?></strong>
    </article>
    <article class="stat-tile">
        <span>Admins</span>
        <strong><?= number_format((int) ($stats['admins'] ?? 0)) ?></strong>
    </article>
    <article class="stat-tile">
        <span>Standard Users</span>
        <strong><?= number_format((int) ($stats['regular'] ?? 0)) ?></strong>
    </article>
</section>

<section class="card no-b shadow-sm admin-card">
    <div class="card-header white d-flex align-items-center justify-content-between">
        <div>
            <strong>All Users</strong>
            <small class="d-block text-muted">Accounts, roles, status, and profile controls</small>
        </div>
        <span class="badge r-3 badge-primary"><?= number_format(count($users ?? [])) ?> records</span>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 admin-users-table">
                <thead>
                <tr>
                    <th>User</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th class="text-right">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <strong>No users found</strong>
                                <span>Create the first account to begin managing access.</span>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach (($users ?? []) as $user): ?>
                    <?php
                    $fullName = trim($user['first_name'] . ' ' . $user['last_name']);
                    $initials = strtoupper(substr((string) $user['first_name'], 0, 1) . substr((string) $user['last_name'], 0, 1));
                    $isSelf = (int) ($currentUser['id'] ?? 0) === (int) $user['id'];
                    ?>
                    <tr>
                        <td>
                            <div class="user-cell">
                                <span class="avatar-letter avatar-md bg-primary text-white"><?= escape($initials ?: 'U') ?></span>
                                <span>
                                    <strong><?= escape($fullName ?: $user['username']) ?></strong>
                                    <small><?= escape($user['email']) ?> · @<?= escape($user['username']) ?></small>
                                </span>
                            </div>
                        </td>
                        <td>
                            <span class="badge r-3 <?= $user['role_name'] === 'superuser' ? 'badge-primary' : 'badge-success' ?>">
                                <?= escape($user['role_name'] === 'superuser' ? 'Admin' : 'Standard') ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge r-3 <?= (int) $user['is_active'] === 1 ? 'badge-info' : 'badge-secondary' ?>">
                                <?= (int) $user['is_active'] === 1 ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td><?= escape(date('M d, Y', strtotime((string) $user['created_at']))) ?></td>
                        <td class="text-right">
                            <div class="table-actions">
                                <a class="icon-action" href="/admin/users/<?= (int) $user['id'] ?>/edit" title="Edit user" aria-label="Edit user">
                                    <i class="icon-pencil"></i>
                                </a>
                                <form action="/admin/users/<?= (int) $user['id'] ?>/delete" method="POST" onsubmit="return confirm('Delete this user? This cannot be undone.');">
                                    <input type="hidden" name="token" value="<?= escape($_csrfToken ?? '') ?>">
                                    <button class="icon-action danger" type="submit" title="Delete user" aria-label="Delete user" <?= $isSelf ? 'disabled' : '' ?>>
                                        <i class="icon-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?= $this->end() ?>
