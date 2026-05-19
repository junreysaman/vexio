<?php

declare(strict_types=1);

namespace App\Services\Forum;

use Framework\Database;
use InvalidArgumentException;

class ForumService
{
    public const CATEGORIES = [
        'discussion' => ['label' => 'Episode Discussion', 'color' => 'purple'],
        'review'     => ['label' => 'Review',             'color' => 'gold'],
        'recommend'  => ['label' => 'Recommendations',    'color' => 'cyan'],
        'news'       => ['label' => 'News',                'color' => 'red'],
        'help'       => ['label' => 'Help & Questions',   'color' => 'green'],
        'offtopic'   => ['label' => 'Off-Topic',          'color' => 'muted'],
    ];

    public const SORT_OPTIONS = [
        'latest'   => 'Latest',
        'replies'  => 'Most Replies',
        'views'    => 'Most Views',
        'votes'    => 'Top Voted',
    ];

    private const PER_PAGE = 20;

    public function __construct(private Database $db)
    {
        $this->ensureSchema();
    }

    // ── Thread listing ────────────────────────────────────────────────────

    public function paginate(
        string $category = '',
        string $sort = 'latest',
        int $page = 1
    ): array {
        $page    = max(1, $page);
        $offset  = ($page - 1) * self::PER_PAGE;
        $where   = "WHERE ft.status = 'published'";
        $params  = [];

        if ($category !== '' && isset(self::CATEGORIES[$category])) {
            $where .= ' AND ft.category = :category';
            $params['category'] = $category;
        }

        $orderBy = match ($sort) {
            'replies' => 'ft.reply_count DESC, ft.created_at DESC',
            'views'   => 'ft.views DESC, ft.created_at DESC',
            'votes'   => 'ft.votes DESC, ft.created_at DESC',
            default   => 'ft.is_pinned DESC, ft.last_reply_at DESC, ft.created_at DESC',
        };

        $total = (int) $this->db->scalar(
            "SELECT COUNT(*) FROM forum_threads ft $where",
            $params
        );

        $threads = $this->db->select(
            "SELECT ft.*,
                    u.username AS author_username,
                    u.first_name AS author_first_name,
                    ru.username AS last_reply_username
             FROM forum_threads ft
             LEFT JOIN users u  ON u.id  = ft.user_id
             LEFT JOIN users ru ON ru.id = ft.last_reply_user_id
             $where
             ORDER BY $orderBy
             LIMIT " . self::PER_PAGE . " OFFSET $offset",
            $params
        );

        return [
            'data' => array_map([$this, 'formatThread'], $threads),
            'meta' => [
                'total'        => $total,
                'per_page'     => self::PER_PAGE,
                'current_page' => $page,
                'last_page'    => max(1, (int) ceil($total / self::PER_PAGE)),
            ],
        ];
    }

    public function stats(): array
    {
        $total   = (int) $this->db->scalar("SELECT COUNT(*) FROM forum_threads WHERE status = 'published'");
        $members = (int) $this->db->scalar('SELECT COUNT(*) FROM users WHERE is_active = 1');
        $today   = (int) $this->db->scalar(
            "SELECT COUNT(*) FROM forum_replies WHERE DATE(created_at) = CURDATE() AND status = 'published'"
        );

        $counts = [];
        foreach (array_keys(self::CATEGORIES) as $cat) {
            $counts[$cat] = (int) $this->db->scalar(
                "SELECT COUNT(*) FROM forum_threads WHERE category = :c AND status = 'published'",
                ['c' => $cat]
            );
        }

        return compact('total', 'members', 'today', 'counts');
    }

    // ── Single thread ─────────────────────────────────────────────────────

    public function findThread(int $id): ?array
    {
        $thread = $this->db->selectOne(
            "SELECT ft.*, u.username AS author_username, u.first_name AS author_first_name
             FROM forum_threads ft
             LEFT JOIN users u ON u.id = ft.user_id
             WHERE ft.id = :id AND ft.status = 'published'
             LIMIT 1",
            ['id' => $id]
        );

        return $thread ? $this->formatThread($thread) : null;
    }

    public function incrementViews(int $threadId): void
    {
        $this->db->increment('forum_threads', 'views', 1, ['id' => $threadId]);
    }

    public function replies(int $threadId, int $page = 1): array
    {
        $perPage = 30;
        $offset  = ($page - 1) * $perPage;

        $total = (int) $this->db->scalar(
            "SELECT COUNT(*) FROM forum_replies WHERE thread_id = :id AND status = 'published'",
            ['id' => $threadId]
        );

        $rows = $this->db->select(
            "SELECT fr.*, u.username, u.first_name, u.last_name
             FROM forum_replies fr
             LEFT JOIN users u ON u.id = fr.user_id
             WHERE fr.thread_id = :id AND fr.status = 'published'
             ORDER BY fr.created_at ASC
             LIMIT $perPage OFFSET $offset",
            ['id' => $threadId]
        );

        return [
            'data' => array_map([$this, 'formatReply'], $rows),
            'meta' => [
                'total'        => $total,
                'per_page'     => $perPage,
                'current_page' => $page,
                'last_page'    => max(1, (int) ceil($total / $perPage)),
            ],
        ];
    }

