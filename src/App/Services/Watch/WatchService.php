<?php

declare(strict_types=1);

namespace App\Services\Watch;

use App\Database\TmdbMetadataSchema;
use App\Support\MediaUrl;
use Framework\Database;

class WatchService
{
    public function __construct(private Database $db)
    {
        TmdbMetadataSchema::ensure($db);
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
            'item' => $this->withWatchUrls($movie),
            'related' => array_map(fn(array $item): array => $this->withWatchUrls($item), $this->related('movie', (int) $movie['id'])),
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
            'show' => $this->withWatchUrls($show),
            'episode' => $this->withEpisodeWatchUrl($show, $episode),
            'seasons' => array_map(fn(array $season): array => $this->withSeasonWatchUrl($show, $season), $this->db->select(
                "SELECT * FROM media_seasons
                 WHERE media_item_id = :media_item_id
                 AND status = 'published'
                 ORDER BY season_number ASC",
                ['media_item_id' => (int) $show['id']]
            )),
            'episodes' => array_map(fn(array $row): array => $this->withEpisodeWatchUrl($show, $row), $this->db->select(
                "SELECT * FROM media_episodes
                 WHERE media_item_id = :media_item_id
                 AND season_number = :season_number
                 AND status = 'published'
                 ORDER BY episode_number ASC",
                [
                    'media_item_id' => (int) $show['id'],
                    'season_number' => max(1, $seasonNumber),
                ]
            )),
            'related' => array_map(fn(array $item): array => $this->withWatchUrls($item), $this->related('tv_show', (int) $show['id'])),
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
                'show' => $this->withWatchUrls($show),
                'episode' => [
                    'title' => 'Series Overview',
                    'synopsis' => $show['synopsis'] ?? '',
                    'season_number' => 1,
                    'episode_number' => 1,
                    'backdrop_url' => $show['backdrop_url'] ?? null,
                    'poster_url' => $show['poster_url'] ?? null,
                    'watch_url' => MediaUrl::watchUrlForItem($show),
                    'watchUrl' => MediaUrl::watchUrlForItem($show),
                ],
                'seasons' => [],
                'episodes' => [],
                'related' => array_map(fn(array $item): array => $this->withWatchUrls($item), $this->related('tv_show', (int) $show['id'])),
            ];
        }

        return $this->episode($tmdbId, (int) $episode['season_number'], (int) $episode['episode_number']);
    }

    private function related(string $type, int $excludeId): array
    {
                // Genre-weighted randomization:
                // 1) Count how many genres each candidate shares with the excluded item
                // 2) Order by shared genre count DESC, then randomize within the same count
                return $this->db->select(
                        "SELECT m.*, COALESCE(shared.shared_count, 0) AS shared_count
                         FROM media_items m
                         LEFT JOIN (
                             SELECT ctl.owner_id, COUNT(DISTINCT ctl.term_id) AS shared_count
                             FROM content_term_links ctl
                             INNER JOIN content_terms ct ON ct.id = ctl.term_id AND ct.taxonomy = 'genres'
                             INNER JOIN (
                                 SELECT ctl2.term_id
                                 FROM content_term_links ctl2
                                 INNER JOIN content_terms ct2 ON ct2.id = ctl2.term_id AND ct2.taxonomy = 'genres'
                                 WHERE ctl2.owner_type = 'item' AND ctl2.owner_id = :current_id
                             ) ex ON ex.term_id = ctl.term_id
                             WHERE ctl.owner_type = 'item'
                             GROUP BY ctl.owner_id
                         ) shared ON shared.owner_id = m.id
                         WHERE m.status = 'published'
                                AND m.type = :type
                                AND m.id <> :exclude_id
                         ORDER BY shared.shared_count DESC, RAND()
                         LIMIT 6",
                        ['type' => $type, 'current_id' => $excludeId, 'exclude_id' => $excludeId]
                );
    }

    public function episodeById(int $tmdbId, int $episodeId): ?array
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
             WHERE id = :id
             AND media_item_id = :media_item_id
             AND status = 'published'
             LIMIT 1",
            ['id' => $episodeId, 'media_item_id' => (int) $show['id']]
        );

        if (!$episode) {
            return null;
        }

        return $this->episode($tmdbId, (int) $episode['season_number'], (int) $episode['episode_number']);
    }

    private function withWatchUrls(array $item): array
    {
        $watchUrl = MediaUrl::watchUrlForItem($item);
        $genreLinks = $this->genreLinks((int) ($item['id'] ?? 0));
        $genreNames = array_map(static fn(array $genre): string => $genre['name'], $genreLinks);
        $networkLinks = $this->networkLinks((int) ($item['id'] ?? 0));
        $networkNames = array_map(static fn(array $network): string => $network['name'], $networkLinks);

        $embedServers = $this->movieEmbedServers($item);
        $embedUrl = $embedServers[0]['url'] ?? null;

        return [
            ...$item,
            'slug' => MediaUrl::itemSlug($item),
            'genres' => implode(', ', $genreNames),
            'genre_names' => $genreNames,
            'genre_links' => $genreLinks,
            'networks' => implode(', ', $networkNames),
            'network_names' => $networkNames,
            'network_links' => $networkLinks,
            'embed_servers' => $embedServers,
            'embedServers' => $embedServers,
            'embed_url' => $embedUrl,
            'embedUrl' => $embedUrl,
            'watch_url' => $watchUrl,
            'watchUrl' => $watchUrl,
        ];
    }

    /**
     * @return array<int, array{name: string, slug: string, url: string}>
     */
    private function genreLinks(int $itemId): array
    {
        if ($itemId < 1) {
            return [];
        }

        $rows = $this->db->select(
            "SELECT content_terms.name, content_terms.slug
             FROM content_term_links
             INNER JOIN content_terms ON content_terms.id = content_term_links.term_id
             WHERE content_term_links.owner_type = :owner_type
             AND content_term_links.owner_id = :owner_id
             AND content_terms.taxonomy = :taxonomy
             ORDER BY content_terms.name ASC",
            [
                'owner_type' => 'item',
                'owner_id' => $itemId,
                'taxonomy' => 'genres',
            ]
        );

        $links = [];
        foreach ($rows as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            $slug = trim((string) ($row['slug'] ?? ''));

            if ($name !== '') {
                $slug = $slug !== '' ? $slug : MediaUrl::slugify($name);
                $links[] = [
                    'name' => $name,
                    'slug' => $slug,
                    'url' => '/genre/' . rawurlencode($slug),
                ];
            }
        }

        return $links;
    }

    /**
     * @return array<int, array{name: string, slug: string, url: string, logo_url: string}>
     */
    private function networkLinks(int $itemId): array
    {
        if ($itemId < 1) {
            return [];
        }

        $profiles = $this->networkProfiles($itemId);
        $rows = $this->db->select(
            "SELECT content_terms.name, content_terms.slug
             FROM content_term_links
             INNER JOIN content_terms ON content_terms.id = content_term_links.term_id
             WHERE content_term_links.owner_type = :owner_type
             AND content_term_links.owner_id = :owner_id
             AND content_terms.taxonomy = :taxonomy
             ORDER BY content_terms.name ASC",
            [
                'owner_type' => 'item',
                'owner_id' => $itemId,
                'taxonomy' => 'networks',
            ]
        );

        $links = [];
        foreach ($rows as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            $slug = trim((string) ($row['slug'] ?? ''));

            if ($name === '') {
                continue;
            }

            $slug = $slug !== '' ? $slug : MediaUrl::slugify($name);
            $profile = $profiles[$slug] ?? [];
            $links[] = [
                'name' => $name,
                'slug' => $slug,
                'url' => '/network/' . rawurlencode($slug),
                'logo_url' => (string) ($profile['logo_url'] ?? ''),
            ];
        }

        return $links;
    }

    /**
     * @return array<string, array{name: string, slug: string, logo_url: string}>
     */
    private function networkProfiles(int $itemId): array
    {
        $row = $this->db->selectOne(
            "SELECT meta_value
             FROM content_meta
             WHERE owner_type = :owner_type
             AND owner_id = :owner_id
             AND meta_key = :meta_key
             LIMIT 1",
            [
                'owner_type' => 'item',
                'owner_id' => $itemId,
                'meta_key' => 'network_profiles',
            ]
        );

        $decoded = json_decode((string) ($row['meta_value'] ?? ''), true);
        if (!is_array($decoded)) {
            return [];
        }

        $profiles = [];
        foreach ($decoded as $profile) {
            if (!is_array($profile)) {
                continue;
            }

            $slug = trim((string) ($profile['slug'] ?? ''));
            if ($slug === '') {
                continue;
            }

            $profiles[$slug] = [
                'name' => (string) ($profile['name'] ?? ''),
                'slug' => $slug,
                'logo_url' => (string) ($profile['logo_url'] ?? ''),
            ];
        }

        return $profiles;
    }

    private function withEpisodeWatchUrl(array $show, array $episode): array
    {
        $watchUrl = MediaUrl::watchUrlForItem($show, $episode);

        $embedServers = $this->episodeEmbedServers($show, $episode);
        $embedUrl = $embedServers[0]['url'] ?? null;

        return [
            ...$episode,
            'embed_servers' => $embedServers,
            'embedServers' => $embedServers,
            'embed_url' => $embedUrl,
            'embedUrl' => $embedUrl,
            'watch_url' => $watchUrl,
            'watchUrl' => $watchUrl,
        ];
    }

    private function withSeasonWatchUrl(array $show, array $season): array
    {
        $episode = $this->db->selectOne(
            "SELECT season_number, episode_number
             FROM media_episodes
             WHERE media_item_id = :media_item_id
             AND season_number = :season_number
             AND status = 'published'
             ORDER BY episode_number ASC
             LIMIT 1",
            [
                'media_item_id' => (int) $show['id'],
                'season_number' => (int) ($season['season_number'] ?? 1),
            ]
        );
        $watchUrl = MediaUrl::watchUrlForItem($show, $episode ?: null);

        return [
            ...$season,
            'watch_url' => $watchUrl,
            'watchUrl' => $watchUrl,
        ];
    }

    /**
     * @return array<int, array{key: string, name: string, url: string, default?: bool}>
     */
    private function movieEmbedServers(array $item): array
    {
        $tmdbId = (int) ($item['tmdb_id'] ?? 0);

        if ($tmdbId < 1) {
            return [];
        }

        $servers = [
            [
                'key' => 'vx-vidfast',
                'name' => 'VX-Vidfast',
                'url' => 'https://vidfast.pro/movie/' . $tmdbId,
                'default' => true,
            ],
            [
                'key' => 'vx-vidnest',
                'name' => 'VX-VidNest',
                'url' => 'https://vidnest.fun/movie/' . $tmdbId,
            ],
            [
                'key' => 'vx-videasy',
                'name' => 'VX-VidEasy',
                'url' => 'https://player.videasy.net/movie/' . $tmdbId,
            ],
            [
                'key' => 'vx-vidup',
                'name' => 'VX-VidUp',
                'url' => 'https://vidup.to/movie/' . $tmdbId,
            ],
        ];

        /*
            [
                'key' => 'vidsrc',
                'name' => 'VidSrc',
                'url' => 'https://vidsrc.mov/embed/movie/' . $tmdbId,
            ],
            [
                'key' => 'videasy',
                'name' => 'VidEasy',
                'url' => 'https://player.videasy.net/movie/' . $tmdbId,
            ],
            [
                'key' => 'vidnest',
                'name' => 'VidNest',
                'url' => 'https://vidnest.fun/movie/' . $tmdbId,
            ],
        ];
        */

        // No custom or additional servers allowed — only MX-VidFast remains.
        // Any legacy `stream_link` entries are intentionally ignored to keep watch pages consistent.

        return $servers;
    }

    /**
     * @return array<int, array{key: string, name: string, url: string, default?: bool}>
     */
    private function episodeEmbedServers(array $show, array $episode): array
    {
        $tmdbId = (int) ($show['tmdb_id'] ?? 0);
        $season = max(1, (int) ($episode['season_number'] ?? 1));
        $episodeNumber = max(1, (int) ($episode['episode_number'] ?? 1));

        if ($tmdbId < 1) {
            return [];
        }

        $servers = [
            [
                'key' => 'vx-vidfast',
                'name' => 'VX-Vidfast',
                'url' => 'https://vidfast.pro/tv/' . $tmdbId . '/' . $season . '/' . $episodeNumber,
                'default' => true,
            ],
            [
                'key' => 'vx-vidnest',
                'name' => 'VX-VidNest',
                'url' => 'https://vidnest.fun/tv/' . $tmdbId . '/' . $season . '/' . $episodeNumber,
            ],
            [
                'key' => 'vx-videasy',
                'name' => 'VX-VidEasy',
                'url' => 'https://player.videasy.net/tv/' . $tmdbId . '/' . $season . '/' . $episodeNumber,
            ],
            [
                'key' => 'vx-vidup',
                'name' => 'VX-VidUp',
                'url' => 'https://vidup.to/tv/' . $tmdbId . '/' . $season . '/' . $episodeNumber . '?autoPlay=true',
            ],
        ];

        /*
            [
                'key' => 'vidsrc',
                'name' => 'VidSrc',
                'url' => 'https://vidsrc.mov/embed/tv/' . $tmdbId . '/' . $season . '/' . $episodeNumber,
            ],
            [
                'key' => 'videasy',
                'name' => 'VidEasy',
                'url' => 'https://player.videasy.net/tv/' . $tmdbId . '/' . $season . '/' . $episodeNumber,
            ],
            [
                'key' => 'vidnest',
                'name' => 'VidNest',
                'url' => 'https://vidnest.fun/tv/' . $tmdbId . '/' . $season . '/' . $episodeNumber,
            ],
        ];
        */

        // No custom or additional servers allowed — only MX-VidFast remains.
        // Any legacy `stream_link` entries are intentionally ignored to keep watch pages consistent.

        return $servers;
    }

}
