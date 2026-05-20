<?php

declare(strict_types=1);

namespace App\Support;

final class MediaImage
{
    public const ROLE_POSTER = 'poster';
    public const ROLE_BACKDROP = 'backdrop';

    /** @var array<string, array{widths: list<int>, primary: int, sizes: string}> */
    private const CONTEXTS = [
        'card' => [
            'role' => self::ROLE_POSTER,
            'widths' => [342, 500],
            'primary' => 500,
            'sizes' => '(max-width: 480px) 42vw, 160px',
        ],
        'thumb' => [
            'role' => self::ROLE_POSTER,
            'widths' => [185, 342],
            'primary' => 342,
            'sizes' => '62px',
        ],
        'heroPoster' => [
            'role' => self::ROLE_POSTER,
            'widths' => [500, 780],
            'primary' => 780,
            'sizes' => '(max-width: 768px) 42vw, 380px',
        ],
        'heroBackdrop' => [
            'role' => self::ROLE_BACKDROP,
            'widths' => [780, 1280],
            'primary' => 1280,
            'sizes' => '100vw',
        ],
        'spotlight' => [
            'role' => self::ROLE_BACKDROP,
            'widths' => [780, 1280],
            'primary' => 1280,
            'sizes' => '(max-width: 768px) 100vw, 60vw',
        ],
        'detail' => [
            'role' => self::ROLE_POSTER,
            'widths' => [500, 780],
            'primary' => 780,
            'sizes' => '(max-width: 640px) 80vw, 320px',
        ],
        'player' => [
            'role' => self::ROLE_BACKDROP,
            'widths' => [780, 1280],
            'primary' => 1280,
            'sizes' => '100vw',
        ],
        'genre' => [
            'role' => self::ROLE_BACKDROP,
            'widths' => [780, 1280],
            'primary' => 780,
            'sizes' => '160px',
        ],
    ];

    private static ?string $publicPath = null;

    /**
     * @param array<string, mixed> $row
     * @return array{src: string, srcset: string, sizes: string, width: int, height: int}
     */
    public static function posterFromRow(array $row, string $context = 'card'): array
    {
        return self::fromRow($row, self::ROLE_POSTER, $context);
    }

    /**
     * @param array<string, mixed> $row
     * @return array{src: string, srcset: string, sizes: string, width: int, height: int}
     */
    public static function backdropFromRow(array $row, string $context = 'heroBackdrop'): array
    {
        return self::fromRow($row, self::ROLE_BACKDROP, $context);
    }

    /**
     * @param array<string, mixed> $row
     * @return array{src: string, srcset: string, sizes: string, width: int, height: int}
     */
    public static function fromRow(array $row, string $role, string $context = 'card'): array
    {
        $preset = self::CONTEXTS[$context] ?? self::CONTEXTS['card'];
        $role = $preset['role'] ?? $role;
        $widths = $preset['widths'];
        $primary = (int) $preset['primary'];
        $sizes = (string) $preset['sizes'];

        $localKey = $role === self::ROLE_BACKDROP ? 'backdrop_image' : 'poster_image';
        $remoteKey = $role === self::ROLE_BACKDROP ? 'backdrop_url' : 'poster_url';

        $local = trim((string) ($row[$localKey] ?? ''));
        $remote = trim((string) ($row[$remoteKey] ?? ''));

        if ($role === self::ROLE_BACKDROP && $remote === '') {
            $remote = trim((string) ($row['poster_url'] ?? ''));
        }

        if ($local !== '') {
            $descriptor = self::fromLocalPath($local, $widths, $primary, $sizes);

            if ($descriptor['src'] !== '') {
                return $descriptor;
            }
        }

        if ($remote !== '') {
            return self::fromRemoteUrl($remote, $widths, $primary, $sizes);
        }

        if ($role === self::ROLE_BACKDROP && $local === '') {
            $posterLocal = trim((string) ($row['poster_image'] ?? ''));
            if ($posterLocal !== '') {
                return self::fromLocalPath($posterLocal, $widths, $primary, $sizes);
            }
        }

        return self::emptyDescriptor($primary, $sizes);
    }

    /**
     * @param list<int> $widths
     * @return array{src: string, srcset: string, sizes: string, width: int, height: int}
     */
    public static function fromLocalPath(string $path, array $widths, int $primaryWidth, string $sizes): array
    {
        $path = self::normalizeWebPath($path);
        if ($path === '') {
            return self::emptyDescriptor($primaryWidth, $sizes);
        }

        $variants = self::discoverVariants($path);
        $entries = [];

        foreach ($widths as $width) {
            $candidate = self::variantPath($path, $width);
            if (isset($variants[$width]) || self::fileExists($candidate)) {
                $entries[$width] = $candidate;
            }
        }

        if ($entries === [] && self::fileExists($path)) {
            $entries[self::parseWidthFromPath($path) ?: $primaryWidth] = $path;
        }

        if ($entries === []) {
            return self::emptyDescriptor($primaryWidth, $sizes);
        }

        $src = $entries[$primaryWidth] ?? $entries[max(array_keys($entries))];

        return [
            'src' => $src,
            'srcset' => self::buildSrcset($entries),
            'sizes' => $sizes,
            'width' => $primaryWidth,
            'height' => (int) round($primaryWidth * 1.5),
        ];
    }

