<?php

declare(strict_types=1);

namespace App\Services\Auth;

use Framework\Database;

class AuthService
{
    public function __construct(private Database $db)
    {
    }

    public function attempt(string $identity, string $password): ?array
    {
        $user = $this->db->selectOne(
            'SELECT users.*, roles.name AS role_name
             FROM users
             INNER JOIN roles ON roles.id = users.role_id
             WHERE (users.email = :identity_email OR users.username = :identity_username)
             AND users.is_active = 1
             LIMIT 1',
            [
                'identity_email' => strtolower(trim($identity)),
                'identity_username' => trim($identity),
            ]
        );

        if (!$user || !password_verify($password, (string) $user['password'])) {
            return null;
        }

        return $this->sessionUser($user);
    }

    public function registerRegularUser(array $data): array
    {
        $roleId = (int) $this->db->value('roles', 'id', ['name' => 'regular']);

        $user = $this->db->insertGet('users', [
            'first_name' => trim((string) $data['first_name']),
            'last_name' => trim((string) $data['last_name']),
            'username' => trim((string) $data['username']),
            'email' => strtolower(trim((string) $data['email'])),
            'password' => password_hash((string) $data['password'], PASSWORD_DEFAULT),
            'role_id' => $roleId,
            'is_active' => 1,
        ]);

        $user['role_name'] = 'regular';

        return $this->sessionUser($user);
    }

    public function identityExists(string $email, string $username): bool
    {
        return $this->db->exists(
            'SELECT 1 FROM users
             WHERE email = :email OR username = :username
             LIMIT 1',
            [
                'email' => strtolower(trim($email)),
                'username' => trim($username),
            ]
        );
    }

    private function sessionUser(array $user): array
    {
        return [
            'id' => (int) $user['id'],
            'first_name' => (string) $user['first_name'],
            'last_name' => (string) $user['last_name'],
            'username' => (string) $user['username'],
            'email' => (string) $user['email'],
            'role' => (string) ($user['role_name'] ?? 'regular'),
        ];
    }
}
