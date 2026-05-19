<?php

declare(strict_types=1);

function inspect(mixed $value): void
{
    echo '<pre>';
    var_dump($value);
    echo '</pre>';
}

function inspectAndDie(mixed $value): void
{
    echo '<pre>';
    var_dump($value);
    echo '</pre>';
    die;
}

function escape(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirectTo(string $path, int $status = 302): void
{
    $path = safeRedirectPath($path);
    header("Location: {$path}", true, $status);
    exit;
}

function safeRedirectPath(string $path, string $fallback = '/'): string
{
    $path = trim($path);

    if ($path === '' || preg_match('/[\r\n]/', $path) || str_starts_with($path, '//')) {
        return $fallback;
    }

    if (!preg_match('#^https?://#i', $path)) {
        return '/' . ltrim($path, '/');
    }

    $targetHost = strtolower((string) parse_url($path, PHP_URL_HOST));
    $currentHost = strtolower(strtok((string) ($_SERVER['HTTP_HOST'] ?? ''), ':') ?: '');
    $appHost = strtolower((string) parse_url((string) ($_ENV['APP_URL'] ?? ''), PHP_URL_HOST));
    $allowedHosts = array_filter([$currentHost, $appHost]);

    if ($targetHost === '' || !in_array($targetHost, $allowedHosts, true)) {
        return $fallback;
    }

    $targetPath = parse_url($path, PHP_URL_PATH) ?: '/';
    $query = parse_url($path, PHP_URL_QUERY);

    return $targetPath . ($query !== null && $query !== '' ? '?' . $query : '');
}

function setFlash(string $key, string $message, string $type = 'success'): void
{
    $_SESSION['flash'][$key] = [
        'message' => $message,
        'type' => $type,
    ];
}

function getAllFlashes(): array
{
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);

    return $flashes;
}

function url(string $path = ''): string
{
    $baseUrl = $_ENV['APP_URL'] ?? (
        ($_SERVER['REQUEST_SCHEME'] ?? 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')
    );

    return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return '/' . ltrim($path, '/');
}

function isActive(string $path): string
{
    $current = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/');
    $target = trim($path, '/');

    return $current === $target ? 'active' : '';
}
 