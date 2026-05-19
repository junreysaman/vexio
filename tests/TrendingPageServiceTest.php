<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Services\Archive\TrendingPageService;

final class FakeTrendingDatabase
{
    public function select(string $query, array $params = []): array
    {
        return [
            [
                'id' => 1,
                'title' => 'Alpha Movie',
                'slug' => 'alpha-movie',
                'type' => 'movie',
                'synopsis' => 'A popular movie.',
                'poster_image' => '/alpha-poster.jpg',
                'poster_url' => '',
                'backdrop_image' => '/alpha-backdrop.jpg',
                'release_year' => 2026,
                'tmdb_id' => 101,
                'tmdb_rating' => '8.5',
                'tmdb_popularity' => '120.5',
                'views' => 5000,
                'is_featured' => 1,
                'created_at' => '2026-05-18 10:00:00',
                'updated_at' => '2026-05-19 10:00:00',
                'genre_names' => 'Action||Adventure',
                'genre_slugs' => 'action||adventure',
            ],
            [
                'id' => 2,
                'title' => 'Beta Series',
                'slug' => 'beta-series',
                'type' => 'tv_show',
                'synopsis' => 'A top rated series.',
                'poster_image' => '/beta-poster.jpg',
                'poster_url' => '',
                'backdrop_image' => '/beta-backdrop.jpg',
                'release_year' => 2025,
                'tmdb_id' => 202,
                'tmdb_rating' => '9.7',
                'tmdb_popularity' => '80.0',
                'views' => 3000,
                'is_featured' => 0,
                'created_at' => '2026-03-10 10:00:00',
                'updated_at' => '2026-03-17 10:00:00',
                'genre_names' => 'Drama',
                'genre_slugs' => 'drama',
            ],
            [
                'id' => 4,
                'title' => 'Delta Low Views High Rating',
                'slug' => 'delta-low-views-high-rating',
                'type' => 'movie',
                'synopsis' => 'A highly rated but less watched movie.',
                'poster_image' => '/delta-poster.jpg',
                'poster_url' => '',
                'backdrop_image' => '/delta-backdrop.jpg',
                'release_year' => 2026,
                'tmdb_id' => 404,
                'tmdb_rating' => '10.0',
                'tmdb_popularity' => '9999.0',
                'views' => 100,
                'is_featured' => 0,
                'created_at' => '2026-05-19 09:30:00',
                'updated_at' => '2026-05-19 11:30:00',
                'genre_names' => 'Action',
                'genre_slugs' => 'action',
            ],
            [
                'id' => 3,
                'title' => 'Gamma Anime',
                'slug' => 'gamma-anime',
                'type' => 'tv_show',
                'synopsis' => '',
                'poster_image' => '',
                'poster_url' => '/gamma-poster.jpg',
                'backdrop_image' => '',
                'release_year' => 2026,
                'tmdb_id' => 303,
                'tmdb_rating' => '7.5',
                'tmdb_popularity' => '250.0',
                'views' => 9000,
                'is_featured' => 0,
                'created_at' => '2026-05-19 09:00:00',
                'updated_at' => '2026-05-19 11:00:00',
                'genre_names' => 'Anime||Fantasy',
                'genre_slugs' => 'anime||fantasy',
            ],
        ];
    }
}

function assertSameValue(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException($message . ' Expected ' . var_export($expected, true) . ', got ' . var_export($actual, true));
    }
}

function assertTrueValue(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$service = new TrendingPageService(fn() => new FakeTrendingDatabase());
$data = $service->pageData();

assertSameValue('Trending', $data['title'], 'Page title should be set.');
assertSameValue(4, $data['total_items'], 'Total item count should include all fake rows.');
assertSameValue('Gamma Anime', $data['spotlight']['title'], 'Highest trending score should become spotlight.');
assertSameValue(['Gamma Anime', 'Alpha Movie', 'Beta Series', 'Delta Low Views High Rating'], array_column($data['items'], 'title'), 'Trending ranks should be ordered by views descending.');
assertSameValue('/watch/tvshow/303/gamma-anime', $data['spotlight']['watch_url'], 'TV watch URL should be generated.');
assertSameValue('/watch/movie/101/alpha-movie', $data['items'][1]['watch_url'], 'Movie watch URL should be generated.');
assertSameValue('anime', $data['items'][0]['primary_category'], 'Anime genre should create anime category.');
assertSameValue('new', $data['items'][0]['secondary_category'], 'Fresh item should create new category.');
assertSameValue('top', $data['items'][2]['secondary_category'], 'Highly rated item should create top category.');
assertTrueValue($data['stats'][0]['value'] !== '0', 'Stats should include formatted views.');
assertTrueValue(count($data['genres']) >= 3, 'Genre summaries should be generated.');
assertTrueValue(is_int($data['items'][0]['scores']['day']) && $data['items'][0]['scores']['day'] > 0, 'Day score should be available for JS sorting.');

echo "TrendingPageServiceTest passed\n";
