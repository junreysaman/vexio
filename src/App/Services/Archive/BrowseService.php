<?php

declare(strict_types=1);

namespace App\Services\Archive;

use App\Database\TmdbMetadataSchema;
use App\Support\MediaUrl;
use Closure;

class BrowseService
{
    public function __construct(private Closure $databaseFactory)
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
                    media_items.poster_image,
                    media_items.poster_url,
                    media_items.release_year,
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
                      media_items.poster_image,
                      media_items.poster_url,
                      media_items.release_year,
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
        $items = $this->getAllItems();

        return [
            'title' => 'Browse',
            'body_class' => 'paper-archive-browse',
            'items' => $items,
            'total_items' => count($items),
            'genres' => $this->getAllGenres(),
            'types' => [
                ['label' => 'All', 'value' => 'all'],
                ['label' => 'Movies', 'value' => 'movie'],
                ['label' => 'TV Shows', 'value' => 'tv_show'],
            ],
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
            'views' => $this->coalesceInt($item['views'] ?? null, null) ?? 0,
            'created_at' => (string) ($item['created_at'] ?? ''),
            'genres' => $genres,
            'genre_label' => $this->genreLabel($genres),
            'poster' => (string) ($item['poster_image'] ?: $item['poster_url'] ?: ''),
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
}
