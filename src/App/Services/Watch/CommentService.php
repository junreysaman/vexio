<?php

declare(strict_types=1);

namespace App\Services\Watch;

use Framework\Database;

class CommentService
{
    public function __construct(private Database $db)
    {
        $this->ensureSchema();
    }

    public function forItem(int $itemId, int $limit = 10): array
    {
        return $this->comments('item', $itemId, $limit);
    }

    public function forEpisode(int $episodeId, int $limit = 10): array
    {
        return $this->comments('episode', $episodeId, $limit);
    }

    public function count(string $ownerType, int $ownerId): int
    {
        if ($ownerId < 1) {
            return 0;
        }

        return (int) $this->db->scalar(
            "SELECT COUNT(*)
             FROM media_comments
             WHERE owner_type = :owner_type
             AND owner_id = :owner_id
             AND status = 'published'",
            [
                'owner_type' => $ownerType,
                'owner_id' => $ownerId,
            ]
        );
    }

    public function create(string $ownerType, int $ownerId, string $body, ?array $user, ?int $parentId = null): array
    {
        $ownerType = $ownerType === 'episode' ? 'episode' : 'item';
        $body = trim(preg_replace('/\s+/', ' ', $body) ?? '');

        if ($ownerId < 1) {
            throw new \InvalidArgumentException('Invalid comment target.');
        }

        if (!$user) {
            throw new \InvalidArgumentException('Please sign in to comment.');
        }

        if ($body === '' || strlen($body) > 1000) {
            throw new \InvalidArgumentException('Comments must be between 1 and 1000 characters.');
        }

        $parentId = max(0, (int) $parentId);
        if ($parentId > 0 && !$this->belongsToOwner($parentId, $ownerType, $ownerId)) {
            throw new \InvalidArgumentException('Invalid reply target.');
        }

        $displayName = trim((string) ($user['username'] ?? ''));
        if ($displayName === '') {
            $displayName = trim((string) (($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')));
        }
        if ($displayName === '') {
            $displayName = 'Guest Viewer';
        }

        $id = $this->db->insert('media_comments', [
            'owner_type' => $ownerType,
            'owner_id' => $ownerId,
            'parent_id' => $parentId > 0 ? $parentId : null,
            'user_id' => $user['id'] ?? null,
            'display_name' => $displayName,
            'body' => $body,
            'status' => 'published',
        ]);

        return $this->db->selectOne(
            "SELECT media_comments.*, users.username
             FROM media_comments
             LEFT JOIN users ON users.id = media_comments.user_id
             WHERE media_comments.id = :id
             LIMIT 1",
            ['id' => (int) $id]
        ) ?? [];
    }

    private function comments(string $ownerType, int $ownerId, int $limit): array
    {
        if ($ownerId < 1) {
            return [];
        }

        $rows = $this->db->select(
            "SELECT media_comments.*, users.username
             FROM media_comments
             LEFT JOIN users ON users.id = media_comments.user_id
             WHERE media_comments.owner_type = :owner_type
             AND media_comments.owner_id = :owner_id
             AND media_comments.status = 'published'
             ORDER BY COALESCE(media_comments.parent_id, media_comments.id) DESC,
                      media_comments.parent_id IS NOT NULL ASC,
                      media_comments.created_at ASC
             LIMIT " . max(1, min(50, $limit)),
            [
                'owner_type' => $ownerType,
                'owner_id' => $ownerId,
            ]
        );

        return $this->thread($rows);
    }

    private function belongsToOwner(int $commentId, string $ownerType, int $ownerId): bool
    {
        return $this->db->exists(
            'SELECT 1 FROM media_comments
             WHERE id = :id
             AND owner_type = :owner_type
             AND owner_id = :owner_id
             LIMIT 1',
            [
                'id' => $commentId,
                'owner_type' => $ownerType,
                'owner_id' => $ownerId,
            ]
        );
    }

    private function thread(array $rows): array
    {
        $comments = [];
        foreach ($rows as $row) {
            $row['replies'] = [];
            $comments[(int) $row['id']] = $row;
        }

        $thread = [];
        foreach ($comments as $id => &$comment) {
            $parentId = (int) ($comment['parent_id'] ?? 0);
            if ($parentId > 0 && isset($comments[$parentId])) {
                $comments[$parentId]['replies'][] = &$comment;
                continue;
            }
            $thread[] = &$comment;
        }
        unset($comment);

        return $thread;
    }

    private function ensureSchema(): void
    {
        if ($this->db->tableExists('media_comments')) {
            $this->ensureSchemaColumn('parent_id', 'parent_id BIGINT UNSIGNED DEFAULT NULL AFTER owner_id');
            return;
        }

        $this->db->query(
            "CREATE TABLE media_comments (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                owner_type ENUM('item', 'episode') NOT NULL,
                owner_id BIGINT UNSIGNED NOT NULL,
                parent_id BIGINT UNSIGNED DEFAULT NULL,
                user_id BIGINT UNSIGNED DEFAULT NULL,
                display_name VARCHAR(140) NOT NULL,
                body TEXT NOT NULL,
                likes INT UNSIGNED NOT NULL DEFAULT 0,
                status ENUM('published', 'hidden') NOT NULL DEFAULT 'published',
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                INDEX idx_media_comments_owner (owner_type, owner_id, status, created_at),
                INDEX idx_media_comments_parent (parent_id),
                INDEX idx_media_comments_user (user_id),
                CONSTRAINT fk_media_comments_user FOREIGN KEY (user_id) REFERENCES users(id)
                    ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        return;
    }

    private function ensureSchemaColumn(string $column, string $definition): void
    {
        $exists = $this->db->exists(
            'SELECT 1 FROM information_schema.columns
             WHERE table_schema = DATABASE()
             AND table_name = :table
             AND column_name = :column
             LIMIT 1',
            ['table' => 'media_comments', 'column' => $column]
        );

        if (!$exists) {
            $this->db->query('ALTER TABLE media_comments ADD COLUMN ' . $definition);
        }
    }
}
