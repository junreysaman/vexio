<?php

declare(strict_types=1);

namespace App\Services\TMDB;

use App\Database\TmdbMetadataSchema;
use App\Support\MediaImage;
use App\Support\MediaUrl;
use Framework\Database;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use RuntimeException;
use Throwable;

class TmdbImporterService
{
    private Client $http;
    private Client $assetHttp;
    private string $apiKey;
    private string $accessToken;
    private string $publicPath;

    public function __construct(private Database $db)
    {
        TmdbMetadataSchema::ensure($db);

        $this->apiKey = trim((string) ($_ENV['TMDB_API_KEY'] ?? ''));
        $this->accessToken = trim((string) ($_ENV['TMDB_ACCESS_TOKEN'] ?? ''));
        $this->publicPath = dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . 'public';

        $this->http = new Client([
            'base_uri' => rtrim((string) ($_ENV['TMDB_API_BASE_URL'] ?? 'https://api.themoviedb.org/3'), '/') . '/',
            'timeout' => (float) ($_ENV['TMDB_TIMEOUT'] ?? 12),
            'http_errors' => false,
        ]);

        $this->assetHttp = new Client([
            'timeout' => (float) ($_ENV['TMDB_TIMEOUT'] ?? 12),
            'http_errors' => false,
        ]);
    }

    public function searchMovies(string $query, int $page = 1, ?int $year = null, ?string $language = null): array
    {
        $params = [
            'query' => $query,
            'page' => max(1, $page),
            'include_adult' => false,
        ];

        if ($year !== null) {
            $params['year'] = $year;
            $params['primary_release_year'] = $year;
        }

        if ($language !== null) {
            $params['with_original_language'] = $language;
        }

        return $this->get('search/movie', $params);
    }

    public function searchTvShows(string $query, int $page = 1, ?int $year = null, ?string $language = null): array
    {
        $params = [
            'query' => $query,
            'page' => max(1, $page),
            'include_adult' => false,
        ];

        if ($year !== null) {
            $params['first_air_date_year'] = $year;
        }

        if ($language !== null) {
            $params['with_original_language'] = $language;
        }

        return $this->get('search/tv', $params);
    }

    public function movieGenres(): array
    {
        return $this->get('genre/movie/list')['genres'] ?? [];
    }

    public function tvGenres(): array
    {
        return $this->get('genre/tv/list')['genres'] ?? [];
    }

    public function discoverMovies(int $page = 1, ?int $year = null, string $sortBy = 'popularity.desc', ?int $genreId = null, ?string $language = null, ?string $country = null): array
    {
        $query = [
            'page' => max(1, $page),
            'include_adult' => false,
            'sort_by' => $this->normalizeSort($sortBy),
        ];

        if ($year !== null) {
            $query['primary_release_year'] = $year;
        }

        if ($genreId !== null && $genreId > 0) {
            $query['with_genres'] = $genreId;
        }

        if ($language !== null) {
            $query['with_original_language'] = $language;
        }

        if ($country !== null) {
            $query['with_origin_country'] = $country;
        }

        return $this->get('discover/movie', $query);
    }

    public function discoverTvShows(int $page = 1, ?int $year = null, string $sortBy = 'popularity.desc', ?int $genreId = null, ?string $language = null, ?string $country = null): array
    {
        $query = [
            'page' => max(1, $page),
            'include_adult' => false,
            'sort_by' => $this->normalizeSort($sortBy),
        ];

        if ($year !== null) {
            $query['first_air_date_year'] = $year;
            $query['first_air_date.gte'] = $year . '-01-01';
            $query['first_air_date.lte'] = $year . '-12-31';
        }

        if ($genreId !== null && $genreId > 0) {
            $query['with_genres'] = $genreId;
        }

        if ($language !== null) {
            $query['with_original_language'] = $language;
        }

        if ($country !== null) {
            $query['with_origin_country'] = $country;
        }

        return $this->get('discover/tv', $query);
    }

    public function trending(string $mediaType = 'all', string $timeWindow = 'week'): array
    {
        $mediaType = in_array($mediaType, ['all', 'movie', 'tv', 'person'], true) ? $mediaType : 'all';
        $timeWindow = in_array($timeWindow, ['day', 'week'], true) ? $timeWindow : 'week';

        return $this->get("trending/{$mediaType}/{$timeWindow}");
    }

    public function movieDetails(int $tmdbMovieId): array
    {
        // Only fetch what we actually store and display:
        // credits (cast/crew), genres, release_dates (certification), external_ids (imdb_id)
        return $this->get('movie/' . $tmdbMovieId, [
            'append_to_response' => 'credits,genres,release_dates,external_ids',
        ]);
    }

    public function tvShowDetails(int $tmdbTvId): array
    {
        // Only fetch what we actually store and display:
        // credits (cast/crew), genres, content_ratings (not used but cheap), created_by
        return $this->get('tv/' . $tmdbTvId, [
            'append_to_response' => 'credits,genres,created_by',
        ]);
    }

    /**
     * Fetches one season with its episode list from TMDB.
     */
    public function tvSeasonDetails(int $tmdbTvId, int $seasonNumber): array
    {
        return $this->get("tv/{$tmdbTvId}/season/{$seasonNumber}");
    }

    public function tvEpisodeDetails(int $tmdbTvId, int $seasonNumber, int $episodeNumber): array
    {
        return $this->get("tv/{$tmdbTvId}/season/{$seasonNumber}/episode/{$episodeNumber}");
    }

    public function importedRootIds(string $tab): array
    {
        return match ($tab) {
            'tv' => array_map('intval', array_column($this->db->select(
                "SELECT tmdb_id AS id FROM media_items WHERE tmdb_type = 'tv_show' AND tmdb_id IS NOT NULL
                 UNION
                 SELECT tmdb_parent_id AS id FROM media_episodes WHERE tmdb_type = 'tv_episode' AND tmdb_parent_id IS NOT NULL"
            ), 'id')),
            default => array_map('intval', array_column($this->db->select(
                "SELECT tmdb_id AS id FROM media_items WHERE tmdb_type = 'movie' AND tmdb_id IS NOT NULL"
            ), 'id')),
        };
    }

    public function importMovie(int $tmdbMovieId, int $views = 0, string $status = 'draft', bool $featured = false): ?array
    {
        $movie = $this->movieDetails($tmdbMovieId);
        $posterUrl = $this->imageUrl($movie['poster_path'] ?? null);
        $backdropUrl = $this->backdropUrl($movie['backdrop_path'] ?? null);
        $castProfiles = $this->castProfileMeta($movie['credits']['cast'] ?? []);
        $crewProfiles = $this->crewProfileMeta($movie['credits']['crew'] ?? []);

        $id = $this->upsertMediaItem('movie', $tmdbMovieId, [
            'title'            => (string) ($movie['title'] ?? $movie['original_title'] ?? 'Untitled Movie'),
            'slug'             => MediaUrl::slugify((string) ($movie['title'] ?? $movie['original_title'] ?? 'Untitled Movie')),
            'original_title'   => (string) ($movie['original_title'] ?? $movie['title'] ?? ''),
            'original_language'=> $this->nullableString($movie['original_language'] ?? null, 10),
            'type'             => 'movie',
            'synopsis'         => (string) ($movie['overview'] ?? ''),
            ...$this->imagePayload($posterUrl, $backdropUrl, 'movies', (string) $tmdbMovieId),
            'stream_link'      => null,
            'rated'            => $this->movieCertification($movie),
            'country'          => $this->csv($movie['origin_country'] ?? []),
            'dt_cast'          => $this->castMeta($movie['credits']['cast'] ?? []),
            'dt_dir'           => $this->directorMeta($movie['credits']['crew'] ?? []),
            'imdb_id'          => $this->nullableString($movie['external_ids']['imdb_id'] ?? null, 40),
            'release_year'     => $this->yearFromDate($movie['release_date'] ?? null),
            'release_date'     => $this->dateOrNull($movie['release_date'] ?? null),
            'runtime_minutes'  => $movie['runtime'] ?? null,
            'tmdb_status'      => $this->nullableString($movie['status'] ?? null, 60),
            'tagline'          => $this->nullableString($movie['tagline'] ?? null, 255),
            'budget'           => isset($movie['budget']) ? (int) $movie['budget'] : null,
            'revenue'          => isset($movie['revenue']) ? (int) $movie['revenue'] : null,
            'origin_country'   => $this->csv($movie['origin_country'] ?? []),
            'tmdb_rating'      => $this->rating($movie),
            'tmdb_popularity'  => $this->popularity($movie),
            'tmdb_vote_count'  => (int) ($movie['vote_count'] ?? 0),
            'views'            => max(0, $views),
            'status'           => $this->normalizeStatus($status),
            'is_featured'      => $featured ? 1 : 0,
        ]);

        // Store cast/crew profiles and sync genres taxonomy
        $this->syncItemMeta($id, $castProfiles, $crewProfiles);
        $this->syncGenres($id, 'item', $this->names($movie['genres'] ?? []));

        return $this->db->findById('media_items', $id);
    }

