<?php

declare(strict_types=1);

namespace App\Controllers\Watch;

use App\Cache\CacheInterface;
use Framework\Http\Request;
use Framework\Http\Response;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class EmbedStreamController
{
    public function __construct(private CacheInterface $cache)
    {
    }

    public function sources(Request $request, Response $response): Response
    {
        if (!$this->enabled()) {
            return $response->error('Vexio embed API integration is disabled.', 503);
        }

        $type = (string) $request->query('type', 'movie');
        $tmdbId = (int) $request->query('tmdbId', 0);
        $season = max(1, (int) $request->query('season', 1));
        $episode = max(1, (int) $request->query('episode', 1));
        $cacheKey = $this->sourceCacheKey($type, $tmdbId, $season, $episode);

        if (!in_array($type, ['movie', 'tv'], true) || $tmdbId < 1) {
            return $response->error('Invalid embed source request.', 422);
        }

        $cachedPayload = $this->cache->get($cacheKey);
        if (is_array($cachedPayload)) {
            return $response
                ->header('X-Vexio-Stream-Cache', 'hit')
                ->json($cachedPayload);
        }

        $cineProPayload = $this->fetchCineProCorePayload($type, $tmdbId, $season, $episode);
        if ($cineProPayload !== null) {
            $normalizedPayload = $this->normalizeSourcePayload($cineProPayload, 'cinepro-core', false);
            if (($normalizedPayload['sources'] ?? []) !== []) {
                $this->cache->set($cacheKey, $normalizedPayload, $this->sourceCacheTtl());

                return $response
                    ->header('X-Vexio-Stream-Cache', 'miss')
                    ->header('X-Vexio-Stream-Provider', 'cinepro-core')
                    ->json($normalizedPayload);
            }
        }

        [$path, $query] = $this->embedApiSourceRequest($type, $tmdbId, $season, $episode);

        try {
            $client = new Client([
                'base_uri' => $this->embedApiBaseUrl(),
                'connect_timeout' => (float) ($_ENV['EMBED_API_CONNECT_TIMEOUT'] ?? 5),
                'timeout' => $this->embedApiTimeoutForRequest(),
                'http_errors' => false,
            ]);

            $payload = $this->fetchEmbedApiPayload($client, $path, $query);

            if ($payload === null) {
                return $response->json([
                    'error' => [
                        'message' => 'Vexio embed API did not return a usable response.',
                        'status' => 502,
                    ],
                ], 502);
            }

            $normalizedPayload = $this->normalizeSourcePayload($payload, 'embed-api', true);
            $this->cache->set($cacheKey, $normalizedPayload, $this->sourceCacheTtl());

            return $response
                ->header('X-Vexio-Stream-Cache', 'miss')
                ->header('X-Vexio-Stream-Provider', 'embed-api')
                ->json($normalizedPayload);
        } catch (GuzzleException $exception) {
            return $response->json([
                'error' => [
                    'message' => 'Unable to reach Vexio embed API.',
                    'detail' => $exception->getMessage(),
                ],
            ], 503);
        }
    }

    private function enabled(): bool
    {
        return $this->cineProCoreEnabled() || filter_var($_ENV['EMBED_API_ENABLED'] ?? true, FILTER_VALIDATE_BOOLEAN);
    }

    private function embedApiBaseUrl(): string
    {
        return rtrim((string) ($_ENV['EMBED_API_URL'] ?? 'https://embed.vexio.asia'), '/');
    }

    private function sourceCacheTtl(): int
    {
        return max(30, (int) ($_ENV['EMBED_API_SOURCE_CACHE_TTL'] ?? 1800));
    }

    private function sourceCacheKey(string $type, int $tmdbId, int $season, int $episode): string
    {
        return 'embed-source:v7:' . implode(':', [$type, $tmdbId, $season, $episode]);
    }

    private function embedApiTimeoutForRequest(): float
    {
        return (float) ($_ENV['EMBED_API_TIMEOUT'] ?? 45);
    }

    private function embedApiSourceRequest(string $type, int $tmdbId, int $season, int $episode): array
    {
        $embedType = $type === 'tv' ? 'series' : 'movie';
        $query = $embedType === 'series'
            ? ['season' => $season, 'episode' => $episode]
            : [];

        return ['/api/streams/' . $embedType . '/' . $tmdbId, $query];
    }

    private function cineProCoreEnabled(): bool
    {
        return filter_var($_ENV['CINEPRO_CORE_ENABLED'] ?? true, FILTER_VALIDATE_BOOLEAN);
    }

    private function cineProCoreBaseUrl(): string
    {
        return rtrim((string) ($_ENV['CINEPRO_CORE_URL'] ?? 'https://embed.vexio.asia'), '/');
    }

    private function cineProCoreSourcePath(string $type, int $tmdbId, int $season, int $episode): string
    {
        if ($type === 'tv') {
            return '/v1/tv/' . $tmdbId . '/seasons/' . $season . '/episodes/' . $episode;
        }

        return '/v1/movies/' . $tmdbId;
    }

    private function fetchCineProCorePayload(string $type, int $tmdbId, int $season, int $episode): ?array
    {
        if (!$this->cineProCoreEnabled()) {
            return null;
        }

        try {
            $client = new Client([
                'base_uri' => $this->cineProCoreBaseUrl(),
                'connect_timeout' => (float) ($_ENV['CINEPRO_CORE_CONNECT_TIMEOUT'] ?? 1.2),
                'timeout' => (float) ($_ENV['CINEPRO_CORE_TIMEOUT'] ?? 9),
                'http_errors' => false,
            ]);

            $apiResponse = $client->get($this->cineProCoreSourcePath($type, $tmdbId, $season, $episode), [
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);

            $status = $apiResponse->getStatusCode();
            $payload = json_decode((string) $apiResponse->getBody(), true);

            if ($status < 200 || $status >= 300 || !is_array($payload)) {
                return null;
            }

            return $payload;
        } catch (GuzzleException) {
            return null;
        }
    }

    private function fetchEmbedApiPayload(Client $client, string $path, array $query): ?array
    {
        $apiResponse = $client->get($path, [
            'query' => $query,
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        $status = $apiResponse->getStatusCode();
        $payload = json_decode((string) $apiResponse->getBody(), true);

        if ($status < 200 || $status >= 300 || !is_array($payload)) {
            return null;
        }

        return $payload;
    }

    private function normalizeSourcePayload(array $payload, string $payloadSource = 'embed-api', bool $proxySubtitles = true): array
    {
        $rawSources = is_array($payload['streams'] ?? null)
            ? $payload['streams']
            : (is_array($payload['sources'] ?? null) ? $payload['sources'] : []);
        $sources = [];

        foreach ($rawSources as $source) {
            if (!is_array($source)) {
                continue;
            }

            $url = trim((string) ($source['url'] ?? $source['file'] ?? ''));
            if ($url === '') {
                continue;
            }

            $normalized = [
                'url' => $url,
                'type' => $this->sourceType($source, $url),
                'quality' => (string) ($source['quality'] ?? $source['label'] ?? 'Auto'),
                'title' => (string) ($source['title'] ?? $source['name'] ?? ''),
                'provider' => $this->normalizeProvider($source['provider'] ?? 'Embed API'),
            ];

            if (isset($source['headers']) && is_array($source['headers'])) {
                $normalized['headers'] = $source['headers'];
            }

            $sourceSubtitles = $this->selectSubtitles($source['subtitles'] ?? $source['tracks'] ?? [], $proxySubtitles);
            if ($sourceSubtitles !== []) {
                $normalized['subtitles'] = $sourceSubtitles;
            }

            $normalized['browserPlayable'] = $this->isBrowserPlayableSource($normalized);

            $sources[] = $normalized;
        }

        $sources = $this->topPlayableSources($sources, 5);
        $subtitles = $this->selectSubtitles($payload['subtitles'] ?? [], $proxySubtitles);

        return [
            'success' => true,
            'source' => $payloadSource,
            'scraperIntegrated' => true,
            'count' => count($sources),
            'sources' => $sources,
            'subtitles' => $subtitles,
            'providerTimings' => is_array($payload['providerTimings'] ?? null) ? $payload['providerTimings'] : [],
        ];
    }

    private function sourceType(array $source, string $url): string
    {
        $type = strtolower(trim((string) ($source['type'] ?? '')));
        $sourceText = strtolower($url . ' ' . urldecode($url) . ' ' . ($source['title'] ?? '') . ' ' . ($source['name'] ?? ''));
        $path = strtolower((string) parse_url($url, PHP_URL_PATH));

        if (str_contains($sourceText, '.mkv') || preg_match('/\b(mkv|matroska)\b/', $sourceText)) {
            return 'mkv';
        }

        if ($type !== '') {
            return $type;
        }

        if (str_ends_with($path, '.m3u8')) {
            return 'hls';
        }

        if (str_ends_with($path, '.webm')) {
            return 'webm';
        }

        if (str_ends_with($path, '.ogg') || str_ends_with($path, '.ogv')) {
            return 'ogg';
        }

        return 'mp4';
    }

    private function normalizeProvider(mixed $provider): array
    {
        if (is_array($provider)) {
            return [
                'id' => strtolower((string) ($provider['id'] ?? $provider['name'] ?? 'embed-api')),
                'name' => (string) ($provider['name'] ?? $provider['id'] ?? 'Embed API'),
            ];
        }

        $name = (string) $provider;

        return [
            'id' => strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name) ?: 'embed-api'),
            'name' => $name !== '' ? $name : 'Embed API',
        ];
    }

    private function topPlayableSources(array $sources, int $count = 5): array
    {
        $playable = array_values(array_filter($sources, fn (array $source): bool => $this->isBrowserPlayableSource($source)));

        if ($playable !== []) {
            usort($playable, fn (array $a, array $b): int => $this->sourceScore($b) <=> $this->sourceScore($a));

            return array_slice($playable, 0, $count);
        }

        $fallbacks = array_values(array_filter($sources, fn (array $source): bool => trim((string) ($source['url'] ?? '')) !== ''));
        usort($fallbacks, fn (array $a, array $b): int => $this->fallbackSourceScore($b) <=> $this->fallbackSourceScore($a));

        return array_map(function (array $source): array {
            $source['browserPlayable'] = false;
            $source['compatibilityWarning'] = 'This source is an MKV/codec fallback and may not play in every browser.';

            return $source;
        }, array_slice($fallbacks, 0, $count));
    }

    private function isBrowserPlayableSource(array $source): bool
    {
        $url = trim((string) ($source['url'] ?? ''));
        $type = strtolower(trim((string) ($source['type'] ?? '')));
        $sourceText = strtolower($url . ' ' . urldecode($url) . ' ' . ($source['title'] ?? '') . ' ' . ($source['quality'] ?? ''));

        if ($url === '') {
            return false;
        }

        if ($type === 'mkv' || str_contains($sourceText, '.mkv') || preg_match('/\b(mkv|matroska)\b/', $sourceText)) {
            return false;
        }

        if (in_array($type, ['hls', 'mp4', 'webm', 'ogg'], true)) {
            return true;
        }

        $path = strtolower((string) parse_url($url, PHP_URL_PATH));

        // Be tolerant of providers that don't keep the extension in the URL path.
        // Examples: query-based HLS playlists, redirected stream URLs, etc.
        $hasHlsHint = str_contains($url, '.m3u8') || str_contains($path, '.m3u8') || preg_match('/\bm3u8\b/i', $url) === 1;
        $hasMp4Hint = str_contains($url, '.mp4') || preg_match('/\bmp4\b/i', $url) === 1;
        $hasWebmHint = str_contains($url, '.webm') || preg_match('/\bwebm\b/i', $url) === 1;
        $hasOggHint = str_contains($url, '.ogg') || str_contains($url, '.ogv') || preg_match('/\bogg\b/i', $url) === 1;

        return str_ends_with($path, '.m3u8')
            || str_ends_with($path, '.mp4')
            || str_ends_with($path, '.webm')
            || str_ends_with($path, '.ogg')
            || str_ends_with($path, '.ogv')
            || $hasHlsHint
            || $hasMp4Hint
            || $hasWebmHint
            || $hasOggHint;
    }

    private function sourceScore(array $source): int
    {
        $type = strtolower(trim((string) ($source['type'] ?? '')));
        $quality = $this->qualityScore((string) ($source['quality'] ?? ''));
        $quality = $quality > 1080 ? 980 : $quality;
        $typeScore = match ($type) {
            'hls' => 40,
            'mp4' => 30,
            default => 0,
        };
        $providerId = strtolower((string) ($source['provider']['id'] ?? ''));
        $providerScore = match ($providerId) {
            'cinesu' => 90,
            'icefy' => 80,
            'vidapi' => 70,
            'videasy' => 60,
            'vidrock' => 50,
            'vixsrc' => 40,
            'dahmermovies' => 30,
            default => 0,
        };

        return ($quality * 100) + $typeScore + $providerScore;
    }

    private function fallbackSourceScore(array $source): int
    {
        $text = strtolower(
            (string) ($source['title'] ?? '')
            . ' ' . (string) ($source['quality'] ?? '')
            . ' ' . urldecode((string) ($source['url'] ?? ''))
        );

        $score = $this->qualityScore((string) ($source['quality'] ?? '')) * 100;

        if (preg_match('/\b(h\.?264|x264|avc)\b/', $text)) {
            $score += 70000;
        }

        if (preg_match('/\b(hevc|h\.?265|x265)\b/', $text)) {
            $score -= 45000;
        }

        if (preg_match('/\b(10bit|10-bit|dv|dolby vision)\b/', $text)) {
            $score -= 25000;
        }

        if (preg_match('/\|\s*(\d+(?:\.\d+)?)\s*gb\s*\|/i', (string) ($source['title'] ?? ''), $matches)) {
            $score -= (int) round(((float) $matches[1]) * 100);
        }

        return $score;
    }

    private function qualityScore(string $quality): int
    {
        $quality = strtolower(trim($quality));

        if ($quality === '') {
            return 0;
        }

        if (str_contains($quality, '4k') || str_contains($quality, '2160')) {
            return 2160;
        }

        if (str_contains($quality, '2k') || str_contains($quality, '1440')) {
            return 1440;
        }

        if (preg_match('/(\d{3,4})\s*p?/', $quality, $matches)) {
            return (int) $matches[1];
        }

        if (str_contains($quality, 'auto') || str_contains($quality, 'hd')) {
            return 1080;
        }

        return 0;
    }

    private function selectSubtitles(mixed $subtitles, bool $proxySubtitles = true): array
    {
        if (!is_array($subtitles)) {
            return [];
        }

        $deduped = [];
        foreach ($subtitles as $subtitle) {
            if (!is_array($subtitle)) {
                continue;
            }

            $url = trim((string) ($subtitle['url'] ?? $subtitle['src'] ?? $subtitle['file'] ?? ''));
            if ($url === '' || isset($deduped[$url])) {
                continue;
            }

            $format = strtolower(trim((string) ($subtitle['format'] ?? $subtitle['type'] ?? pathinfo((string) parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION))));
            $deduped[$url] = [
                'url' => $proxySubtitles ? $this->subtitleUrl($url, is_array($subtitle['headers'] ?? null) ? $subtitle['headers'] : []) : $url,
                'label' => (string) ($subtitle['label'] ?? $subtitle['language'] ?? $subtitle['lang'] ?? 'Subtitle'),
                'language' => (string) ($subtitle['language'] ?? $subtitle['lang'] ?? $subtitle['srclang'] ?? ''),
                'format' => $format ?: 'vtt',
            ];
        }

        return array_slice(array_values($deduped), 0, 20);
    }

    private function subtitleUrl(string $url, array $headers = []): string
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }

        if (!filter_var($_ENV['EMBED_API_PROXY_SUBTITLES'] ?? true, FILTER_VALIDATE_BOOLEAN)) {
            return $url;
        }

        $query = ['url' => $url];
        if ($headers !== []) {
            $query['headers'] = json_encode($headers, JSON_UNESCAPED_SLASHES);
        }

        return $this->embedApiBaseUrl() . '/sub-proxy?' . http_build_query($query);
    }

}
