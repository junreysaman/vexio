<?php

declare(strict_types=1);

namespace App\Services\TMDB;

use App\Database\TmdbMetadataSchema;
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
    private string $imageBaseUrl;
    private string $backdropBaseUrl;
    private string $publicPath;

    public function __construct(private Database $db)
    {
        TmdbMetadataSchema::ensure($db);

        $this->apiKey = trim((string) ($_ENV['TMDB_API_KEY'] ?? ''));
        $this->accessToken = trim((string) ($_ENV['TMDB_ACCESS_TOKEN'] ?? ''));
        $this->imageBaseUrl = rtrim((string) ($_ENV['TMDB_IMAGE_BASE_URL'] ?? 'https://image.tmdb.org/t/p/original'), '/');
        $this->backdropBaseUrl = rtrim((string) ($_ENV['TMDB_BACKDROP_BASE_URL'] ?? 'https://image.tmdb.org/t/p/original'), '/');
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
        return $this->get('movie/' . $tmdbMovieId, [
            'append_to_response' => 'credits,videos,images,release_dates,external_ids,keywords,watch/providers,recommendations,similar',
            'include_image_language' => ($_ENV['TMDB_IMAGE_LANGUAGES'] ?? 'en,null'),
        ]);
    }

    public function tvShowDetails(int $tmdbTvId): array
    {
        return $this->get('tv/' . $tmdbTvId, [
            'append_to_response' => 'credits,aggregate_credits,videos,images,content_ratings,external_ids,keywords,watch/providers,recommendations,similar',
            'include_image_language' => ($_ENV['TMDB_IMAGE_LANGUAGES'] ?? 'en,null'),
        ]);
    }

    /**
     * Fetches one season with its episode list and artwork metadata from TMDB.
     */
    public function tvSeasonDetails(int $tmdbTvId, int $seasonNumber): array
    {
        return $this->get("tv/{$tmdbTvId}/season/{$seasonNumber}", [
            'append_to_response' => 'credits,videos,images',
            'include_image_language' => ($_ENV['TMDB_IMAGE_LANGUAGES'] ?? 'en,null'),
        ]);
    }

    public function tvEpisodeDetails(int $tmdbTvId, int $seasonNumber, int $episodeNumber): array
    {
        return $this->get("tv/{$tmdbTvId}/season/{$seasonNumber}/episode/{$episodeNumber}", [
            'append_to_response' => 'credits,videos,images',
            'include_image_language' => ($_ENV['TMDB_IMAGE_LANGUAGES'] ?? 'en,null'),
        ]);
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
        $imdbId = $this->nullableString($movie['external_ids']['imdb_id'] ?? null, 40);
        $youtube = $this->firstYoutubeKey($movie['videos']['results'] ?? []);
        $images = $this->imageLines($movie['images']['backdrops'] ?? []);
        $cast = $this->castMeta($movie['credits']['cast'] ?? []);
        $directors = $this->directorMeta($movie['credits']['crew'] ?? []);
        $payload = [
            'title' => (string) ($movie['title'] ?? $movie['original_title'] ?? 'Untitled Movie'),
            'slug' => MediaUrl::slugify((string) ($movie['title'] ?? $movie['original_title'] ?? 'Untitled Movie')),
            'original_title' => (string) ($movie['original_title'] ?? $movie['title'] ?? ''),
            'original_language' => $this->nullableString($movie['original_language'] ?? null, 10),
            'type' => 'movie',
            'synopsis' => (string) ($movie['overview'] ?? ''),
            'poster_url' => $posterUrl,
            'poster_image' => $this->downloadImageAsWebp($posterUrl, 'movies', 'poster-' . $tmdbMovieId),
            'backdrop_image' => $this->downloadImageAsWebp($backdropUrl, 'movies', 'backdrop-' . $tmdbMovieId),
            'stream_link' => null,
            'youtube_id' => $youtube !== null ? '[' . $youtube . ']' : null,
            'rated' => $this->movieCertification($movie),
            'country' => $this->csv($movie['origin_country'] ?? []),
            'imagenes' => $images,
            'dt_cast' => $cast,
            'dt_dir' => $directors,
            'imdb_id' => $imdbId,
            'release_year' => $this->yearFromDate($movie['release_date'] ?? null),
            'release_date' => $this->dateOrNull($movie['release_date'] ?? null),
            'runtime_minutes' => $movie['runtime'] ?? null,
            'tmdb_status' => $this->nullableString($movie['status'] ?? null, 60),
            'tagline' => $this->nullableString($movie['tagline'] ?? null, 255),
            'homepage_url' => $this->nullableString($movie['homepage'] ?? null, 500),
            'adult' => !empty($movie['adult']) ? 1 : 0,
            'budget' => isset($movie['budget']) ? (int) $movie['budget'] : null,
            'revenue' => isset($movie['revenue']) ? (int) $movie['revenue'] : null,
            'origin_country' => $this->csv($movie['origin_country'] ?? []),
            'spoken_languages' => $this->languageCsv($movie['spoken_languages'] ?? []),
            'tmdb_rating' => $this->rating($movie),
            'tmdb_popularity' => $this->popularity($movie),
            'tmdb_vote_count' => (int) ($movie['vote_count'] ?? 0),
            'views' => max(0, $views),
            'status' => $this->normalizeStatus($status),
            'is_featured' => $featured ? 1 : 0,
            'dt_featured_post' => $featured ? 1 : 0,
        ];

        $id = $this->upsertMediaItem('movie', $tmdbMovieId, $payload);
        $this->syncMovieDooPlayData($id, $movie, $imdbId, $images, $youtube, $cast, $directors);

        return $this->db->findById('media_items', $id);
    }

    public function importTvShow(int $tmdbTvId, int $views = 0, string $status = 'draft', bool $featured = false): ?array
    {
        $show = $this->tvShowDetails($tmdbTvId);
        $folder = 'tv';
        $posterUrl = $this->imageUrl($show['poster_path'] ?? null);
        $backdropUrl = $this->backdropUrl($show['backdrop_path'] ?? null);
        $youtube = $this->firstYoutubeKey($show['videos']['results'] ?? []);
        $images = $this->imageLines($show['images']['backdrops'] ?? []);
        $cast = $this->castMeta($show['credits']['cast'] ?? []);
        $creators = $this->creatorMeta($show['created_by'] ?? []);
        $payload = [
            'title' => (string) ($show['name'] ?? $show['original_name'] ?? 'Untitled Series'),
            'slug' => MediaUrl::slugify((string) ($show['name'] ?? $show['original_name'] ?? 'Untitled Series')),
            'original_title' => (string) ($show['original_name'] ?? $show['name'] ?? ''),
            'original_language' => $this->nullableString($show['original_language'] ?? null, 10),
            'type' => 'tv_show',
            'synopsis' => (string) ($show['overview'] ?? ''),
            'poster_url' => $posterUrl,
            'poster_image' => $this->downloadImageAsWebp($posterUrl, $folder, 'poster-' . $tmdbTvId),
            'backdrop_image' => $this->downloadImageAsWebp($backdropUrl, $folder, 'backdrop-' . $tmdbTvId),
            'stream_link' => null,
            'youtube_id' => $youtube !== null ? '[' . $youtube . ']' : null,
            'imagenes' => $images,
            'dt_cast' => $cast,
            'dt_creator' => $creators,
            'release_year' => $this->yearFromDate($show['first_air_date'] ?? null),
            'release_date' => $this->dateOrNull($show['first_air_date'] ?? null),
            'runtime_minutes' => (int) (($show['episode_run_time'][0] ?? null) ?: 0) ?: null,
            'tmdb_status' => $this->nullableString($show['status'] ?? null, 60),
            'tagline' => $this->nullableString($show['tagline'] ?? null, 255),
            'homepage_url' => $this->nullableString($show['homepage'] ?? null, 500),
            'number_of_seasons' => isset($show['number_of_seasons']) ? (int) $show['number_of_seasons'] : null,
            'number_of_episodes' => isset($show['number_of_episodes']) ? (int) $show['number_of_episodes'] : null,
            'last_air_date' => $this->dateOrNull($show['last_air_date'] ?? null),
            'in_production' => array_key_exists('in_production', $show) ? (!empty($show['in_production']) ? 1 : 0) : null,
            'origin_country' => $this->csv($show['origin_country'] ?? []),
            'spoken_languages' => $this->languageCsv($show['spoken_languages'] ?? []),
            'tmdb_rating' => $this->rating($show),
            'tmdb_popularity' => $this->popularity($show),
            'tmdb_vote_count' => (int) ($show['vote_count'] ?? 0),
            'views' => max(0, $views),
            'status' => $this->normalizeStatus($status),
            'is_featured' => $featured ? 1 : 0,
            'dt_featured_post' => $featured ? 1 : 0,
        ];

        $id = $this->upsertMediaItem('tv_show', $tmdbTvId, $payload);
        $this->syncTvShowDooPlayData($id, $show, $images, $youtube, $cast, $creators);

        return $this->db->findById('media_items', $id);
    }

    public function generateTvSeasons(int $tmdbTvId, string $status = 'draft'): array
    {
        $show = $this->tvShowDetails($tmdbTvId);
        $seasonSummaries = array_values(array_filter(
            $show['seasons'] ?? [],
            fn(array $season): bool => (int) ($season['season_number'] ?? 0) > 0
        ));

        $generated = [
            'seasons' => 0,
            'skipped' => 0,
        ];

        foreach ($seasonSummaries as $seasonSummary) {
            $seasonNumber = (int) ($seasonSummary['season_number'] ?? 0);

            if ($seasonNumber < 1) {
                $generated['skipped']++;
                continue;
            }

            $season = $this->tvSeasonDetails($tmdbTvId, $seasonNumber);
            $this->upsertTvSeasonFromData($tmdbTvId, $show, $season, $seasonNumber, 0, $status);
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
        
        // Ensure parent series and season exist first
        $parent = $this->ensureSeriesItem($tmdbTvId, $status);
        if (empty($parent['id'])) {
            throw new RuntimeException('Unable to create or find the TV series in the database.');
        }
        
        $seasonItem = $this->ensureSeasonItem($tmdbTvId, $show, $seasonNumber, $status);
        if (empty($seasonItem['id'])) {
            throw new RuntimeException('Unable to create or find the season in the database.');
        }

        $generated = [
            'episodes' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        foreach ($episodes as $episode) {
            $episodeNumber = (int) ($episode['episode_number'] ?? 0);

            if ($episodeNumber < 1) {
                $generated['skipped']++;
                continue;
            }

            try {
                $this->upsertTvEpisodeFromData($tmdbTvId, $show, $episode, $seasonNumber, $episodeNumber, 0, $status);
                $generated['episodes']++;
            } catch (Throwable $e) {
                $generated['errors'][] = 'Episode ' . $episodeNumber . ': ' . $e->getMessage();
            }
        }

        // Mark season as generated
        $this->db->update('media_seasons', ['clgnrt' => 1], [
            'media_item_id' => (int) $parent['id'],
            'season_number' => $seasonNumber,
        ]);

        return $generated;
    }

    public function importTvEpisode(int $tmdbTvId, int $seasonNumber, int $episodeNumber, int $views = 0, string $status = 'draft'): ?array
    {
        $show = $this->tvShowDetails($tmdbTvId);
        $episode = $this->tvEpisodeDetails($tmdbTvId, $seasonNumber, $episodeNumber);

        return $this->upsertTvEpisodeFromData($tmdbTvId, $show, $episode, $seasonNumber, $episodeNumber, $views, $status);
    }

    /**
     * Imports one season into media_seasons for a TV show title.
     */
    public function importTvSeason(
        int $tmdbTvId,
        int $seasonNumber,
        int $views = 0,
        string $status = 'draft'
    ): ?array {
        $show = $this->tvShowDetails($tmdbTvId);
        $season = $this->tvSeasonDetails($tmdbTvId, $seasonNumber);

        return $this->upsertTvSeasonFromData($tmdbTvId, $show, $season, $seasonNumber, $views, $status);
    }

    /**
     * Creates or updates one episode using episode data already returned by TMDB season details.
     */
    private function upsertTvEpisodeFromData(
        int $tmdbTvId,
        array $show,
        array $episode,
        int $seasonNumber,
        int $episodeNumber,
        int $views,
        string $status
    ): ?array {
        $parent = $this->ensureSeriesItem($tmdbTvId, $status);
        $season = $this->ensureSeasonItem($tmdbTvId, $show, $seasonNumber, $status);
        $episodeTitle = trim((string) ($episode['name'] ?? 'Episode ' . $episodeNumber));
        $showTitle = trim((string) ($show['name'] ?? $show['original_name'] ?? 'Untitled Series'));
        $tmdbType = 'tv_episode';
        $folder = 'tv-episodes';
        $posterUrl = $this->imageUrl($show['poster_path'] ?? null);
        $backdropUrl = $this->backdropUrl($episode['still_path'] ?? null) ?? $this->backdropUrl($show['backdrop_path'] ?? null);

        $payload = [
            'title' => $showTitle . ' - ' . $episodeTitle,
            'serie' => $showTitle,
            'episode_name' => $episodeTitle,
            'synopsis' => (string) ($episode['overview'] ?? $show['overview'] ?? ''),
            'poster_url' => $posterUrl,
            'poster_image' => $this->downloadImageAsWebp($posterUrl, $folder, 'poster-' . $tmdbTvId . '-s' . $seasonNumber . 'e' . $episodeNumber),
            'backdrop_image' => $this->downloadImageAsWebp($backdropUrl, $folder, 'backdrop-' . $tmdbTvId . '-s' . $seasonNumber . 'e' . $episodeNumber),
            'stream_link' => null,
            'release_year' => $this->yearFromDate($episode['air_date'] ?? $show['first_air_date'] ?? null),
            'air_date' => $this->dateOrNull($episode['air_date'] ?? null),
            'imagenes' => $this->imageLines($episode['images']['stills'] ?? []),
            'season_number' => $seasonNumber,
            'episode_number' => $episodeNumber,
            'views' => max(0, $views),
            'status' => $this->normalizeStatus($status),
        ];

        $externalId = (int) ($episode['id'] ?? 0);
        $tmdbId = $externalId > 0 ? $externalId : (int) ($tmdbTvId . $seasonNumber . $episodeNumber);
        $id = $this->upsertMediaEpisode((int) $parent['id'], (int) $season['id'], $tmdbType, $tmdbId, $tmdbTvId, $payload);
        $this->syncEpisodeDooPlayData($id, $tmdbTvId, $showTitle, $episode, $seasonNumber, $episodeNumber, $payload['imagenes']);

        return $this->db->findById('media_episodes', $id);
    }

    /**
     * Creates or updates one season using season data already returned by TMDB.
     */
    private function upsertTvSeasonFromData(
        int $tmdbTvId,
        array $show,
        array $season,
        int $seasonNumber,
        int $views,
        string $status
    ): ?array {
        $parent = $this->ensureSeriesItem($tmdbTvId, $status);
        $showTitle = trim((string) ($show['name'] ?? $show['original_name'] ?? 'Untitled Series'));
        $seasonTitle = trim((string) ($season['name'] ?? 'Season ' . $seasonNumber));
        $tmdbType = 'tv_season';
        $folder = 'tv-seasons';
        $posterUrl = $this->imageUrl($season['poster_path'] ?? null) ?? $this->imageUrl($show['poster_path'] ?? null);
        $backdropUrl = $this->backdropUrl($show['backdrop_path'] ?? null);

        $payload = [
            'title' => $showTitle . ' - ' . $seasonTitle,
            'serie' => $showTitle,
            'synopsis' => (string) ($season['overview'] ?? $show['overview'] ?? ''),
            'poster_url' => $posterUrl,
            'poster_image' => $this->downloadImageAsWebp($posterUrl, $folder, 'poster-' . $tmdbTvId . '-s' . $seasonNumber),
            'backdrop_image' => $this->downloadImageAsWebp($backdropUrl, $folder, 'backdrop-' . $tmdbTvId . '-s' . $seasonNumber),
            'release_year' => $this->yearFromDate($season['air_date'] ?? $show['first_air_date'] ?? null),
            'air_date' => $this->dateOrNull($season['air_date'] ?? null),
            'season_number' => $seasonNumber,
            'status' => $this->normalizeStatus($status),
        ];

        $externalId = (int) ($season['id'] ?? 0);
        $tmdbId = $externalId > 0 ? $externalId : (int) ($tmdbTvId . $seasonNumber);
        $id = $this->upsertMediaSeason((int) $parent['id'], $tmdbType, $tmdbId, $tmdbTvId, $payload);
        $this->syncSeasonDooPlayData($id, $tmdbTvId, $showTitle, $season, $seasonNumber);

        return $this->db->findById('media_seasons', $id);
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
        $headers = [
            'Accept' => 'application/json',
        ];

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

    /**
     * Ensures the parent TV title exists before season or episode rows are imported.
     */
    private function ensureSeriesItem(int $tmdbTvId, string $status): array
    {
        $tmdbType = 'tv_show';
        $existing = $this->db->selectOne(
            'SELECT * FROM media_items WHERE tmdb_type = :tmdb_type AND tmdb_id = :tmdb_id LIMIT 1',
            ['tmdb_type' => $tmdbType, 'tmdb_id' => $tmdbTvId]
        );

        if ($existing) {
            return $existing;
        }

        return $this->importTvShow($tmdbTvId, 0, $status) ?? [];
    }

    /**
     * Ensures a season row exists for an episode import when TMDB episode data is generated directly.
     */
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

    /**
     * Creates or updates a top-level media item by TMDB identity.
     */
    private function upsertMediaItem(string $tmdbType, int $tmdbId, array $payload): int
    {
        $existing = $this->db->selectOne(
            'SELECT id FROM media_items WHERE tmdb_type = :tmdb_type AND tmdb_id = :tmdb_id LIMIT 1',
            [
                'tmdb_type' => $tmdbType,
                'tmdb_id' => $tmdbId,
            ]
        );

        $payload = [
            ...$payload,
            'tmdb_type' => $tmdbType,
            'tmdb_id' => $tmdbId,
        ];

        if ($existing) {
            $this->db->updateById('media_items', (int) $existing['id'], $payload);
            return (int) $existing['id'];
        }

        return (int) $this->db->insert('media_items', $payload);
    }

    /**
     * Creates or updates a season row under its parent media item.
     */
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

    /**
     * Creates or updates an episode row under its parent media item and season.
     */
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

    private function syncMovieDooPlayData(
        int $itemId,
        array $movie,
        ?string $imdbId,
        ?string $images,
        ?string $youtube,
        ?string $cast,
        ?string $directors
    ): void {
        $releaseYear = (string) $this->yearFromDate($movie['release_date'] ?? null);
        $this->syncMeta('item', $itemId, [
            'ids' => $imdbId,
            'idtmdb' => (string) ($movie['id'] ?? ''),
            'dt_poster' => $movie['poster_path'] ?? null,
            'dt_backdrop' => $movie['backdrop_path'] ?? null,
            'imagenes' => $images,
            'youtube_id' => $youtube !== null ? '[' . $youtube . ']' : null,
            'imdbRating' => $this->rating($movie),
            'imdbVotes' => $movie['vote_count'] ?? null,
            'Rated' => $this->movieCertification($movie),
            'Country' => $this->csv($movie['origin_country'] ?? []),
            'original_title' => $movie['original_title'] ?? null,
            'release_date' => $movie['release_date'] ?? null,
            'vote_average' => $movie['vote_average'] ?? null,
            'vote_count' => $movie['vote_count'] ?? null,
            'tagline' => $movie['tagline'] ?? null,
            'runtime' => $movie['runtime'] ?? null,
            'dt_cast' => $cast,
            'dt_dir' => $directors,
        ]);

        $this->syncTerms('item', $itemId, 'genres', $this->names($movie['genres'] ?? []));
        $this->syncTerms('item', $itemId, 'dtyear', $releaseYear !== '' ? [$releaseYear] : []);
        $this->syncTerms('item', $itemId, 'dtcast', $this->names(array_slice($movie['credits']['cast'] ?? [], 0, 10)));
        $this->syncTerms('item', $itemId, 'dtdirector', $this->crewNamesByDepartment($movie['credits']['crew'] ?? [], 'Directing'));
    }

    private function syncTvShowDooPlayData(
        int $itemId,
        array $show,
        ?string $images,
        ?string $youtube,
        ?string $cast,
        ?string $creators
    ): void {
        $releaseYear = (string) $this->yearFromDate($show['first_air_date'] ?? null);
        $runtime = $show['episode_run_time'][0] ?? null;
        $this->syncMeta('item', $itemId, [
            'ids' => $show['id'] ?? null,
            'imagenes' => $images,
            'youtube_id' => $youtube !== null ? '[' . $youtube . ']' : null,
            'episode_run_time' => $runtime,
            'dt_poster' => $show['poster_path'] ?? null,
            'dt_backdrop' => $show['backdrop_path'] ?? null,
            'first_air_date' => $show['first_air_date'] ?? null,
            'last_air_date' => $show['last_air_date'] ?? null,
            'number_of_episodes' => $show['number_of_episodes'] ?? null,
            'number_of_seasons' => $show['number_of_seasons'] ?? null,
            'original_name' => $show['original_name'] ?? null,
            'imdbRating' => $this->rating($show),
            'imdbVotes' => $show['vote_count'] ?? null,
            'dt_cast' => $cast,
            'dt_creator' => $creators,
        ]);

        $this->syncTerms('item', $itemId, 'genres', $this->names($show['genres'] ?? []));
        $this->syncTerms('item', $itemId, 'dtyear', $releaseYear !== '' ? [$releaseYear] : []);
        $this->syncTerms('item', $itemId, 'dtnetworks', $this->names($show['networks'] ?? []));
        $this->syncTerms('item', $itemId, 'dtstudio', $this->names($show['production_companies'] ?? []));
        $this->syncTerms('item', $itemId, 'dtcast', $this->names(array_slice($show['credits']['cast'] ?? [], 0, 10)));
        $this->syncTerms('item', $itemId, 'dtcreator', $this->names($show['created_by'] ?? []));
    }

    private function syncSeasonDooPlayData(int $seasonId, int $tmdbTvId, string $showTitle, array $season, int $seasonNumber): void
    {
        $this->syncMeta('season', $seasonId, [
            'ids' => $tmdbTvId,
            'temporada' => $seasonNumber,
            'serie' => $showTitle,
            'air_date' => $season['air_date'] ?? null,
            'dt_poster' => $season['poster_path'] ?? null,
        ]);
    }

    private function syncEpisodeDooPlayData(
        int $episodeId,
        int $tmdbTvId,
        string $showTitle,
        array $episode,
        int $seasonNumber,
        int $episodeNumber,
        ?string $images
    ): void {
        $this->syncMeta('episode', $episodeId, [
            'ids' => $tmdbTvId,
            'temporada' => $seasonNumber,
            'episodio' => $episodeNumber,
            'serie' => $showTitle,
            'episode_name' => $episode['name'] ?? null,
            'air_date' => $episode['air_date'] ?? null,
            'imagenes' => $images,
            'dt_backdrop' => $episode['still_path'] ?? null,
        ]);
    }

    private function syncMeta(string $ownerType, int $ownerId, array $meta): void
    {
        foreach ($meta as $key => $value) {
            $value = is_scalar($value) ? trim((string) $value) : null;

            if ($value === null || $value === '') {
                $this->db->delete('content_meta', [
                    'owner_type' => $ownerType,
                    'owner_id' => $ownerId,
                    'meta_key' => $key,
                ]);
                continue;
            }

            $this->db->updateOrInsert('content_meta', [
                'owner_type' => $ownerType,
                'owner_id' => $ownerId,
                'meta_key' => $key,
            ], ['meta_value' => $value]);
        }
    }

    private function syncTerms(string $ownerType, int $ownerId, string $taxonomy, array $names): void
    {
        $existing = $this->db->select(
            'SELECT content_term_links.term_id
             FROM content_term_links
             INNER JOIN content_terms ON content_terms.id = content_term_links.term_id
             WHERE content_term_links.owner_type = :owner_type
             AND content_term_links.owner_id = :owner_id
             AND content_terms.taxonomy = :taxonomy',
            ['owner_type' => $ownerType, 'owner_id' => $ownerId, 'taxonomy' => $taxonomy]
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
                'taxonomy' => $taxonomy,
                'slug' => $slug,
            ], ['name' => $name]);

            $this->attachPivot('content_term_links', [
                'owner_type' => $ownerType,
                'owner_id' => $ownerId,
                'term_id' => $termId,
            ]);
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

    private function attachPivot(string $table, array $where): void
    {
        if (!$this->db->existsWhere($table, $where)) {
            $this->db->insert($table, $where);
        }
    }

    public function posterUrl(?string $path): ?string
    {
        return $this->imageUrl($path);
    }

    public function backdropUrl(?string $path): ?string
    {
        $path = trim((string) $path);

        if ($path === '') {
            return null;
        }

        return $this->backdropBaseUrl . '/' . ltrim($path, '/');
    }

    private function imageUrl(?string $path): ?string
    {
        $path = trim((string) $path);

        if ($path === '') {
            return null;
        }

        return $this->imageBaseUrl . '/' . ltrim($path, '/');
    }

    private function yearFromDate(?string $date): ?int
    {
        $date = trim((string) $date);

        if ($date === '') {
            return null;
        }

        return (int) substr($date, 0, 4);
    }

    private function dateOrNull(?string $date): ?string
    {
        $date = trim((string) $date);

        if ($date === '') {
            return null;
        }

        return substr($date, 0, 10);
    }

    private function dateTimeOrNull(?string $datetime): ?string
    {
        $timestamp = strtotime((string) $datetime);

        return $timestamp === false ? null : date('Y-m-d H:i:s', $timestamp);
    }

    private function nullableString(mixed $value, int $maxLength): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return mb_substr($value, 0, $maxLength);
    }

    private function csv(array $values): ?string
    {
        $values = array_values(array_filter(array_map(
            fn(mixed $value): string => trim((string) $value),
            $values
        )));

        return $values === [] ? null : implode(',', $values);
    }

    private function languageCsv(array $languages): ?string
    {
        return $this->csv(array_map(
            fn(array $language): string => (string) ($language['iso_639_1'] ?? ''),
            $languages
        ));
    }

    private function normalizeStatus(string $status): string
    {
        return in_array($status, ['draft', 'published', 'archived'], true) ? $status : 'draft';
    }

    private function rating(array $data): ?float
    {
        return isset($data['vote_average']) ? round((float) $data['vote_average'], 1) : null;
    }

    private function popularity(array $data): ?float
    {
        return isset($data['popularity']) ? round((float) $data['popularity'], 3) : null;
    }

    private function downloadImageAsWebp(?string $url, string $folder, string $name): ?string
    {
        if ($url === null) {
            return null;
        }

        try {
            $response = $this->assetHttp->get($url);
        } catch (GuzzleException) {
            return null;
        }

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            return null;
        }

        $image = @imagecreatefromstring((string) $response->getBody());

        if (!$image) {
            return null;
        }

        $relativeDir = '/uploads/tmdb/' . trim($folder, '/');
        $directory = $this->publicPath . str_replace('/', DIRECTORY_SEPARATOR, $relativeDir);

        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $safeName = preg_replace('/[^a-zA-Z0-9_-]+/', '-', strtolower($name)) ?: bin2hex(random_bytes(8));
        $relativePath = $relativeDir . '/' . $safeName . '.webp';
        $absolutePath = $this->publicPath . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);
        imagewebp($image, $absolutePath, 82);
        imagedestroy($image);

        return $relativePath;
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

    private function imageLines(array $images): ?string
    {
        $paths = [];

        foreach ($images as $image) {
            $path = trim((string) ($image['file_path'] ?? ''));

            if ($path !== '') {
                $paths[] = $path;
            }

            if (count($paths) === 10) {
                break;
            }
        }

        return $paths === [] ? null : implode("\n", $paths);
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
            $path = $profile === null || $profile === '' ? 'null' : (string) $profile;
            $character = trim((string) ($person['character'] ?? ''));
            $items[] = '[' . $path . ';' . $name . ',' . $character . ']';

            if (count($items) === 10) {
                break;
            }
        }

        return $items === [] ? null : implode('', $items);
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
            $path = $profile === null || $profile === '' ? 'null' : (string) $profile;
            $items[] = '[' . $path . ';' . $name . ']';
        }

        return $items === [] ? null : implode('', $items);
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
            $path = $profile === null || $profile === '' ? 'null' : (string) $profile;
            $items[] = '[' . $path . ';' . $name . ']';
        }

        return $items === [] ? null : implode('', $items);
    }

    private function names(array $items): array
    {
        return array_values(array_filter(array_map(
            fn(array $item): string => trim((string) ($item['name'] ?? '')),
            $items
        )));
    }

    private function crewNamesByDepartment(array $crew, string $department): array
    {
        return array_values(array_filter(array_map(
            fn(array $item): string => (string) ($item['department'] ?? '') === $department
                ? trim((string) ($item['name'] ?? ''))
                : '',
            $crew
        )));
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
