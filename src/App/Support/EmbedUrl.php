<?php

declare(strict_types=1);

namespace App\Support;

class EmbedUrl
{
    public static function baseUrl(): string
    {
        $configured = trim((string) ($_ENV['VEXIO_EMBED_URL'] ?? $_ENV['SCRAPER_EMBED_URL'] ?? ''));
        if ($configured !== '') {
            return rtrim($configured, '/');
        }

        $env = strtolower(trim((string) ($_ENV['APP_ENV'] ?? 'production')));
        if (in_array($env, ['local', 'development', 'dev'], true)) {
            return 'http://127.0.0.1:3000';
        }

        return 'https://embed.vexio.asia';
    }

    public static function movie(int $tmdbId): string
    {
        if ($tmdbId < 1) {
            return '';
        }

        return self::baseUrl() . '/?id=' . rawurlencode((string) $tmdbId);
    }

    public static function tv(int $tmdbId, int $season, int $episode): string
    {
        if ($tmdbId < 1 || $season < 1 || $episode < 1) {
            return '';
        }

        return self::baseUrl()
            . '/?id=' . rawurlencode((string) $tmdbId)
            . '&s=' . rawurlencode((string) $season)
            . '&e=' . rawurlencode((string) $episode);
    }
}
