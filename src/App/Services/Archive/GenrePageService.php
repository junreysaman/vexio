<?php

declare(strict_types=1);

namespace App\Services\Archive;

use App\Database\TmdbMetadataSchema;
use App\Support\MediaUrl;
use Closure;

class GenrePageService
{
    public function __construct(private Closure $databaseFactory)
    {
    }

    /**
     * Build the data payload for the genre archive page.
     *
     * @return array<string, mixed>
     */
    public function pageData(?string $slug = null, int $limit = 24): array
    {
        $genre = $slug !== null && trim($slug) !== '' ? $this->genreBySlug($slug) : null;
        $items = $genre ? $this->itemsForGenre((int) $genre['id'], $limit) : [];
        $genres = $this->genres();
        $rankedGenres = $this->rankGenresByTotal($genres);

        return [
            'title' => $genre ? (string) $genre['name'] : 'Genres',
            'body_class' => 'paper-archive-genre-page',
            'genres' => $genres,
            'featured_genres' => array_slice($rankedGenres, 0, 2),
            'main_genres' => array_slice($rankedGenres, 2, 8),
            'more_genres' => array_slice($rankedGenres, 10),
            'active_genre' => $genre,
            'items' => $items,
            'total_items' => count($items),
            'total_catalog_items' => array_sum(array_map(static fn(array $genre): int => (int) ($genre['total'] ?? 0), $genres)),
        ];
    }

