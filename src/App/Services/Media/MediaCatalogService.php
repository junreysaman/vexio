<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Database\TmdbMetadataSchema;
use App\Support\MediaUrl;
use Framework\Database;

class MediaCatalogService
{
    public function __construct(private Database $db)
    {
        TmdbMetadataSchema::ensure($db);
    }

    public function trendingByViews(int $limit = 8): array
    {
        $items = $this->db->select(
            'SELECT id, title, slug, type, tmdb_id, views, poster_url, backdrop_url, status, release_year
             FROM media_items
             WHERE status = :status
             ORDER BY views DESC, updated_at DESC
             LIMIT ' . max(1, $limit),
            ['status' => 'published']
        );

        return array_map(function (array $item): array {
            $watchUrl = MediaUrl::watchUrlForItem($item);

            return [
                ...$item,
                'slug' => MediaUrl::itemSlug($item),
                'watch_url' => $watchUrl,
                'watchUrl' => $watchUrl,
            ];
        }, $items);
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
