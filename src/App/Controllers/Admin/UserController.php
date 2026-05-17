<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Services\Admin\UserService;
use Framework\Http\Request;
use Framework\Http\Response;
use Framework\TemplateEngine;

class UserController
{
    public function __construct(
        private TemplateEngine $view,
        private UserService $users
    ) {
    }

    public function index(Request $request, Response $response): Response
    {
        return $response->html($this->view->render('admin/users/index', 'layouts/backend/paper', [
            'title' => 'Users',
            'body_class' => 'paper-backend users-page',
            'users' => $this->users->all(),
            'stats' => $this->users->stats(),
        ]));
    }

    public function create(Request $request, Response $response): Response
    {
        return $response->html($this->view->render('admin/users/form', 'layouts/backend/paper', [
            'title' => 'Create User',
            'body_class' => 'paper-backend users-page',
            'mode' => 'create',
            'roles' => $this->users->roles(),
            'user' => [],
        ]));
    }

    public function store(Request $request, Response $response): void
    {
        $data = $this->userData($request);
        $this->validate($data, null, true);

        $id = $this->users->create($data);
        setFlash('users', 'User created successfully.', 'success');
        redirectTo('/admin/users/' . $id . '/edit');
    }

    public function edit(Request $request, Response $response, string $id): Response
    {
        $user = $this->findOrRedirect((int) $id);

        return $response->html($this->view->render('admin/users/form', 'layouts/backend/paper', [
            'title' => 'Edit User',
            'body_class' => 'paper-backend users-page',
            'mode' => 'edit',
            'roles' => $this->users->roles(),
            'user' => $user,
        ]));
    }

    public function update(Request $request, Response $response, string $id): void
    {
        $userId = (int) $id;
        $this->findOrRedirect($userId);

        $data = $this->userData($request);
        $this->validate($data, $userId, false);

        $this->users->update($userId, $data);
        setFlash('users', 'User updated successfully.', 'success');
        redirectTo('/admin/users/' . $userId . '/edit');
    }

    public function destroy(Request $request, Response $response, string $id): void
    {
        $userId = (int) $id;
        $user = $this->findOrRedirect($userId);

        if (($request->user()['id'] ?? null) === $userId) {
            setFlash('users', 'You cannot delete your own admin session.', 'danger');
            redirectTo('/admin/users');
        }

        $this->users->delete($userId);
        setFlash('users', 'Deleted ' . $user['first_name'] . ' ' . $user['last_name'] . '.', 'success');
        redirectTo('/admin/users');
    }

    private function userData(Request $request): array
    {
        return [
            'first_name' => trim((string) $request->post('first_name', '')),
            'last_name' => trim((string) $request->post('last_name', '')),
            'username' => trim((string) $request->post('username', '')),
            'email' => strtolower(trim((string) $request->post('email', ''))),
            'password' => (string) $request->post('password', ''),
            'password_confirmation' => (string) $request->post('password_confirmation', ''),
            'role_id' => (int) $request->post('role_id', 0),
            'is_active' => $request->post('is_active') === '1' ? 1 : 0,
        ];
    }

    private function validate(array $data, ?int $userId, bool $passwordRequired): void
    {
        foreach (['first_name', 'last_name', 'username', 'email'] as $field) {
            if ($data[$field] === '') {
                $this->backWithFormError('Please complete all required profile fields.', $data, $userId);
            }
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->backWithFormError('Please enter a valid email address.', $data, $userId);
        }

        if (!$this->users->roleExists((int) $data['role_id'])) {
            $this->backWithFormError('Please choose a valid role.', $data, $userId);
        }

        if ($this->users->identityExists($data['email'], $data['username'], $userId)) {
            $this->backWithFormError('Email or username is already in use.', $data, $userId);
        }

        if ($passwordRequired && $data['password'] === '') {
            $this->backWithFormError('Please set an initial password.', $data, $userId);
        }

        if ($data['password'] !== '' && strlen($data['password']) < 8) {
            $this->backWithFormError('Password must be at least 8 characters.', $data, $userId);
        }

        if ($data['password'] !== $data['password_confirmation']) {
            $this->backWithFormError('Password confirmation must match.', $data, $userId);
        }
    }

    private function findOrRedirect(int $id): array
    {
        $user = $id > 0 ? $this->users->find($id) : null;

        if (!$user) {
            setFlash('users', 'The requested user could not be found.', 'danger');
            redirectTo('/admin/users');
        }

        return $user;
    }

    private function backWithFormError(string $message, array $data, ?int $userId): void
    {
        setFlash('users', $message, 'danger');
        $_SESSION['oldFormData'] = array_diff_key($data, array_flip(['password', 'password_confirmation', 'token']));

        redirectTo($userId ? '/admin/users/' . $userId . '/edit' : '/admin/users/create');
    }
}