    public function importTvShow(int $tmdbTvId, int $views = 0, string $status = 'draft', bool $featured = false): ?array
    {
        $show = $this->tvShowDetails($tmdbTvId);
        $posterUrl = $this->imageUrl($show['poster_path'] ?? null);
        $backdropUrl = $this->backdropUrl($show['backdrop_path'] ?? null);
        $castProfiles = $this->castProfileMeta($show['credits']['cast'] ?? []);
        $crewSource = array_merge($show['created_by'] ?? [], $show['credits']['crew'] ?? []);
        $crewProfiles = $this->crewProfileMeta($crewSource);

        $id = $this->upsertMediaItem('tv_show', $tmdbTvId, [
            'title'              => (string) ($show['name'] ?? $show['original_name'] ?? 'Untitled Series'),
            'slug'               => MediaUrl::slugify((string) ($show['name'] ?? $show['original_name'] ?? 'Untitled Series')),
            'original_title'     => (string) ($show['original_name'] ?? $show['name'] ?? ''),
            'original_language'  => $this->nullableString($show['original_language'] ?? null, 10),
            'type'               => 'tv_show',
            'synopsis'           => (string) ($show['overview'] ?? ''),
            ...$this->imagePayload($posterUrl, $backdropUrl, 'tv', (string) $tmdbTvId),
            'stream_link'        => null,
            'dt_cast'            => $this->castMeta($show['credits']['cast'] ?? []),
            'dt_creator'         => $this->creatorMeta($show['created_by'] ?? []),
            'release_year'       => $this->yearFromDate($show['first_air_date'] ?? null),
            'release_date'       => $this->dateOrNull($show['first_air_date'] ?? null),
            'runtime_minutes'    => (int) (($show['episode_run_time'][0] ?? null) ?: 0) ?: null,
            'tmdb_status'        => $this->nullableString($show['status'] ?? null, 60),
            'tagline'            => $this->nullableString($show['tagline'] ?? null, 255),
            'number_of_seasons'  => isset($show['number_of_seasons']) ? (int) $show['number_of_seasons'] : null,
            'number_of_episodes' => isset($show['number_of_episodes']) ? (int) $show['number_of_episodes'] : null,
            'last_air_date'      => $this->dateOrNull($show['last_air_date'] ?? null),
            'in_production'      => array_key_exists('in_production', $show) ? (!empty($show['in_production']) ? 1 : 0) : null,
            'origin_country'     => $this->csv($show['origin_country'] ?? []),
            'tmdb_rating'        => $this->rating($show),
            'tmdb_popularity'    => $this->popularity($show),
            'tmdb_vote_count'    => (int) ($show['vote_count'] ?? 0),
            'views'              => max(0, $views),
            'status'             => $this->normalizeStatus($status),
            'is_featured'        => $featured ? 1 : 0,
        ]);

        // Store cast/crew profiles and sync genres taxonomy
        $this->syncItemMeta($id, $castProfiles, $crewProfiles);
        $this->syncGenres($id, 'item', $this->names($show['genres'] ?? []));

        return $this->db->findById('media_items', $id);
    }

    public function generateTvSeasons(int $tmdbTvId, string $status = 'draft'): array
    {
        $show = $this->tvShowDetails($tmdbTvId);
        $seasonSummaries = array_values(array_filter(
            $show['seasons'] ?? [],
            fn(array $season): bool => (int) ($season['season_number'] ?? 0) > 0
        ));

        $generated = ['seasons' => 0, 'skipped' => 0];

        foreach ($seasonSummaries as $seasonSummary) {
            $seasonNumber = (int) ($seasonSummary['season_number'] ?? 0);

            if ($seasonNumber < 1) {
                $generated['skipped']++;
                continue;
            }

            $season = $this->tvSeasonDetails($tmdbTvId, $seasonNumber);
            $this->upsertTvSeasonFromData($tmdbTvId, $show, $season, $seasonNumber, 0, $status, null);
            $generated['seasons']++;
        }

        $this->db->update('media_items', ['clgnrt' => 1], [
            'tmdb_type' => 'tv_show',
            'tmdb_id' => $tmdbTvId,
        ]);

        return $generated;
    }

    public function generateTvEpisodesForSeason(int $tmdbTvId, int $seasonNumber, string $status = 'draft'): array
    {
        $show = $this->tvShowDetails($tmdbTvId);

        if (empty($show['id'])) {
            throw new RuntimeException('Unable to fetch TV show details from TMDB.');
        }

        $season = $this->tvSeasonDetails($tmdbTvId, $seasonNumber);

        if (empty($season)) {
            throw new RuntimeException('Unable to fetch season details from TMDB.');
        }

        $episodes = $season['episodes'] ?? [];

        if (empty($episodes)) {
            throw new RuntimeException('This season has no episodes available on TMDB.');
        }

        $parent = $this->ensureSeriesItem($tmdbTvId, $status);
        if (empty($parent['id'])) {
            throw new RuntimeException('Unable to create or find the TV series in the database.');
        }

        $seasonItem = $this->ensureSeasonItem($tmdbTvId, $show, $seasonNumber, $status);
        if (empty($seasonItem['id'])) {
            throw new RuntimeException('Unable to create or find the season in the database.');
        }

        $generated = ['episodes' => 0, 'skipped' => 0, 'errors' => []];

        foreach ($episodes as $episode) {
            $episodeNumber = (int) ($episode['episode_number'] ?? 0);

            if ($episodeNumber < 1) {
                $generated['skipped']++;
                continue;
            }

            try {
                $this->upsertTvEpisodeFromData(
                    $tmdbTvId,
                    $show,
                    $episode,
                    $seasonNumber,
                    $episodeNumber,
                    0,
                    $status,
                    $parent,
                    $seasonItem
                );
                $generated['episodes']++;
            } catch (Throwable $e) {
                $generated['errors'][] = 'Episode ' . $episodeNumber . ': ' . $e->getMessage();
            }
        }

        $this->db->update('media_seasons', ['clgnrt' => 1], [
            'media_item_id' => (int) $parent['id'],
            'season_number' => $seasonNumber,
        ]);

        return $generated;
    }

