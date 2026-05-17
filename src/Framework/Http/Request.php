<?php

declare(strict_types=1);

namespace Framework\Http;

class Request
{
    private ?array $jsonPayload = null;

    public function __construct(
        private string $method,
        private string $path,
        private array $query = [],
        private array $post = [],
        private array $files = [],
        private array $cookies = [],
        private array $server = [],
        private array $headers = [],
        private string $body = ''
    ) {
        $this->method = strtoupper($method);
        $this->path = '/' . ltrim($path, '/');
        $this->headers = array_change_key_case($headers, CASE_LOWER);
    }

    public static function capture(): self
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

        return new self(
            $_SERVER['REQUEST_METHOD'] ?? 'GET',
            $path,
            $_GET,
            $_POST,
            $_FILES,
            $_COOKIE,
            $_SERVER,
            self::headersFromServer($_SERVER),
            (string) file_get_contents('php://input')
        );
    }

    public function method(): string
    {
        return $this->method;
    }

    public function isMethod(string $method): bool
    {
        return $this->method === strtoupper($method);
    }

    public function path(): string
    {
        return $this->path;
    }

    public function query(?string $key = null, mixed $default = null): mixed
    {
        return $this->valueFrom($this->query, $key, $default);
    }

    public function post(?string $key = null, mixed $default = null): mixed
    {
        return $this->valueFrom($this->post, $key, $default);
    }

    public function input(?string $key = null, mixed $default = null): mixed
    {
        $input = [...$this->query, ...$this->post];

        return $this->valueFrom($input, $key, $default);
    }

    public function data(?string $key = null, mixed $default = null): mixed
    {
        $data = $this->isJson()
            ? [...$this->query, ...$this->json()]
            : $this->input();

        return $this->valueFrom($data, $key, $default);
    }

    public function all(): array
    {
        return $this->data();
    }

    public function only(array $keys): array
    {
        return array_intersect_key($this->input(), array_flip($keys));
    }

    public function except(array $keys): array
    {
        return array_diff_key($this->input(), array_flip($keys));
    }

    public function json(?string $key = null, mixed $default = null): mixed
    {
        if ($this->jsonPayload === null) {
            $decoded = json_decode($this->body, true);
            $this->jsonPayload = is_array($decoded) ? $decoded : [];
        }

        return $this->valueFrom($this->jsonPayload, $key, $default);
    }

    public function isJson(): bool
    {
        return str_contains(strtolower((string) $this->header('Content-Type', '')), 'application/json');
    }

    public function wantsJson(): bool
    {
        return str_contains(strtolower((string) $this->header('Accept', '')), 'application/json');
    }

    public function expectsJson(): bool
    {
        return $this->wantsJson() || $this->isJson();
    }

    public function file(?string $key = null, mixed $default = null): mixed
    {
        return $this->valueFrom($this->files, $key, $default);
    }

    public function cookie(?string $key = null, mixed $default = null): mixed
    {
        return $this->valueFrom($this->cookies, $key, $default);
    }

    public function server(?string $key = null, mixed $default = null): mixed
    {
        return $this->valueFrom($this->server, $key, $default);
    }

    public function header(string $name, mixed $default = null): mixed
    {
        return $this->headers[strtolower($name)] ?? $default;
    }

    public function body(): string
    {
        return $this->body;
    }

    public function session(?string $key = null, mixed $default = null): mixed
    {
        return $this->valueFrom($_SESSION ?? [], $key, $default);
    }

    public function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    private function valueFrom(array $source, ?string $key, mixed $default): mixed
    {
        if ($key === null) {
            return $source;
        }

        return $source[$key] ?? $default;
    }

    private static function headersFromServer(array $server): array
    {
        $headers = [];

        foreach ($server as $key => $value) {
            if (!str_starts_with($key, 'HTTP_')) {
                continue;
            }

            $name = str_replace('_', '-', strtolower(substr($key, 5)));
            $headers[$name] = $value;
        }

        if (isset($server['CONTENT_TYPE'])) {
            $headers['content-type'] = $server['CONTENT_TYPE'];
        }

        if (isset($server['CONTENT_LENGTH'])) {
            $headers['content-length'] = $server['CONTENT_LENGTH'];
        }

        return $headers;
    }
}