    /**
     * @param list<int> $widths
     * @return array{src: string, srcset: string, sizes: string, width: int, height: int}
     */
    public static function fromRemoteUrl(string $url, array $widths, int $primaryWidth, string $sizes): array
    {
        $entries = [];
        foreach ($widths as $width) {
            $entries[$width] = self::tmdbSizedUrl($url, 'w' . $width);
        }

        $src = $entries[$primaryWidth] ?? reset($entries) ?: $url;

        return [
            'src' => $src,
            'srcset' => self::buildSrcset($entries),
            'sizes' => $sizes,
            'width' => $primaryWidth,
            'height' => (int) round($primaryWidth * 1.5),
        ];
    }

    /**
     * @return array{src: string, srcset: string, sizes: string, width: int, height: int}
     */
    public static function fromString(string $url, string $context = 'card'): array
    {
        $preset = self::CONTEXTS[$context] ?? self::CONTEXTS['card'];
        $url = trim($url);

        if ($url === '') {
            return self::emptyDescriptor((int) $preset['primary'], (string) $preset['sizes']);
        }

        if (str_starts_with($url, '/uploads/')) {
            return self::fromLocalPath($url, $preset['widths'], (int) $preset['primary'], (string) $preset['sizes']);
        }

        return self::fromRemoteUrl($url, $preset['widths'], (int) $preset['primary'], (string) $preset['sizes']);
    }

    public static function srcOnly(array $descriptor): string
    {
        return (string) ($descriptor['src'] ?? '');
    }