    /**
     * Finds all episodes with status='scheduled' or status='draft' whose air_date has arrived,
     * reimports them from TMDB to get the latest metadata (backdrop, synopsis, etc.),
     * then sets their status to 'published'.
     *
     * Episodes with no air_date that are stuck as 'scheduled' are also published
     * (status-only update, since there's no TMDB episode ID to reimport from).
     *
     * Returns a summary array with counts and any per-episode errors.
     */
    public function publishScheduled(): array
    {
        $today = new \DateTimeImmutable('today', new \DateTimeZone('UTC'));

        // Fetch releasable episodes that have a TMDB parent ID (needed for reimport).
        // Scheduled episodes without an air date are also released so they do not stay hidden forever.
        $due = $this->db->select(
            "SELECT id, tmdb_parent_id, season_number, episode_number, air_date, status, views
             FROM media_episodes
             WHERE tmdb_parent_id IS NOT NULL
             AND tmdb_parent_id > 0
             AND (
                 (
                     status = 'scheduled'
                     AND (air_date IS NULL OR air_date <= :today)
                 )
                 OR (
                     status = 'draft'
                     AND air_date IS NOT NULL
                     AND air_date <= :draft_today
                 )
             )
             ORDER BY tmdb_parent_id ASC, season_number ASC, episode_number ASC",
            [
                'today' => $today->format('Y-m-d'),
                'draft_today' => $today->format('Y-m-d'),
            ]
        );

        $result = ['published' => 0, 'scheduled' => 0, 'draft' => 0, 'errors' => []];

        foreach ($due as $row) {
            $tmdbTvId      = (int) $row['tmdb_parent_id'];
            $seasonNumber  = (int) $row['season_number'];
            $episodeNumber = (int) $row['episode_number'];
            $previousStatus = (string) ($row['status'] ?? '');

            try {
                // Full reimport fetches fresh metadata from TMDB and upserts the row.
                // episodeStatus() will resolve to 'published' since air_date <= today.
                $this->importTvEpisode(
                    $tmdbTvId,
                    $seasonNumber,
                    $episodeNumber,
                    (int) ($row['views'] ?? 0),
                    'published'
                );
                $result['published']++;
                if (isset($result[$previousStatus])) {
                    $result[$previousStatus]++;
                }
            } catch (\Throwable $e) {
                // Fall back to a status-only update so the episode isn't stuck forever.
                try {
                    $this->db->updateById('media_episodes', (int) $row['id'], ['status' => 'published']);
                    $result['published']++;
                    if (isset($result[$previousStatus])) {
                        $result[$previousStatus]++;
                    }
                } catch (\Throwable) {
                    // ignore secondary failure
                }

                $result['errors'][] = "S{$seasonNumber}E{$episodeNumber} (TMDB show {$tmdbTvId}): " . $e->getMessage();
            }
        }

        return $result;
    }

    public function importTvEpisode(int $tmdbTvId, int $seasonNumber, int $episodeNumber, int $views = 0, string $status = 'draft'): ?array
    {
        $show = $this->tvShowDetails($tmdbTvId);
        $episode = $this->tvEpisodeDetails($tmdbTvId, $seasonNumber, $episodeNumber);

        return $this->upsertTvEpisodeFromData($tmdbTvId, $show, $episode, $seasonNumber, $episodeNumber, $views, $status);
    }

    public function importTvSeason(int $tmdbTvId, int $seasonNumber, int $views = 0, string $status = 'draft'): ?array
    {
        $show = $this->tvShowDetails($tmdbTvId);
        $season = $this->tvSeasonDetails($tmdbTvId, $seasonNumber);

        return $this->upsertTvSeasonFromData($tmdbTvId, $show, $season, $seasonNumber, $views, $status);
    }

    /**
     * Backfill locally cached WebP assets for already imported records.
     *
     * @return array{
     *   scope:string,
     *   limit:int,
     *   scanned:int,
     *   hydrated:int,
     *   failed:int,
     *   posters_hydrated:int,
     *   backdrops_hydrated:int,
     *   variants_regenerated:int
     * }
     */
    public function hydrateMissingImages(string $scope = 'all', int $limit = 50): array
    {
        $scope = in_array($scope, ['all', 'items', 'seasons', 'episodes'], true) ? $scope : 'all';
        $limit = max(1, min(200, $limit));

        $result = [
            'scope' => $scope,
            'limit' => $limit,
            'scanned' => 0,
            'hydrated' => 0,
            'failed' => 0,
            'posters_hydrated' => 0,
            'backdrops_hydrated' => 0,
            'variants_regenerated' => 0,
        ];

        if ($scope === 'all' || $scope === 'items') {
            $rows = $this->db->select(
                "SELECT id, type, tmdb_id, poster_url, poster_image, backdrop_image
                 FROM media_items
                 WHERE (
                     poster_url IS NULL OR poster_url = ''
                     OR backdrop_image IS NULL OR backdrop_image = ''
                 )
                 ORDER BY id DESC
                 LIMIT {$limit}"
            );

            foreach ($rows as $row) {
                $result['scanned']++;
                $type = (string) ($row['type'] ?? 'movie');
                $tmdbId = (int) ($row['tmdb_id'] ?? 0);
                $id = (int) ($row['id'] ?? 0);

                $updates = [];
                $posterUrl = trim((string) ($row['poster_url'] ?? ''));

                if ($posterUrl === '' && $tmdbId > 0) {
                    $posterUrl = $this->resolvePosterUrlForItem($type, $tmdbId) ?? '';
                    if ($posterUrl !== '') {
                        $updates['poster_url'] = $posterUrl;
                        $result['posters_hydrated']++;
                    }
                }

                if (trim((string) ($row['backdrop_image'] ?? '')) === '') {
                    $backdropUrl = $this->resolveBackdropUrlForItem($type, $tmdbId, $posterUrl);
                    if ($backdropUrl !== null) {
                        $updates['backdrop_image'] = $backdropUrl;
                        $result['backdrops_hydrated']++;
                    }
                }

                if ($updates !== []) {
                    $this->db->updateById('media_items', $id, $updates);
                    $result['hydrated']++;
                } else {
                    $result['failed']++;
                }
            }
        }

        if ($scope === 'all' || $scope === 'seasons') {
            $this->hydrateSeasonImages($limit, $result);
        }

        if ($scope === 'all' || $scope === 'episodes') {
            $this->hydrateEpisodeImages($limit, $result);
        }

        return $result;
    }

    /**
     * Regenerate width variants for rows that already have a local primary asset.
     *
     * @return array{scope:string, limit:int, scanned:int, variants_regenerated:int, failed:int}
     */
    public function regenerateImageVariants(string $scope = 'all', int $limit = 50): array
    {
        $scope = in_array($scope, ['all', 'items', 'seasons', 'episodes'], true) ? $scope : 'all';
        $limit = max(1, min(200, $limit));

        if (!$this->downloadsImages()) {
            return [
                'scope' => $scope,
                'limit' => $limit,
                'scanned' => 0,
                'variants_regenerated' => 0,
                'failed' => 0,
                'skipped' => true,
                'message' => 'Local WebP variants are disabled (TMDB_DOWNLOAD_IMAGES=false). Images use TMDB URLs only.',
            ];
        }

        $result = [
            'scope' => $scope,
            'limit' => $limit,
            'scanned' => 0,
            'variants_regenerated' => 0,
            'failed' => 0,
        ];

        if ($scope === 'all' || $scope === 'items') {
            $rows = $this->db->select(
                "SELECT id, type, tmdb_id, poster_url, poster_image, backdrop_image
                 FROM media_items
                 WHERE (
                    (poster_image IS NOT NULL AND poster_image <> '' AND poster_image LIKE '/uploads/%')
                    OR (backdrop_image IS NOT NULL AND backdrop_image <> '' AND backdrop_image LIKE '/uploads/%')
                 )
                 ORDER BY id DESC
                 LIMIT {$limit}"
            );

            foreach ($rows as $row) {
                $result['scanned']++;
                $type = (string) ($row['type'] ?? 'movie');
                $folder = $type === 'tv_show' ? 'tv' : 'movies';
                $tmdbId = (int) ($row['tmdb_id'] ?? 0);
                $id = (int) ($row['id'] ?? 0);
                $key = (string) ($tmdbId ?: $id);
                $changed = false;

                $posterPath = trim((string) ($row['poster_image'] ?? ''));
                if ($posterPath !== '' && MediaImage::needsVariantRegeneration($posterPath, MediaImage::posterWidths())) {
                    $regenerated = $this->regenerateVariantsFromLocal(
                        $posterPath,
                        trim((string) ($row['poster_url'] ?? '')),
                        $folder,
                        'poster-' . $key,
                        MediaImage::ROLE_POSTER
                    );
                    if ($regenerated !== null) {
                        $this->db->updateById('media_items', $id, ['poster_image' => $regenerated]);
                        $changed = true;
                    }
                }

                $backdropPath = trim((string) ($row['backdrop_image'] ?? ''));
                if ($backdropPath !== '' && MediaImage::needsVariantRegeneration($backdropPath, MediaImage::backdropWidths())) {
                    $backdropUrl = $this->resolveBackdropUrlForItem($type, $tmdbId, trim((string) ($row['poster_url'] ?? '')));
                    $regenerated = $this->regenerateVariantsFromLocal(
                        $backdropPath,
                        $backdropUrl ?? '',
                        $folder,
                        'backdrop-' . $key,
                        MediaImage::ROLE_BACKDROP
                    );
                    if ($regenerated !== null) {
                        $this->db->updateById('media_items', $id, ['backdrop_image' => $regenerated]);
                        $changed = true;
                    }
                }

                if ($changed) {
                    $result['variants_regenerated']++;
                } else {
                    $result['failed']++;
                }
            }
        }

        if ($scope === 'all' || $scope === 'seasons') {
            $this->regenerateSeasonVariants($limit, $result);
        }

        if ($scope === 'all' || $scope === 'episodes') {
            $this->regenerateEpisodeVariants($limit, $result);
        }

        return $result;
    }