    // ── Create thread ─────────────────────────────────────────────────────

    public function createThread(array $data, array $user): array
    {
        $title    = trim((string) ($data['title'] ?? ''));
        $body     = trim((string) ($data['body'] ?? ''));
        $category = (string) ($data['category'] ?? 'discussion');

        if ($title === '' || strlen($title) < 5 || strlen($title) > 200) {
            throw new InvalidArgumentException('Title must be between 5 and 200 characters.');
        }

        if ($body === '' || strlen($body) < 10 || strlen($body) > 10000) {
            throw new InvalidArgumentException('Body must be between 10 and 10,000 characters.');
        }

        if (!isset(self::CATEGORIES[$category])) {
            throw new InvalidArgumentException('Invalid category.');
        }

        $id = $this->db->insert('forum_threads', [
            'user_id'         => (int) $user['id'],
            'category'        => $category,
            'title'           => $title,
            'body'            => $body,
            'status'          => 'published',
            'views'           => 0,
            'reply_count'     => 0,
            'votes'           => 0,
            'is_pinned'       => 0,
            'last_reply_at'   => date('Y-m-d H:i:s'),
        ]);

        return $this->findThread((int) $id) ?? [];
    }

    // ── Create reply ──────────────────────────────────────────────────────

    public function createReply(int $threadId, string $body, array $user): array
    {
        $body = trim($body);

        if ($body === '' || strlen($body) < 2 || strlen($body) > 5000) {
            throw new InvalidArgumentException('Reply must be between 2 and 5,000 characters.');
        }

        $thread = $this->db->findById('forum_threads', $threadId);
        if (!$thread || $thread['status'] !== 'published') {
            throw new InvalidArgumentException('Thread not found.');
        }

        $id = $this->db->insert('forum_replies', [
            'thread_id' => $threadId,
            'user_id'   => (int) $user['id'],
            'body'      => $body,
            'status'    => 'published',
            'votes'     => 0,
        ]);

        // Update thread reply count + last reply info
        $this->db->update('forum_threads', [
            'reply_count'          => (int) $thread['reply_count'] + 1,
            'last_reply_at'        => date('Y-m-d H:i:s'),
            'last_reply_user_id'   => (int) $user['id'],
        ], ['id' => $threadId]);

        $reply = $this->db->selectOne(
            "SELECT fr.*, u.username, u.first_name, u.last_name
             FROM forum_replies fr
             LEFT JOIN users u ON u.id = fr.user_id
             WHERE fr.id = :id LIMIT 1",
            ['id' => (int) $id]
        );

        return $this->formatReply($reply ?? []);
    }

    // ── Voting ────────────────────────────────────────────────────────────

    public function voteThread(int $threadId, int $userId, int $value): int
    {
        $value = $value >= 0 ? 1 : -1;

        $existing = $this->db->selectOne(
            'SELECT * FROM forum_votes WHERE target_type = :t AND target_id = :id AND user_id = :u LIMIT 1',
            ['t' => 'thread', 'id' => $threadId, 'u' => $userId]
        );

        if ($existing) {
            if ((int) $existing['value'] === $value) {
                // Undo vote
                $this->db->delete('forum_votes', ['id' => (int) $existing['id']]);
                $this->db->increment('forum_threads', 'votes', -$value, ['id' => $threadId]);
            } else {
                // Change vote
                $this->db->updateById('forum_votes', (int) $existing['id'], ['value' => $value]);
                $this->db->increment('forum_threads', 'votes', $value * 2, ['id' => $threadId]);
            }
        } else {
            $this->db->insert('forum_votes', [
                'target_type' => 'thread',
                'target_id'   => $threadId,
                'user_id'     => $userId,
                'value'       => $value,
            ]);
            $this->db->increment('forum_threads', 'votes', $value, ['id' => $threadId]);
        }

        return (int) $this->db->scalar(
            'SELECT votes FROM forum_threads WHERE id = :id',
            ['id' => $threadId]
        );
    }

    // ── Formatters ────────────────────────────────────────────────────────

