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

        if (!self::downloadsImagesEnabled()) {
            if ($remote !== '') {
                return self::fromRemoteUrl($remote, $widths, $primary, $sizes, $role);
            }

            if ($local !== '' && self::isRemoteUrl($local)) {
                return self::fromRemoteUrl($local, $widths, $primary, $sizes, $role);
            }

            if ($role === self::ROLE_BACKDROP) {
                $posterRemote = trim((string) ($row['poster_url'] ?? ''));
                if ($posterRemote !== '') {
                    return self::fromRemoteUrl($posterRemote, $widths, $primary, $sizes, self::ROLE_POSTER);
                }
            }
        }

        if ($local !== '' && self::isRemoteUrl($local)) {
            return self::fromRemoteUrl($local, $widths, $primary, $sizes, $role);
        }

        if ($local !== '' && self::isLocalWebPath($local)) {
            $descriptor = self::fromLocalPath($local, $widths, $primary, $sizes);

            if ($descriptor['src'] !== '') {
                return $descriptor;
            }
        }

        if ($remote !== '') {
            return self::fromRemoteUrl($remote, $widths, $primary, $sizes, $role);
        }

        if ($role === self::ROLE_BACKDROP && $local === '') {
            $posterLocal = trim((string) ($row['poster_image'] ?? ''));
            if ($posterLocal !== '' && self::isRemoteUrl($posterLocal)) {
                return self::fromRemoteUrl($posterLocal, $widths, $primary, $sizes, self::ROLE_POSTER);
            }
            if ($posterLocal !== '' && self::isLocalWebPath($posterLocal)) {
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
    public static function fromRemoteUrl(
        string $url,
        array $widths,
        int $primaryWidth,
        string $sizes,
        string $role = self::ROLE_POSTER
    ): array {
        $canonical = self::canonicalTmdbUrl($url, $role);
        $envSize = self::envSizeToken($role);
        $src = self::tmdbSizedUrl($canonical, $envSize, $role);

        $entries = [];
        if ($envSize !== 'original') {
            foreach ($widths as $width) {
                $token = 'w' . $width;
                $sized = self::tmdbSizedUrl($canonical, $token, $role);
                if ($sized !== '' && $sized !== $src) {
                    $entries[$width] = $sized;
                }
            }

            if (!isset($entries[$primaryWidth])) {
                $entries[$primaryWidth] = $src;
            }
        }

        ksort($entries);

        return [
            'src' => $src !== '' ? $src : $canonical,
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

        $role = (string) ($preset['role'] ?? self::ROLE_POSTER);

        if (self::isLocalWebPath($url)) {
            return self::fromLocalPath($url, $preset['widths'], (int) $preset['primary'], (string) $preset['sizes']);
        }

        return self::fromRemoteUrl($url, $preset['widths'], (int) $preset['primary'], (string) $preset['sizes'], $role);
    }

    public static function srcOnly(array $descriptor): string
    {
        return (string) ($descriptor['src'] ?? '');
    }

    /**
     * Best share/OG image URL for a catalogue row (poster, then backdrop).
     */
    public static function ogImageFromRow(array $row): string
    {
        $poster = self::srcOnly(self::posterFromRow($row, 'detail'));
        if ($poster !== '') {
            return $poster;
        }

        return self::srcOnly(self::backdropFromRow($row, 'heroBackdrop'));
    }

    public static function adminPosterSrc(array $row): string
    {
        return self::srcOnly(self::posterFromRow($row, 'thumb'));
    }

    public static function adminBackdropSrc(array $row): string
    {
        return self::srcOnly(self::backdropFromRow($row, 'thumb'));
    }

    public static function displayPosterUrl(array $row): string
    {
        $url = trim((string) ($row['poster_url'] ?? ''));
        if ($url !== '') {
            return self::canonicalTmdbUrl($url, self::ROLE_POSTER);
        }

        $legacy = trim((string) ($row['poster_image'] ?? ''));
        if ($legacy !== '' && self::isRemoteUrl($legacy)) {
            return self::canonicalTmdbUrl($legacy, self::ROLE_POSTER);
        }

        return '';
    }

    public static function displayBackdropUrl(array $row): string
    {
        $stored = trim((string) ($row['backdrop_image'] ?? ''));
        if ($stored === '') {
            return '';
        }

        if (self::isLocalWebPath($stored) && self::downloadsImagesEnabled()) {
            return $stored;
        }

        if (self::isRemoteUrl($stored)) {
            return self::canonicalTmdbUrl($stored, self::ROLE_BACKDROP);
        }

        return (string) (self::buildTmdbAssetUrl($stored, self::ROLE_BACKDROP) ?? $stored);
    }

    /**
     * @param array{poster_url?: string, poster_image?: string, backdrop_image?: string} $fields
     * @return array{poster_url: ?string, poster_image: ?string, backdrop_image: ?string}
     */
    public static function normalizeStoredImages(array $fields): array
    {
        $posterUrl = trim((string) ($fields['poster_url'] ?? ''));
        $posterImage = trim((string) ($fields['poster_image'] ?? ''));
        $backdrop = trim((string) ($fields['backdrop_image'] ?? ''));

        if ($posterUrl !== '') {
            $posterUrl = self::canonicalTmdbUrl($posterUrl, self::ROLE_POSTER)
                ?: (string) (self::buildTmdbAssetUrl($posterUrl, self::ROLE_POSTER) ?? $posterUrl);
        } elseif ($posterImage !== '' && self::isRemoteUrl($posterImage)) {
            $posterUrl = self::canonicalTmdbUrl($posterImage, self::ROLE_POSTER);
        }

        if ($backdrop !== '') {
            if (self::isLocalWebPath($backdrop) && self::downloadsImagesEnabled()) {
                // keep local WebP path
            } elseif (self::isRemoteUrl($backdrop) || !self::isLocalWebPath($backdrop)) {
                $backdrop = self::canonicalTmdbUrl($backdrop, self::ROLE_BACKDROP)
                    ?: (string) (self::buildTmdbAssetUrl($backdrop, self::ROLE_BACKDROP) ?? $backdrop);
            }
        }

        if (!self::downloadsImagesEnabled()) {
            $posterImage = self::isLocalWebPath($posterImage) ? null : null;
        }

        return [
            'poster_url' => $posterUrl !== '' ? $posterUrl : null,
            'poster_image' => $posterImage !== '' ? $posterImage : null,
            'backdrop_image' => $backdrop !== '' ? $backdrop : null,
        ];
    }

    /**
     * Build a TMDB asset URL from an API path (e.g. /abc.jpg) using env base URLs.
     */
    public static function buildTmdbAssetUrl(?string $path, string $role = self::ROLE_POSTER): ?string
    {
        $path = trim((string) $path);
        if ($path === '') {
            return null;
        }

        if (self::isRemoteUrl($path)) {
            return self::canonicalTmdbUrl($path, $role);
        }

        $base = $role === self::ROLE_BACKDROP ? self::backdropBaseUrl() : self::posterBaseUrl();

        return $base . '/' . ltrim($path, '/');
    }

    public static function posterBaseUrl(): string
    {
        return rtrim((string) ($_ENV['TMDB_IMAGE_BASE_URL'] ?? 'https://image.tmdb.org/t/p/original'), '/');
    }

    public static function backdropBaseUrl(): string
    {
        return rtrim((string) ($_ENV['TMDB_BACKDROP_BASE_URL'] ?? 'https://image.tmdb.org/t/p/original'), '/');
    }

    /** Size segment from env base URL: original, w500, w1280, etc. */
    public static function envSizeToken(string $role = self::ROLE_POSTER): string
    {
        $base = $role === self::ROLE_BACKDROP ? self::backdropBaseUrl() : self::posterBaseUrl();

        if (preg_match('#/t/p/(w\d+|original)$#', $base, $matches)) {
            return (string) $matches[1];
        }

        return 'original';
    }

    /**
     * Normalize a stored TMDB URL or file path to the configured env base for the role.
     */
    public static function canonicalTmdbUrl(string $url, string $role = self::ROLE_POSTER): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }

        if (!self::isRemoteUrl($url)) {
            return (string) (self::buildTmdbAssetUrl($url, $role) ?? '');
        }

        if (!preg_match('#/t/p/(w\d+|original)/(.+)$#', $url, $matches)) {
            return $url;
        }

        $base = $role === self::ROLE_BACKDROP ? self::backdropBaseUrl() : self::posterBaseUrl();
        if (!preg_match('#/t/p/(w\d+|original)$#', $base)) {
            return $url;
        }

        return $base . '/' . $matches[2];
    }

    public static function tmdbSizedUrl(string $url, string $size, string $role = self::ROLE_POSTER): string
    {
        $url = trim(self::canonicalTmdbUrl($url, $role));
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

    public static function downloadsImagesEnabled(): bool
    {
        return filter_var($_ENV['TMDB_DOWNLOAD_IMAGES'] ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    public static function isRemoteUrl(string $value): bool
    {
        $value = trim($value);

        return $value !== ''
            && (str_starts_with($value, 'https://') || str_starts_with($value, 'http://'));
    }

    public static function isLocalWebPath(string $path): bool
    {
        $path = trim($path);

        return $path !== '' && str_starts_with($path, '/') && !self::isRemoteUrl($path);
    }

    private static function normalizeWebPath(string $path): string
    {
        $path = trim($path);

        return self::isLocalWebPath($path) ? $path : '';
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
