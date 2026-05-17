<?php

declare(strict_types=1);

namespace Framework\Http;

class Response
{
    private int $statusCode = 200;
    private array $headers = [];
    private string $body = '';

    public function status(int $statusCode): self
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;

        return $this;
    }

    public function body(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function html(string $html, int $statusCode = 200): self
    {
        return $this
            ->status($statusCode)
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->body($html);
    }

    public function json(mixed $data, int $statusCode = 200): self
    {
        return $this
            ->status($statusCode)
            ->header('Content-Type', 'application/json; charset=UTF-8')
            ->body(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '{}');
    }

    public function created(mixed $data = null): self
    {
        return $this->json($data ?? ['created' => true], 201);
    }

    public function noContent(): self
    {
        return $this
            ->status(204)
            ->body('');
    }

    public function error(string $message, int $statusCode = 400, array $extra = []): self
    {
        return $this->json([
            'error' => [
                'message' => $message,
                ...$extra,
            ],
        ], $statusCode);
    }

    public function redirect(string $path, int $statusCode = 302): self
    {
        return $this
            ->status($statusCode)
            ->header('Location', $this->safeRedirectPath($path))
            ->body('');
    }

    public function isEmpty(): bool
    {
        return $this->body === '' && $this->headers === [] && $this->statusCode === 200;
    }

    public function send(bool $includeBody = true): void
    {
        if (!headers_sent()) {
            http_response_code($this->statusCode);

            foreach ($this->headers as $name => $value) {
                header("{$name}: {$value}", true);
            }
        }

        if ($includeBody) {
            echo $this->body;
        }
    }

    private function safeRedirectPath(string $path): string
    {
        $path = trim($path);

        if ($path === '' || preg_match('/[\r\n]/', $path) || str_starts_with($path, '//')) {
            return '/';
        }

        if (!preg_match('#^https?://#i', $path)) {
            return '/' . ltrim($path, '/');
        }

        $targetHost = strtolower((string) parse_url($path, PHP_URL_HOST));
        $currentHost = strtolower(strtok((string) ($_SERVER['HTTP_HOST'] ?? ''), ':') ?: '');
        $appHost = strtolower((string) parse_url((string) ($_ENV['APP_URL'] ?? ''), PHP_URL_HOST));
        $allowedHosts = array_filter([$currentHost, $appHost]);

        if ($targetHost === '' || !in_array($targetHost, $allowedHosts, true)) {
            return '/';
        }

        $targetPath = parse_url($path, PHP_URL_PATH) ?: '/';
        $query = parse_url($path, PHP_URL_QUERY);

        return $targetPath . ($query !== null && $query !== '' ? '?' . $query : '');
    }
}
