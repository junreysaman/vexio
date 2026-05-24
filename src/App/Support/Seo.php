<?php

declare(strict_types=1);

namespace App\Support;

final class Seo
{
    public const DEFAULT_DESCRIPTION = 'Stream movies, TV shows, and anime on VEXIO. Browse trending titles, explore genres, and discover watch pages with clean metadata and curated recommendations.';
    public const DEFAULT_IMAGE = '/brand/vexio-logo-primary-1600x480.png';

    public static function origin(): string
    {
        $appUrl = trim((string) ($_ENV['APP_URL'] ?? ''));
        $appHost = strtolower((string) parse_url($appUrl, PHP_URL_HOST));

        if ($appUrl !== '' && $appHost !== '' && !self::isLocalOrPrivateHost($appHost)) {
            return rtrim($appUrl, '/');
        }

        $host = trim((string) ($_SERVER['HTTP_HOST'] ?? ''));
        if ($host === '' || preg_match('/[\r\n]/', $host)) {
            return rtrim($appUrl !== '' ? $appUrl : 'http://localhost', '/');
        }

        $proto = strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
        if (!in_array($proto, ['http', 'https'], true)) {
            $proto = ((string) ($_SERVER['HTTPS'] ?? '') !== '' && (string) ($_SERVER['HTTPS'] ?? '') !== 'off')
                ? 'https'
                : 'http';
        }

        return $proto . '://' . $host;
    }

    public static function canonicalUrl(?string $path = null): string
    {
        $path = trim((string) ($path ?? self::currentPath()));

        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        return self::origin() . '/' . ltrim($path !== '' ? $path : '/', '/');
    }

    public static function currentPath(): string
    {
        $path = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);

        return is_string($path) && $path !== '' ? $path : '/';
    }

    public static function absoluteUrl(?string $url): string
    {
        $url = trim((string) $url);
        if ($url === '') {
            $url = self::DEFAULT_IMAGE;
        }

        if (preg_match('#^https?://#i', $url)) {
            return $url;
        }

        if (str_starts_with($url, '//')) {
            return 'https:' . $url;
        }

        return self::canonicalUrl($url);
    }

    public static function description(?string $text, int $limit = 160): string
    {
        $clean = self::cleanText((string) $text);
        if ($clean === '') {
            $clean = self::DEFAULT_DESCRIPTION;
        }

        return self::truncate($clean, $limit);
    }

    public static function truncate(string $text, int $limit = 160): string
    {
        $text = self::cleanText($text);
        if ($limit < 20 || strlen($text) <= $limit) {
            return $text;
        }

        $slice = rtrim(substr($text, 0, $limit - 1));
        $space = strrpos($slice, ' ');
        if ($space !== false && $space > 60) {
            $slice = substr($slice, 0, $space);
        }

        return rtrim($slice, " \t\n\r\0\x0B.,;:") . '...';
    }

    public static function website(string $siteName): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $siteName,
            'url' => self::origin() . '/',
        ];
    }

    public static function organization(string $siteName, ?string $logo = null): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $siteName,
            'url' => self::origin() . '/',
            'logo' => self::absoluteUrl($logo ?: self::DEFAULT_IMAGE),
        ];
    }

    /**
     * @param list<array{name: string, url: string}> $items
     */
    public static function breadcrumb(array $items): array
    {
        $list = [];
        foreach (array_values($items) as $index => $item) {
            $name = trim((string) ($item['name'] ?? ''));
            $url = trim((string) ($item['url'] ?? ''));
            if ($name === '' || $url === '') {
                continue;
            }

            $list[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $name,
                'item' => self::canonicalUrl($url),
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $list,
        ];
    }

    public static function jsonLd(array $data): string
    {
        return (string) json_encode(
            self::withoutEmpty($data),
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
        );
    }

    public static function cleanText(string $text): string
    {
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    }

    private static function withoutEmpty(mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }

        $out = [];
        foreach ($value as $key => $item) {
            if ($item === null || $item === '' || $item === []) {
                continue;
            }

            $out[$key] = self::withoutEmpty($item);
        }

        return $out;
    }

    private static function isLocalOrPrivateHost(string $host): bool
    {
        $host = strtolower(trim($host, '[]'));

        if ($host === 'localhost' || $host === '127.0.0.1' || $host === '::1') {
            return true;
        }

        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
            return false;
        }

        return filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
    }
}
