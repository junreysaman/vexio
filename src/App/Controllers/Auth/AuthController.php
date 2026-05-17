<?php

declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Services\Auth\AuthService;
use Framework\Http\Request;
use Framework\Http\Response;

class AuthController
{
    public function __construct(private AuthService $auth)
    {
    }

    public function authenticate(Request $request, Response $response): void
    {
        $identity = trim((string) $request->post('identity', ''));
        $password = (string) $request->post('password', '');

        if ($identity === '' || $password === '') {
            $this->backWithError('Please enter your username or email and password.', $identity);
        }

        $user = $this->auth->attempt($identity, $password);

        if (!$user) {
            $this->backWithError('Those credentials do not match an active account.', $identity);
        }

        session_regenerate_id(true);
        $_SESSION['user'] = $user;
        setFlash('auth', 'Welcome back, ' . $user['first_name'] . '.', 'success');

        redirectTo($user['role'] === 'superuser' ? '/admin/dashboard' : '/');
    }

    public function store(Request $request, Response $response): void
    {
        $data = $request->only([
            'first_name',
            'last_name',
            'username',
            'email',
            'password',
            'password_confirmation',
        ]);

        $this->validateRegistration($data);

        if ($this->auth->identityExists((string) $data['email'], (string) $data['username'])) {
            $this->backWithFormError('An account already exists with that email or username.', $data);
        }

        $user = $this->auth->registerRegularUser($data);

        session_regenerate_id(true);
        $_SESSION['user'] = $user;
        setFlash('auth', 'Your standard account is ready.', 'success');

        redirectTo('/');
    }

    public function logout(Request $request, Response $response): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_destroy();
        redirectTo('/login');
    }

    private function validateRegistration(array $data): void
    {
        $required = ['first_name', 'last_name', 'username', 'email', 'password', 'password_confirmation'];

        foreach ($required as $field) {
            if (trim((string) ($data[$field] ?? '')) === '') {
                $this->backWithFormError('Please complete every field.', $data);
            }
        }

        if (!filter_var((string) $data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->backWithFormError('Please enter a valid email address.', $data);
        }

        if (strlen((string) $data['password']) < 8) {
            $this->backWithFormError('Password must be at least 8 characters.', $data);
        }

        if ($data['password'] !== $data['password_confirmation']) {
            $this->backWithFormError('Password confirmation must match.', $data);
        }
    }

    private function backWithError(string $message, string $identity = ''): void
    {
        setFlash('auth_error', $message, 'danger');
        $_SESSION['oldFormData'] = ['identity' => $identity];
        redirectTo('/login');
    }

    private function backWithFormError(string $message, array $data): void
    {
        setFlash('auth_error', $message, 'danger');
        $_SESSION['oldFormData'] = array_diff_key($data, array_flip(['password', 'password_confirmation', 'token']));
        redirectTo('/register');
    }
}