    private function formatThread(array $row): array
    {
        $category = (string) ($row['category'] ?? 'discussion');
        $catInfo  = self::CATEGORIES[$category] ?? self::CATEGORIES['discussion'];
        $author   = (string) ($row['author_username'] ?? $row['author_first_name'] ?? 'Anonymous');
        $lastUser = (string) ($row['last_reply_username'] ?? '');

        return [
            'id'          => (int) $row['id'],
            'title'       => (string) $row['title'],
            'body'        => (string) $row['body'],
            'preview'     => mb_substr(strip_tags((string) $row['body']), 0, 160),
            'category'    => $category,
            'cat_label'   => $catInfo['label'],
            'cat_color'   => $catInfo['color'],
            'author'      => $author,
            'author_id'   => (int) ($row['user_id'] ?? 0),
            'is_pinned'   => (bool) ($row['is_pinned'] ?? false),
            'views'       => (int) ($row['views'] ?? 0),
            'reply_count' => (int) ($row['reply_count'] ?? 0),
            'votes'       => (int) ($row['votes'] ?? 0),
            'last_reply'  => $lastUser !== '' ? $lastUser : null,
            'last_reply_at' => (string) ($row['last_reply_at'] ?? $row['created_at'] ?? ''),
            'created_at'  => (string) ($row['created_at'] ?? ''),
            'time_ago'    => $this->timeAgo((string) ($row['created_at'] ?? '')),
            'url'         => '/forum/thread/' . (int) $row['id'],
        ];
    }

    private function formatReply(array $row): array
    {
        $author = (string) ($row['username'] ?? $row['first_name'] ?? 'Anonymous');

        return [
            'id'         => (int) ($row['id'] ?? 0),
            'thread_id'  => (int) ($row['thread_id'] ?? 0),
            'body'       => (string) ($row['body'] ?? ''),
            'author'     => $author,
            'author_id'  => (int) ($row['user_id'] ?? 0),
            'votes'      => (int) ($row['votes'] ?? 0),
            'created_at' => (string) ($row['created_at'] ?? ''),
            'time_ago'   => $this->timeAgo((string) ($row['created_at'] ?? '')),
        ];
    }

    private function timeAgo(string $datetime): string
    {
        if ($datetime === '') {
            return 'just now';
        }

        $diff = time() - (int) strtotime($datetime);

        return match (true) {
            $diff < 60     => 'just now',
            $diff < 3600   => (int) ($diff / 60) . ' min ago',
            $diff < 86400  => (int) ($diff / 3600) . ' hours ago',
            $diff < 604800 => (int) ($diff / 86400) . ' days ago',
            default        => date('M j, Y', (int) strtotime($datetime)),
        };
    }

    // ── Schema ────────────────────────────────────────────────────────────

    private function ensureSchema(): void
    {
        if (!$this->db->tableExists('forum_threads')) {
            $this->db->query(
                "CREATE TABLE forum_threads (
                    id                   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    user_id              BIGINT UNSIGNED DEFAULT NULL,
                    category             VARCHAR(40) NOT NULL DEFAULT 'discussion',
                    title                VARCHAR(200) NOT NULL,
                    body                 TEXT NOT NULL,
                    status               ENUM('published','hidden','deleted') NOT NULL DEFAULT 'published',
                    is_pinned            TINYINT(1) NOT NULL DEFAULT 0,
                    views                BIGINT UNSIGNED NOT NULL DEFAULT 0,
                    reply_count          INT UNSIGNED NOT NULL DEFAULT 0,
                    votes                INT NOT NULL DEFAULT 0,
                    last_reply_at        DATETIME DEFAULT NULL,
                    last_reply_user_id   BIGINT UNSIGNED DEFAULT NULL,
                    created_at           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    INDEX idx_forum_threads_status_cat (status, category),
                    INDEX idx_forum_threads_user (user_id),
                    INDEX idx_forum_threads_pinned (is_pinned, last_reply_at),
                    CONSTRAINT fk_forum_threads_user
                        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );
        }

        if (!$this->db->tableExists('forum_replies')) {
            $this->db->query(
                "CREATE TABLE forum_replies (
                    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    thread_id  BIGINT UNSIGNED NOT NULL,
                    user_id    BIGINT UNSIGNED DEFAULT NULL,
                    body       TEXT NOT NULL,
                    status     ENUM('published','hidden','deleted') NOT NULL DEFAULT 'published',
                    votes      INT NOT NULL DEFAULT 0,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    INDEX idx_forum_replies_thread (thread_id, status, created_at),
                    INDEX idx_forum_replies_user (user_id),
                    CONSTRAINT fk_forum_replies_thread
                        FOREIGN KEY (thread_id) REFERENCES forum_threads(id) ON DELETE CASCADE,
                    CONSTRAINT fk_forum_replies_user
                        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );
        }

        if (!$this->db->tableExists('forum_votes')) {
            $this->db->query(
                "CREATE TABLE forum_votes (
                    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    target_type ENUM('thread','reply') NOT NULL,
                    target_id   BIGINT UNSIGNED NOT NULL,
                    user_id     BIGINT UNSIGNED NOT NULL,
                    value       TINYINT NOT NULL DEFAULT 1,
                    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    UNIQUE KEY uq_forum_votes (target_type, target_id, user_id),
                    INDEX idx_forum_votes_user (user_id),
                    CONSTRAINT fk_forum_votes_user
                        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );
        }
    }
}
