<?php

declare(strict_types=1);

namespace App\Services\Watch;

use Framework\Database;

class WatchService
{
    public function __construct(private Database $db)
    {
    }

    public function movie(int $tmdbId): ?array
    {
        $movie = $this->db->selectOne(
            "SELECT * FROM media_items
             WHERE tmdb_id = :tmdb_id
             AND tmdb_type = 'movie'
             AND type = 'movie'
             AND status = 'published'
             LIMIT 1",
            ['tmdb_id' => $tmdbId]
        );

        if (!$movie) {
            return null;
        }

        $this->db->increment('media_items', 'views', 1, ['id' => (int) $movie['id']]);

        return [
            'item' => $movie,
            'related' => $this->related('movie', (int) $movie['id']),
        ];
    }

    public function episode(int $tmdbId, int $seasonNumber, int $episodeNumber): ?array
    {
        $show = $this->db->selectOne(
            "SELECT * FROM media_items
             WHERE tmdb_id = :tmdb_id
             AND tmdb_type = 'tv_show'
             AND type = 'tv_show'
             AND status = 'published'
             LIMIT 1",
            ['tmdb_id' => $tmdbId]
        );

        if (!$show) {
            return null;
        }

        $episode = $this->db->selectOne(
            "SELECT * FROM media_episodes
             WHERE media_item_id = :media_item_id
             AND season_number = :season_number
             AND episode_number = :episode_number
             AND status = 'published'
             LIMIT 1",
            [
                'media_item_id' => (int) $show['id'],
                'season_number' => max(1, $seasonNumber),
                'episode_number' => max(1, $episodeNumber),
            ]
        );

        if (!$episode) {
            return null;
        }

        $this->db->increment('media_episodes', 'views', 1, ['id' => (int) $episode['id']]);

        return [
            'show' => $show,
            'episode' => $episode,
            'seasons' => $this->db->select(
                "SELECT * FROM media_seasons
                 WHERE media_item_id = :media_item_id
                 AND status = 'published'
                 ORDER BY season_number ASC",
                ['media_item_id' => (int) $show['id']]
            ),
            'episodes' => $this->db->select(
                "SELECT * FROM media_episodes
                 WHERE media_item_id = :media_item_id
                 AND season_number = :season_number
                 AND status = 'published'
                 ORDER BY episode_number ASC",
                [
                    'media_item_id' => (int) $show['id'],
                    'season_number' => max(1, $seasonNumber),
                ]
            ),
        ];
    }

    public function firstEpisode(int $tmdbId): ?array
    {
        $show = $this->db->selectOne(
            "SELECT * FROM media_items
             WHERE tmdb_id = :tmdb_id
             AND tmdb_type = 'tv_show'
             AND type = 'tv_show'
             AND status = 'published'
             LIMIT 1",
            ['tmdb_id' => $tmdbId]
        );

        if (!$show) {
            return null;
        }

        $episode = $this->db->selectOne(
            "SELECT season_number, episode_number
             FROM media_episodes
             WHERE media_item_id = :media_item_id
             AND status = 'published'
             ORDER BY season_number ASC, episode_number ASC
             LIMIT 1",
            ['media_item_id' => (int) $show['id']]
        );

        if (!$episode) {
            return [
                'show' => $show,
                'episode' => [
                    'title' => 'Series Overview',
                    'synopsis' => $show['synopsis'] ?? '',
                    'season_number' => 1,
                    'episode_number' => 1,
                    'backdrop_image' => $show['backdrop_image'] ?? null,
                    'poster_image' => $show['poster_image'] ?? null,
                    'poster_url' => $show['poster_url'] ?? null,
                ],
                'seasons' => [],
                'episodes' => [],
            ];
        }

        return $this->episode($tmdbId, (int) $episode['season_number'], (int) $episode['episode_number']);
    }

    private function related(string $type, int $excludeId): array
    {
        return $this->db->select(
            "SELECT * FROM media_items
             WHERE status = 'published'
             AND type = :type
             AND id <> :id
             ORDER BY tmdb_rating DESC, tmdb_popularity DESC, views DESC
             LIMIT 6",
            ['type' => $type, 'id' => $excludeId]
        );
    }
}
