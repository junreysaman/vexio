<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Services\TMDB\TmdbImporterService;
use App\Support\LocaleDisplay;
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
        $filters = $this->discoverFilters($request->query());

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
            'filters' => $filters,
            'languageOptions' => $this->languageOptions(),
            'countryOptions' => $this->countryOptions(),
            'genres' => $genresByTab[$tab] ?? [],
            'genresByTab' => $genresByTab,
            'networkOptions' => $this->tmdb->networkOptions(),
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
        $filters = $this->discoverFilters($request->query());

        try {
            $importedIds = $this->tmdb->importedRootIds($tab);
            $results = [];
            $seenIds = [];
            $scanPage = $page;
            $totalPages = 500;
            $totalResults = 0;
            $scannedPages = 0;
            $targetCount = 20;
            $maxScanPages = 20;

            while (count($results) < $targetCount && $scanPage <= min(500, $totalPages) && $scannedPages < $maxScanPages) {
                $data = $this->fetch($tab, $query, $scanPage, $year, $sort, $genre, $language, $country, $filters);
                $totalPages = min(500, (int) ($data['total_pages'] ?? $totalPages));
                $totalResults = (int) ($data['total_results'] ?? $totalResults);
                $scannedPages++;

                $items = array_values(array_filter(
                    $this->mapResults($tab, $data['results'] ?? [], $importedIds, $language, $country, $filters),
                    fn(array $item): bool => !$item['imported'] && $this->isReleasedResult($item)
                ));

                foreach ($items as $item) {
                    $id = (int) ($item['id'] ?? 0);
                    if ($id < 1 || isset($seenIds[$id])) {
                        continue;
                    }

                    $seenIds[$id] = true;
                    $results[] = $item;

                    if (count($results) >= $targetCount) {
                        break;
                    }
                }

                $scanPage++;
            }

            return $response->json([
                'ok' => true,
                'results' => $results,
                'meta' => [
                    'page' => $page,
                    'next_page' => $scanPage <= $totalPages ? $scanPage : null,
                    'total_pages' => $totalPages,
                    'total_results' => $totalResults,
                    'visible_results' => count($results),
                    'scanned_pages' => $scannedPages,
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
        $featured = filter_var($payload['featured'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $filters = $this->discoverFilters($payload);

        if ($tmdbId < 1) {
            return $response->error('Select a valid TMDB item to import.', 422, [
                'csrf_token' => $this->refreshCsrfToken(),
            ]);
        }

        try {
            if ($tab === 'movies') {
                $item = $this->tmdb->importMovie($tmdbId, 0, $status, $featured, $filters);
            } else {
                $item = $this->tmdb->importTvShow($tmdbId, 0, $status, $featured, $filters);
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

    private function fetch(string $tab, string $query, int $page, ?int $year, string $sort, ?int $genre, ?string $language, ?string $country, array $filters): array
    {
        if ($query !== '') {
            return match ($tab) {
                'tv' => $this->tmdb->searchTvShows($query, $page, $year, $language),
                default => $this->tmdb->searchMovies($query, $page, $year, $language),
            };
        }

        return match ($tab) {
            'tv' => $this->tmdb->discoverTvShows($page, $year, $sort, $genre, $language, $country, $filters),
            default => $this->tmdb->discoverMovies($page, $year, $sort, $genre, $language, $country, $filters),
        };
    }

    private function mapResults(string $tab, array $results, array $importedIds = [], ?string $language = null, ?string $country = null, array $filters = []): array
    {
        $filtered = array_filter($results, function (array $item) use ($tab, $language, $country, $filters): bool {
            if (empty($filters['include_adult']) && $this->isAdultResult($item)) {
                return false;
            }

            if ($tab === 'movies' && empty($filters['include_video']) && !empty($item['video'])) {
                return false;
            }

            if (!$this->isEligibleRatingResult($item, $filters)) {
                return false;
            }

            $date = (string) ($tab === 'movies' ? ($item['release_date'] ?? '') : ($item['first_air_date'] ?? ''));
            if (!$this->isEligibleDateResult($date, $filters)) {
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
                'vote_count' => (int) ($item['vote_count'] ?? 0),
                'popularity' => round((float) ($item['popularity'] ?? 0)),
                'language' => LocaleDisplay::languageName((string) ($item['original_language'] ?? '')),
                'imported' => in_array($id, $importedIds, true),
            ];
        }, array_values($filtered));
    }

    private function activeTab(string $tab): string
    {
        return in_array($tab, self::TABS, true) ? $tab : 'movies';
    }

    private function isReleasedResult(array $item): bool
    {
        $date = trim((string) ($item['date'] ?? ''));
        if ($date === '') {
            return false;
        }

        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return false;
        }

        return $timestamp <= strtotime('today');
    }

    private function isAdultResult(array $item): bool
    {
        if (filter_var($item['adult'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            return true;
        }

        $text = strtolower(trim(implode(' ', array_filter([
            (string) ($item['title'] ?? ''),
            (string) ($item['original_title'] ?? ''),
            (string) ($item['name'] ?? ''),
            (string) ($item['original_name'] ?? ''),
            (string) ($item['overview'] ?? ''),
        ]))));

        if ($text === '') {
            return false;
        }

        foreach ($this->adultTitlePatterns() as $pattern) {
            if (preg_match($pattern, $text) === 1) {
                return true;
            }
        }

        return false;
    }

    private function isEligibleDateResult(string $date, array $filters): bool
    {
        $date = trim($date);

        if ($date === '') {
            return true;
        }

        if (!empty($filters['date_gte']) && $date < $filters['date_gte']) {
            return false;
        }

        if (!empty($filters['date_lte']) && $date > $filters['date_lte']) {
            return false;
        }

        return true;
    }

    private function isEligibleRatingResult(array $item, array $filters): bool
    {
        if (!isset($item['vote_average'])) {
            return false;
        }

        $rating = round((float) $item['vote_average'], 1);
        $voteCount = (int) ($item['vote_count'] ?? 0);

        if (isset($filters['vote_average_gte']) && $filters['vote_average_gte'] !== null && $rating < (float) $filters['vote_average_gte']) {
            return false;
        }

        if (isset($filters['vote_average_lte']) && $filters['vote_average_lte'] !== null && $rating > (float) $filters['vote_average_lte']) {
            return false;
        }

        if (isset($filters['vote_count_gte']) && $filters['vote_count_gte'] !== null && $voteCount < (int) $filters['vote_count_gte']) {
            return false;
        }

        if (isset($filters['vote_count_lte']) && $filters['vote_count_lte'] !== null && $voteCount > (int) $filters['vote_count_lte']) {
            return false;
        }

        return true;
    }

    /**
     * TMDB occasionally leaves adult=false on softcore/direct-to-video titles.
     *
     * @return list<string>
     */
    private function adultTitlePatterns(): array
    {
        return [
            '/\bmy\s+wife[\'’]s\s+sister\b/u',
            '/\bwife[\'’]s\s+sister\b/u',
            '/\bsister[-\s]?in[-\s]?law\b/u',
            '/\byoung\s+(?:sister|wife|mother|aunt)\b/u',
            '/\b(?:stepmom|stepmother|stepdad|stepfather|stepsister|stepbrother)\b/u',
            '/\b(?:sex|sexual|sexy|porn|porno|pornographic)\b/u',
            '/\bsex\b/u',
            '/\bsoftcore\b/u',
            '/\berotic\b/u',
            '/\berotica\b/u',
            '/\b(?:lust|seduction|seduce|sensual|nude|naked)\b/u',
            '/\b(?:mistress|prostitute|prostitution|brothel|call\s+girl)\b/u',
            '/\b(?:affair|adultery)\b/u',
            '/\b(?:massage\s+parlor|hostess|escort)\b/u',
            '/\badult\b/u',
        ];
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

    private function discoverFilters(array $source): array
    {
        return [
            'include_adult' => $this->booleanFilter($source['include_adult'] ?? false),
            'include_video' => $this->booleanFilter($source['include_video'] ?? false),
            'vote_average_gte' => $this->floatFilter($source['vote_average_gte'] ?? TmdbImporterService::MIN_ROOT_RATING, 0, 10),
            'vote_average_lte' => $this->floatFilter($source['vote_average_lte'] ?? TmdbImporterService::MAX_ROOT_RATING, 0, 10),
            'vote_count_gte' => $this->intFilter($source['vote_count_gte'] ?? TmdbImporterService::MIN_ROOT_VOTE_COUNT, 0),
            'vote_count_lte' => $this->intFilter($source['vote_count_lte'] ?? null, 0),
            'date_gte' => $this->dateFilter($source['date_gte'] ?? null),
            'date_lte' => $this->dateFilter($source['date_lte'] ?? null),
            'primary_date_gte' => $this->dateFilter($source['primary_date_gte'] ?? null),
            'primary_date_lte' => $this->dateFilter($source['primary_date_lte'] ?? null),
            'region' => $this->country((string) ($source['region'] ?? '')),
            'watch_region' => $this->country((string) ($source['watch_region'] ?? '')),
            'network' => $this->intFilter($source['network'] ?? null, 1),
        ];
    }

    private function booleanFilter(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    private function floatFilter(mixed $value, float $min, float $max): ?float
    {
        $value = trim((string) $value);
        if ($value === '' || !is_numeric($value)) {
            return null;
        }

        $value = round((float) $value, 1);

        return $value >= $min && $value <= $max ? $value : null;
    }

    private function intFilter(mixed $value, int $min): ?int
    {
        $value = trim((string) $value);
        if ($value === '' || !preg_match('/^\d+$/', $value)) {
            return null;
        }

        $value = (int) $value;

        return $value >= $min ? $value : null;
    }

    private function dateFilter(mixed $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        return $value;
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