    /**
     * Generate missing TV seasons and episodes for imported TV shows.
     *
     * @return array{limit:int,shows_scanned:int,seasons_created:int,episodes_created:int,errors:list<string>}
     */
    public function generateMissingTvSeasonsAndEpisodes(int $limit = 10, string $status = 'draft'): array
    {
        $limit = max(1, min(100, $limit));
        $status = $this->normalizeStatus($status);

        $shows = $this->db->select(
            "SELECT id, tmdb_id
             FROM media_items
             WHERE tmdb_type = 'tv_show'
             AND tmdb_id IS NOT NULL
             AND tmdb_id > 0
             ORDER BY id DESC
             LIMIT {$limit}"
        );

        $result = [
            'limit' => $limit,
            'shows_scanned' => 0,
            'seasons_created' => 0,
            'episodes_created' => 0,
            'errors' => [],
        ];

        foreach ($shows as $showRow) {
            $result['shows_scanned']++;
            $tmdbTvId = (int) ($showRow['tmdb_id'] ?? 0);
            $mediaItemId = (int) ($showRow['id'] ?? 0);

            if ($tmdbTvId < 1 || $mediaItemId < 1) {
                continue;
            }

            try {
                $show = $this->tvShowDetails($tmdbTvId);
                $parent = $this->ensureSeriesItem($tmdbTvId, $status);

                $existingSeasons = $this->db->select(
                    'SELECT id, season_number FROM media_seasons WHERE media_item_id = :media_item_id',
                    ['media_item_id' => $mediaItemId]
                );
                $seasonMap = [];
                foreach ($existingSeasons as $seasonRow) {
                    $seasonMap[(int) ($seasonRow['season_number'] ?? 0)] = (int) ($seasonRow['id'] ?? 0);
                }

                $seasonSummaries = array_values(array_filter(
                    $show['seasons'] ?? [],
                    static fn(array $season): bool => (int) ($season['season_number'] ?? 0) > 0
                ));

                foreach ($seasonSummaries as $seasonSummary) {
                    $seasonNumber = (int) ($seasonSummary['season_number'] ?? 0);
                    if ($seasonNumber < 1) {
                        continue;
                    }

                    $seasonData = $this->tvSeasonDetails($tmdbTvId, $seasonNumber);
                    $isNewSeason = !isset($seasonMap[$seasonNumber]);
                    $seasonItem = $this->upsertTvSeasonFromData(
                        $tmdbTvId,
                        $show,
                        $seasonData,
                        $seasonNumber,
                        0,
                        $status,
                        $parent
                    );

                    if ($isNewSeason) {
                        $result['seasons_created']++;
                    }

                    $seasonId = (int) ($seasonItem['id'] ?? 0);
                    if ($seasonId < 1) {
                        continue;
                    }

                    $existingEpisodes = $this->db->select(
                        'SELECT episode_number FROM media_episodes WHERE media_item_id = :media_item_id AND season_number = :season_number',
                        ['media_item_id' => $mediaItemId, 'season_number' => $seasonNumber]
                    );
                    $episodeMap = [];
                    foreach ($existingEpisodes as $episodeRow) {
                        $episodeMap[(int) ($episodeRow['episode_number'] ?? 0)] = true;
                    }

                    foreach (($seasonData['episodes'] ?? []) as $episode) {
                        $episodeNumber = (int) ($episode['episode_number'] ?? 0);
                        if ($episodeNumber < 1) {
                            continue;
                        }

                        $isNewEpisode = !isset($episodeMap[$episodeNumber]);
                        $this->upsertTvEpisodeFromData(
                            $tmdbTvId,
                            $show,
                            $episode,
                            $seasonNumber,
                            $episodeNumber,
                            0,
                            $status,
                            $parent,
                            ['id' => $seasonId]
                        );
                        if ($isNewEpisode) {
                            $result['episodes_created']++;
                        }
                    }

                    $this->db->updateById('media_seasons', $seasonId, ['clgnrt' => 1]);
                }

                $this->db->updateById('media_items', $mediaItemId, ['clgnrt' => 1]);
            } catch (Throwable $e) {
                $result['errors'][] = 'TMDB show ' . $tmdbTvId . ': ' . $e->getMessage();
            }
        }

        return $result;
    }

    private function upsertTvEpisodeFromData(
        int $tmdbTvId,
        array $show,
        array $episode,
        int $seasonNumber,
        int $episodeNumber,
        int $views,
        string $status,
        ?array $parentOverride = null,
        ?array $seasonOverride = null
    ): ?array {
        $parent = $parentOverride ?: $this->ensureSeriesItem($tmdbTvId, $status);
        $season = $seasonOverride ?: $this->ensureSeasonItem($tmdbTvId, $show, $seasonNumber, $status);
        $episodeTitle = trim((string) ($episode['name'] ?? 'Episode ' . $episodeNumber));
        $showTitle = trim((string) ($show['name'] ?? $show['original_name'] ?? 'Untitled Series'));
        $posterUrl = $this->imageUrl($episode['still_path'] ?? null)
            ?? $this->imageUrl($show['poster_path'] ?? null);
        $backdropUrl = $this->backdropUrl($episode['still_path'] ?? null) ?? $this->backdropUrl($show['backdrop_path'] ?? null);

        $airDate = $this->dateOrNull($episode['air_date'] ?? null);

        $payload = [
            'title'          => $showTitle . ' - ' . $episodeTitle,
            'serie'          => $showTitle,
            'episode_name'   => $episodeTitle,
            'synopsis'       => (string) ($episode['overview'] ?? $show['overview'] ?? ''),
            ...$this->imagePayload($posterUrl, $backdropUrl, 'tv-episodes', $tmdbTvId . '-s' . $seasonNumber . 'e' . $episodeNumber),
            'stream_link'    => null,
            'release_year'   => $this->yearFromDate($episode['air_date'] ?? $show['first_air_date'] ?? null),
            'air_date'       => $airDate,
            'season_number'  => $seasonNumber,
            'episode_number' => $episodeNumber,
            'views'          => max(0, $views),
            'status'         => $this->episodeStatus($status, $airDate),
        ];

        $externalId = (int) ($episode['id'] ?? 0);
        $tmdbId = $externalId > 0 ? $externalId : (int) ($tmdbTvId . $seasonNumber . $episodeNumber);
        try {
            $id = $this->upsertMediaEpisode((int) $parent['id'], (int) $season['id'], 'tv_episode', $tmdbId, $tmdbTvId, $payload);
        } catch (Throwable $e) {
            // Handle schema drift where status ENUM differs.
            $message = strtolower($e->getMessage());
            if (!str_contains($message, 'status') || (!str_contains($message, '1265') && !str_contains($message, 'truncat'))) {
                throw $e;
            }

            // Retry with safest status.
            $payload['status'] = 'draft';
            try {
                $id = $this->upsertMediaEpisode((int) $parent['id'], (int) $season['id'], 'tv_episode', $tmdbId, $tmdbTvId, $payload);
            } catch (Throwable) {
                // Final retry: omit the status field entirely to use DB default.
                unset($payload['status']);
                $id = $this->upsertMediaEpisode((int) $parent['id'], (int) $season['id'], 'tv_episode', $tmdbId, $tmdbTvId, $payload);
            }
        }

        return $this->db->findById('media_episodes', $id);
    }

