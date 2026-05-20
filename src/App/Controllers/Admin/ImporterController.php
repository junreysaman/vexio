<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Services\TMDB\TmdbImporterService;
use Framework\Http\Request;
use Framework\Http\Response;
use Framework\TemplateEngine;
use RuntimeException;

class ImporterController
{
    private const TABS = ['movies', 'tv'];

    public function __construct(
        private TemplateEngine $view,
        private TmdbImporterService $tmdb
    ) {
    }

    public function index(Request $request, Response $response): Response
    {
        $tab = $this->activeTab((string) $request->query('tab', 'movies'));
        $query = trim((string) $request->query('q', ''));
        $year = $this->year((string) $request->query('year', ''));
        $sort = $this->sortForTab($tab, (string) $request->query('sort', 'popularity.desc'));
        $genre = $this->genre((string) $request->query('genre', ''));
        $language = $this->language((string) $request->query('language', ''));
        $country = $this->country((string) $request->query('country', ''));

        $genresByTab = [
            'movies' => $this->genresForTab('movies'),
            'tv' => $this->genresForTab('tv'),
        ];

        return $response->html($this->view->render('admin/importer/index', 'layouts/backend/paper', [
            'title' => 'TMDB Importer',
            'body_class' => 'paper-backend importer-page',
            'activeTab' => $tab,
            'query' => $query,
            'year' => $year,
            'sort' => $sort,
            'genre' => $genre,
            'language' => $language,
            'country' => $country,
            'languageOptions' => $this->languageOptions(),
            'countryOptions' => $this->countryOptions(),
            'genres' => $genresByTab[$tab] ?? [],
            'genresByTab' => $genresByTab,
            'importerStats' => $this->importerStats(),
            'importedData' => $this->importedDataMap(),
        ]));
    }

    public function results(Request $request, Response $response): Response
    {
        $tab = $this->activeTab((string) $request->query('tab', 'movies'));
        $query = trim((string) $request->query('q', ''));
        $year = $this->year((string) $request->query('year', ''));
        $page = max(1, (int) $request->query('page', 1));
        $sort = $this->sortForTab($tab, (string) $request->query('sort', 'popularity.desc'));
        $genre = $this->genre((string) $request->query('genre', ''));
        $language = $this->language((string) $request->query('language', ''));
        $country = $this->country((string) $request->query('country', ''));

        try {
            $data = $this->fetch($tab, $query, $page, $year, $sort, $genre, $language, $country);
            $importedIds = $this->tmdb->importedRootIds($tab);
            $results = array_values(array_filter(
                $this->mapResults($tab, $data['results'] ?? [], $importedIds, $language, $country),
                fn(array $item): bool => !$item['imported']
            ));

            return $response->json([
                'ok' => true,
                'results' => $results,
                'meta' => [
                    'page' => (int) ($data['page'] ?? $page),
                    'total_pages' => min(500, (int) ($data['total_pages'] ?? 1)),
                    'total_results' => (int) ($data['total_results'] ?? count($results)),
                    'visible_results' => count($results),
                ],
            ]);
        } catch (RuntimeException $exception) {
            return $response->error($exception->getMessage(), 422);
        }
    }

    public function import(Request $request, Response $response): Response
    {
        $payload = $request->data();
        $tab = $this->activeTab((string) ($payload['tab'] ?? 'movies'));
        $tmdbId = (int) ($payload['tmdb_id'] ?? 0);
        $status = 'published';
        $featured = !empty($payload['featured']);

        if ($tmdbId < 1) {
            return $response->error('Select a valid TMDB item to import.', 422, [
                'csrf_token' => $this->refreshCsrfToken(),
            ]);
        }

        try {
            if ($tab === 'movies') {
                $item = $this->tmdb->importMovie($tmdbId, 0, $status, $featured);
            } else {
                $item = $this->tmdb->importTvShow($tmdbId, 0, $status, $featured);
            }

            return $response->json([
                'ok' => true,
                'message' => 'Imported "' . ($item['title'] ?? 'TMDB item') . '" as ' . $status . ($featured ? ' and featured' : '') . '.',
                'item' => $item,
                'csrf_token' => $this->refreshCsrfToken(),
            ]);
        } catch (RuntimeException $exception) {
            return $response->error($exception->getMessage(), 422, [
                'csrf_token' => $this->refreshCsrfToken(),
            ]);
        }
    }

