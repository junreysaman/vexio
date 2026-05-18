<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Services\Watch\CommentService as WatchCommentService;
use Framework\Database;

class CommentService
{
    public function __construct(
        private Database $db,
        private WatchCommentService $schema
    ) {
    }

    public function paginate(string $status = 'all', string $search = '', int $page = 1, int $perPage = 20): array
    {
        $page = max(1, $page);
        $perPage = min(100, max(5, $perPage));
        $offset = ($page - 1) * $perPage;
        [$where, $params] = $this->filterClause($status, $search);

        $total = (int) $this->db->scalar('SELECT COUNT(*) FROM media_comments ' . $where, $params);

        $rows = $this->db->select(
            "SELECT media_comments.*,
                    users.username,
                    users.email,
                    item.title AS item_title,
                    episode.episode_name,
                    episode.episode_number,
                    episode.season_number,
                    episode_item.title AS episode_item_title
             FROM media_comments
             LEFT JOIN users ON users.id = media_comments.user_id
             LEFT JOIN media_items item ON media_comments.owner_type = 'item' AND item.id = media_comments.owner_id
             LEFT JOIN media_episodes episode ON media_comments.owner_type = 'episode' AND episode.id = media_comments.owner_id
             LEFT JOIN media_items episode_item ON episode_item.id = episode.media_item_id
             {$where}
             ORDER BY media_comments.created_at DESC, media_comments.id DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        return [
            'data' => $rows,
            'meta' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => max(1, (int) ceil($total / $perPage)),
            ],
        ];
    }

    public function stats(): array
    {
        return [
            'total' => (int) $this->db->scalar('SELECT COUNT(*) FROM media_comments'),
            'published' => (int) $this->db->countWhere('media_comments', ['status' => 'published']),
            'hidden' => (int) $this->db->countWhere('media_comments', ['status' => 'hidden']),
            'replies' => (int) $this->db->scalar('SELECT COUNT(*) FROM media_comments WHERE parent_id IS NOT NULL'),
        ];
    }

    public function setStatus(int $id, string $status): void
    {
        if (!in_array($status, ['published', 'hidden'], true)) {
            return;
        }

        $this->db->updateById('media_comments', $id, ['status' => $status]);
    }

    public function delete(int $id): void
    {
        $this->db->delete('media_comments', ['parent_id' => $id]);
        $this->db->deleteById('media_comments', $id);
    }

    private function filterClause(string $status, string $search): array
    {
        $clauses = [];
        $params = [];

        if (in_array($status, ['published', 'hidden'], true)) {
            $clauses[] = 'media_comments.status = :status';
            $params['status'] = $status;
        }

        $search = trim($search);
        if ($search !== '') {
            $clauses[] = '(media_comments.body LIKE :search
                OR media_comments.display_name LIKE :search
                OR users.username LIKE :search
                OR users.email LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        return [
            $clauses ? ' WHERE ' . implode(' AND ', $clauses) : '',
            $params,
        ];
    }
}
