<?php

declare(strict_types=1);

namespace App\Services\Admin\Content;

use App\Database\TmdbMetadataSchema;
use App\Support\MediaUrl;
use Framework\Database;

class ContentService
{
    public const TYPES = [
        'all' => 'All Content',
        'movie' => 'Movies',
        'tv_show' => 'TV Shows',
    ];

    public const STATUSES = [
        'draft' => 'Draft',
        'published' => 'Published',
        'archived' => 'Archived',
    ];

    public function __construct(private Database $db)
    {
        TmdbMetadataSchema::ensure($db);
    }

    /**
     * Returns a paginated top-level catalogue list filtered by type, status, and search term.
     */
    public function paginate(string $type = 'all', string $search = '', string $status = 'all', int $page = 1, int $perPage = 20): array
    {
        $page = max(1, $page);
        $perPage = min(100, max(5, $perPage));
        $offset = ($page - 1) * $perPage;
        [$whereSql, $params] = $this->filterClause($type, $search, $status);

        $total = (int) $this->db->scalar(
            'SELECT COUNT(*) FROM media_items' . $whereSql,
            $params
        );

        $items = $this->db->select(
            'SELECT media_items.*,
                    COUNT(DISTINCT media_seasons.id) AS seasons_count,
                    COUNT(DISTINCT media_episodes.id) AS episodes_count
             FROM media_items
             LEFT JOIN media_seasons ON media_seasons.media_item_id = media_items.id
             LEFT JOIN media_episodes ON media_episodes.media_item_id = media_items.id'
            . $whereSql .
            ' GROUP BY media_items.id
              ORDER BY media_items.updated_at DESC, media_items.created_at DESC, media_items.id DESC
              LIMIT ' . $perPage . ' OFFSET ' . $offset,
            $params
        );

        return [
            'data' => array_map([$this, 'withWatchUrls'], $items),
            'meta' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => max(1, (int) ceil($total / $perPage)),
            ],
        ];
    }

    /**
     * Counts top-level catalogue records for the content type filter tiles.
     */
    public function stats(): array
    {
        $stats = [
            'all' => (int) $this->db->scalar('SELECT COUNT(*) FROM media_items'),
        ];

        foreach (array_keys(self::TYPES) as $type) {
            if ($type === 'all') {
                continue;
            }

            $stats[$type] = (int) $this->db->countWhere('media_items', ['type' => $type]);
        }

        return $stats;
    }

    /**
     * Finds a single top-level media item by primary key.
     */
    public function find(int $id): ?array
    {
        $item = $this->db->selectOne(
            'SELECT media_items.*,
                    COUNT(DISTINCT media_seasons.id) AS seasons_count,
                    COUNT(DISTINCT media_episodes.id) AS episodes_count
             FROM media_items
             LEFT JOIN media_seasons ON media_seasons.media_item_id = media_items.id
             LEFT JOIN media_episodes ON media_episodes.media_item_id = media_items.id
             WHERE media_items.id = :id
             GROUP BY media_items.id
             LIMIT 1',
            ['id' => $id]
        );

        return $item ? $this->withWatchUrls($item) : null;
    }

    /**
     * Updates editable metadata for a movie or TV show title.
     */
    public function update(int $id, array $data): void
    {
        $this->db->updateById('media_items', $id, [
            'title' => trim((string) $data['title']),
            'slug' => $this->normalizeSlug((string) ($data['slug'] ?? ''), (string) $data['title']),
            'type' => (string) $data['type'],
            'synopsis' => trim((string) $data['synopsis']),
            'poster_url' => trim((string) ($data['poster_url'] ?? '')) ?: null,
            'poster_image' => trim((string) ($data['poster_image'] ?? '')) ?: null,
            'backdrop_image' => trim((string) ($data['backdrop_image'] ?? '')) ?: null,
            'stream_link' => trim((string) ($data['stream_link'] ?? '')) ?: null,
            'release_year' => $this->nullableInt($data['release_year'] ?? null),
            'is_featured' => !empty($data['is_featured']) ? 1 : 0,
            'tmdb_rating' => $this->nullableFloat($data['tmdb_rating'] ?? null),
            'tmdb_popularity' => $this->nullableFloat($data['tmdb_popularity'] ?? null),
            'tmdb_vote_count' => max(0, (int) ($data['tmdb_vote_count'] ?? 0)),
            'views' => max(0, (int) ($data['views'] ?? 0)),
            'status' => (string) $data['status'],
        ]);
    }

    /**
     * Deletes a media item. Related seasons and episodes are removed by database cascade rules.
     */
    public function delete(int $id): void
    {
        $this->db->delete('media_episodes', ['media_item_id' => $id]);
        $this->db->delete('media_seasons', ['media_item_id' => $id]);
        $this->db->deleteById('media_items', $id);
    }

    /**
     * Deletes multiple media items by id and returns the rows that were removed for asset cleanup.
     */
    public function bulkDelete(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), fn(int $id): bool => $id > 0)));

        if ($ids === []) {
            return [];
        }

        $placeholders = [];
        $params = [];

        foreach ($ids as $index => $id) {
            $key = 'id_' . $index;
            $placeholders[] = ':' . $key;
            $params[$key] = $id;
        }

        $items = $this->db->select(
            'SELECT * FROM media_items WHERE id IN (' . implode(',', $placeholders) . ')',
            $params
        );

        $this->db->query(
            'DELETE FROM media_episodes WHERE media_item_id IN (' . implode(',', $placeholders) . ')',
            $params
        );

        $this->db->query(
            'DELETE FROM media_seasons WHERE media_item_id IN (' . implode(',', $placeholders) . ')',
            $params
        );

        $this->db->query(
            'DELETE FROM media_items WHERE id IN (' . implode(',', $placeholders) . ')',
            $params
        );

        return $items;
    }

    /**
     * Loads seasons and episodes for a single TV show title for edit page context.
     */
    public function hierarchy(int $mediaItemId): array
    {
        $seasons = $this->db->select(
            'SELECT * FROM media_seasons WHERE media_item_id = :id ORDER BY season_number ASC',
            ['id' => $mediaItemId]
        );

        $episodes = $this->db->select(
            'SELECT * FROM media_episodes WHERE media_item_id = :id ORDER BY season_number ASC, episode_number ASC',
            ['id' => $mediaItemId]
        );

        return [
            'seasons' => $seasons,
            'episodes' => $episodes,
        ];
    }

    public function findSeason(int $mediaItemId, int $seasonId): ?array
    {
        return $this->db->selectOne(
            'SELECT * FROM media_seasons WHERE id = :id AND media_item_id = :media_item_id LIMIT 1',
            ['id' => $seasonId, 'media_item_id' => $mediaItemId]
        );
    }

    public function findEpisode(int $mediaItemId, int $episodeId): ?array
    {
        return $this->db->selectOne(
            'SELECT * FROM media_episodes WHERE id = :id AND media_item_id = :media_item_id LIMIT 1',
            ['id' => $episodeId, 'media_item_id' => $mediaItemId]
        );
    }

    public function updateSeason(int $mediaItemId, int $seasonId, array $data): void
    {
        $this->db->update('media_seasons', [
            'title' => trim((string) $data['title']),
            'synopsis' => trim((string) ($data['synopsis'] ?? '')),
            'poster_url' => trim((string) ($data['poster_url'] ?? '')) ?: null,
            'poster_image' => trim((string) ($data['poster_image'] ?? '')) ?: null,
            'backdrop_image' => trim((string) ($data['backdrop_image'] ?? '')) ?: null,
            'season_number' => max(1, (int) ($data['season_number'] ?? 1)),
            'release_year' => $this->nullableInt($data['release_year'] ?? null),
            'status' => (string) $data['status'],
        ], [
            'id' => $seasonId,
            'media_item_id' => $mediaItemId,
        ]);
    }

    public function updateEpisode(int $mediaItemId, int $episodeId, array $data): void
    {
        $this->db->update('media_episodes', [
            'title' => trim((string) $data['title']),
            'synopsis' => trim((string) ($data['synopsis'] ?? '')),
            'poster_url' => trim((string) ($data['poster_url'] ?? '')) ?: null,
            'poster_image' => trim((string) ($data['poster_image'] ?? '')) ?: null,
            'backdrop_image' => trim((string) ($data['backdrop_image'] ?? '')) ?: null,
            'stream_link' => trim((string) ($data['stream_link'] ?? '')) ?: null,
            'season_number' => max(1, (int) ($data['season_number'] ?? 1)),
            'episode_number' => max(1, (int) ($data['episode_number'] ?? 1)),
            'release_year' => $this->nullableInt($data['release_year'] ?? null),
            'views' => max(0, (int) ($data['views'] ?? 0)),
            'status' => (string) $data['status'],
        ], [
            'id' => $episodeId,
            'media_item_id' => $mediaItemId,
        ]);
    }

    public function createEpisode(int $mediaItemId, array $data): int
    {
        $item = $this->find($mediaItemId);
        $seasonNumber = max(1, (int) ($data['season_number'] ?? 1));
        $episodeNumber = max(1, (int) ($data['episode_number'] ?? 1));
        $season = $this->db->selectOne(
            'SELECT id FROM media_seasons WHERE media_item_id = :media_item_id AND season_number = :season_number LIMIT 1',
            ['media_item_id' => $mediaItemId, 'season_number' => $seasonNumber]
        );

        return (int) $this->db->insert('media_episodes', [
            'media_item_id' => $mediaItemId,
            'media_season_id' => $season ? (int) $season['id'] : null,
            'title' => trim((string) $data['title']),
            'serie' => trim((string) ($item['title'] ?? '')) ?: null,
            'episode_name' => trim((string) $data['episode_name']) ?: trim((string) $data['title']),
            'synopsis' => trim((string) ($data['synopsis'] ?? '')),
            'poster_url' => trim((string) ($data['poster_url'] ?? '')) ?: null,
            'poster_image' => trim((string) ($data['poster_image'] ?? '')) ?: null,
            'backdrop_image' => trim((string) ($data['backdrop_image'] ?? '')) ?: null,
            'stream_link' => trim((string) ($data['stream_link'] ?? '')) ?: null,
            'season_number' => $seasonNumber,
            'episode_number' => $episodeNumber,
            'release_year' => $this->nullableInt($data['release_year'] ?? null),
            'views' => max(0, (int) ($data['views'] ?? 0)),
            'status' => (string) $data['status'],
        ]);
    }

    public function episodeNumberExists(int $mediaItemId, int $seasonNumber, int $episodeNumber, ?int $exceptEpisodeId = null): bool
    {
        $params = [
            'media_item_id' => $mediaItemId,
            'season_number' => max(1, $seasonNumber),
            'episode_number' => max(1, $episodeNumber),
        ];
        $sql = 'SELECT 1 FROM media_episodes
                WHERE media_item_id = :media_item_id
                AND season_number = :season_number
                AND episode_number = :episode_number';

        if ($exceptEpisodeId !== null) {
            $sql .= ' AND id <> :episode_id';
            $params['episode_id'] = $exceptEpisodeId;
        }

        return $this->db->exists($sql . ' LIMIT 1', $params);
    }

    public function deleteSeason(int $mediaItemId, int $seasonId): void
    {
        $this->db->update('media_episodes', ['media_season_id' => null], [
            'media_item_id' => $mediaItemId,
            'media_season_id' => $seasonId,
        ]);
        $this->db->delete('media_seasons', ['id' => $seasonId, 'media_item_id' => $mediaItemId]);
    }

    public function deleteEpisode(int $mediaItemId, int $episodeId): void
    {
        $this->db->delete('media_episodes', ['id' => $episodeId, 'media_item_id' => $mediaItemId]);
    }

    /**
     * Confirms a submitted media type can be saved as a top-level content item.
     */
    public function validType(string $type): bool
    {
        return $type !== 'all' && isset(self::TYPES[$type]);
    }

    /**
     * Confirms a submitted publishing state is recognized by the schema.
     */
    public function validStatus(string $status): bool
    {
        return isset(self::STATUSES[$status]);
    }

    /**
     * Builds a reusable SQL filter clause for catalogue searches.
     */
    private function filterClause(string $type, string $search, string $status): array
    {
        $where = [];
        $params = [];

        if ($type !== 'all' && isset(self::TYPES[$type])) {
            $where[] = 'media_items.type = :type';
            $params['type'] = $type;
        }

        if ($status !== 'all' && isset(self::STATUSES[$status])) {
            $where[] = 'media_items.status = :status';
            $params['status'] = $status;
        }

        if ($search !== '') {
            $where[] = '(media_items.title LIKE :search OR media_items.slug LIKE :search OR media_items.synopsis LIKE :search OR media_items.tmdb_id = :tmdb_id)';
            $params['search'] = '%' . $search . '%';
            $params['tmdb_id'] = ctype_digit($search) ? (int) $search : 0;
        }

        return [
            $where ? ' WHERE ' . implode(' AND ', $where) : '',
            $params,
        ];
    }

    /**
     * Normalizes optional integer form fields for storage.
     */
    private function nullableInt(mixed $value): ?int
    {
        $value = trim((string) $value);

        return $value === '' ? null : (int) $value;
    }

    private function nullableFloat(mixed $value): ?float
    {
        $value = trim((string) $value);

        return $value === '' ? null : (float) $value;
    }

    private function normalizeSlug(string $slug, string $fallback): string
    {
        return MediaUrl::slugify($slug !== '' ? $slug : $fallback);
    }

    private function withWatchUrls(array $item): array
    {
        $watchUrl = MediaUrl::watchUrlForItem($item);

        return [
            ...$item,
            'slug' => MediaUrl::itemSlug($item),
            'watch_url' => $watchUrl,
            'watchUrl' => $watchUrl,
        ];
    }
}
