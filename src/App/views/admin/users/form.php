<?= $this->start('content') ?>

<?php
$isEdit = ($mode ?? 'create') === 'edit';
$formData = array_merge($user ?? [], $oldFormData ?? []);
$action = $isEdit ? '/admin/users/' . (int) ($user['id'] ?? 0) . '/edit' : '/admin/users';
$selectedRole = (int) ($formData['role_id'] ?? 2);
$active = (int) ($formData['is_active'] ?? 1);
?>

<div class="admin-page-head">
    <div>
        <span class="paper-pill"><?= $isEdit ? 'Edit' : 'Create' ?></span>
        <h1><?= $isEdit ? 'Edit User' : 'Add New User' ?></h1>
        <p><?= $isEdit ? 'Update profile, access role, status, or reset password.' : 'Create an admin-managed account with a clear role and status.' ?></p>
    </div>
    <a class="btn btn-outline-secondary" href="/admin/users">
        <i class="icon-arrow_back mr-2"></i>Back to Users
    </a>
</div>

<form action="<?= escape($action) ?>" method="POST" class="user-form">
    <input type="hidden" name="token" value="<?= escape($_csrfToken ?? '') ?>">

    <div class="row my-3">
        <div class="col-lg-8">
            <div class="card no-b shadow-sm admin-card">
                <div class="card-body">
                    <h5 class="card-title">Profile</h5>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="col-form-label s-12" for="first_name">FIRST NAME</label>
                            <input class="form-control r-0 light s-12" id="first_name" name="first_name" type="text" value="<?= escape($formData['first_name'] ?? '') ?>" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="col-form-label s-12" for="last_name">LAST NAME</label>
                            <input class="form-control r-0 light s-12" id="last_name" name="last_name" type="text" value="<?= escape($formData['last_name'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="col-form-label s-12" for="username"><i class="icon-account_circle mr-2"></i>USERNAME</label>
                            <input class="form-control r-0 light s-12" id="username" name="username" type="text" value="<?= escape($formData['username'] ?? '') ?>" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="col-form-label s-12" for="email"><i class="icon-envelope-o mr-2"></i>EMAIL</label>
                            <input class="form-control r-0 light s-12" id="email" name="email" type="email" value="<?= escape($formData['email'] ?? '') ?>" required>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="card-body">
                    <h5 class="card-title">Security</h5>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="col-form-label s-12" for="password"><?= $isEdit ? 'NEW PASSWORD' : 'PASSWORD' ?></label>
                            <input class="form-control r-0 light s-12" id="password" name="password" type="password" minlength="8" <?= $isEdit ? '' : 'required' ?>>
                            <?php if ($isEdit): ?>
                                <small class="text-muted">Leave blank to keep the current password.</small>
                            <?php endif; ?>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="col-form-label s-12" for="password_confirmation">CONFIRM PASSWORD</label>
                            <input class="form-control r-0 light s-12" id="password_confirmation" name="password_confirmation" type="password" minlength="8" <?= $isEdit ? '' : 'required' ?>>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="card-body">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="icon-save mr-2"></i><?= $isEdit ? 'Save Changes' : 'Create User' ?>
                    </button>
                    <a href="/admin/users" class="btn btn-link">Cancel</a>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card no-b shadow-sm admin-card">
                <div class="card-body">
                    <h5 class="card-title">Access</h5>
                    <div class="form-group">
                        <label class="col-form-label s-12" for="role_id">ROLE</label>
                        <select class="custom-select form-control r-0 light s-12" id="role_id" name="role_id" required>
                            <?php foreach (($roles ?? []) as $role): ?>
                                <option value="<?= (int) $role['id'] ?>" <?= (int) $role['id'] === $selectedRole ? 'selected' : '' ?>>
                                    <?= escape($role['name'] === 'superuser' ? 'Admin' : 'Standard User') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group mb-0">
                        <label class="col-form-label s-12">STATUS</label>
                        <div class="custom-control custom-radio">
                            <input type="radio" id="active_yes" name="is_active" value="1" class="custom-control-input" <?= $active === 1 ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="active_yes">Active</label>
                        </div>
                        <div class="custom-control custom-radio">
                            <input type="radio" id="active_no" name="is_active" value="0" class="custom-control-input" <?= $active === 0 ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="active_no">Inactive</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card no-b shadow-sm admin-card mt-3">
                <div class="card-body">
                    <h5 class="card-title">Account Notes</h5>
                    <p class="text-muted mb-0">Public registration still creates standard users only. This admin form is the controlled place to create or promote admin accounts.</p>
                </div>
            </div>
        </div>
    </div>
</form>

<?= $this->end() ?>
