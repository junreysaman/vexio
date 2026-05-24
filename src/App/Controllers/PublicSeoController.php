<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Config\Paths;
use App\Services\Archive\BrowseService;
use Framework\Http\Request;
use Framework\Http\Response;

/**
 * Crawler-facing plain-text and XML endpoints: robots.txt, sitemap.xml, ads.txt.
 */
final class PublicSeoController
{
    /** @var list<string> */
    private const STATIC_SITEMAP_PATHS = [
        '/',
        '/archive/browse',
        '/archive/trending',
        '/genres',
        '/networks',
        '/archive/genres',
        '/archive/networks',
        '/faq',
        '/contact',
        '/report-issue',
        '/request-title',
        '/privacy-policy',
        '/terms-of-use',
        '/dmca',
        '/advertise',
    ];

    public function __construct(private BrowseService $browse)
    {
    }

    public function robots(Request $request, Response $response): Response
    {
        $origin = $this->canonicalOrigin($request);
        $sitemapLine = 'Sitemap: ' . $this->absoluteUrl($origin, 'sitemap.xml');

        $lines = [
            'User-agent: *',
            'Allow: /',
            '',
            'Disallow: /admin',
            'Disallow: /login',
            'Disallow: /register',
            'Disallow: /logout',
            'Disallow: /api/',
            '',
            $sitemapLine,
            '',
        ];

        return $this->plain($response, implode("\n", $lines));
    }

    public function sitemap(Request $request, Response $response): Response
    {
        $origin = $this->canonicalOrigin($request);
        $body = $this->buildSitemapIndex([
            ['path' => '/sitemap-static.xml'],
            ['path' => '/sitemap-watch.xml'],
            ['path' => '/sitemap-episodes.xml'],
        ], $origin);

        return $this->xml($response, $body);
    }

    public function staticSitemap(Request $request, Response $response): Response
    {
        $body = $this->buildSitemapXml($this->staticEntries(), $this->canonicalOrigin($request));

        return $this->xml($response, $body);
    }

    public function watchSitemap(Request $request, Response $response): Response
    {
        $limit = (int) ($_ENV['SITEMAP_WATCH_MAX_URLS'] ?? $_ENV['SITEMAP_MAX_URLS'] ?? 20000);
        $limit = max(100, min(50000, $limit));
        $body = $this->buildSitemapXml(
            $this->uniqueEntries($this->browse->getPublishedWatchPathsForSitemap($limit)),
            $this->canonicalOrigin($request)
        );

        return $this->xml($response, $body);
    }

    public function episodeSitemap(Request $request, Response $response): Response
    {
        $limit = (int) ($_ENV['SITEMAP_EPISODE_MAX_URLS'] ?? 20000);
        $limit = max(100, min(50000, $limit));
        $body = $this->buildSitemapXml(
            $this->uniqueEntries($this->browse->getPublishedEpisodePathsForSitemap($limit)),
            $this->canonicalOrigin($request)
        );

        return $this->xml($response, $body);
    }

    /**
     * @return list<array{path: string, lastmod: ?string, type?: string}>
     */
    private function staticEntries(): array
    {
        $entries = [];
        foreach (self::STATIC_SITEMAP_PATHS as $path) {
            $entries[] = ['path' => $path, 'lastmod' => null, 'type' => 'static'];
        }

        foreach ($this->browse->getAllGenres() as $genre) {
            $path = (string) ($genre['url'] ?? '');
            if ($path !== '') {
                $entries[] = ['path' => $path, 'lastmod' => null, 'type' => 'taxonomy'];
            }
        }

        return $this->uniqueEntries($entries);
    }

    /**
     * @param list<array{path: string, lastmod: ?string, type?: string}> $entries
     * @return list<array{path: string, lastmod: ?string, type?: string}>
     */
    private function uniqueEntries(array $entries): array
    {
        $seen = [];
        $unique = [];
        foreach ($entries as $entry) {
            $path = (string) ($entry['path'] ?? '');
            if ($path === '' || isset($seen[$path])) {
                continue;
            }
            $seen[$path] = true;
            $unique[] = $entry;
        }

        return $unique;
    }

    public function adsTxt(Request $request, Response $response): Response
    {
        $file = Paths::STORAGE . '/seo/ads.txt';

        if (is_readable($file)) {
            $raw = (string) file_get_contents($file);

            return $this->plain($response, rtrim($raw) . "\n");
        }

        $lines = [];
        $publisher = trim((string) ($_ENV['GOOGLE_ADS_PUBLISHER_ID'] ?? ''));
        if ($publisher !== '') {
            if (!str_starts_with(strtolower($publisher), 'pub-')) {
                $publisher = 'pub-' . $publisher;
            }
            $lines[] = 'google.com, ' . $publisher . ', DIRECT, f08c47fec0942fa0';
        }

        if ($lines === []) {
            $lines = [
                'google.com, pub-4538119672977781, DIRECT, f08c47fec0942fa0',
            ];
        }

        return $this->plain($response, implode("\n", $lines) . "\n");
    }

