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
    :root { color-scheme: dark; --bg: #05070c; --panel: #0b111b; --panel2: rgba(9, 14, 23, .82); --line: rgba(255,255,255,.12); --text: #f7fbff; --muted: #a5afbf; --cyan: #00c8f0; --red: #e8173f; --gold: #ffc340; }
    * { box-sizing: border-box; }
    body { margin: 0; min-height: 100vh; background: #000; color: var(--text); font-family: Arial, Helvetica, sans-serif; overflow: hidden; }
    button, select, input { font: inherit; }
    .player { position: fixed; inset: 0; background: #000; overflow: hidden; }
    video { width: 100%; height: 100%; background: #000; display: block; }
    .shade { pointer-events: none; position: absolute; inset: 0; background: linear-gradient(180deg, rgba(0,0,0,.52), transparent 22%, transparent 58%, rgba(0,0,0,.82)); opacity: 0; transition: opacity .2s ease; }
    .player.show-ui .shade, .player.paused .shade { opacity: 1; }
    .topbar { position: absolute; top: 0; left: 0; right: 0; display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 16px 18px; opacity: 0; transition: opacity .2s ease; pointer-events: none; }
    .player.show-ui .topbar, .player.paused .topbar { opacity: 1; pointer-events: auto; }
    .brand { display: inline-flex; align-items: center; gap: 10px; min-width: 0; }
    .brand img { width: 34px; height: 34px; object-fit: contain; border-radius: 7px; }
    .brand strong { font-size: 14px; letter-spacing: 0; }
    .brand span { color: var(--cyan); }
    .meta { min-width: 0; color: var(--muted); font-size: 12px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .watermark { position: absolute; right: 18px; top: 72px; display: inline-flex; align-items: center; gap: 8px; padding: 8px 10px; border: 1px solid rgba(255,255,255,.13); border-radius: 6px; background: rgba(5,7,12,.38); opacity: .72; pointer-events: none; }
    .watermark img { width: 24px; height: 24px; object-fit: contain; }
    .watermark span { font-size: 11px; font-weight: 800; letter-spacing: 0; }
    .center-play { position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); width: 74px; height: 74px; border: 1px solid rgba(255,255,255,.18); border-radius: 50%; background: rgba(5,7,12,.72); color: #fff; display: grid; place-items: center; font-size: 13px; font-weight: 800; cursor: pointer; opacity: 0; transition: opacity .2s ease, transform .2s ease; }
    .player.paused .center-play, .player.show-ui .center-play:focus-visible { opacity: 1; }
    .center-play:hover { transform: translate(-50%, -50%) scale(1.04); border-color: var(--cyan); }
    .controls { position: absolute; left: 0; right: 0; bottom: 0; padding: 12px 14px 14px; background: linear-gradient(180deg, transparent, rgba(3,5,10,.94)); opacity: 0; transition: opacity .2s ease; pointer-events: none; }
    .player.show-ui .controls, .player.paused .controls { opacity: 1; pointer-events: auto; }
    .timeline { width: 100%; height: 18px; display: flex; align-items: center; }
    .timeline input { width: 100%; accent-color: var(--cyan); cursor: pointer; }
    .control-row { display: grid; grid-template-columns: auto 1fr auto; align-items: center; gap: 12px; }
    .cluster { display: flex; align-items: center; gap: 8px; min-width: 0; }
    .icon-btn { min-width: 42px; height: 38px; padding: 0 10px; border: 1px solid var(--line); border-radius: 6px; background: rgba(15,22,34,.86); color: #fff; display: grid; place-items: center; cursor: pointer; font-size: 12px; font-weight: 800; }
    .icon-btn:hover, .icon-btn:focus-visible { border-color: var(--cyan); color: var(--cyan); outline: none; }
    .time { color: var(--muted); font-size: 12px; white-space: nowrap; }
    .volume { width: 96px; accent-color: var(--cyan); }
    .select { min-width: 132px; max-width: 210px; height: 38px; border: 1px solid var(--line); border-radius: 6px; background: rgba(15,22,34,.92); color: #fff; padding: 0 8px; }
    .state { position: absolute; inset: 0; display: grid; place-items: center; padding: 24px; text-align: center; background: radial-gradient(circle at center, rgba(0, 200, 240, .15), transparent 40%), rgba(5,7,12,.96); }
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
      .watermark { right: 10px; top: 62px; }
      .topbar { padding: 12px; }
      .controls { padding: 10px; }
      .control-row { grid-template-columns: 1fr; gap: 10px; }
      .cluster { justify-content: center; flex-wrap: wrap; }
      .volume { width: 82px; }
      .select { flex: 1; min-width: 112px; }
    }
  </style>
</head>
<body>
  <div class="player paused show-ui" id="player">
    <video id="video" playsinline preload="metadata"></video>
    <div class="shade"></div>
    <div class="topbar">
      <div class="brand">
        <img src="/favicon.png" alt="">
        <strong>VEXIO<span>HD</span></strong>
      </div>
      <div class="meta" id="sourceCount">Preparing Vexio stream</div>
    </div>
    <div class="watermark">
      <img src="/favicon.png" alt="">
      <span>VEXIO</span>
    </div>
    <button class="center-play" id="centerPlay" type="button" aria-label="Play or pause">Play</button>
    <div class="state" id="state">
      <div class="state-card">
        <div class="spinner" id="spinner"></div>
        <h1 id="stateTitle">Finding best stream</h1>
        <p id="stateText">Asking Vexio for one high quality playable source.</p>
        <div class="scan-line"><span class="scan-dot"></span><span id="scanText">Scanning provider 02MovieDownloader</span></div>
        <div class="provider-list" id="providerList"></div>
      </div>
    </div>
    <div class="controls" id="controls">
      <div class="timeline">
        <input id="seek" type="range" min="0" max="1000" value="0" aria-label="Seek">
      </div>
      <div class="control-row">
        <div class="cluster">
          <button class="icon-btn" id="playBtn" type="button" aria-label="Play or pause">Play</button>
          <button class="icon-btn" id="muteBtn" type="button" aria-label="Mute">Vol</button>
          <input class="volume" id="volume" type="range" min="0" max="1" step="0.01" value="1" aria-label="Volume">
          <span class="time" id="timeText">0:00 / 0:00</span>
        </div>
        <div></div>
        <div class="cluster">
          <select class="select" id="subtitleSelect" aria-label="Subtitles"><option value="">English</option></select>
          <button class="icon-btn" id="fullscreenBtn" type="button" aria-label="Fullscreen">Full</button>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/hls.js@1"></script>
  <script>
    const endpoint = {$this->jsonForScript($sourceUrl)};
    const providerNames = ['02MovieDownloader', 'CineSu', 'VidRock', 'Videasy', 'VixSrc', 'VidApi'];
    const player = document.getElementById('player');
    const video = document.getElementById('video');
    const state = document.getElementById('state');
    const stateTitle = document.getElementById('stateTitle');
    const stateText = document.getElementById('stateText');
    const spinner = document.getElementById('spinner');
    const sourceCount = document.getElementById('sourceCount');
    const scanText = document.getElementById('scanText');
    const providerList = document.getElementById('providerList');
    const centerPlay = document.getElementById('centerPlay');
    const playBtn = document.getElementById('playBtn');
    const muteBtn = document.getElementById('muteBtn');
    const fullscreenBtn = document.getElementById('fullscreenBtn');
    const seek = document.getElementById('seek');
    const volume = document.getElementById('volume');
    const timeText = document.getElementById('timeText');
    const subtitleSelect = document.getElementById('subtitleSelect');
    let hls;
    let uiTimer;
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

    function formatTime(value) {
      if (!Number.isFinite(value) || value < 0) return '0:00';
      const total = Math.floor(value);
      const hours = Math.floor(total / 3600);
      const minutes = Math.floor((total % 3600) / 60);
      const seconds = String(total % 60).padStart(2, '0');
      return hours > 0 ? hours + ':' + String(minutes).padStart(2, '0') + ':' + seconds : minutes + ':' + seconds;
    }

    function showUi() {
      player.classList.add('show-ui');
      window.clearTimeout(uiTimer);
      if (!video.paused) uiTimer = window.setTimeout(() => player.classList.remove('show-ui'), 3000);
    }

    function updatePlayState() {
      player.classList.toggle('paused', video.paused);
      playBtn.textContent = video.paused ? 'Play' : 'Pause';
      centerPlay.textContent = video.paused ? 'Play' : 'Pause';
      showUi();
    }

    function togglePlay() {
      if (video.paused) {
        video.play().catch(() => {});
      } else {
        video.pause();
      }
    }

    function updateTime() {
      const duration = video.duration || 0;
      const current = video.currentTime || 0;
      seek.value = duration ? String(Math.round((current / duration) * 1000)) : '0';
      timeText.textContent = formatTime(current) + ' / ' + formatTime(duration);
    }

    function setSubtitle(index) {
      Array.from(video.textTracks).forEach((track, trackIndex) => {
        track.mode = trackIndex === index ? 'showing' : 'disabled';
      });
      subtitleSelect.value = String(index);
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
      video.querySelectorAll('track').forEach(track => track.remove());
      subtitleSelect.innerHTML = '';

      const usable = subtitles
        .filter(subtitle => subtitle?.url && String(subtitle.format || 'vtt').toLowerCase() === 'vtt')
        .slice(0, 14);

      if (!usable.length) {
        const option = document.createElement('option');
        option.value = '-1';
        option.textContent = 'No subtitles';
        subtitleSelect.appendChild(option);
        subtitleSelect.disabled = true;
        return;
      }

      subtitleSelect.disabled = false;
      usable.forEach((subtitle, index) => {
        const track = document.createElement('track');
        track.kind = 'subtitles';
        track.src = subtitle.url;
        track.label = subtitle.label || 'Subtitle';
        track.srclang = subtitleLanguage(subtitle);
        track.mode = 'disabled';
        video.appendChild(track);

        const option = document.createElement('option');
        option.value = String(index);
        option.textContent = track.label;
        subtitleSelect.appendChild(option);
      });

      const englishIndex = usable.findIndex(subtitle => /english|eng/i.test(String(subtitle.label || subtitle.language || '')));
      window.setTimeout(() => setSubtitle(englishIndex >= 0 ? englishIndex : 0), 250);
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
        hls.on(Hls.Events.MANIFEST_PARSED, () => video.play().catch(() => {}));
      } else {
        video.src = source.url;
        video.play().catch(() => {});
      }

      hideState();
      updatePlayState();
    }

    async function boot() {
      startScanning();
      setState('Finding best stream', 'Asking Vexio for one high quality playable source.', true);
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
        sourceCount.textContent = sourceLabel(sources[0]);
        playSource(sources[0]);
      } catch (error) {
        stopScanning();
        setState('Vexio is unavailable', error.message || 'Unable to load streams from Vexio.');
      }
    }

    playBtn.addEventListener('click', togglePlay);
    centerPlay.addEventListener('click', togglePlay);
    video.addEventListener('click', togglePlay);
    video.addEventListener('play', updatePlayState);
    video.addEventListener('pause', updatePlayState);
    video.addEventListener('timeupdate', updateTime);
    video.addEventListener('loadedmetadata', updateTime);
    video.addEventListener('durationchange', updateTime);
    player.addEventListener('mousemove', showUi);
    player.addEventListener('touchstart', showUi, { passive: true });
    seek.addEventListener('input', () => {
      if (video.duration) video.currentTime = (Number(seek.value) / 1000) * video.duration;
    });
    volume.addEventListener('input', () => {
      video.volume = Number(volume.value);
      video.muted = video.volume === 0;
      muteBtn.textContent = video.muted ? 'Mute' : 'Vol';
    });
    muteBtn.addEventListener('click', () => {
      video.muted = !video.muted;
      muteBtn.textContent = video.muted ? 'Mute' : 'Vol';
    });
    subtitleSelect.addEventListener('change', () => setSubtitle(Number(subtitleSelect.value)));
    fullscreenBtn.addEventListener('click', () => {
      if (document.fullscreenElement) {
        document.exitFullscreen().catch(() => {});
      } else {
        player.requestFullscreen().catch(() => {});
      }
    });
    document.addEventListener('keydown', event => {
      if (event.key === ' ') {
        event.preventDefault();
        togglePlay();
      }
      if (event.key.toLowerCase() === 'f') fullscreenBtn.click();
      if (event.key.toLowerCase() === 'm') muteBtn.click();
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