    public static function tmdbSizedUrl(string $url, string $size): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }

        if (preg_match('#/t/p/(w\d+|original)/#', $url)) {
            return (string) preg_replace('#/t/p/(w\d+|original)/#', '/t/p/' . $size . '/', $url, 1);
        }

        return $url;
    }

    public static function variantPath(string $primaryPath, int $width): string
    {
        $primaryPath = self::normalizeWebPath($primaryPath);
        if ($primaryPath === '') {
            return '';
        }

        if (preg_match('/-w\d+\.webp$/i', $primaryPath)) {
            return (string) preg_replace('/-w\d+\.webp$/i', '-w' . $width . '.webp', $primaryPath);
        }

        if (str_ends_with(strtolower($primaryPath), '.webp')) {
            return substr($primaryPath, 0, -5) . '-w' . $width . '.webp';
        }

        return $primaryPath;
    }

    /**
     * @return array<int, string> width => web path
     */
    public static function discoverVariants(string $primaryPath): array
    {
        $primaryPath = self::normalizeWebPath($primaryPath);
        if ($primaryPath === '') {
            return [];
        }

        $absolute = self::absolutePath($primaryPath);
        if ($absolute === null) {
            return [];
        }

        $directory = dirname($absolute);
        $base = pathinfo($absolute, PATHINFO_FILENAME);
        $legacyBase = preg_replace('/-w\d+$/', '', $base) ?: $base;
        $pattern = $directory . DIRECTORY_SEPARATOR . $legacyBase . '-w*.webp';
        $found = [];

        foreach (glob($pattern) ?: [] as $file) {
            if (!is_file($file)) {
                continue;
            }

            $name = pathinfo($file, PATHINFO_FILENAME);
            if (preg_match('/-w(\d+)$/', $name, $matches)) {
                $found[(int) $matches[1]] = self::webPathFromAbsolute($file);
            }
        }

        if ($found === [] && is_file($absolute)) {
            $width = self::parseWidthFromPath($primaryPath);
            $found[$width ?: 0] = $primaryPath;
        }

        ksort($found);

        return $found;
    }

    /**
     * @return list<int>
     */
    public static function posterWidths(): array
    {
        return self::parseEnvWidths('TMDB_POSTER_WIDTHS', [342, 500, 780]);
    }

    /**
     * @return list<int>
     */
    public static function backdropWidths(): array
    {
        return self::parseEnvWidths('TMDB_BACKDROP_WIDTHS', [780, 1280]);
    }

    public static function posterPrimaryWidth(): int
    {
        $widths = self::posterWidths();

        if (in_array(500, $widths, true)) {
            return 500;
        }

        return $widths !== [] ? $widths[array_key_last($widths)] : 500;
    }

    public static function backdropPrimaryWidth(): int
    {
        $widths = self::backdropWidths();

        if (in_array(1280, $widths, true)) {
            return 1280;
        }

        return $widths !== [] ? $widths[array_key_last($widths)] : 1280;
    }

    public static function qualityForWidth(int $width, string $role = self::ROLE_POSTER): int
    {
        if ($role === self::ROLE_BACKDROP) {
            if ($width >= 1280) {
                return self::envInt('TMDB_WEBP_QUALITY_HERO', 88);
            }

            return self::envInt('TMDB_WEBP_QUALITY_CARD', 85);
        }

        if ($width >= 780) {
            return self::envInt('TMDB_WEBP_QUALITY_HERO', 88);
        }

        if ($width >= 500) {
            return self::envInt('TMDB_WEBP_QUALITY_CARD', 85);
        }

        return self::envInt('TMDB_WEBP_QUALITY_THUMB', 82);
    }

    public static function needsVariantRegeneration(string $primaryPath, array $expectedWidths): bool
    {
        $variants = self::discoverVariants($primaryPath);
        $legacy = !preg_match('/-w\d+\.webp$/i', $primaryPath);

        if ($legacy) {
            return true;
        }

        foreach ($expectedWidths as $width) {
            if (!isset($variants[$width])) {
                return true;
            }
        }

        return false;
    }

    public static function deleteVariants(?string $primaryPath): void
    {
        $primaryPath = self::normalizeWebPath((string) $primaryPath);
        if ($primaryPath === '') {
            return;
        }

        $absolute = self::absolutePath($primaryPath);
        if ($absolute === null) {
            return;
        }

        $paths = [ $absolute ];
        $directory = dirname($absolute);
        $legacyBase = preg_replace('/-w\d+$/', '', pathinfo($absolute, PATHINFO_FILENAME)) ?: pathinfo($absolute, PATHINFO_FILENAME);
        foreach (glob($directory . DIRECTORY_SEPARATOR . $legacyBase . '*.webp') ?: [] as $file) {
            $paths[] = $file;
        }

        foreach (array_unique($paths) as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }

    public static function publicRoot(): string
    {
        if (self::$publicPath === null) {
            self::$publicPath = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'public';
        }

        return self::$publicPath;
    }

    /**
     * @param array<int, string> $entries
     */
    private static function buildSrcset(array $entries): string
    {
        if (count($entries) < 2) {
            return '';
        }

        $parts = [];
        foreach ($entries as $width => $url) {
            if ($width < 1 || $url === '') {
                continue;
            }
            $parts[] = $url . ' ' . $width . 'w';
        }

        return implode(', ', $parts);
    }

    /**
     * @return array{src: string, srcset: string, sizes: string, width: int, height: int}
     */
    private static function emptyDescriptor(int $primaryWidth, string $sizes): array
    {
        return [
            'src' => '',
            'srcset' => '',
            'sizes' => $sizes,
            'width' => $primaryWidth,
            'height' => (int) round($primaryWidth * 1.5),
        ];
    }

    private static function normalizeWebPath(string $path): string
    {
        $path = trim($path);

        return $path !== '' && str_starts_with($path, '/') ? $path : '';
    }

    private static function fileExists(string $webPath): bool
    {
        $absolute = self::absolutePath($webPath);

        return $absolute !== null && is_file($absolute);
    }

    private static function absolutePath(string $webPath): ?string
    {
        $webPath = self::normalizeWebPath($webPath);
        if ($webPath === '') {
            return null;
        }

        $absolute = realpath(self::publicRoot() . str_replace('/', DIRECTORY_SEPARATOR, $webPath));
        $publicRoot = realpath(self::publicRoot());

        if (!$absolute || !$publicRoot || !str_starts_with($absolute, $publicRoot)) {
            return null;
        }

        return $absolute;
    }

    private static function webPathFromAbsolute(string $absolute): string
    {
        $publicRoot = realpath(self::publicRoot()) ?: self::publicRoot();
        $relative = ltrim(str_replace('\\', '/', substr($absolute, strlen($publicRoot))), '/');

        return '/' . $relative;
    }

    private static function parseWidthFromPath(string $path): int
    {
        if (preg_match('/-w(\d+)\.webp$/i', $path, $matches)) {
            return (int) $matches[1];
        }

        return 0;
    }

    /**
     * @return list<int>
     */
    private static function parseEnvWidths(string $key, array $default): array
    {
        $raw = trim((string) ($_ENV[$key] ?? ''));
        if ($raw === '') {
            return $default;
        }

        $widths = array_values(array_unique(array_filter(array_map(
            static fn (string $part): int => max(1, (int) trim($part)),
            explode(',', $raw)
        ))));
        sort($widths);

        return $widths !== [] ? $widths : $default;
    }

    private static function envInt(string $key, int $default): int
    {
        $value = (int) ($_ENV[$key] ?? $default);

        return max(40, min(92, $value));
    }
}
