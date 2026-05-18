<?php

declare(strict_types=1);

namespace App\Support;

class MediaUrl
{
    public static function slugify(string $value): string
    {
        $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $value) ?? ''));
        $slug = trim($slug, '-');

        return $slug !== '' ? $slug : 'untitled';
    }

    public static function itemSlug(array $item): string
    {
        $slug = trim((string) ($item['slug'] ?? ''));

        return $slug !== '' ? $slug : self::slugify((string) ($item['title'] ?? 'Untitled'));
    }

    public static function movie(int $tmdbId, string $slug): string
    {
        return '/watch/movie/' . $tmdbId . '/' . rawurlencode(self::slugify($slug));
    }

    public static function tvShow(int $tmdbId, string $slug): string
    {
        return '/watch/tvshow/' . $tmdbId . '/' . rawurlencode(self::slugify($slug));
    }

    public static function tvEpisode(int $tmdbId, string $slug, int $seasonNumber, int $episodeNumber): string
    {
        return self::tvShow($tmdbId, $slug)
            . '/season/' . max(1, $seasonNumber)
            . '/episode/' . max(1, $episodeNumber);
    }

    public static function watchUrlForItem(array $item, ?array $episode = null): ?string
    {
        $tmdbId = (int) ($item['tmdb_id'] ?? 0);

        if ($tmdbId < 1) {
            return null;
        }

        $slug = self::itemSlug($item);

        if (($item['type'] ?? '') === 'movie') {
            return self::movie($tmdbId, $slug);
        }

        if (($item['type'] ?? '') !== 'tv_show') {
            return null;
        }

        if ($episode !== null) {
            return self::tvEpisode(
                $tmdbId,
                $slug,
                (int) ($episode['season_number'] ?? 1),
                (int) ($episode['episode_number'] ?? 1)
            );
        }

        return self::tvShow($tmdbId, $slug);
    }
}