    private function upsertTvSeasonFromData(
        int $tmdbTvId,
        array $show,
        array $season,
        int $seasonNumber,
        int $views,
        string $status,
        ?array $parentOverride = null
    ): ?array {
        $parent = $parentOverride ?: $this->ensureSeriesItem($tmdbTvId, $status);
        $showTitle = trim((string) ($show['name'] ?? $show['original_name'] ?? 'Untitled Series'));
        $seasonTitle = trim((string) ($season['name'] ?? 'Season ' . $seasonNumber));
        $posterUrl = $this->imageUrl($season['poster_path'] ?? null) ?? $this->imageUrl($show['poster_path'] ?? null);
        $backdropUrl = $this->backdropUrl($show['backdrop_path'] ?? null);

        $payload = [
            'title'          => $showTitle . ' - ' . $seasonTitle,
            'serie'          => $showTitle,
            'synopsis'       => (string) ($season['overview'] ?? $show['overview'] ?? ''),
            ...$this->imagePayload($posterUrl, $backdropUrl, 'tv-seasons', $tmdbTvId . '-s' . $seasonNumber),
            'release_year'   => $this->yearFromDate($season['air_date'] ?? $show['first_air_date'] ?? null),
            'air_date'       => $this->dateOrNull($season['air_date'] ?? null),
            'season_number'  => $seasonNumber,
            // Seasons should never be 'scheduled' (that's episode-only in some schemas).
            'status'         => $this->normalizeSeasonStatus($status),
        ];

        $externalId = (int) ($season['id'] ?? 0);
        $tmdbId = $externalId > 0 ? $externalId : (int) ($tmdbTvId . $seasonNumber);
        try {
            $id = $this->upsertMediaSeason((int) $parent['id'], 'tv_season', $tmdbId, $tmdbTvId, $payload);
        } catch (Throwable $e) {
            // Handle schema drift where status ENUM differs.
            $message = strtolower($e->getMessage());
            if (!str_contains($message, 'status') || (!str_contains($message, '1265') && !str_contains($message, 'truncat'))) {
                throw $e;
            }

            // Retry with safest status.
            $payload['status'] = 'draft';
            try {
                $id = $this->upsertMediaSeason((int) $parent['id'], 'tv_season', $tmdbId, $tmdbTvId, $payload);
            } catch (Throwable) {
                // Final retry: omit the status field entirely to use DB default.
                unset($payload['status']);
                $id = $this->upsertMediaSeason((int) $parent['id'], 'tv_season', $tmdbId, $tmdbTvId, $payload);
            }
        }

        return $this->db->findById('media_seasons', $id);
    }

    private function normalizeSeasonStatus(string $status): string
    {
        $status = $this->normalizeStatus($status);

        return in_array($status, ['draft', 'published', 'archived'], true) ? $status : 'draft';
    }

    private function get(string $path, array $query = []): array
    {
        $this->ensureCredentials();

        if ($this->accessToken === '') {
            $query['api_key'] = $this->apiKey;
        }

        try {
            $response = $this->http->get(ltrim($path, '/'), [
                'headers' => $this->headers(),
                'query' => ['language' => $_ENV['TMDB_LANGUAGE'] ?? 'en-US', ...$query],
            ]);
        } catch (GuzzleException $exception) {
            throw new RuntimeException('TMDB request failed: ' . $exception->getMessage(), 0, $exception);
        }

        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        if (!is_array($data)) {
            throw new RuntimeException('TMDB returned an invalid JSON response.');
        }

        $statusCode = $response->getStatusCode();

        if ($statusCode < 200 || $statusCode >= 300) {
            throw new RuntimeException((string) ($data['status_message'] ?? 'TMDB request failed.'), $statusCode);
        }

        return $data;
    }

    private function headers(): array
    {
        $headers = ['Accept' => 'application/json'];

        if ($this->accessToken !== '') {
            $headers['Authorization'] = 'Bearer ' . $this->accessToken;
        }

        return $headers;
    }

    private function ensureCredentials(): void
    {
        if ($this->accessToken === '' && $this->apiKey === '') {
            throw new RuntimeException('TMDB credentials are missing. Set TMDB_ACCESS_TOKEN or TMDB_API_KEY in .env.');
        }
    }

    private function ensureSeriesItem(int $tmdbTvId, string $status): array
    {
        $existing = $this->db->selectOne(
            "SELECT * FROM media_items WHERE tmdb_type = 'tv_show' AND tmdb_id = :tmdb_id LIMIT 1",
            ['tmdb_id' => $tmdbTvId]
        );

        return $existing ?: ($this->importTvShow($tmdbTvId, 0, $status) ?? []);
    }

    private function ensureSeasonItem(int $tmdbTvId, array $show, int $seasonNumber, string $status): array
    {
        $parent = $this->ensureSeriesItem($tmdbTvId, $status);
        $existing = $this->db->selectOne(
            'SELECT * FROM media_seasons WHERE media_item_id = :media_item_id AND season_number = :season_number LIMIT 1',
            ['media_item_id' => (int) $parent['id'], 'season_number' => $seasonNumber]
        );

        if ($existing) {
            return $existing;
        }

        $season = $this->tvSeasonDetails($tmdbTvId, $seasonNumber);

        return $this->upsertTvSeasonFromData($tmdbTvId, $show, $season, $seasonNumber, 0, $status) ?? [];
    }

    private function upsertMediaItem(string $tmdbType, int $tmdbId, array $payload): int
    {
        $existing = $this->db->selectOne(
            'SELECT id FROM media_items WHERE tmdb_type = :tmdb_type AND tmdb_id = :tmdb_id LIMIT 1',
            ['tmdb_type' => $tmdbType, 'tmdb_id' => $tmdbId]
        );

        $payload = [...$payload, 'tmdb_type' => $tmdbType, 'tmdb_id' => $tmdbId];

        if ($existing) {
            $this->db->updateById('media_items', (int) $existing['id'], $payload);
            return (int) $existing['id'];
        }

        return (int) $this->db->insert('media_items', $payload);
    }

    private function upsertMediaSeason(int $mediaItemId, string $tmdbType, int $tmdbId, int $tmdbParentId, array $payload): int
    {
        $existing = $this->db->selectOne(
            'SELECT id FROM media_seasons WHERE tmdb_type = :tmdb_type AND tmdb_id = :tmdb_id LIMIT 1',
            ['tmdb_type' => $tmdbType, 'tmdb_id' => $tmdbId]
        );

        $payload = [
            ...$payload,
            'media_item_id' => $mediaItemId,
            'tmdb_type' => $tmdbType,
            'tmdb_id' => $tmdbId,
            'tmdb_parent_id' => $tmdbParentId,
        ];

        if ($existing) {
            $this->db->updateById('media_seasons', (int) $existing['id'], $payload);
            return (int) $existing['id'];
        }

        return (int) $this->db->insert('media_seasons', $payload);
    }

