<?php

declare(strict_types=1);

namespace App\Services\Home;

use App\Cache\CacheInterface;
use App\Database\TmdbMetadataSchema;
use App\Support\MediaImage;
use App\Support\MediaUrl;
use Framework\Database;

class HomeService
{
    /**
     * HomeService constructor.
     *
     * @param Database $db Database connection used to query homepage content.
     */
    public function __construct(
        private Database $db,
        private CacheInterface $cache
    ) {
        TmdbMetadataSchema::ensure($db);
    }

    /**
     * Builds the data payload used for server-rendering the homepage.
     *
     * @return array<string, mixed>
     */
    public function pageData(): array
    {
        $cacheKey = 'home:pageData:v3';
        $useCache = filter_var($_ENV['HOME_PAGE_CACHE_ENABLED'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $ttl = (int) ($_ENV['HOME_PAGE_CACHE_TTL'] ?? 120);
        $ttl = max(15, min(3600, $ttl));

        if ($useCache) {
            $cached = $this->cache->get($cacheKey);
            if (is_array($cached)) {
                return $cached;
            }
        }

        $data = [
            'title' => 'Home',
            'featured' => $this->getFeatured(),
            'trending' => $this->getTrending(),
            'recentlyAdded' => $this->recentlyAdded(),
            'newEpisodes' => $this->newEpisodes(),
            'topByTmdb' => $this->getTopByTmdb(),
            'genres' => $this->getAllGenre(),
        ];

        if ($useCache) {
            $this->cache->set($cacheKey, $data, $ttl);
        }

        return $data;
    }

    /**
     * Returns published featured titles for the homepage hero section.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getFeatured(): array
    {
        $items = $this->db->select(
            'SELECT *
             FROM media_items
             WHERE status = :status
             AND is_featured = 1
             ORDER BY tmdb_rating DESC, tmdb_popularity DESC, views DESC, updated_at DESC, id DESC
             LIMIT 10',
            ['status' => 'published']
        );

        return array_map(function (array $item): array {
            return $this->heroPayload($item);
        }, $items);
    }

    /**
     * Returns the weekly trending titles based on views and recent activity.
     *
     * Because the schema only stores aggregate view counts, this method
     * filters to published items with recent updates within the last 7 days
     * and sorts by views to approximate weekly trending behaviour.
     *
     * @param int $limit Number of items to return.
     * @param string $type Optional filter for movie or tv_show values.
     * @return array<int, array<string, mixed>>
     */
    public function getTrending(int $limit = 5, string $type = 'all'): array
    {
        $limit = max(1, min(20, $limit));
        $allowedTypes = ['movie', 'tv_show'];

        $where = ['media_items.status = :status', 'media_items.updated_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)'];
        $params = ['status' => 'published'];

        if (in_array($type, $allowedTypes, true)) {
            $where[] = 'media_items.type = :type';
            $params['type'] = $type;
        }

        $items = $this->db->select(
            'SELECT media_items.*
             FROM media_items
             WHERE ' . implode(' AND ', $where) . '
             ORDER BY media_items.views DESC, media_items.tmdb_rating DESC, media_items.tmdb_popularity DESC, media_items.updated_at DESC, media_items.id DESC
             LIMIT ' . $limit,
            $params
        );

        return array_map(function (array $item): array {
            return $this->trendingPayload($item);
        }, $items);
    }

    /**
     * Returns the top catalogue items ranked by TMDb rating.
     *
     * This method selects the highest-rated published items and exposes a fixed
     * set of top titles for display in a TMDb-based spotlight section.
     *
     * @param int $limit Number of items to return.
     * @return array<int, array<string, mixed>>
     */
    public function getTopByTmdb(int $limit = 6): array
    {
        $limit = max(1, min(20, $limit));

        $items = $this->db->select(
            'SELECT *
             FROM media_items
             WHERE status = :status
             ORDER BY tmdb_rating DESC, tmdb_popularity DESC, views DESC, updated_at DESC, id DESC
             LIMIT ' . $limit,
            ['status' => 'published']
        );

        return array_map(function (array $item): array {
            return $this->trendingPayload($item);
        }, $items);
    }

    /**
     * Returns the full list of registered genres.
     *
     * This method pulls genres from the content taxonomy and exposes them
     * as label/url pairs and representative images for homepage genre cards.
     *
     * @return array<int, array{name: string, url: string, image: string}>
     */
    public function getAllGenre(): array
    {
        $genres = $this->db->select(
            'SELECT id, name, slug
             FROM content_terms
             WHERE taxonomy = :taxonomy
             ORDER BY name ASC',
            ['taxonomy' => 'genres']
        );

        return array_map(function (array $genre): array {
            $name = (string) ($genre['name'] ?? 'Unknown');
            $slug = (string) ($genre['slug'] ?? '');
            $link = $this->genreLink($name, $slug);

            $imageMedia = $this->getGenreRepresentativeMedia((int) ($genre['id'] ?? 0), $slug);

            return [
                'name' => $name,
                'url' => $link['url'],
                'image' => MediaImage::srcOnly($imageMedia),
                'image_media' => $imageMedia,
            ];
        }, $genres);
    }

    /**
     * Returns a representative image for a genre using one of its published items.
     *
     * @param int $termId Genre term ID.
     * @param string $slug Genre slug used for fallback image seeding.
     * @return string
     */
    /**
     * @return array{src: string, srcset: string, sizes: string, width: int, height: int}
     */
    private function getGenreRepresentativeMedia(int $termId, string $slug): array
    {
        $fallback = 'https://picsum.photos/seed/vexio-genre-' . rawurlencode($slug ?: 'unknown') . '/420/260';

        if ($termId < 1) {
            return MediaImage::fromString($fallback, 'genre');
        }

        $item = $this->db->selectOne(
            'SELECT media_items.poster_image, media_items.backdrop_image, media_items.poster_url
             FROM media_items
             INNER JOIN content_term_links ON media_items.id = content_term_links.owner_id
             INNER JOIN content_terms ON content_terms.id = content_term_links.term_id
             WHERE media_items.status = :status
             AND content_term_links.owner_type = :owner_type
             AND content_terms.taxonomy = :taxonomy
             AND content_term_links.term_id = :term_id
             ORDER BY media_items.tmdb_rating DESC, media_items.views DESC, media_items.updated_at DESC
             LIMIT 1',
            [
                'status' => 'published',
                'owner_type' => 'item',
                'taxonomy' => 'genres',
                'term_id' => $termId,
            ]
        );

        if (!$item) {
            return MediaImage::fromString($fallback, 'genre');
        }

        $media = MediaImage::backdropFromRow($item, 'genre');
        if (MediaImage::srcOnly($media) !== '') {
            return $media;
        }

        return MediaImage::fromString($fallback, 'genre');
    }

    /**
     * Returns the latest published catalogue items for the recently added section.
     *
     * @param int $limit Number of items to return.
     * @return array<int, array<string, mixed>>
     */
    public function recentlyAdded(int $limit = 25): array
    {
        $limit = max(1, min(50, $limit));

        $items = $this->db->select(
            'SELECT *
             FROM media_items
             WHERE status = :status
             ORDER BY created_at DESC, updated_at DESC, id DESC
             LIMIT ' . $limit,
            ['status' => 'published']
        );

        return array_map(function (array $item): array {
            return $this->recentlyAddedPayload($item);
        }, $items);
    }

    /**
     * Returns the latest published TV episodes for the homepage.
     *
     * @param int $limit Number of episodes to return.
     * @return array<int, array<string, mixed>>
     */
    public function newEpisodes(int $limit = 18): array
    {
        $limit = max(1, min(40, $limit));

        $episodes = $this->db->select(
            'SELECT
                media_episodes.*,
                media_items.id AS show_id,
                media_items.title AS show_title,
                media_items.slug AS show_slug,
                media_items.type AS show_type,
                media_items.tmdb_id AS show_tmdb_id,
                media_items.tmdb_rating AS show_tmdb_rating,
                media_items.poster_url AS show_poster_url,
                media_items.poster_image AS show_poster_image,
                media_items.backdrop_image AS show_backdrop_image,
                media_items.release_year AS show_release_year
             FROM media_episodes
             INNER JOIN media_items ON media_items.id = media_episodes.media_item_id
             WHERE media_episodes.status = :episode_status
             AND media_items.status = :item_status
             AND media_items.type = :type
             ORDER BY media_episodes.air_date DESC, media_episodes.created_at DESC, media_episodes.updated_at DESC, media_episodes.id DESC
             LIMIT ' . $limit,
            [
                'episode_status' => 'published',
                'item_status' => 'published',
                'type' => 'tv_show',
            ]
        );

        return array_map(function (array $episode): array {
            return $this->episodePayload($episode);
        }, $episodes);
    }

    /**
     * Formats a media item for homepage hero rendering.
     *
     * @param array<string, mixed> $item Raw media item row.
     * @return array<string, mixed>
     */
    private function heroPayload(array $item): array
    {
        $title = (string) ($item['title'] ?? 'Untitled');
        $words = preg_split('/\s+/', $title) ?: [];
        $highlight = count($words) > 1 ? array_pop($words) : '';
        $genres = $this->genreLinks((int) $item['id'], null, $this->typeLabel((string) ($item['type'] ?? '')));
        $watchUrl = $this->watchUrlForItem($item);

        return [
            'id' => (int) $item['id'],
            'slug' => MediaUrl::itemSlug($item),
            'title' => trim(implode(' ', $words)) ?: $title,
            'titleHl' => $highlight,
            'badge' => $this->typeLabel((string) ($item['type'] ?? '')),
            'genre' => $this->genreLabel($genres),
            'genres' => $genres,
            'year' => (string) ($item['release_year'] ?: 'N/A'),
            'eps' => ($item['type'] ?? '') === 'movie' ? 'Movie' : 'Series',
            'score' => (string) ($item['tmdb_rating'] ?: 'N/A'),
            'desc' => (string) ($item['synopsis'] ?: 'Featured from the Vexio catalogue.'),
            'poster' => MediaImage::srcOnly(MediaImage::posterFromRow($item, 'heroPoster')),
            'backdrop' => MediaImage::srcOnly(MediaImage::backdropFromRow($item, 'heroBackdrop')),
            'poster_media' => MediaImage::posterFromRow($item, 'heroPoster'),
            'backdrop_media' => MediaImage::backdropFromRow($item, 'heroBackdrop'),
            'streamLink' => $item['stream_link'] ?? null,
            'watchUrl' => $watchUrl,
            'watch_url' => $watchUrl,
            'color' => 'c1',
            'accent' => 'rgba(0,60,140,.55)',
        ];
    }

    /**
     * Formats a media item for the recently added homepage grid.
     *
     * @param array<string, mixed> $item Raw media item row.
     * @return array<string, mixed>
     */
    private function recentlyAddedPayload(array $item): array
    {
        $genres = $this->genreLinks((int) $item['id'], 1, $this->typeLabel((string) ($item['type'] ?? '')));
        $watchUrl = $this->watchUrlForItem($item);

        return [
            'id' => (int) $item['id'],
            'slug' => MediaUrl::itemSlug($item),
            'title' => (string) ($item['title'] ?? 'Untitled'),
            'type' => (string) ($item['type'] ?? 'unknown'),
            'genre' => $this->genreLabel($genres),
            'genres' => $genres,
            'year' => (string) ($item['release_year'] ?: 'N/A'),
            'score' => (string) ($item['tmdb_rating'] ?: 'N/A'),
            'poster' => MediaImage::srcOnly(MediaImage::posterFromRow($item, 'card')),
            'poster_media' => MediaImage::posterFromRow($item, 'card'),
            'backdrop' => MediaImage::srcOnly(MediaImage::backdropFromRow($item, 'heroBackdrop')),
            'backdrop_media' => MediaImage::backdropFromRow($item, 'heroBackdrop'),
            'watchUrl' => $watchUrl,
            'watch_url' => $watchUrl,
            'synopsis' => (string) ($item['synopsis'] ?: ''),
            'is_featured' => !empty($item['is_featured']),
        ];
    }

    /**
     * Formats a TV episode for the homepage new episodes row.
     *
     * @param array<string, mixed> $episode Raw joined episode/show row.
     * @return array<string, mixed>
     */
    private function episodePayload(array $episode): array
    {
        $show = [
            'id' => (int) ($episode['show_id'] ?? 0),
            'title' => (string) ($episode['show_title'] ?? 'TV Show'),
            'slug' => (string) ($episode['show_slug'] ?? ''),
            'type' => (string) ($episode['show_type'] ?? 'tv_show'),
            'tmdb_id' => (int) ($episode['show_tmdb_id'] ?? 0),
        ];

        $imageRow = [
            'poster_url' => (string) (($episode['poster_url'] ?? '') ?: ($episode['show_poster_url'] ?? '')),
            'poster_image' => (string) (($episode['poster_image'] ?? '') ?: ($episode['show_poster_image'] ?? '')),
            'backdrop_image' => (string) (($episode['backdrop_image'] ?? '') ?: ($episode['show_backdrop_image'] ?? '')),
        ];

        $watchUrl = MediaUrl::watchUrlForItem($show, $episode);
        $season = (int) ($episode['season_number'] ?? 1);
        $episodeNumber = (int) ($episode['episode_number'] ?? 1);
        $episodeName = trim((string) (($episode['episode_name'] ?? '') ?: ($episode['title'] ?? '')));

        return [
            'id' => (int) ($episode['id'] ?? 0),
            'show_id' => (int) ($episode['show_id'] ?? 0),
            'show_title' => (string) ($episode['show_title'] ?? 'TV Show'),
            'title' => $episodeName !== '' ? $episodeName : 'Episode ' . $episodeNumber,
            'season_number' => $season,
            'episode_number' => $episodeNumber,
            'label' => 'S' . $season . ' E' . $episodeNumber,
            'air_date' => (string) ($episode['air_date'] ?? ''),
            'year' => (string) (($episode['release_year'] ?? '') ?: ($episode['show_release_year'] ?? '')),
            'score' => (string) (($episode['show_tmdb_rating'] ?? '') ?: 'N/A'),
            'backdrop' => MediaImage::srcOnly(MediaImage::backdropFromRow($imageRow, 'spotlight')),
            'backdrop_media' => MediaImage::backdropFromRow($imageRow, 'spotlight'),
            'watchUrl' => $watchUrl,
            'watch_url' => $watchUrl,
        ];
    }

    /**
     * Formats a media item for the homepage trending list.
     *
     * @param array<string, mixed> $item Raw media item row.
     * @return array<string, mixed>
     */
    private function trendingPayload(array $item): array
    {
        $genres = $this->genreLinks((int) $item['id'], 2, $this->typeLabel((string) ($item['type'] ?? '')));
        $watchUrl = $this->watchUrlForItem($item);

        return [
            'id' => (int) $item['id'],
            'slug' => MediaUrl::itemSlug($item),
            'title' => (string) ($item['title'] ?? 'Untitled'),
            'type' => (string) ($item['type'] ?? 'unknown'),
            'genre' => $this->genreLabel($genres),
            'genres' => $genres,
            'year' => (string) ($item['release_year'] ?: 'N/A'),
            'score' => (string) ($item['tmdb_rating'] ?: 'N/A'),
            'poster' => MediaImage::srcOnly(MediaImage::posterFromRow($item, 'thumb')),
            'poster_media' => MediaImage::posterFromRow($item, 'thumb'),
            'backdrop' => MediaImage::srcOnly(MediaImage::backdropFromRow($item, 'spotlight')),
            'backdrop_media' => MediaImage::backdropFromRow($item, 'spotlight'),
            'watchUrl' => $watchUrl,
            'watch_url' => $watchUrl,
            'views' => (int) ($item['views'] ?? 0),
            'is_featured' => !empty($item['is_featured']),
        ];
    }

    /**
     * Returns a public watch URL for a media item.
     *
     * @param array<string, mixed> $item Raw media item row.
     * @return string|null
     */
    private function watchUrlForItem(array $item): ?string
    {
        return MediaUrl::watchUrlForItem($item);
    }

    /**
     * Returns link-ready genre data for a media item.
     *
     * @param int $itemId Media item ID.
     * @param int|null $limit Optional number of genres to return.
     * @param string $fallback Label used when no genres are linked yet.
     * @return array<int, array{name: string, url: string}>
     */
    private function genreLinks(int $itemId, ?int $limit, string $fallback): array
    {
        if ($itemId < 1) {
            return [$this->genreLink($fallback, $this->slugify($fallback))];
        }

        $sql = 'SELECT content_terms.name, content_terms.slug
                FROM content_term_links
                INNER JOIN content_terms ON content_terms.id = content_term_links.term_id
                WHERE content_term_links.owner_type = :owner_type
                AND content_term_links.owner_id = :owner_id
                AND content_terms.taxonomy = :taxonomy
                ORDER BY content_terms.name ASC';

        if ($limit !== null) {
            $sql .= ' LIMIT ' . max(1, $limit);
        }

        $genres = $this->db->select($sql, [
            'owner_type' => 'item',
            'owner_id' => $itemId,
            'taxonomy' => 'genres',
        ]);

        $links = [];
        foreach ($genres as $genre) {
            $name = trim((string) ($genre['name'] ?? ''));

            if ($name === '') {
                continue;
            }

            $links[] = $this->genreLink($name, (string) ($genre['slug'] ?? ''));
        }

        return $links === [] ? [$this->genreLink($fallback, $this->slugify($fallback))] : $links;
    }

    /**
     * Joins genre names into a compact fallback label.
     *
     * @param array<int, array{name: string, url: string}> $genres
     * @return string
     */
    private function genreLabel(array $genres): string
    {
        $names = array_map(fn(array $genre): string => $genre['name'], $genres);

        return implode(' / ', $names);
    }

    /**
     * Formats one genre link for future archive routes.
     *
     * @return array{name: string, url: string}
     */
    private function genreLink(string $name, string $slug): array
    {
        $slug = trim($slug) !== '' ? $slug : $this->slugify($name);

        return [
            'name' => $name,
            'url' => '/genre/' . rawurlencode($slug),
        ];
    }

    /**
     * Builds a simple URL slug without requiring routing support yet.
     */
    private function slugify(string $value): string
    {
        return MediaUrl::slugify($value);
    }

    /**
     * Converts an internal media type into a user-facing label.
     *
     * @param string $type Media type identifier.
     * @return string Human-readable type label.
     */
    private function typeLabel(string $type): string
    {
        return match ($type) {
            'movie' => 'Movie',
            'tv_show' => 'TV Show',
            default => ucfirst(str_replace('_', ' ', $type)),
        };
    }
}