    /**
     * @return array<int, array{id: int, name: string, slug: string, url: string, total: int, backdrop: string, poster: string, model_title: string}>
     */
    public function genres(): array
    {
        $db = ($this->databaseFactory)();
        TmdbMetadataSchema::ensure($db);

        $rows = $db->select(
            'SELECT content_terms.id,
                    content_terms.name,
                    content_terms.slug,
                    COUNT(DISTINCT media_items.id) AS total,
                    (
                        SELECT COALESCE(NULLIF(model_items.backdrop_image, \'\'), NULLIF(model_items.poster_image, \'\'), NULLIF(model_items.poster_url, \'\'), \'\')
                        FROM media_items model_items
                        INNER JOIN content_term_links model_links
                            ON model_links.owner_id = model_items.id
                            AND model_links.owner_type = \'item\'
                            AND model_links.term_id = content_terms.id
                        WHERE model_items.status = :model_backdrop_status
                        AND model_items.type IN (\'movie\', \'tv_show\')
                        AND COALESCE(NULLIF(model_items.backdrop_image, \'\'), NULLIF(model_items.poster_image, \'\'), NULLIF(model_items.poster_url, \'\'), \'\') <> \'\'
                        ORDER BY
                            CASE WHEN NULLIF(model_items.backdrop_image, \'\') IS NOT NULL THEN 0 ELSE 1 END,
                            model_items.tmdb_rating DESC,
                            model_items.views DESC,
                            model_items.created_at DESC
                        LIMIT 1
                    ) AS model_backdrop,
                    (
                        SELECT model_titles.title
                        FROM media_items model_titles
                        INNER JOIN content_term_links model_title_links
                            ON model_title_links.owner_id = model_titles.id
                            AND model_title_links.owner_type = \'item\'
                            AND model_title_links.term_id = content_terms.id
                        WHERE model_titles.status = :model_title_status
                        AND model_titles.type IN (\'movie\', \'tv_show\')
                        ORDER BY model_titles.tmdb_rating DESC, model_titles.views DESC, model_titles.created_at DESC
                        LIMIT 1
                    ) AS model_title
             FROM content_terms
             LEFT JOIN content_term_links
                ON content_term_links.term_id = content_terms.id
                AND content_term_links.owner_type = \'item\'
             LEFT JOIN media_items
                ON media_items.id = content_term_links.owner_id
                AND media_items.status = :status
                AND media_items.type IN (\'movie\', \'tv_show\')
             WHERE content_terms.taxonomy = :taxonomy
             GROUP BY content_terms.id, content_terms.name, content_terms.slug
             ORDER BY content_terms.name ASC',
            [
                'status' => 'published',
                'model_backdrop_status' => 'published',
                'model_title_status' => 'published',
                'taxonomy' => 'genres',
            ]
        );

        return array_map(fn(array $row): array => [
            'id' => (int) ($row['id'] ?? 0),
            'name' => (string) ($row['name'] ?? 'Unknown'),
            'slug' => (string) ($row['slug'] ?? ''),
            'url' => '/genre/' . rawurlencode((string) ($row['slug'] ?? '')),
            'total' => (int) ($row['total'] ?? 0),
            'backdrop' => (string) ($row['model_backdrop'] ?? ''),
            'poster' => (string) ($row['model_backdrop'] ?? ''),
            'model_title' => (string) ($row['model_title'] ?? ''),
        ], $rows);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function genreBySlug(string $slug): ?array
    {
        $db = ($this->databaseFactory)();
        TmdbMetadataSchema::ensure($db);

        $row = $db->selectOne(
            'SELECT id, name, slug
             FROM content_terms
             WHERE taxonomy = :taxonomy
             AND slug = :slug
             LIMIT 1',
            ['taxonomy' => 'genres', 'slug' => trim($slug)]
        );

        if (!$row) {
            return null;
        }

        return [
            'id' => (int) ($row['id'] ?? 0),
            'name' => (string) ($row['name'] ?? 'Unknown'),
            'slug' => (string) ($row['slug'] ?? ''),
            'url' => '/genre/' . rawurlencode((string) ($row['slug'] ?? '')),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function itemsForGenre(int $genreId, int $limit = 24): array
    {
        if ($genreId < 1) {
            return [];
        }

        $db = ($this->databaseFactory)();
        TmdbMetadataSchema::ensure($db);
        $limit = max(1, min(60, $limit));

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
                    media_items.created_at
             FROM media_items
             INNER JOIN content_term_links
                ON content_term_links.owner_id = media_items.id
                AND content_term_links.owner_type = \'item\'
                AND content_term_links.term_id = :genre_id
             WHERE media_items.status = :status
             AND media_items.type IN (\'movie\', \'tv_show\')
             ORDER BY media_items.tmdb_rating DESC, media_items.views DESC, media_items.created_at DESC
             LIMIT ' . $limit,
            ['genre_id' => $genreId, 'status' => 'published']
        );

        return array_map(fn(array $item): array => $this->itemPayload($item), $items);
    }

    /**
     * @param array<string, mixed> $item
     * @return array<string, mixed>
     */
    private function itemPayload(array $item): array
    {
        $type = (string) ($item['type'] ?? 'unknown');

        return [
            'id' => (int) ($item['id'] ?? 0),
            'title' => (string) ($item['title'] ?? 'Untitled'),
            'slug' => (string) ($item['slug'] ?? ''),
            'type' => $type,
            'type_label' => $this->typeLabel($type),
            'synopsis' => (string) ($item['synopsis'] ?? ''),
            'tmdb_id' => is_numeric($item['tmdb_id'] ?? null) ? (int) $item['tmdb_id'] : null,
            'poster' => (string) (($item['poster_url'] ?? '') ?: ($item['poster_image'] ?? '')),
            'release_year' => is_numeric($item['release_year'] ?? null) ? (int) $item['release_year'] : null,
            'tmdb_rating' => is_numeric($item['tmdb_rating'] ?? null) ? (float) $item['tmdb_rating'] : null,
            'views' => (int) ($item['views'] ?? 0),
            'created_at' => (string) ($item['created_at'] ?? ''),
            'watch_url' => MediaUrl::watchUrlForItem($item),
            'watchUrl' => MediaUrl::watchUrlForItem($item),
        ];
    }

    private function typeLabel(string $type): string
    {
        return match ($type) {
            'movie' => 'Movie',
            'tv_show' => 'TV Show',
            default => ucfirst(str_replace('_', ' ', $type)),
        };
    }

    /**
     * @param array<int, array<string, mixed>> $genres
     * @return array<int, array<string, mixed>>
     */
    private function rankGenresByTotal(array $genres): array
    {
        usort($genres, static function (array $left, array $right): int {
            $totalCompare = (int) ($right['total'] ?? 0) <=> (int) ($left['total'] ?? 0);

            if ($totalCompare !== 0) {
                return $totalCompare;
            }

            return strcasecmp((string) ($left['name'] ?? ''), (string) ($right['name'] ?? ''));
        });

        return $genres;
    }
}