    public function hydrateImages(Request $request, Response $response): Response
    {
        $payload = $request->data();
        $scope = (string) ($payload['scope'] ?? 'all');
        $limit = (int) ($payload['limit'] ?? 50);

        try {
            $summary = $this->tmdb->hydrateMissingImages($scope, $limit);

            return $response->json([
                'ok' => true,
                'message' => sprintf(
                    'Hydration complete: scanned %d, hydrated %d, posters %d, backdrops %d, failed %d.',
                    (int) ($summary['scanned'] ?? 0),
                    (int) ($summary['hydrated'] ?? 0),
                    (int) ($summary['posters_hydrated'] ?? 0),
                    (int) ($summary['backdrops_hydrated'] ?? 0),
                    (int) ($summary['failed'] ?? 0)
                ),
                'summary' => $summary,
                'csrf_token' => $this->refreshCsrfToken(),
            ]);
        } catch (RuntimeException $exception) {
            return $response->error($exception->getMessage(), 422, [
                'csrf_token' => $this->refreshCsrfToken(),
            ]);
        }
    }

    public function generateMissingTvContent(Request $request, Response $response): Response
    {
        $payload = $request->data();
        $limit = (int) ($payload['limit'] ?? 10);
        $status = 'published';

        try {
            $summary = $this->tmdb->generateMissingTvSeasonsAndEpisodes($limit, $status);

            return $response->json([
                'ok' => true,
                'message' => sprintf(
                    'Processed %d shows. Created %d seasons and %d episodes.',
                    (int) ($summary['shows_scanned'] ?? 0),
                    (int) ($summary['seasons_created'] ?? 0),
                    (int) ($summary['episodes_created'] ?? 0)
                ),
                'summary' => $summary,
                'csrf_token' => $this->refreshCsrfToken(),
            ]);
        } catch (RuntimeException $exception) {
            return $response->error($exception->getMessage(), 422, [
                'csrf_token' => $this->refreshCsrfToken(),
            ]);
        }
    }

    private function fetch(string $tab, string $query, int $page, ?int $year, string $sort, ?int $genre, ?string $language, ?string $country): array
    {
        if ($query !== '') {
            return match ($tab) {
                'tv' => $this->tmdb->searchTvShows($query, $page, $year, $language),
                default => $this->tmdb->searchMovies($query, $page, $year, $language),
            };
        }

        return match ($tab) {
            'tv' => $this->tmdb->discoverTvShows($page, $year, $sort, $genre, $language, $country),
            default => $this->tmdb->discoverMovies($page, $year, $sort, $genre, $language, $country),
        };
    }

    private function mapResults(string $tab, array $results, array $importedIds = [], ?string $language = null, ?string $country = null): array
    {
        $filtered = array_filter($results, function (array $item) use ($language, $country): bool {
            if (!empty($item['adult'])) {
                return false;
            }

            if ($language !== null && strtolower((string) ($item['original_language'] ?? '')) !== $language) {
                return false;
            }

            if ($country !== null) {
                $countries = array_map('strtoupper', (array) ($item['origin_country'] ?? []));
                if ($countries !== [] && !in_array($country, $countries, true)) {
                    return false;
                }
            }

            return !empty($item['id']);
        });

        return array_map(function (array $item) use ($tab, $importedIds): array {
            $isMovie = $tab === 'movies';
            $title = (string) ($isMovie ? ($item['title'] ?? $item['original_title'] ?? '') : ($item['name'] ?? $item['original_name'] ?? ''));
            $date = (string) ($isMovie ? ($item['release_date'] ?? '') : ($item['first_air_date'] ?? ''));
            $id = (int) ($item['id'] ?? 0);

            return [
                'id' => $id,
                'title' => $title !== '' ? $title : 'Untitled',
                'date' => $date,
                'year' => $date !== '' ? substr($date, 0, 4) : 'N/A',
                'overview' => (string) ($item['overview'] ?? ''),
                'poster_url' => $this->tmdb->posterUrl($item['poster_path'] ?? null),
                'backdrop_url' => $this->tmdb->backdropUrl($item['backdrop_path'] ?? null),
                'vote_average' => round((float) ($item['vote_average'] ?? 0), 1),
                'popularity' => round((float) ($item['popularity'] ?? 0)),
                'language' => strtoupper((string) ($item['original_language'] ?? '')),
                'imported' => in_array($id, $importedIds, true),
            ];
        }, array_values($filtered));
    }

