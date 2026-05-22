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

            $normalizedPayload = $this->normalizeSourcePayload($payload);
            $this->cache->set($cacheKey, $normalizedPayload, $this->sourceCacheTtl());

            return $response
                ->header('X-Vexio-Stream-Cache', 'miss')
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
        return filter_var($_ENV['EMBED_API_ENABLED'] ?? true, FILTER_VALIDATE_BOOLEAN);
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
        return 'embed-source:v4:' . implode(':', [$type, $tmdbId, $season, $episode]);
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

    private function normalizeSourcePayload(array $payload): array
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

            $sourceSubtitles = $this->selectSubtitles($source['subtitles'] ?? $source['tracks'] ?? []);
            if ($sourceSubtitles !== []) {
                $normalized['subtitles'] = $sourceSubtitles;
            }

            $sources[] = $normalized;
        }

        $sources = $this->topPlayableSources($sources, 5);
        $subtitles = $this->selectSubtitles($payload['subtitles'] ?? []);

        return [
            'success' => true,
            'source' => 'embed-api',
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

        if ($playable === []) {
            return [];
        }

        usort($playable, fn (array $a, array $b): int => $this->sourceScore($b) <=> $this->sourceScore($a));

        return array_slice($playable, 0, $count);
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

        return str_ends_with($path, '.m3u8')
            || str_ends_with($path, '.mp4')
            || str_ends_with($path, '.webm')
            || str_ends_with($path, '.ogg')
            || str_ends_with($path, '.ogv');
    }

    private function sourceScore(array $source): int
    {
        $type = strtolower(trim((string) ($source['type'] ?? '')));
        $quality = $this->qualityScore((string) ($source['quality'] ?? ''));
        $typeScore = match ($type) {
            'hls' => 40,
            'mp4' => 30,
            default => 0,
        };

        return ($quality * 100) + $typeScore;
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

    private function selectSubtitles(mixed $subtitles): array
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
                'url' => $this->subtitleUrl($url, is_array($subtitle['headers'] ?? null) ? $subtitle['headers'] : []),
                'label' => (string) ($subtitle['label'] ?? $subtitle['language'] ?? $subtitle['lang'] ?? 'Subtitle'),
                'language' => (string) ($subtitle['language'] ?? $subtitle['lang'] ?? $subtitle['srclang'] ?? ''),
                'format' => $format ?: 'vtt',
            ];
        }

        return array_values($deduped);
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