    private function plain(Response $response, string $body): Response
    {
        return $response
            ->status(200)
            ->header('Content-Type', 'text/plain; charset=UTF-8')
            ->body($body);
    }

    private function xml(Response $response, string $body): Response
    {
        return $response
            ->status(200)
            ->header('Content-Type', 'application/xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=900')
            ->body($body);
    }

    /**
     * @param list<array{path: string, lastmod?: ?string}> $entries
     */
    private function buildSitemapIndex(array $entries, string $origin): string
    {
        $parts = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
        ];

        foreach ($entries as $entry) {
            $path = (string) ($entry['path'] ?? '');
            if ($path === '') {
                continue;
            }

            $parts[] = '  <sitemap>';
            $parts[] = '    <loc>' . $this->escapeXml($this->absoluteUrl($origin, $path)) . '</loc>';
            $lastmod = $entry['lastmod'] ?? null;
            if (is_string($lastmod) && $lastmod !== '') {
                $parts[] = '    <lastmod>' . $this->escapeXml($lastmod) . '</lastmod>';
            }
            $parts[] = '  </sitemap>';
        }

        $parts[] = '</sitemapindex>';

        return implode("\n", $parts) . "\n";
    }

    /**
     * @param list<array{path: string, lastmod: ?string, type?: string}> $entries
     */
    private function buildSitemapXml(array $entries, string $origin): string
    {
        $parts = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
        ];

        foreach ($entries as $entry) {
            $path = (string) ($entry['path'] ?? '');
            if ($path === '') {
                continue;
            }

            $loc = $this->escapeXml($this->absoluteUrl($origin, $path));
            $parts[] = '  <url>';
            $parts[] = '    <loc>' . $loc . '</loc>';

            $lastmod = $entry['lastmod'] ?? null;
            if (is_string($lastmod) && $lastmod !== '') {
                $parts[] = '    <lastmod>' . $this->escapeXml($lastmod) . '</lastmod>';
            }

            $parts[] = '    <changefreq>' . $this->changeFrequency($path, (string) ($entry['type'] ?? '')) . '</changefreq>';
            $parts[] = '    <priority>' . $this->priority($path, (string) ($entry['type'] ?? '')) . '</priority>';
            $parts[] = '  </url>';
        }

        $parts[] = '</urlset>';

        return implode("\n", $parts) . "\n";
    }

    private function escapeXml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function absoluteUrl(string $origin, string $path): string
    {
        return rtrim($origin, '/') . '/' . ltrim($path, '/');
    }

    private function changeFrequency(string $path, string $type): string
    {
        if ($path === '/' || str_starts_with($path, '/archive/')) {
            return 'daily';
        }

        if ($type === 'movie' || $type === 'tv_show' || $type === 'episode') {
            return 'weekly';
        }

        return 'monthly';
    }

    private function priority(string $path, string $type): string
    {
        if ($path === '/') {
            return '1.0';
        }

        if (in_array($path, ['/archive/browse', '/archive/trending'], true)) {
            return '0.9';
        }

        if ($type === 'movie' || $type === 'tv_show') {
            return '0.8';
        }

        if ($type === 'episode') {
            return '0.7';
        }

        return '0.6';
    }

    private function canonicalOrigin(Request $request): string
    {
        $appUrl = trim((string) ($_ENV['APP_URL'] ?? ''));
        $appHost = strtolower((string) parse_url($appUrl, PHP_URL_HOST));

        if ($appUrl !== '' && $appHost !== '' && !$this->isLocalOrPrivateHost($appHost)) {
            return rtrim($appUrl, '/');
        }

        $host = trim((string) $request->header('Host', $request->server('HTTP_HOST', '')));
        if ($host === '' || preg_match('/[\r\n]/', $host)) {
            return rtrim($appUrl !== '' ? $appUrl : 'http://localhost', '/');
        }

        $proto = strtolower((string) $request->header('X-Forwarded-Proto', ''));
        if (!in_array($proto, ['http', 'https'], true)) {
            $proto = ((string) $request->server('HTTPS', '') !== '' && (string) $request->server('HTTPS', '') !== 'off')
                ? 'https'
                : 'http';
        }

        return $proto . '://' . $host;
    }

    private function isLocalOrPrivateHost(string $host): bool
    {
        $host = strtolower(trim($host, '[]'));

        if ($host === 'localhost' || $host === '127.0.0.1' || $host === '::1') {
            return true;
        }

        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
            return false;
        }

        $private = filter_var(
            $host,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );

        return $private === false;
    }
}
