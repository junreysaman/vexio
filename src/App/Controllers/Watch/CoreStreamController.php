<?php

declare(strict_types=1);

namespace App\Controllers\Watch;

use Framework\Http\Request;
use Framework\Http\Response;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class CoreStreamController
{
    public function player(Request $request, Response $response, string $type, string $tmdbId): Response
    {
        $type = $type === 'tv' ? 'tv' : 'movie';
        $season = max(1, (int) $request->query('season', 1));
        $episode = max(1, (int) $request->query('episode', 1));
        $title = trim((string) $request->query('title', 'Vexio'));

        return $response->html($this->playerHtml($type, (int) $tmdbId, $season, $episode, $title));
    }

    public function sources(Request $request, Response $response): Response
    {
        if (!$this->enabled()) {
            return $response->error('Vexio streaming integration is disabled.', 503);
        }

        $type = (string) $request->query('type', 'movie');
        $tmdbId = (int) $request->query('tmdbId', 0);
        $season = max(1, (int) $request->query('season', 1));
        $episode = max(1, (int) $request->query('episode', 1));

        if (!in_array($type, ['movie', 'tv'], true) || $tmdbId < 1) {
            return $response->error('Invalid Core source request.', 422);
        }

        $coreBaseUrl = $this->coreBaseUrl();
        $path = $type === 'tv'
            ? "/v1/tv/{$tmdbId}/seasons/{$season}/episodes/{$episode}"
            : "/v1/movies/{$tmdbId}";

        try {
            $client = new Client([
                'base_uri' => $coreBaseUrl,
                'connect_timeout' => (float) ($_ENV['CINEPRO_CORE_CONNECT_TIMEOUT'] ?? 5),
                'timeout' => (float) ($_ENV['CINEPRO_CORE_TIMEOUT'] ?? 180),
                'http_errors' => false,
            ]);

            $coreResponse = $client->get($path, [
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);

            $status = $coreResponse->getStatusCode();
            $payload = json_decode((string) $coreResponse->getBody(), true);

            if ($status < 200 || $status >= 300 || !is_array($payload)) {
                return $response->json([
                    'error' => [
                        'message' => 'Vexio streaming did not return a usable response.',
                        'status' => $status,
                    ],
                ], 502);
            }

            return $response->json($this->normalizeSourcePayload($payload, $coreBaseUrl, $this->vexioProxyBaseUrl()));
        } catch (GuzzleException $exception) {
            return $response->json([
                'error' => [
                    'message' => 'Unable to reach Vexio streaming.',
                    'detail' => $exception->getMessage(),
                ],
            ], 503);
        }
    }

    public function proxy(Request $request, Response $response): Response
    {
        if (!$this->enabled()) {
            return $response->error('Vexio streaming integration is disabled.', 503);
        }

        $data = trim((string) $request->query('data', ''));
        if ($data === '') {
            return $response->error('Missing proxy data.', 422);
        }

        try {
            $client = new Client([
                'base_uri' => $this->coreBaseUrl(),
                'connect_timeout' => (float) ($_ENV['CINEPRO_CORE_CONNECT_TIMEOUT'] ?? 5),
                'timeout' => (float) ($_ENV['CINEPRO_CORE_PROXY_TIMEOUT'] ?? 0),
                'http_errors' => false,
            ]);

            $coreResponse = $client->get('/v1/proxy', [
                'query' => ['data' => $data],
                'stream' => true,
                'headers' => array_filter([
                    'Accept' => (string) $request->header('Accept', '*/*'),
                    'Range' => (string) $request->header('Range', ''),
                ]),
            ]);

            return $this->proxyResponse($response, $coreResponse);
        } catch (GuzzleException $exception) {
            return $response->json([
                'error' => [
                    'message' => 'Unable to proxy Vexio stream.',
                    'detail' => $exception->getMessage(),
                ],
            ], 503);
        }
    }

    private function enabled(): bool
    {
        return filter_var($_ENV['CINEPRO_CORE_ENABLED'] ?? true, FILTER_VALIDATE_BOOLEAN);
    }

    private function coreBaseUrl(): string
    {
        return rtrim((string) ($_ENV['CINEPRO_CORE_URL'] ?? 'http://127.0.0.1:3000'), '/');
    }

    private function corePublicUrl(string $fallback): string
    {
        return rtrim((string) ($_ENV['CINEPRO_CORE_PUBLIC_URL'] ?? $fallback), '/');
    }

    private function vexioProxyBaseUrl(): string
    {
        return rtrim((string) ($_ENV['APP_URL'] ?? ''), '/') . '/api/core/proxy';
    }

    private function normalizeSourcePayload(array $payload, string $coreBaseUrl, string $proxyBaseUrl): array
    {
        $sources = [];
        foreach (($payload['sources'] ?? []) as $source) {
            if (!is_array($source)) {
                continue;
            }

            $url = trim((string) ($source['url'] ?? ''));
            if ($url === '') {
                continue;
            }

            $source['url'] = $this->rewriteCoreProxyUrl($url, $coreBaseUrl, $proxyBaseUrl);
            $sources[] = $source;
        }

        $bestSource = $this->bestPlayableSource($sources);

        $payload['sources'] = $bestSource ? [$bestSource] : [];
        $payload['subtitles'] = $this->selectSubtitles(
            $this->normalizeUrlList($payload['subtitles'] ?? [], $coreBaseUrl, $proxyBaseUrl)
        );

        return $payload;
    }

    private function bestPlayableSource(array $sources): ?array
    {
        $playable = array_values(array_filter($sources, fn (array $source): bool => $this->isBrowserPlayableSource($source)));

        if ($playable === []) {
            return null;
        }

        usort($playable, fn (array $a, array $b): int => $this->sourceScore($b) <=> $this->sourceScore($a));

        $preferred = array_values(array_filter(
            $playable,
            fn (array $source): bool => $this->qualityScore((string) ($source['quality'] ?? '')) >= 1080
        ));

        return $preferred[0] ?? $playable[0];
    }

    private function isBrowserPlayableSource(array $source): bool
    {
        $url = trim((string) ($source['url'] ?? ''));
        $type = strtolower(trim((string) ($source['type'] ?? '')));

        if ($url === '') {
            return false;
        }

        if (in_array($type, ['hls', 'mp4'], true)) {
            return true;
        }

        $path = strtolower((string) parse_url($url, PHP_URL_PATH));

        return str_ends_with($path, '.m3u8') || str_ends_with($path, '.mp4');
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
        $englishAudioScore = $this->hasEnglishAudio($source) ? 20 : 0;

        return ($quality * 100) + $typeScore + $englishAudioScore;
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

        return 0;
    }

    private function hasEnglishAudio(array $source): bool
    {
        foreach (($source['audioTracks'] ?? []) as $track) {
            if (!is_array($track)) {
                continue;
            }

            $language = strtolower((string) ($track['language'] ?? ''));
            $label = strtolower((string) ($track['label'] ?? ''));

            if (in_array($language, ['en', 'eng', 'english'], true) || str_contains($label, 'english')) {
                return true;
            }
        }

        return false;
    }

    private function selectSubtitles(array $subtitles): array
    {
        $deduped = [];
        foreach ($subtitles as $subtitle) {
            $url = trim((string) ($subtitle['url'] ?? ''));
            if ($url === '' || isset($deduped[$url])) {
                continue;
            }

            $format = strtolower(trim((string) ($subtitle['format'] ?? pathinfo((string) parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION))));
            $subtitle['format'] = $format ?: 'vtt';
            $deduped[$url] = $subtitle;
        }

        $selected = array_values($deduped);
        usort($selected, fn (array $a, array $b): int => $this->subtitleScore($b) <=> $this->subtitleScore($a));

        return array_slice($selected, 0, 30);
    }

    private function subtitleScore(array $subtitle): int
    {
        $label = strtolower((string) ($subtitle['label'] ?? ''));
        $format = strtolower((string) ($subtitle['format'] ?? ''));
        $score = 0;

        if ($format === 'vtt') {
            $score += 100;
        }

        if (str_contains($label, 'english') || $label === 'eng') {
            $score += 50;
        }

        if (str_contains($label, 'cc')) {
            $score += 10;
        }

        return $score;
    }

    private function normalizeUrlList(mixed $items, string $coreBaseUrl, string $proxyBaseUrl): array
    {
        if (!is_array($items)) {
            return [];
        }

        $normalized = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $url = trim((string) ($item['url'] ?? ''));
            if ($url === '') {
                continue;
            }

            $item['url'] = $this->rewriteCoreProxyUrl($url, $coreBaseUrl, $proxyBaseUrl);

            $normalized[] = $item;
        }

        return $normalized;
    }

    private function rewriteCoreProxyUrl(string $url, string $coreBaseUrl, string $proxyBaseUrl): string
    {
        $path = (string) parse_url($url, PHP_URL_PATH);

        if ($path !== '/v1/proxy') {
            return $url;
        }

        $urlHost = strtolower((string) parse_url($url, PHP_URL_HOST));
        $coreHost = strtolower((string) parse_url($coreBaseUrl, PHP_URL_HOST));
        $corePublicHost = strtolower((string) parse_url($this->corePublicUrl($coreBaseUrl), PHP_URL_HOST));
        $appHost = strtolower((string) parse_url((string) ($_ENV['APP_URL'] ?? ''), PHP_URL_HOST));
        $knownHosts = array_filter(array_unique([$coreHost, $corePublicHost, $appHost, 'localhost', '127.0.0.1']));

        if ($urlHost !== '' && !in_array($urlHost, $knownHosts, true)) {
            return $url;
        }

        $query = (string) parse_url($url, PHP_URL_QUERY);

        return $proxyBaseUrl . ($query !== '' ? '?' . $query : '');
    }

    private function proxyResponse(Response $response, ResponseInterface $coreResponse): Response
    {
        $contentType = $coreResponse->getHeaderLine('Content-Type') ?: 'application/octet-stream';

        if (!$this->isTextResponse($contentType)) {
            $this->streamBinaryResponse($coreResponse, $contentType);
            exit;
        }

        $body = (string) $coreResponse->getBody();
        $body = $this->rewriteProxyText($body);

        $response->status($coreResponse->getStatusCode())
            ->header('Content-Type', $contentType)
            ->body($body);

        foreach (['Accept-Ranges', 'Content-Range', 'ETag', 'Last-Modified'] as $header) {
            $value = $coreResponse->getHeaderLine($header);
            if ($value !== '') {
                $response->header($header, $value);
            }
        }

        return $response;
    }

    private function streamBinaryResponse(ResponseInterface $coreResponse, string $contentType): void
    {
        if (!headers_sent()) {
            http_response_code($coreResponse->getStatusCode());
            header('Content-Type: ' . $contentType, true);

            foreach (['Content-Length', 'Accept-Ranges', 'Content-Range', 'ETag', 'Last-Modified', 'Cache-Control'] as $header) {
                $value = $coreResponse->getHeaderLine($header);
                if ($value !== '') {
                    header($header . ': ' . $value, true);
                }
            }
        }

        while (ob_get_level() > 0) {
            @ob_end_flush();
        }

        $stream = $coreResponse->getBody();
        while (!$stream->eof()) {
            echo $stream->read(8192);
            flush();
        }
    }

    private function isTextResponse(string $contentType): bool
    {
        $contentType = strtolower($contentType);

        return str_contains($contentType, 'mpegurl')
            || str_contains($contentType, 'text/')
            || str_contains($contentType, 'json')
            || str_contains($contentType, 'javascript');
    }

    private function rewriteProxyText(string $body): string
    {
        $proxyBaseUrl = $this->vexioProxyBaseUrl();
        $coreBaseUrl = $this->coreBaseUrl();
        $corePublicUrl = $this->corePublicUrl($coreBaseUrl);
        $appUrl = rtrim((string) ($_ENV['APP_URL'] ?? ''), '/');

        $needles = array_filter(array_unique([
            rtrim($coreBaseUrl, '/') . '/v1/proxy',
            rtrim($corePublicUrl, '/') . '/v1/proxy',
            rtrim($appUrl, '/') . '/v1/proxy',
            '/v1/proxy',
        ]));

        return str_replace($needles, $proxyBaseUrl, $body);
    }

    private function playerHtml(string $type, int $tmdbId, int $season, int $episode, string $title): string
    {
        $title = htmlspecialchars($title !== '' ? $title : 'Vexio', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $sourceUrl = '/api/core/sources?' . http_build_query([
            'type' => $type,
            'tmdbId' => $tmdbId,
            'season' => $season,
            'episode' => $episode,
        ]);

        return <<<HTML
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{$title}</title>
  <style>
    :root { color-scheme: dark; --bg: #06080d; --panel: #111827; --text: #f7fbff; --muted: #98a2b3; --accent: #00c8f0; --danger: #ff5e7d; }
    * { box-sizing: border-box; }
    body { margin: 0; min-height: 100vh; background: var(--bg); color: var(--text); font-family: Arial, Helvetica, sans-serif; overflow: hidden; }
    .shell { position: fixed; inset: 0; display: grid; grid-template-rows: 1fr auto; }
    video { width: 100%; height: 100%; background: #000; display: block; }
    .state { position: absolute; inset: 0; display: grid; place-items: center; padding: 24px; text-align: center; background: radial-gradient(circle at center, rgba(0, 200, 240, .12), transparent 40%), #06080d; }
    .state-card { width: min(520px, 100%); }
    .spinner { width: 42px; height: 42px; border: 3px solid rgba(255,255,255,.15); border-top-color: var(--accent); border-radius: 50%; margin: 0 auto 18px; animation: spin .8s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }
    h1 { margin: 0 0 8px; font-size: 20px; line-height: 1.25; font-weight: 800; }
    p { margin: 0; color: var(--muted); font-size: 14px; line-height: 1.45; }
    .bar { display: flex; align-items: center; gap: 10px; padding: 10px; background: rgba(6,8,13,.92); border-top: 1px solid rgba(255,255,255,.08); overflow-x: auto; }
    .source-btn { border: 1px solid rgba(255,255,255,.12); background: var(--panel); color: var(--text); border-radius: 6px; padding: 8px 10px; font-size: 12px; font-weight: 700; cursor: pointer; white-space: nowrap; }
    .source-btn.active { border-color: var(--accent); color: var(--accent); }
    .badge { margin-left: auto; color: var(--muted); font-size: 12px; white-space: nowrap; }
    .hidden { display: none; }
  </style>
</head>
<body>
  <div class="shell">
    <video id="video" controls playsinline preload="metadata"></video>
    <div class="state" id="state">
      <div class="state-card">
        <div class="spinner" id="spinner"></div>
        <h1 id="stateTitle">Finding best stream</h1>
        <p id="stateText">Asking Vexio for one high quality playable source.</p>
      </div>
    </div>
    <div class="bar" id="sourceBar">
      <span class="badge" id="sourceCount">Vexio</span>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/hls.js@1"></script>
  <script>
    const endpoint = {$this->jsonForScript($sourceUrl)};
    const video = document.getElementById('video');
    const state = document.getElementById('state');
    const stateTitle = document.getElementById('stateTitle');
    const stateText = document.getElementById('stateText');
    const spinner = document.getElementById('spinner');
    const sourceBar = document.getElementById('sourceBar');
    const sourceCount = document.getElementById('sourceCount');
    let hls;

    function setState(title, text, loading = false) {
      stateTitle.textContent = title;
      stateText.textContent = text;
      spinner.classList.toggle('hidden', !loading);
      state.classList.remove('hidden');
    }

    function hideState() {
      state.classList.add('hidden');
    }

    function sourceLabel(source) {
      const provider = source.provider?.name || source.provider?.id || 'Source';
      const quality = source.quality || source.type || '';
      return provider + (quality ? ' ' + quality : '');
    }

    function addSubtitles(subtitles) {
      video.querySelectorAll('track').forEach(track => track.remove());
      subtitles
        .filter(subtitle => subtitle?.url && String(subtitle.format || 'vtt').toLowerCase() === 'vtt')
        .slice(0, 12)
        .forEach((subtitle, index) => {
          const track = document.createElement('track');
          track.kind = 'subtitles';
          track.src = subtitle.url;
          track.label = subtitle.label || 'Subtitle';
          track.srclang = (subtitle.language || subtitle.label || 'sub').toString().slice(0, 12).toLowerCase();
          if (index === 0 && /english|eng/i.test(track.label)) {
            track.default = true;
          }
          video.appendChild(track);
        });
    }

    function playSource(source) {
      if (!source?.url) return;
      if (hls) {
        hls.destroy();
        hls = null;
      }

      if (source.type === 'hls' && window.Hls && Hls.isSupported()) {
        hls = new Hls({ enableWorker: true });
        hls.loadSource(source.url);
        hls.attachMedia(video);
      } else {
        video.src = source.url;
      }

      hideState();
      video.play().catch(() => {});
    }

    async function boot() {
      setState('Finding best stream', 'Asking Vexio for one high quality playable source.', true);
      try {
        const response = await fetch(endpoint, { headers: { Accept: 'application/json' } });
        const data = await response.json();
        if (!response.ok || data.error) {
          throw new Error(data.error?.message || 'Vexio stream request failed');
        }

        const sources = Array.isArray(data.sources) ? data.sources.filter(source => source.url) : [];
        if (!sources.length) {
          setState('No playable stream found', 'Vexio responded, but no browser-playable source was available.');
          return;
        }

        addSubtitles(Array.isArray(data.subtitles) ? data.subtitles : []);
        sourceCount.textContent = sourceLabel(sources[0]);
        playSource(sources[0]);
      } catch (error) {
        setState('Vexio is unavailable', error.message || 'Unable to load streams from Vexio.');
      }
    }

    boot();
  </script>
</body>
</html>
HTML;
    }

    private function jsonForScript(string $value): string
    {
        return json_encode($value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?: '""';
    }
}
