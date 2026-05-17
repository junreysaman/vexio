<?php

declare(strict_types=1);

namespace App\Services\Media;

use Framework\Database;

class MediaCatalogService
{
    public function __construct(private Database $db)
    {
    }

    public function trendingByViews(int $limit = 8): array
    {
        return $this->db->select(
            'SELECT id, title, type, views, poster_url, poster_image, backdrop_image, status, release_year
             FROM media_items
             WHERE status = :status
             ORDER BY views DESC, updated_at DESC
             LIMIT ' . max(1, $limit),
            ['status' => 'published']
        );
    }

    public function dashboardStats(): array
    {
        return [
            'published' => (int) $this->db->countWhere('media_items', ['status' => 'published']),
            'movies' => (int) $this->db->countWhere('media_items', ['type' => 'movie']),
            'tvEpisodes' => (int) $this->db->countWhere('media_episodes', ['tmdb_type' => 'tv_episode']),
            'totalViews' => (int) ($this->db->scalar('SELECT COALESCE(SUM(views), 0) FROM media_items') ?? 0)
                + (int) ($this->db->scalar('SELECT COALESCE(SUM(views), 0) FROM media_episodes') ?? 0),
        ];
    }
}