    private function upsertMediaEpisode(int $mediaItemId, int $mediaSeasonId, string $tmdbType, int $tmdbId, int $tmdbParentId, array $payload): int
    {
        $existing = $this->db->selectOne(
            'SELECT id FROM media_episodes
             WHERE (tmdb_type = :tmdb_type AND tmdb_id = :tmdb_id)
             OR (
                media_item_id = :media_item_id
                AND season_number = :season_number
                AND episode_number = :episode_number
             )
             LIMIT 1',
            [
                'tmdb_type' => $tmdbType,
                'tmdb_id' => $tmdbId,
                'media_item_id' => $mediaItemId,
                'season_number' => (int) ($payload['season_number'] ?? 0),
                'episode_number' => (int) ($payload['episode_number'] ?? 0),
            ]
        );

        $payload = [
            ...$payload,
            'media_item_id' => $mediaItemId,
            'media_season_id' => $mediaSeasonId,
            'tmdb_type' => $tmdbType,
            'tmdb_id' => $tmdbId,
            'tmdb_parent_id' => $tmdbParentId,
        ];

        if ($existing) {
            $this->db->updateById('media_episodes', (int) $existing['id'], $payload);
            return (int) $existing['id'];
        }

        return (int) $this->db->insert('media_episodes', $payload);
    }

    /**
     * Writes cast_profiles and crew_profiles into content_meta for a media item.
     * These are the only two meta keys actually read by the frontend.
     */
    private function syncItemMeta(int $itemId, ?string $castProfiles, ?string $crewProfiles): void
    {
        foreach (['cast_profiles' => $castProfiles, 'crew_profiles' => $crewProfiles] as $key => $value) {
            if ($value === null || $value === '') {
                $this->db->delete('content_meta', [
                    'owner_type' => 'item',
                    'owner_id' => $itemId,
                    'meta_key' => $key,
                ]);
                continue;
            }

            $this->db->updateOrInsert('content_meta', [
                'owner_type' => 'item',
                'owner_id' => $itemId,
                'meta_key' => $key,
            ], ['meta_value' => $value]);
        }
    }

    /**
     * Syncs the genres taxonomy for a media item.
     * Only the genres taxonomy is queried anywhere in the project.
     */
    private function syncGenres(int $ownerId, string $ownerType, array $names): void
    {
        // Remove existing genre links for this item
        $existing = $this->db->select(
            'SELECT content_term_links.term_id
             FROM content_term_links
             INNER JOIN content_terms ON content_terms.id = content_term_links.term_id
             WHERE content_term_links.owner_type = :owner_type
             AND content_term_links.owner_id = :owner_id
             AND content_terms.taxonomy = :taxonomy',
            ['owner_type' => $ownerType, 'owner_id' => $ownerId, 'taxonomy' => 'genres']
        );

        foreach ($existing as $row) {
            $this->db->delete('content_term_links', [
                'owner_type' => $ownerType,
                'owner_id' => $ownerId,
                'term_id' => (int) $row['term_id'],
            ]);
        }

        foreach (array_values(array_unique(array_filter(array_map('trim', $names)))) as $name) {
            $slug = $this->slug($name);
            $termId = $this->upsertAndGetId('content_terms', [
                'taxonomy' => 'genres',
                'slug' => $slug,
            ], ['name' => $name]);

            if (!$this->db->existsWhere('content_term_links', ['owner_type' => $ownerType, 'owner_id' => $ownerId, 'term_id' => $termId])) {
                $this->db->insert('content_term_links', ['owner_type' => $ownerType, 'owner_id' => $ownerId, 'term_id' => $termId]);
            }
        }
    }

    private function upsertAndGetId(string $table, array $where, array $data): int
    {
        $existing = $this->db->findOneWhere($table, $where, ['id']);

        if ($existing) {
            if ($data !== []) {
                $this->db->update($table, $data, $where);
            }
            return (int) $existing['id'];
        }

        return (int) $this->db->insert($table, [...$where, ...$data]);
    }

    public function posterUrl(?string $path): ?string
    {
        return MediaImage::buildTmdbAssetUrl($path, MediaImage::ROLE_POSTER);
    }

    public function backdropUrl(?string $path): ?string
    {
        return MediaImage::buildTmdbAssetUrl($path, MediaImage::ROLE_BACKDROP);
    }

    private function imageUrl(?string $path): ?string
    {
        return $this->posterUrl($path);
    }

    private function yearFromDate(?string $date): ?int
    {
        $date = trim((string) $date);

        return $date !== '' ? (int) substr($date, 0, 4) : null;
    }

    private function dateOrNull(?string $date): ?string
    {
        $date = trim((string) $date);

        return $date !== '' ? substr($date, 0, 10) : null;
    }

