<?php

declare(strict_types=1);

namespace App\Services\Search;

use App\Database\TmdbMetadataSchema;
use App\Support\MediaUrl;
use Framework\Database;

class SearchService
{
    public function __construct(private Database $db)
    {
        TmdbMetadataSchema::ensure($db);
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function live(string $query, int $limit = 8): array
    {
        $query = trim(preg_replace('/\s+/', ' ', $query) ?? '');

        if (strlen($query) < 2) {
            return [];
        }

        $limit = max(1, min(12, $limit));
        $like = '%' . $query . '%';

        $items = $this->db->select(
            "SELECT DISTINCT media_items.id,
                    media_items.title,
                    media_items.slug,
                    media_items.type,
                    media_items.tmdb_id,
                    media_items.original_title,
                    media_items.release_year,
                    media_items.poster_image,
                    media_items.poster_url,
                    media_items.backdrop_image,
                    media_items.views,
                    media_items.tmdb_rating,
                    media_items.updated_at
             FROM media_items
             LEFT JOIN content_term_links
                ON content_term_links.owner_type = 'item'
                AND content_term_links.owner_id = media_items.id
             LEFT JOIN content_terms
                ON content_terms.id = content_term_links.term_id
                AND content_terms.taxonomy = 'genres'
             WHERE media_items.status = :status
             AND media_items.type IN ('movie', 'tv_show')
             AND (
                media_items.title LIKE :title_query
                OR media_items.slug LIKE :slug_query
                OR media_items.original_title LIKE :original_title_query
                OR content_terms.name LIKE :genre_name_query
                OR content_terms.slug LIKE :genre_slug_query
             )
             ORDER BY media_items.views DESC, media_items.tmdb_rating DESC, media_items.updated_at DESC, media_items.id DESC
             LIMIT {$limit}",
            [
                'status' => 'published',
                'title_query' => $like,
                'slug_query' => $like,
                'original_title_query' => $like,
                'genre_name_query' => $like,
                'genre_slug_query' => $like,
            ]
        );

        return array_values(array_filter(array_map([$this, 'resultPayload'], $items)));
    }

    /**
     * @param array<string, mixed> $item
     * @return array<string, string>|null
     */
    private function resultPayload(array $item): ?array
    {
        $watchUrl = MediaUrl::watchUrlForItem($item);

        if ($watchUrl === null) {
            return null;
        }

        return [
            'title' => (string) ($item['title'] ?? 'Untitled'),
            'year' => is_numeric($item['release_year'] ?? null) ? (string) $item['release_year'] : '',
            'image' => (string) (($item['poster_image'] ?? '') ?: ($item['poster_url'] ?? '') ?: ($item['backdrop_image'] ?? '')),
            'watchUrl' => $watchUrl,
        ];
    }
}
