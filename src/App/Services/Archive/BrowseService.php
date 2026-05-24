<?php

declare(strict_types=1);

namespace App\Services\Archive;

use App\Cache\CacheInterface;
use App\Database\TmdbMetadataSchema;
use App\Support\LocaleDisplay;
use App\Support\MediaImage;
use App\Support\MediaUrl;
use Closure;

class BrowseService
{
    public function __construct(
        private Closure $databaseFactory,
        private CacheInterface $cache
    )
    {
    }

    /**
     * Return the lean catalogue fields needed by the browse page.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAllItems(): array
    {
        $db = ($this->databaseFactory)();
        TmdbMetadataSchema::ensure($db);

        $items = $db->select(
            'SELECT media_items.id,
                    media_items.title,
                    media_items.slug,
                    media_items.type,
                       media_items.synopsis,
                       media_items.poster_url,
                       media_items.release_year,
                    media_items.release_date,
                    media_items.original_language,
                    media_items.country,
                    media_items.origin_country,
                    media_items.tmdb_id,
                    media_items.tmdb_rating,
                    media_items.views,
                    media_items.is_featured,
                    media_items.created_at,
                    GROUP_CONCAT(DISTINCT content_terms.name ORDER BY content_terms.name SEPARATOR \'||\') AS genre_names,
                    GROUP_CONCAT(DISTINCT content_terms.slug ORDER BY content_terms.name SEPARATOR \'||\') AS genre_slugs
             FROM media_items
             LEFT JOIN content_term_links
                ON content_term_links.owner_type = \'item\'
                AND content_term_links.owner_id = media_items.id
             LEFT JOIN content_terms
                ON content_terms.id = content_term_links.term_id
                AND content_terms.taxonomy = \'genres\'
             WHERE media_items.status = :status
             AND media_items.type IN (\'movie\', \'tv_show\')
             GROUP BY media_items.id,
                      media_items.title,
                      media_items.slug,
                      media_items.type,
                      media_items.synopsis,
                       media_items.poster_url,
                       media_items.release_year,
                       media_items.release_date,
                       media_items.original_language,
                       media_items.country,
                       media_items.origin_country,
                       media_items.tmdb_id,
                      media_items.tmdb_rating,
                      media_items.views,
                      media_items.is_featured,
                      media_items.created_at,
                      media_items.updated_at
             ORDER BY media_items.updated_at DESC, media_items.created_at DESC, media_items.id DESC',
            ['status' => 'published']
        );

        return array_map([$this, 'itemPayload'], $items);
    }

    /**
     * Backward compatibility alias.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAll(): array
    {
        return $this->getAllItems();
    }

    /**
     * Build the browse page data payload.
     *
     * @return array<string, mixed>
     */
    public function pageData(): array
    {
        $cacheKey = 'archive:browse:pageData:v3';
        $cached = $this->cache->get($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        $paginatedData = $this->getPaginatedItems(1, 24);

        $data = [
            'title' => 'Browse',
            'body_class' => 'paper-archive-browse',
            'items' => $paginatedData['items'],
            'total_items' => $paginatedData['total'],
            'total_pages' => $paginatedData['total_pages'],
            'current_page' => $paginatedData['page'],
            'page_size' => $paginatedData['limit'],
            'genres' => $this->getAllGenres(),
            'countries' => $this->getAllCountries(),
            'types' => [
                ['label' => 'All', 'value' => 'all'],
                ['label' => 'Movies', 'value' => 'movie'],
                ['label' => 'TV Shows', 'value' => 'tv_show'],
            ],
        ];

        $this->cache->set($cacheKey, $data, $this->publicCacheTtl());

        return $data;
    }

    /**
     * Return paginated items for the browse page.
     *
     * @param int $page Page number (1-based)
     * @param int $limit Items per page
     * @param array<string, mixed> $filters Filter parameters (type, genres, countries, rating, year_from, year_to)
     * @return array{items: array<int, array<string, mixed>>, total: int, page: int, limit: int, total_pages: int}
     */
    public function getPaginatedItems(int $page = 1, int $limit = 24, array $filters = []): array
    {
        $page = max(1, $page);
        $limit = max(1, min(100, $limit));
        $offset = ($page - 1) * $limit;

        $type = $filters['type'] ?? 'all';
        $genres = $filters['genres'] ?? [];
        $countries = $filters['countries'] ?? [];
        $rating = (float) ($filters['rating'] ?? 0);
        $yearFrom = (int) ($filters['year_from'] ?? 0);
        $yearTo = (int) ($filters['year_to'] ?? 0);

        $db = ($this->databaseFactory)();
        TmdbMetadataSchema::ensure($db);

        // Build WHERE conditions
        $whereConditions = ['media_items.status = :status'];
        $params = ['status' => 'published'];

        if ($type !== 'all') {
            $whereConditions[] = 'media_items.type = :type';
            $params['type'] = $type;
        }

        if ($rating > 0) {
            $whereConditions[] = 'media_items.tmdb_rating >= :rating';
            $params['rating'] = $rating;
        }

        if ($yearFrom > 0) {
            $whereConditions[] = 'media_items.release_year >= :year_from';
            $params['year_from'] = $yearFrom;
        }

        if ($yearTo > 0) {
            $whereConditions[] = 'media_items.release_year <= :year_to';
            $params['year_to'] = $yearTo;
        }

        // Genre filtering
        if (!empty($genres) && is_array($genres)) {
            $genrePlaceholders = [];
            foreach ($genres as $index => $genre) {
                $paramKey = 'genre_' . $index;
                $genrePlaceholders[] = ':' . $paramKey;
                $params[$paramKey] = $genre;
            }
            $whereConditions[] = 'EXISTS (
                SELECT 1 FROM content_term_links
                INNER JOIN content_terms ON content_terms.id = content_term_links.term_id
                WHERE content_term_links.owner_type = \'item\'
                AND content_term_links.owner_id = media_items.id
                AND content_terms.taxonomy = \'genres\'
                AND content_terms.slug IN (' . implode(',', $genrePlaceholders) . ')
            )';
        }

        // Country filtering
        if (!empty($countries) && is_array($countries)) {
            $countryConditions = [];
            foreach ($countries as $index => $country) {
                $code = strtoupper(trim((string) $country));
                if ($code === '') {
                    continue;
                }

                $countryKey = 'country_' . $index;
                $originCountryKey = 'origin_country_' . $index;
                $countryCsvKey = 'country_csv_' . $index;
                $originCountryCsvKey = 'origin_country_csv_' . $index;
                $params[$countryKey] = $code;
                $params[$originCountryKey] = $code;
                $params[$countryCsvKey] = $code;
                $params[$originCountryCsvKey] = $code;
                $countryConditions[] = '(
                    media_items.country = :' . $countryKey . '
                    OR media_items.origin_country = :' . $originCountryKey . '
                    OR FIND_IN_SET(:' . $countryCsvKey . ', REPLACE(media_items.country, \'|\', \',\')) > 0
                    OR FIND_IN_SET(:' . $originCountryCsvKey . ', REPLACE(media_items.origin_country, \'|\', \',\')) > 0
                )';
            }

            if ($countryConditions !== []) {
                $whereConditions[] = '(' . implode(' OR ', $countryConditions) . ')';
            }
        }

        $whereClause = implode(' AND ', $whereConditions);

        // Get total count
        $totalQuery = 'SELECT COUNT(DISTINCT media_items.id) AS total
                       FROM media_items
                       WHERE ' . $whereClause;
        $totalResult = $db->selectOne($totalQuery, $params);
        $total = (int) ($totalResult['total'] ?? 0);

        // Get paginated items
        $itemsQuery = 'SELECT media_items.id,
                    media_items.title,
                    media_items.slug,
                    media_items.type,
                       media_items.synopsis,
                       media_items.poster_url,
                       media_items.release_year,
                    media_items.release_date,
                    media_items.original_language,
                    media_items.country,
                    media_items.origin_country,
                    media_items.tmdb_id,
                    media_items.tmdb_rating,
                    media_items.views,
                    media_items.is_featured,
                    media_items.created_at,
                    GROUP_CONCAT(DISTINCT content_terms.name ORDER BY content_terms.name SEPARATOR \'||\') AS genre_names,
                    GROUP_CONCAT(DISTINCT content_terms.slug ORDER BY content_terms.name SEPARATOR \'||\') AS genre_slugs
             FROM media_items
             LEFT JOIN content_term_links
                ON content_term_links.owner_type = \'item\'
                AND content_term_links.owner_id = media_items.id
             LEFT JOIN content_terms
                ON content_terms.id = content_term_links.term_id
                AND content_terms.taxonomy = \'genres\'
             WHERE ' . $whereClause . '
             GROUP BY media_items.id,
                      media_items.title,
                      media_items.slug,
                      media_items.type,
                      media_items.synopsis,
                       media_items.poster_url,
                       media_items.release_year,
                       media_items.release_date,
                       media_items.original_language,
                       media_items.country,
                       media_items.origin_country,
                       media_items.tmdb_id,
                      media_items.tmdb_rating,
                      media_items.views,
                      media_items.is_featured,
                      media_items.created_at,
                      media_items.updated_at
             ORDER BY media_items.updated_at DESC, media_items.created_at DESC, media_items.id DESC
             LIMIT :limit OFFSET :offset';

        $params['limit'] = $limit;
        $params['offset'] = $offset;

        $items = $db->select($itemsQuery, $params);

        $totalPages = $limit > 0 ? (int) ceil($total / $limit) : 0;

        return [
            'items' => array_map([$this, 'itemPayload'], $items),
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => $totalPages,
        ];
    }

    /**
     * Return all genre terms for the browse page filters.
     *
     * @return array<int, array{name: string, slug: string, url: string}>
     */
    public function getAllGenres(): array
    {
        $db = ($this->databaseFactory)();
        TmdbMetadataSchema::ensure($db);

        $genres = $db->select(
            'SELECT id, name, slug
             FROM content_terms
             WHERE taxonomy = :taxonomy
             ORDER BY name ASC',
            ['taxonomy' => 'genres']
        );

        return array_map(function (array $genre): array {
            $name = (string) ($genre['name'] ?? 'Unknown');
            $slug = (string) ($genre['slug'] ?? '');

            return [
                'name' => $name,
                'slug' => $slug,
                'url' => $this->genreLink($name, $slug)['url'],
            ];
        }, $genres);
    }

    /**
     * Return normalized country options for browse filters.
     *
     * @return array<int, array{name: string, slug: string}>
     */
    public function getAllCountries(): array
    {
        $db = ($this->databaseFactory)();
        TmdbMetadataSchema::ensure($db);

        $rows = $db->select(
            'SELECT country, origin_country
             FROM media_items
             WHERE status = :status
             AND type IN (\'movie\', \'tv_show\')',
            ['status' => 'published']
        );

        $countries = [];
        foreach ($rows as $row) {
            $value = trim(implode(',', array_filter([
                (string) ($row['country'] ?? ''),
                (string) ($row['origin_country'] ?? ''),
            ])));

            foreach (LocaleDisplay::countryList($value) as $country) {
                $countries[$country['code']] = [
                    'code' => $country['code'],
                    'name' => $country['name'],
                    'slug' => $country['slug'],
                ];
            }
        }

        uasort($countries, fn(array $left, array $right): int => $left['name'] <=> $right['name']);

        return array_values($countries);
    }

    /**
     * Published watch URLs for sitemap generation (lightweight; no genre JOIN).
     *
     * @return list<array{path: string, lastmod: ?string}>
     */
    public function getPublishedWatchPathsForSitemap(int $limit = 50000): array
    {
        $limit = max(1, min(100000, $limit));
        $db = ($this->databaseFactory)();
        TmdbMetadataSchema::ensure($db);

        $rows = $db->select(
            'SELECT tmdb_id, slug, title, type, updated_at
             FROM media_items
             WHERE status = :status
             AND type IN (\'movie\', \'tv_show\')
             AND tmdb_id IS NOT NULL AND tmdb_id > 0
             ORDER BY updated_at DESC, id DESC
             LIMIT ' . $limit,
            ['status' => 'published']
        );

        $out = [];
        foreach ($rows as $row) {
            $path = MediaUrl::watchUrlForItem($row);
            if ($path === null) {
                continue;
            }

            $raw = $row['updated_at'] ?? null;
            $lastmod = null;
            if (is_string($raw) && $raw !== '') {
                $ts = strtotime($raw);
                $lastmod = $ts !== false ? date('c', $ts) : null;
            }

            $out[] = ['path' => $path, 'lastmod' => $lastmod];
        }

        return $out;
    }

    /**
     * Build the compact catalog payload used by browse cards and filters.
     *
     * @param array<string, mixed> $item Raw media_items row.
     * @return array<string, mixed>
     */
    private function itemPayload(array $item): array
    {
        $itemId = (int) ($item['id'] ?? 0);
        $type = (string) ($item['type'] ?? 'unknown');
        $genres = $this->genreLinksFromRow($item, $this->typeLabel($type));
        $year = $this->coalesceInt($item['release_year'] ?? null, null);
        $rating = $this->coalesceFloat($item['tmdb_rating'] ?? null, null);
        $tmdbId = $this->coalesceInt($item['tmdb_id'] ?? null, null);
        $countries = LocaleDisplay::countryList($this->countryValue($item));

        return [
            'id' => $itemId,
            'title' => (string) ($item['title'] ?? 'Untitled'),
            'slug' => (string) ($item['slug'] ?? ''),
            'type' => $type,
            'type_label' => $this->typeLabel($type),
            'synopsis' => (string) ($item['synopsis'] ?? ''),
            'is_featured' => !empty($item['is_featured']),
            'tmdb_id' => $tmdbId,
            'tmdb_rating' => $rating,
            'release_year' => $year,
            'release_date' => (string) ($item['release_date'] ?? ''),
            'original_language' => (string) ($item['original_language'] ?? ''),
            'language_label' => LocaleDisplay::languageName((string) ($item['original_language'] ?? '')),
            'countries' => $countries,
            'country_label' => $countries === [] ? 'N/A' : implode(', ', array_map(fn(array $country): string => $country['name'], $countries)),
            'views' => $this->coalesceInt($item['views'] ?? null, null) ?? 0,
            'created_at' => (string) ($item['created_at'] ?? ''),
            'genres' => $genres,
            'genre_label' => $this->genreLabel($genres),
            'poster' => MediaImage::srcOnly(MediaImage::posterFromRow($item, 'card')),
            'poster_media' => MediaImage::posterFromRow($item, 'card'),
            'watch_url' => MediaUrl::watchUrlForItem($item),
            'watchUrl' => MediaUrl::watchUrlForItem($item),
        ];
    }

    private function genreLinksFromRow(array $item, string $fallback): array
    {
        $names = $this->splitPipeList((string) ($item['genre_names'] ?? ''));
        $slugs = $this->splitPipeList((string) ($item['genre_slugs'] ?? ''));

        if ($names === []) {
            return [$this->genreLink($fallback, $this->slugify($fallback))];
        }

        $genres = [];
        foreach ($names as $index => $name) {
            $genres[] = $this->genreLink($name, $slugs[$index] ?? '');
        }

        return $genres;
    }

    /**
     * @return array<int, string>
     */
    private function splitPipeList(string $value): array
    {
        if (trim($value) === '') {
            return [];
        }

        return array_values(array_filter(array_map(
            fn(string $item): string => trim($item),
            explode('||', $value)
        ), fn(string $item): bool => $item !== ''));
    }

    private function genreLabel(array $genres): string
    {
        return implode(' / ', array_map(fn(array $genre): string => $genre['name'], $genres));
    }

    private function genreLink(string $name, string $slug): array
    {
        $slug = trim($slug) !== '' ? $slug : $this->slugify($name);

        return [
            'name' => $name,
            'url' => '/genre/' . rawurlencode($slug),
        ];
    }

    private function slugify(string $value): string
    {
        return MediaUrl::slugify($value);
    }

    private function typeLabel(string $type): string
    {
        return match ($type) {
            'movie' => 'Movie',
            'tv_show' => 'TV Show',
            default => ucfirst(str_replace('_', ' ', $type)),
        };
    }

    private function coalesceInt(mixed $first, mixed $second): ?int
    {
        if (is_numeric($first)) {
            return (int) $first;
        }

        if (is_numeric($second)) {
            return (int) $second;
        }

        return null;
    }

    private function coalesceFloat(mixed $first, mixed $second): ?float
    {
        if (is_numeric($first)) {
            return (float) $first;
        }

        if (is_numeric($second)) {
            return (float) $second;
        }

        return null;
    }

    private function countryValue(array $item): string
    {
        return trim(implode(',', array_filter([
            (string) ($item['country'] ?? ''),
            (string) ($item['origin_country'] ?? ''),
        ])));
    }

    private function publicCacheTtl(): int
    {
        return max(60, min(3600, (int) ($_ENV['PUBLIC_PAGE_CACHE_TTL'] ?? 600)));
    }
}