    private function activeTab(string $tab): string
    {
        return in_array($tab, self::TABS, true) ? $tab : 'movies';
    }

    private function sortForTab(string $tab, string $sort): string
    {
        if ($tab === 'movies' && $sort === 'first_air_date.desc') {
            return 'release_date.desc';
        }

        if ($tab === 'tv' && $sort === 'release_date.desc') {
            return 'first_air_date.desc';
        }

        return $sort;
    }

    private function year(string $year): ?int
    {
        if (!preg_match('/^\d{4}$/', $year)) {
            return null;
        }

        return (int) $year;
    }

    private function genre(string $genre): ?int
    {
        if (!preg_match('/^\d+$/', $genre)) {
            return null;
        }

        $genre = (int) $genre;

        return $genre > 0 ? $genre : null;
    }

    private function language(string $language): ?string
    {
        $language = strtolower(trim($language));

        return preg_match('/^[a-z]{2}$/', $language) ? $language : null;
    }

    private function country(string $country): ?string
    {
        $country = strtoupper(trim($country));

        return preg_match('/^[A-Z]{2}$/', $country) ? $country : null;
    }

    private function languageOptions(): array
    {
        return [
            'en' => 'English',
            'ja' => 'Japanese',
            'ko' => 'Korean',
            'zh' => 'Chinese',
            'fr' => 'French',
            'es' => 'Spanish',
            'de' => 'German',
            'it' => 'Italian',
            'pt' => 'Portuguese',
            'hi' => 'Hindi',
            'tl' => 'Tagalog',
        ];
    }

    private function countryOptions(): array
    {
        return [
            'US' => 'United States',
            'JP' => 'Japan',
            'KR' => 'South Korea',
            'CN' => 'China',
            'PH' => 'Philippines',
            'GB' => 'United Kingdom',
            'FR' => 'France',
            'ES' => 'Spain',
            'DE' => 'Germany',
            'IT' => 'Italy',
            'IN' => 'India',
        ];
    }

    private function genresForTab(string $tab): array
    {
        try {
            $genres = $tab === 'tv' ? $this->tmdb->tvGenres() : $this->tmdb->movieGenres();
        } catch (RuntimeException) {
            return [];
        }

        usort($genres, fn(array $left, array $right): int => strcmp((string) ($left['name'] ?? ''), (string) ($right['name'] ?? '')));

        return array_values(array_filter($genres, fn(array $genre): bool => !empty($genre['id']) && !empty($genre['name'])));
    }

    private function importerStats(): array
    {
        try {
            $movies = count($this->tmdb->importedRootIds('movies'));
            $shows = count($this->tmdb->importedRootIds('tv'));
        } catch (RuntimeException) {
            $movies = 0;
            $shows = 0;
        }

        return [
            'credits' => 'TMDB',
            'used' => $movies + $shows,
            'requests' => 'Live',
        ];
    }

    private function refreshCsrfToken(): string
    {
        $_SESSION['token'] = bin2hex(random_bytes(32));

        return $_SESSION['token'];
    }

    private function importedDataMap(): array
    {
        return [
            [
                'label' => 'Movies',
                'table' => 'media_items + content_meta',
                'fields' => 'post type movies; ids IMDb, idtmdb, dt_poster, dt_backdrop, imagenes, youtube_id, imdbRating, imdbVotes, Rated, Country, original_title, release_date, vote_average, vote_count, tagline, runtime, dt_cast, dt_dir',
            ],
            [
                'label' => 'TV Shows',
                'table' => 'media_items + content_meta',
                'fields' => 'post type tvshows; ids TMDB, imagenes, youtube_id, episode_run_time, dt_poster, dt_backdrop, first_air_date, last_air_date, number_of_episodes, number_of_seasons, original_name, imdbRating, imdbVotes, dt_cast, dt_creator',
            ],
            [
                'label' => 'Seasons and Episodes',
                'table' => 'media_seasons, media_episodes + content_meta',
                'fields' => 'seasons: ids, temporada, serie, air_date, dt_poster; episodes: ids, temporada, episodio, serie, episode_name, air_date, imagenes, dt_backdrop',
            ],
            [
                'label' => 'Taxonomies',
                'table' => 'content_terms, content_term_links',
                'fields' => 'genres, dtyear, dtcast, dtdirector, dtcreator, dtnetworks, dtstudio, dtquality using DooPlay taxonomy slugs',
            ],
        ];
    }
}