    private function nullableString(mixed $value, int $maxLength): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? mb_substr($value, 0, $maxLength) : null;
    }

    private function csv(array $values): ?string
    {
        $values = array_values(array_filter(array_map(
            fn(mixed $value): string => trim((string) $value),
            $values
        )));

        return $values !== [] ? implode(',', $values) : null;
    }

    private function normalizeStatus(string $status): string
    {
        return in_array($status, ['draft', 'published', 'archived', 'scheduled'], true) ? $status : 'draft';
    }

    /**
     * Determines the correct status for an episode based on its air date.
     * If the requested status is 'published' but the air date is in the future,
     * the episode is stored as 'scheduled' instead so it stays hidden until it airs.
     * If there is no air date at all, the requested status is used as-is.
     */
    private function episodeStatus(string $requestedStatus, ?string $airDate): string
    {
        $requestedStatus = $this->normalizeStatus($requestedStatus);

        if ($requestedStatus !== 'published' || $airDate === null || $airDate === '') {
            return $requestedStatus;
        }

        $today = new \DateTimeImmutable('today', new \DateTimeZone('UTC'));
        $air   = \DateTimeImmutable::createFromFormat('Y-m-d', substr($airDate, 0, 10), new \DateTimeZone('UTC'));

        if ($air === false) {
            return $requestedStatus;
        }

        return $air > $today ? 'scheduled' : 'published';
    }

    private function rating(array $data): ?float
    {
        return isset($data['vote_average']) ? round((float) $data['vote_average'], 1) : null;
    }

    private function popularity(array $data): ?float
    {
        return isset($data['popularity']) ? round((float) $data['popularity'], 3) : null;
    }

    private function downloadImageVariants(?string $url, string $folder, string $name, string $role): ?string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }

        $image = $this->fetchImageResource($url);
        if ($image === null) {
            return null;
        }

        try {
            return $this->saveImageVariants($image, $folder, $name, $role);
        } finally {
            imagedestroy($image);
        }
    }

    private function regenerateVariantsFromLocal(
        string $localPath,
        string $remoteUrl,
        string $folder,
        string $name,
        string $role
    ): ?string {
        $absolute = MediaImage::publicRoot() . str_replace('/', DIRECTORY_SEPARATOR, $localPath);
        $image = is_file($absolute) ? @imagecreatefromstring((string) file_get_contents($absolute)) : null;

        if (!$image && trim($remoteUrl) !== '') {
            return $this->downloadImageVariants($remoteUrl, $folder, $name, $role);
        }

        if (!$image) {
            return null;
        }

        try {
            return $this->saveImageVariants($image, $folder, $name, $role);
        } finally {
            imagedestroy($image);
        }
    }

    /**
     * @param resource|\GdImage $image
     */
    private function saveImageVariants($image, string $folder, string $name, string $role): ?string
    {
        if (!function_exists('imagewebp')) {
            return null;
        }

        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        $widths = $role === MediaImage::ROLE_BACKDROP ? MediaImage::backdropWidths() : MediaImage::posterWidths();
        $primaryWidth = $role === MediaImage::ROLE_BACKDROP
            ? MediaImage::backdropPrimaryWidth()
            : MediaImage::posterPrimaryWidth();

        $relativeDir = '/uploads/tmdb/' . trim($folder, '/');
        $directory = $this->publicPath . str_replace('/', DIRECTORY_SEPARATOR, $relativeDir);

        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $safeName = preg_replace('/[^a-zA-Z0-9_-]+/', '-', strtolower($name)) ?: bin2hex(random_bytes(8));
        $primaryPath = null;

        foreach ($widths as $targetWidth) {
            $resized = $this->resizeToWidth($image, $targetWidth);
            $relativePath = $relativeDir . '/' . $safeName . '-w' . $targetWidth . '.webp';
            $absolutePath = $this->publicPath . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
            $quality = MediaImage::qualityForWidth($targetWidth, $role);

            if (!imagewebp($resized, $absolutePath, $quality)) {
                if ($resized !== $image) {
                    imagedestroy($resized);
                }
                continue;
            }

            if ($resized !== $image) {
                imagedestroy($resized);
            }

            if ($targetWidth === $primaryWidth) {
                $primaryPath = $relativePath;
            }
        }

        if ($primaryPath === null && $widths !== []) {
            $fallbackWidth = $widths[array_key_last($widths)];
            $primaryPath = $relativeDir . '/' . $safeName . '-w' . $fallbackWidth . '.webp';
        }

        return $primaryPath;
    }

    /**
     * @return \GdImage|null
     */
    private function fetchImageResource(string $url): ?\GdImage
    {
        try {
            $response = $this->assetHttp->get($url);
        } catch (GuzzleException) {
            return null;
        }

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            return null;
        }

        $image = @imagecreatefromstring((string) $response->getBody());

        return $image instanceof \GdImage ? $image : null;
    }

    /**
     * @param \GdImage $source
     * @return \GdImage
     */
    private function resizeToWidth(\GdImage $source, int $targetWidth): \GdImage
    {
        $srcW = imagesx($source);
        $srcH = imagesy($source);

        if ($srcW <= 0 || $srcH <= 0) {
            return $source;
        }

        if ($srcW <= $targetWidth) {
            return $source;
        }

        $targetHeight = max(1, (int) round($srcH * ($targetWidth / $srcW)));
        $resized = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagecopyresampled($resized, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $srcW, $srcH);

        return $resized;
    }

    private function resolvePosterUrlForItem(string $type, int $tmdbId): ?string
    {
        if ($tmdbId < 1) {
            return null;
        }

        try {
            if ($type === 'tv_show') {
                $details = $this->tvShowDetails($tmdbId);

                return $this->imageUrl($details['poster_path'] ?? null);
            }

            $details = $this->movieDetails($tmdbId);

            return $this->imageUrl($details['poster_path'] ?? null);
        } catch (Throwable) {
            return null;
        }
    }

    private function resolveBackdropUrlForItem(string $type, int $tmdbId, string $posterUrl): ?string
    {
        if ($tmdbId < 1) {
            return $posterUrl !== '' ? $posterUrl : null;
        }

        try {
            if ($type === 'tv_show') {
                $details = $this->tvShowDetails($tmdbId);

                return $this->backdropUrl($details['backdrop_path'] ?? null) ?? ($posterUrl !== '' ? $posterUrl : null);
            }

            $details = $this->movieDetails($tmdbId);

            return $this->backdropUrl($details['backdrop_path'] ?? null) ?? ($posterUrl !== '' ? $posterUrl : null);
        } catch (Throwable) {
            return $posterUrl !== '' ? $posterUrl : null;
        }
    }

    /**
     * @param array<string, int> $result
     */
    private function hydrateSeasonImages(int $limit, array &$result): void
    {
        $rows = $this->db->select(
            "SELECT id, tmdb_parent_id, season_number, poster_url, poster_image, backdrop_image
             FROM media_seasons
             WHERE (
                poster_url IS NULL OR poster_url = ''
                OR backdrop_image IS NULL OR backdrop_image = ''
             )
             ORDER BY id DESC
             LIMIT {$limit}"
        );

        foreach ($rows as $row) {
            $result['scanned']++;
            $showTmdbId = (int) ($row['tmdb_parent_id'] ?? 0);
            $seasonNumber = (int) ($row['season_number'] ?? 0);
            $id = (int) ($row['id'] ?? 0);
            $posterUrl = (string) ($row['poster_url'] ?? '');
            $key = ($showTmdbId ?: $id) . '-s' . max(1, $seasonNumber);

            $updates = [];
            if ($posterUrl === '' && $showTmdbId > 0) {
                $posterUrl = $this->resolvePosterUrlForItem('tv_show', $showTmdbId) ?? '';
                if ($posterUrl !== '') {
                    $updates['poster_url'] = $posterUrl;
                    $result['posters_hydrated']++;
                }
            }

            if (trim((string) ($row['backdrop_image'] ?? '')) === '') {
                $backdropUrl = $this->resolveBackdropUrlForItem('tv_show', $showTmdbId, $posterUrl);
                if ($backdropUrl !== null) {
                    $updates['backdrop_image'] = $backdropUrl;
                    $result['backdrops_hydrated']++;
                }
            }

            if ($updates !== []) {
                $this->db->updateById('media_seasons', $id, $updates);
                $result['hydrated']++;
            } else {
                $result['failed']++;
            }
        }
    }

    /**
     * @param array<string, int> $result
     */
    private function hydrateEpisodeImages(int $limit, array &$result): void
    {
        $rows = $this->db->select(
            "SELECT id, tmdb_parent_id, season_number, episode_number, poster_url, poster_image, backdrop_image
             FROM media_episodes
             WHERE (
                poster_url IS NULL OR poster_url = ''
                OR backdrop_image IS NULL OR backdrop_image = ''
             )
             ORDER BY id DESC
             LIMIT {$limit}"
        );

        foreach ($rows as $row) {
            $result['scanned']++;
            $showTmdbId = (int) ($row['tmdb_parent_id'] ?? 0);
            $seasonNumber = (int) ($row['season_number'] ?? 0);
            $episodeNumber = (int) ($row['episode_number'] ?? 0);
            $id = (int) ($row['id'] ?? 0);
            $posterUrl = (string) ($row['poster_url'] ?? '');
            $key = ($showTmdbId ?: $id) . '-s' . max(1, $seasonNumber) . 'e' . max(1, $episodeNumber);

            $updates = [];
            if ($posterUrl === '' && $showTmdbId > 0) {
                $posterUrl = $this->resolvePosterUrlForItem('tv_show', $showTmdbId) ?? '';
                if ($posterUrl !== '') {
                    $updates['poster_url'] = $posterUrl;
                    $result['posters_hydrated']++;
                }
            }

            if (trim((string) ($row['backdrop_image'] ?? '')) === '') {
                $backdropUrl = $this->resolveBackdropUrlForItem('tv_show', $showTmdbId, $posterUrl);
                if ($backdropUrl !== null) {
                    $updates['backdrop_image'] = $backdropUrl;
                    $result['backdrops_hydrated']++;
                }
            }

            if ($updates !== []) {
                $this->db->updateById('media_episodes', $id, $updates);
                $result['hydrated']++;
            } else {
                $result['failed']++;
            }
        }
    }

    /**
     * @param array<string, int> $result
     */
    private function regenerateSeasonVariants(int $limit, array &$result): void
    {
        $rows = $this->db->select(
            "SELECT id, tmdb_parent_id, season_number, poster_url, poster_image, backdrop_image
             FROM media_seasons
             WHERE (poster_image IS NOT NULL AND poster_image <> '')
                OR (backdrop_image IS NOT NULL AND backdrop_image <> '')
             ORDER BY id DESC
             LIMIT {$limit}"
        );

        foreach ($rows as $row) {
            $result['scanned']++;
            $this->regenerateRowVariants($row, 'tv-seasons', 'tv_show', $result);
        }
    }

    /**
     * @param array<string, int> $result
     */
    private function regenerateEpisodeVariants(int $limit, array &$result): void
    {
        $rows = $this->db->select(
            "SELECT id, tmdb_parent_id, season_number, episode_number, poster_url, poster_image, backdrop_image
             FROM media_episodes
             WHERE (poster_image IS NOT NULL AND poster_image <> '')
                OR (backdrop_image IS NOT NULL AND backdrop_image <> '')
             ORDER BY id DESC
             LIMIT {$limit}"
        );

        foreach ($rows as $row) {
            $result['scanned']++;
            $showTmdbId = (int) ($row['tmdb_parent_id'] ?? 0);
            $seasonNumber = (int) ($row['season_number'] ?? 0);
            $episodeNumber = (int) ($row['episode_number'] ?? 0);
            $id = (int) ($row['id'] ?? 0);
            $key = ($showTmdbId ?: $id) . '-s' . max(1, $seasonNumber) . 'e' . max(1, $episodeNumber);
            $this->regenerateRowVariants($row, 'tv-episodes', 'tv_show', $result, $key);
        }
    }

    /**
     * @param array<string, mixed> $row
     * @param array<string, int> $result
     */
    private function regenerateRowVariants(
        array $row,
        string $folder,
        string $type,
        array &$result,
        ?string $nameKey = null
    ): void {
        $showTmdbId = (int) ($row['tmdb_parent_id'] ?? 0);
        $seasonNumber = (int) ($row['season_number'] ?? 0);
        $id = (int) ($row['id'] ?? 0);
        $key = $nameKey ?? (($showTmdbId ?: $id) . '-s' . max(1, $seasonNumber));
        $changed = false;

        $posterPath = trim((string) ($row['poster_image'] ?? ''));
        if ($posterPath !== '' && MediaImage::needsVariantRegeneration($posterPath, MediaImage::posterWidths())) {
            $regenerated = $this->regenerateVariantsFromLocal(
                $posterPath,
                trim((string) ($row['poster_url'] ?? '')),
                $folder,
                'poster-' . $key,
                MediaImage::ROLE_POSTER
            );
            if ($regenerated !== null) {
                $table = str_contains($folder, 'episode') ? 'media_episodes' : 'media_seasons';
                $this->db->updateById($table, $id, ['poster_image' => $regenerated]);
                $changed = true;
            }
        }

        $backdropPath = trim((string) ($row['backdrop_image'] ?? ''));
        if ($backdropPath !== '' && MediaImage::needsVariantRegeneration($backdropPath, MediaImage::backdropWidths())) {
            $backdropUrl = $this->resolveBackdropUrlForItem($type, $showTmdbId, trim((string) ($row['poster_url'] ?? '')));
            $regenerated = $this->regenerateVariantsFromLocal(
                $backdropPath,
                $backdropUrl ?? '',
                $folder,
                'backdrop-' . $key,
                MediaImage::ROLE_BACKDROP
            );
            if ($regenerated !== null) {
                $table = str_contains($folder, 'episode') ? 'media_episodes' : 'media_seasons';
                $this->db->updateById($table, $id, ['backdrop_image' => $regenerated]);
                $changed = true;
            }
        }

        if ($changed) {
            $result['variants_regenerated']++;
        } else {
            $result['failed']++;
        }
    }

    private function downloadsImages(): bool
    {
        return MediaImage::downloadsImagesEnabled();
    }

    /**
     * @return array{poster_url: ?string, poster_image: ?string, backdrop_image: ?string}
     */
    private function imagePayload(?string $posterUrl, ?string $backdropUrl, string $folder, string $key): array
    {
        $posterUrl = trim((string) $posterUrl) ?: null;
        $backdropUrl = trim((string) $backdropUrl) ?: null;

        if (!$this->downloadsImages()) {
            return [
                'poster_url' => $posterUrl,
                'poster_image' => null,
                'backdrop_image' => $backdropUrl,
            ];
        }

        return [
            'poster_url' => $posterUrl,
            'poster_image' => $this->downloadImageVariants($posterUrl, $folder, 'poster-' . $key, MediaImage::ROLE_POSTER),
            'backdrop_image' => $this->downloadImageVariants($backdropUrl, $folder, 'backdrop-' . $key, MediaImage::ROLE_BACKDROP),
        ];
    }

    private function castMeta(array $cast): ?string
    {
        $items = [];

        foreach ($cast as $person) {
            $name = trim((string) ($person['name'] ?? ''));

            if ($name === '') {
                continue;
            }

            $profile = $person['profile_path'] ?? null;
            $path = ($profile === null || $profile === '') ? 'null' : (string) $profile;
            $character = trim((string) ($person['character'] ?? ''));
            $items[] = '[' . $path . ';' . $name . ',' . $character . ']';

            if (count($items) === 10) {
                break;
            }
        }

        return $items !== [] ? implode('', $items) : null;
    }

    private function castProfileMeta(array $cast): ?string
    {
        $items = [];

        foreach ($cast as $person) {
            $name = trim((string) ($person['name'] ?? ''));

            if ($name === '') {
                continue;
            }

            $profilePath = trim((string) ($person['profile_path'] ?? ''));

            $items[] = [
                'tmdb_id'      => (int) ($person['id'] ?? 0),
                'name'         => $name,
                'character'    => trim((string) ($person['character'] ?? '')),
                'profile_path' => $profilePath !== '' ? $profilePath : null,
                'profile_image'=> null,
            ];

            if (count($items) === 12) {
                break;
            }
        }

        return $items !== [] ? json_encode($items, JSON_UNESCAPED_SLASHES) : null;
    }

    private function crewProfileMeta(array $crew): ?string
    {
        $items = [];
        $seen = [];

        foreach ($crew as $person) {
            $name = trim((string) ($person['name'] ?? ''));

            if ($name === '') {
                continue;
            }

            $job = trim((string) (($person['job'] ?? '') ?: ($person['known_for_department'] ?? '') ?: ($person['department'] ?? '') ?: 'Crew'));
            $key = ((int) ($person['id'] ?? 0)) . ':' . strtolower($name) . ':' . strtolower($job);

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $profilePath = trim((string) ($person['profile_path'] ?? ''));

            $items[] = [
                'tmdb_id'      => (int) ($person['id'] ?? 0),
                'name'         => $name,
                'job'          => $job,
                'profile_path' => $profilePath !== '' ? $profilePath : null,
                'profile_image'=> null,
            ];

            if (count($items) === 12) {
                break;
            }
        }

        return $items !== [] ? json_encode($items, JSON_UNESCAPED_SLASHES) : null;
    }

    private function directorMeta(array $crew): ?string
    {
        $items = [];

        foreach ($crew as $person) {
            if ((string) ($person['department'] ?? '') !== 'Directing') {
                continue;
            }

            $name = trim((string) ($person['name'] ?? ''));

            if ($name === '') {
                continue;
            }

            $profile = $person['profile_path'] ?? null;
            $path = ($profile === null || $profile === '') ? 'null' : (string) $profile;
            $items[] = '[' . $path . ';' . $name . ']';
        }

        return $items !== [] ? implode('', $items) : null;
    }

    private function creatorMeta(array $creators): ?string
    {
        $items = [];

        foreach ($creators as $creator) {
            $name = trim((string) ($creator['name'] ?? ''));

            if ($name === '') {
                continue;
            }

            $profile = $creator['profile_path'] ?? null;
            $path = ($profile === null || $profile === '') ? 'null' : (string) $profile;
            $items[] = '[' . $path . ';' . $name . ']';
        }

        return $items !== [] ? implode('', $items) : null;
    }

    private function movieCertification(array $movie): ?string
    {
        foreach (($movie['release_dates']['results'] ?? []) as $country) {
            foreach (($country['release_dates'] ?? []) as $release) {
                $certification = trim((string) ($release['certification'] ?? ''));

                if ($certification !== '') {
                    return $certification;
                }
            }
        }

        return null;
    }

    private function names(array $items): array
    {
        return array_values(array_filter(array_map(
            fn(array $item): string => trim((string) ($item['name'] ?? '')),
            $items
        )));
    }

    private function firstYoutubeKey(array $videos): ?string
    {
        foreach ($videos as $video) {
            if (strtolower((string) ($video['site'] ?? '')) === 'youtube' && !empty($video['key'])) {
                return (string) $video['key'];
            }
        }

        return null;
    }

    private function slug(string $value): string
    {
        $slug = strtolower(trim($value));
        $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug) ?: '';
        $slug = trim($slug, '-');

        return $slug !== '' ? $slug : bin2hex(random_bytes(4));
    }

    private function normalizeSort(string $sortBy): string
    {
        $allowed = [
            'popularity.desc',
            'popularity.asc',
            'vote_average.desc',
            'vote_average.asc',
            'first_air_date.desc',
            'release_date.desc',
        ];

        return in_array($sortBy, $allowed, true) ? $sortBy : 'popularity.desc';
    }
}
