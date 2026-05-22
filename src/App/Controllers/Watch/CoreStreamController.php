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
        $server = $this->normalizeServerKey((string) $request->query('server', 'vexio-s1'));

        return $response->html($this->playerHtml($type, (int) $tmdbId, $season, $episode, $title, $server));
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
        $server = $this->normalizeServerKey((string) $request->query('server', 'vexio-s1'));
        $providers = $this->providersForServer($server);

        if (!in_array($type, ['movie', 'tv'], true) || $tmdbId < 1) {
            return $response->error('Invalid Core source request.', 422);
        }

        $coreBaseUrl = $this->coreBaseUrl();
        $pathPrefix = $providers === []
            ? '/v1'
            : '/v1/filtered/' . rawurlencode(implode(',', $providers));
        $path = $type === 'tv'
            ? "{$pathPrefix}/tv/{$tmdbId}/seasons/{$season}/episodes/{$episode}"
            : "{$pathPrefix}/movies/{$tmdbId}";

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
        $proxyOrigin = trim((string) ($_ENV['CINEPRO_CORE_PROXY_PUBLIC_URL'] ?? ''));
        if ($proxyOrigin === '') {
            $proxyOrigin = 'https://proxy.vexio.asia';
        }

        return rtrim($proxyOrigin, '/') . '/api/core/proxy';
    }

    private function normalizeServerKey(string $server): string
    {
        $server = strtolower(trim($server));

        return in_array($server, ['vexio-s1', 'vexio-s2', 'vexio-s3', 'vexio-s4', 'vexio-s5', 'vexio-multi'], true)
            ? $server
            : 'vexio-s1';
    }

    private function providersForServer(string $server): array
    {
        return match ($server) {
            'vexio-s1' => ['02moviedownloader'],
            'vexio-s2' => ['cinesu'],
            'vexio-s3' => ['vidrock'],
            'vexio-s4' => ['videasy'],
            'vexio-s5' => ['vixsrc'],
            default => [],
        };
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
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Range, Accept, Content-Type')
            ->header('Access-Control-Expose-Headers', 'Content-Length, Content-Range, Accept-Ranges')
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
            header('Access-Control-Allow-Origin: *', true);
            header('Access-Control-Allow-Methods: GET, OPTIONS', true);
            header('Access-Control-Allow-Headers: Range, Accept, Content-Type', true);
            header('Access-Control-Expose-Headers: Content-Length, Content-Range, Accept-Ranges', true);

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

    private function playerHtml(string $type, int $tmdbId, int $season, int $episode, string $title, string $server): string
    {
        $title = htmlspecialchars($title !== '' ? $title : 'Vexio', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $sourceUrl = '/api/core/sources?' . http_build_query([
            'type' => $type,
            'tmdbId' => $tmdbId,
            'season' => $season,
            'episode' => $episode,
            'server' => $server,
        ]);
        $serverLabel = htmlspecialchars($server, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return <<<HTML
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{$title}</title>
  <link rel="stylesheet" href="/assets/vendor/videojs/video-js.min.css">
  <style>
    :root { color-scheme: dark; --bg: #05070c; --line: rgba(255,255,255,.12); --text: #f7fbff; --muted: #a5afbf; --cyan: #00c8f0; --red: #e8173f; }
    * { box-sizing: border-box; }
    html, body { margin: 0; width: 100%; height: 100%; background: #000; color: var(--text); font-family: Arial, Helvetica, sans-serif; overflow: hidden; }
    .player-shell { position: fixed; inset: 0; background: #000; overflow: hidden; }
    .video-js { width: 100%; height: 100%; background: #000; }
    .video-js .vjs-big-play-button { left: 50%; top: 50%; transform: translate(-50%, -50%); width: 74px; height: 74px; line-height: 72px; border-radius: 50%; border-color: rgba(0,200,240,.7); background: rgba(5,7,12,.78); }
    .video-js:hover .vjs-big-play-button, .video-js .vjs-big-play-button:focus { background: rgba(0,200,240,.2); border-color: var(--cyan); }
    .video-js .vjs-control-bar { background: linear-gradient(180deg, transparent, rgba(2,5,10,.96)); height: 4.2em; padding: 0 8px 6px; }
    .video-js .vjs-slider { background: rgba(255,255,255,.2); }
    .video-js .vjs-play-progress, .video-js .vjs-volume-level { background: var(--cyan); }
    .video-js .vjs-load-progress { background: rgba(255,255,255,.25); }
    .video-js .vjs-menu-button-popup .vjs-menu { width: 16em; left: -7em; }
    .topbar { position: absolute; z-index: 4; top: 0; left: 0; right: 0; display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 14px 16px; pointer-events: none; background: linear-gradient(180deg, rgba(0,0,0,.62), transparent); }
    .brand { display: inline-flex; align-items: center; gap: 10px; min-width: 0; }
    .brand img { width: 32px; height: 32px; object-fit: contain; border-radius: 7px; }
    .brand strong { font-size: 13px; letter-spacing: 0; white-space: nowrap; }
    .brand span { color: var(--cyan); }
    .meta { min-width: 0; color: var(--muted); font-size: 12px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .watermark { position: absolute; z-index: 4; left: 14px; bottom: 72px; display: inline-flex; align-items: center; gap: 8px; max-width: calc(100% - 28px); padding: 7px 9px; border: 1px solid rgba(255,255,255,.13); border-radius: 6px; background: rgba(5,7,12,.42); opacity: .72; pointer-events: none; }
    .watermark img { width: 24px; height: 24px; object-fit: contain; flex: 0 0 auto; }
    .watermark span { font-size: 11px; font-weight: 800; letter-spacing: 0; white-space: nowrap; }
    .state { position: absolute; inset: 0; display: grid; place-items: center; padding: 24px; text-align: center; background: radial-gradient(circle at center, rgba(0, 200, 240, .15), transparent 40%), rgba(5,7,12,.96); }
    .state { z-index: 5; }
    .state-card { width: min(560px, 100%); }
    .spinner { width: 46px; height: 46px; border: 3px solid rgba(255,255,255,.14); border-top-color: var(--cyan); border-radius: 50%; margin: 0 auto 18px; animation: spin .8s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }
    h1 { margin: 0 0 8px; font-size: 21px; line-height: 1.25; font-weight: 900; letter-spacing: 0; }
    p { margin: 0; color: var(--muted); font-size: 14px; line-height: 1.45; }
    .scan-line { margin: 18px auto 0; display: inline-flex; align-items: center; gap: 8px; color: #fff; font-size: 13px; font-weight: 800; }
    .scan-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--cyan); box-shadow: 0 0 18px var(--cyan); }
    .provider-list { margin: 16px auto 0; display: flex; flex-wrap: wrap; gap: 8px; justify-content: center; max-width: 480px; }
    .provider-pill { border: 1px solid rgba(255,255,255,.12); border-radius: 6px; padding: 7px 9px; color: var(--muted); background: rgba(255,255,255,.04); font-size: 12px; }
    .provider-pill.active { color: var(--cyan); border-color: rgba(0,200,240,.45); background: rgba(0,200,240,.1); }
    .provider-pill.done { color: #fff; border-color: rgba(255,255,255,.18); }
    .hidden { display: none; }
    @media (max-width: 700px) {
      .topbar { padding: 10px; }
      .brand img { width: 28px; height: 28px; }
      .brand strong { font-size: 12px; }
      .meta { font-size: 11px; }
      .watermark { left: 10px; bottom: 64px; padding: 6px 8px; }
      .watermark img { width: 20px; height: 20px; }
      .video-js .vjs-control-bar { height: 4.8em; }
    }
  </style>
</head>
<body>
  <div class="player-shell" id="playerShell">
    <video id="vexioVideo" class="video-js vjs-default-skin vjs-big-play-centered" controls playsinline preload="metadata" crossorigin="anonymous"></video>
    <div class="topbar">
      <div class="brand">
        <img src="/favicon.png" alt="">
        <strong>VEXIO<span>HD</span></strong>
      </div>
      <div class="meta" id="sourceCount">Preparing {$serverLabel}</div>
    </div>
    <div class="watermark">
      <img src="/favicon.png" alt="">
      <span>VEXIO</span>
    </div>
    <div class="state" id="state">
      <div class="state-card">
        <div class="spinner" id="spinner"></div>
        <h1 id="stateTitle">Finding best stream</h1>
        <p id="stateText">Asking {$serverLabel} for one high quality playable source.</p>
        <div class="scan-line"><span class="scan-dot"></span><span id="scanText">Scanning provider</span></div>
        <div class="provider-list" id="providerList"></div>
      </div>
    </div>
  </div>
  <script src="/assets/vendor/videojs/video.min.js"></script>
  <script>
    const endpoint = {$this->jsonForScript($sourceUrl)};
    const serverKey = {$this->jsonForScript($server)};
    const providerGroups = {
      'vexio-s1': ['02MovieDownloader'],
      'vexio-s2': ['CineSu'],
      'vexio-s3': ['VidRock'],
      'vexio-s4': ['Videasy'],
      'vexio-s5': ['VixSrc'],
      'vexio-multi': ['02MovieDownloader', 'CineSu', 'VidRock', 'Videasy', 'VixSrc', 'VidApi']
    };
    const providerNames = providerGroups[serverKey] || providerGroups['vexio-s1'];
    const state = document.getElementById('state');
    const stateTitle = document.getElementById('stateTitle');
    const stateText = document.getElementById('stateText');
    const spinner = document.getElementById('spinner');
    const sourceCount = document.getElementById('sourceCount');
    const scanText = document.getElementById('scanText');
    const providerList = document.getElementById('providerList');
    const player = videojs('vexioVideo', {
      autoplay: false,
      controls: true,
      fluid: false,
      fill: true,
      responsive: true,
      liveui: true,
      html5: {
        vhs: {
          overrideNative: true,
          enableLowInitialPlaylist: true
        }
      },
      controlBar: {
        pictureInPictureToggle: true,
        volumePanel: { inline: false }
      }
    });
    let scanTimer;
    let scanIndex = 0;

    function setState(title, text, loading = false) {
      stateTitle.textContent = title;
      stateText.textContent = text;
      spinner.classList.toggle('hidden', !loading);
      state.classList.remove('hidden');
    }

    function hideState() {
      stopScanning(true);
      state.classList.add('hidden');
    }

    function renderProviders() {
      providerList.innerHTML = '';
      providerNames.forEach((name, index) => {
        const pill = document.createElement('span');
        pill.className = 'provider-pill';
        if (index < scanIndex - 1) pill.classList.add('done');
        if (index === (scanIndex - 1) % providerNames.length) pill.classList.add('active');
        pill.textContent = name;
        providerList.appendChild(pill);
      });
    }

    function updateScan() {
      const name = providerNames[scanIndex % providerNames.length];
      scanText.textContent = 'Scanning provider ' + name + ' ...';
      scanIndex += 1;
      renderProviders();
    }

    function startScanning() {
      scanIndex = 0;
      updateScan();
      scanTimer = window.setInterval(updateScan, 900);
    }

    function stopScanning(done = false) {
      if (scanTimer) {
        window.clearInterval(scanTimer);
        scanTimer = null;
      }
      if (done) scanText.textContent = 'Best playable source selected';
    }

    function sourceLabel(source) {
      const provider = source.provider?.name || source.provider?.id || 'Source';
      const quality = source.quality || source.type || '';
      return provider + (quality ? ' ' + quality : '');
    }

    function subtitleLanguage(subtitle) {
      const label = String(subtitle.label || subtitle.language || '').toLowerCase();
      if (label.includes('english') || label === 'eng') return 'en';
      if (label.includes('spanish') || label === 'spa') return 'es';
      if (label.includes('french') || label === 'fre') return 'fr';
      if (label.includes('german') || label === 'ger') return 'de';
      return label.replace(/[^a-z-]/g, '').slice(0, 12) || 'sub';
    }

    function addSubtitles(subtitles) {
      const usable = subtitles
        .filter(subtitle => subtitle?.url && String(subtitle.format || 'vtt').toLowerCase() === 'vtt')
        .slice(0, 14);

      usable.forEach((subtitle, index) => {
        player.addRemoteTextTrack({
          kind: 'subtitles',
          src: subtitle.url,
          label: subtitle.label || 'Subtitle',
          srclang: subtitleLanguage(subtitle),
          default: index === 0 && /english|eng/i.test(String(subtitle.label || subtitle.language || ''))
        }, false);
      });
    }

    function playSource(source) {
      if (!source?.url) return;
      const sourceType = String(source.type || '').toLowerCase();
      const mimeType = sourceType === 'mp4' || String(source.url).toLowerCase().includes('.mp4')
        ? 'video/mp4'
        : 'application/x-mpegURL';
      player.src({ src: source.url, type: mimeType });
      hideState();
      player.ready(() => player.play()?.catch(() => {}));
    }

    async function boot() {
      startScanning();
      setState('Finding best stream', 'Asking ' + serverKey + ' for one high quality playable source.', true);
      try {
        const response = await fetch(endpoint, { headers: { Accept: 'application/json' } });
        const data = await response.json();
        if (!response.ok || data.error) {
          throw new Error(data.error?.message || 'Vexio stream request failed');
        }

        const sources = Array.isArray(data.sources) ? data.sources.filter(source => source.url) : [];
        if (!sources.length) {
          stopScanning();
          setState('No playable stream found', 'Vexio responded, but no browser-playable source was available.');
          return;
        }

        addSubtitles(Array.isArray(data.subtitles) ? data.subtitles : []);
        sourceCount.textContent = serverKey + ' - ' + sourceLabel(sources[0]);
        playSource(sources[0]);
      } catch (error) {
        stopScanning();
        setState('Vexio is unavailable', error.message || 'Unable to load streams from Vexio.');
      }
    }

    document.addEventListener('keydown', event => {
      if (event.key === ' ') {
        event.preventDefault();
        player.paused() ? player.play()?.catch(() => {}) : player.pause();
      }
      if (event.key.toLowerCase() === 'f') player.requestFullscreen();
      if (event.key.toLowerCase() === 'm') player.muted(!player.muted());
    });

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
