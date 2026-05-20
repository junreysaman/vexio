<?php

declare(strict_types=1);

namespace App\Services\Archive;

use App\Database\TmdbMetadataSchema;
use App\Support\MediaUrl;
use Closure;

class TrendingPageService
{
    public function __construct(private Closure $databaseFactory)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function pageData(): array
    {
        $items = $this->getTrendingItems();

        return [
            'title' => 'Trending',
            'body_class' => 'paper-archive-trending-page',
            'items' => $items,
            'spotlight' => $items[0] ?? null,
            'spotlight_sidebar' => array_slice($items, 1, 4),
            'top_chart' => array_slice($this->sortByViews($items), 0, 10),
            'watched_today' => array_slice($this->sortByViews($items), 0, 8),
            'regions' => $this->regions($items),
            'genres' => $this->genres($items),
            'stats' => $this->stats($items),
            'total_items' => count($items),
            'filters' => [
                ['label' => 'All Trending', 'value' => 'all'],
                ['label' => 'Series', 'value' => 'tv_show'],
                ['label' => 'Movies', 'value' => 'movie'],
                ['label' => 'Anime', 'value' => 'anime'],
                ['label' => 'New Releases', 'value' => 'new'],
                ['label' => 'Top Rated', 'value' => 'top'],
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getTrendingItems(): array
    {
        $db = ($this->databaseFactory)();

        if (is_object($db) && method_exists($db, 'tableExists')) {
            TmdbMetadataSchema::ensure($db);
        }

        $rows = $db->select(
            'SELECT media_items.id,
                    media_items.title,
                    media_items.slug,
                    media_items.type,
                    media_items.synopsis,
                    media_items.poster_image,
                    media_items.poster_url,
                    media_items.backdrop_image,
                    media_items.release_year,
                    media_items.tmdb_id,
                    media_items.tmdb_rating,
                    media_items.tmdb_popularity,
                    media_items.views,
                    media_items.is_featured,
                    media_items.created_at,
                    media_items.updated_at,
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
                      media_items.backdrop_image,
                      media_items.release_year,
                      media_items.tmdb_id,
                      media_items.tmdb_rating,
                      media_items.tmdb_popularity,
                      media_items.views,
                      media_items.is_featured,
                      media_items.created_at,
                      media_items.updated_at
             ORDER BY media_items.views DESC, media_items.tmdb_rating DESC, media_items.tmdb_popularity DESC, media_items.updated_at DESC
             LIMIT 48',
            ['status' => 'published']
        );

        $items = $this->sortByViews(array_map([$this, 'itemPayload'], $rows));

        foreach ($items as $index => $item) {
            $items[$index]['rank'] = $index + 1;
        }

        return $items;
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @return array<int, array<string, string>>
     */
    private function stats(array $items): array
    {
        $views = array_sum(array_map(fn(array $item): int => (int) $item['views'], $items));
        $rising = count(array_filter($items, fn(array $item): bool => $item['scores']['day'] >= $item['scores']['week'] * 0.7));
        $ratings = array_values(array_filter(array_map(fn(array $item): float => (float) $item['rating'], $items)));
        $averageRating = $ratings === [] ? 0 : array_sum($ratings) / count($ratings);

        return [
            ['value' => $this->compactNumber($views), 'label' => 'Catalogue Views', 'change' => '+18% this week', 'tone' => 'gold'],
            ['value' => (string) $rising, 'label' => 'Rising Titles', 'change' => '+24h momentum', 'tone' => 'purple'],
            ['value' => number_format($averageRating, 1), 'label' => 'Avg. Rating', 'change' => 'Top picks', 'tone' => 'green'],
            ['value' => $this->compactNumber((int) round($views * 0.08)), 'label' => 'Active Watchers', 'change' => 'Live estimate', 'tone' => 'accent'],
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @return array<int, array<string, mixed>>
     */
    private function regions(array $items): array
    {
        $top = array_slice($items, 0, 6);
        $regions = [
            ['name' => 'Japan', 'flag' => 'JP'],
            ['name' => 'USA', 'flag' => 'US'],
            ['name' => 'South Korea', 'flag' => 'KR'],
            ['name' => 'China', 'flag' => 'CN'],
            ['name' => 'Philippines', 'flag' => 'PH'],
            ['name' => 'Global', 'flag' => 'GL'],
        ];

        return array_map(function (array $region, int $index) use ($top): array {
            $item = $top[$index] ?? $top[0] ?? null;

            return [
                'name' => $region['name'],
                'flag' => $region['flag'],
                'count' => $this->compactNumber(max(100, (int) (($item['views'] ?? 1200) / 3))),
                'image' => (string) ($item['backdrop'] ?? $item['poster'] ?? 'https://picsum.photos/seed/vexio-region/420/260'),
            ];
        }, $regions, array_keys($regions));
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @return array<int, array<string, mixed>>
     */
    private function genres(array $items): array
    {
        $genres = [];

        foreach ($items as $item) {
            foreach ($item['genres'] as $genre) {
                $name = $genre['name'];
                $slug = $genre['slug'];
                $genres[$slug] ??= [
                    'name' => $name,
                    'slug' => $slug,
                    'url' => $genre['url'],
                    'count' => 0,
                    'score' => 0,
                ];
                $genres[$slug]['count']++;
                $genres[$slug]['score'] += $item['scores']['week'];
            }
        }

        usort($genres, fn(array $a, array $b): int => $b['score'] <=> $a['score']);

        return array_slice(array_map(function (array $genre): array {
            return [
                ...$genre,
                'trend' => '+' . min(499, max(24, (int) round($genre['score'] / max(1, $genre['count']) / 40))) . '% this week',
                'width' => min(96, max(34, (int) round($genre['score'] / 150))),
            ];
        }, $genres), 0, 8);
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @return array<int, array<string, mixed>>
     */
    private function sortByViews(array $items): array
    {
        usort($items, function (array $a, array $b): int {
            return ((int) $b['views'] <=> (int) $a['views'])
                ?: ((float) $b['rating'] <=> (float) $a['rating'])
                ?: ($b['scores']['week'] <=> $a['scores']['week']);
        });

        return $items;
    }

    /**
     * @param array<string, mixed> $item
     * @return array<string, mixed>
     */
    private function itemPayload(array $item): array
    {
        $views = (int) ($item['views'] ?? 0);
        $rating = (float) ($item['tmdb_rating'] ?? 0);
        $createdAt = strtotime((string) ($item['created_at'] ?? '')) ?: 0;
        $updatedAt = strtotime((string) ($item['updated_at'] ?? '')) ?: $createdAt;
        $ageDays = max(0, (time() - max($createdAt, $updatedAt)) / 86400);
        $genres = $this->genreLinksFromRow($item, $this->typeLabel((string) ($item['type'] ?? '')));
        $isAnime = $this->containsGenre($genres, 'anime');
        $isNew = $ageDays <= 10;
        $isTop = $rating >= 8.8;
        $scores = [
            'day' => $views,
            'week' => $views,
            'month' => $views,
        ];
        $poster = (string) ($item['poster_url'] ?: $item['poster_image'] ?: 'https://picsum.photos/seed/vexio-trending-' . (int) ($item['id'] ?? 0) . '/500/750');
        $backdrop = (string) ($item['backdrop_image'] ?: $poster);

        return [
            'id' => (int) ($item['id'] ?? 0),
            'title' => (string) ($item['title'] ?? 'Untitled'),
            'slug' => MediaUrl::itemSlug($item),
            'type' => (string) ($item['type'] ?? 'unknown'),
            'type_label' => $this->typeLabel((string) ($item['type'] ?? '')),
            'primary_category' => $isAnime ? 'anime' : (string) ($item['type'] ?? 'unknown'),
            'secondary_category' => $isNew ? 'new' : ($isTop ? 'top' : 'trending'),
            'synopsis' => (string) ($item['synopsis'] ?: 'A trending title from the Vexio catalogue.'),
            'poster' => $poster,
            'backdrop' => $backdrop,
            'year' => (string) ($item['release_year'] ?: 'N/A'),
            'rating' => $rating,
            'rating_label' => $rating > 0 ? number_format($rating, 1) : 'N/A',
            'views' => $views,
            'views_label' => $this->compactNumber($views) . ' views',
            'watch_url' => MediaUrl::watchUrlForItem($item) ?? '/archive/browse',
            'genres' => $genres,
            'genre_label' => implode(' / ', array_map(fn(array $genre): string => $genre['name'], array_slice($genres, 0, 2))),
            'scores' => $scores,
        ];
    }

    /**
     * @param array<int, array<string, string>> $genres
     */
    private function containsGenre(array $genres, string $slug): bool
    {
        foreach ($genres as $genre) {
            if ($genre['slug'] === $slug || str_contains(strtolower($genre['name']), $slug)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, array{name: string, slug: string, url: string}>
     */
    private function genreLinksFromRow(array $item, string $fallback): array
    {
        $names = $this->splitPipeList((string) ($item['genre_names'] ?? ''));
        $slugs = $this->splitPipeList((string) ($item['genre_slugs'] ?? ''));

        if ($names === []) {
            $names = [$fallback];
            $slugs = [MediaUrl::slugify($fallback)];
        }

        $genres = [];
        foreach ($names as $index => $name) {
            $slug = $slugs[$index] ?? MediaUrl::slugify($name);
            $genres[] = [
                'name' => $name,
                'slug' => $slug,
                'url' => '/genre/' . rawurlencode($slug),
            ];
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

    private function typeLabel(string $type): string
    {
        return match ($type) {
            'movie' => 'Movie',
            'tv_show' => 'Series',
            default => ucfirst(str_replace('_', ' ', $type)),
        };
    }

    private function compactNumber(int $value): string
    {
        if ($value >= 1000000) {
            return rtrim(rtrim(number_format($value / 1000000, 1), '0'), '.') . 'M';
        }

        if ($value >= 1000) {
            return rtrim(rtrim(number_format($value / 1000, 1), '0'), '.') . 'K';
        }

        return (string) $value;
    }
}
