<?php

declare(strict_types=1);

namespace App\Services\Admin;

use Framework\Database;

class UserService
{
    public function __construct(private Database $db)
    {
    }

    public function all(): array
    {
        return $this->db->select(
            'SELECT users.id, users.first_name, users.last_name, users.username, users.email,
                    users.is_active, users.created_at, users.updated_at, roles.name AS role_name
             FROM users
             INNER JOIN roles ON roles.id = users.role_id
             ORDER BY users.created_at DESC, users.id DESC'
        );
    }

    public function stats(): array
    {
        return [
            'total' => (int) $this->db->scalar('SELECT COUNT(*) FROM users'),
            'active' => (int) $this->db->countWhere('users', ['is_active' => 1]),
            'admins' => (int) $this->db->scalar(
                'SELECT COUNT(*)
                 FROM users
                 INNER JOIN roles ON roles.id = users.role_id
                 WHERE roles.name = :role',
                ['role' => 'superuser']
            ),
            'regular' => (int) $this->db->scalar(
                'SELECT COUNT(*)
                 FROM users
                 INNER JOIN roles ON roles.id = users.role_id
                 WHERE roles.name = :role',
                ['role' => 'regular']
            ),
        ];
    }

    public function find(int $id): ?array
    {
        return $this->db->selectOne(
            'SELECT users.*, roles.name AS role_name
             FROM users
             INNER JOIN roles ON roles.id = users.role_id
             WHERE users.id = :id
             LIMIT 1',
            ['id' => $id]
        );
    }

    public function roles(): array
    {
        return $this->db->select('SELECT id, name, description FROM roles ORDER BY id ASC');
    }

    public function create(array $data): int
    {
        return (int) $this->db->insert('users', [
            'first_name' => trim((string) $data['first_name']),
            'last_name' => trim((string) $data['last_name']),
            'username' => trim((string) $data['username']),
            'email' => strtolower(trim((string) $data['email'])),
            'password' => password_hash((string) $data['password'], PASSWORD_DEFAULT),
            'role_id' => (int) $data['role_id'],
            'is_active' => (int) ($data['is_active'] ?? 1),
        ]);
    }

    public function update(int $id, array $data): void
    {
        $payload = [
            'first_name' => trim((string) $data['first_name']),
            'last_name' => trim((string) $data['last_name']),
            'username' => trim((string) $data['username']),
            'email' => strtolower(trim((string) $data['email'])),
            'role_id' => (int) $data['role_id'],
            'is_active' => (int) ($data['is_active'] ?? 0),
        ];

        if (!empty($data['password'])) {
            $payload['password'] = password_hash((string) $data['password'], PASSWORD_DEFAULT);
        }

        $this->db->updateById('users', $id, $payload);
    }

    public function delete(int $id): void
    {
        $this->db->deleteById('users', $id);
    }

    public function identityExists(string $email, string $username, ?int $exceptId = null): bool
    {
        $params = [
            'email' => strtolower(trim($email)),
            'username' => trim($username),
        ];

        $sql = 'SELECT 1 FROM users WHERE (email = :email OR username = :username)';

        if ($exceptId !== null) {
            $sql .= ' AND id <> :except_id';
            $params['except_id'] = $exceptId;
        }

        return $this->db->exists($sql . ' LIMIT 1', $params);
    }

    public function roleExists(int $roleId): bool
    {
        return $this->db->existsWhere('roles', ['id' => $roleId]);
    }
}
