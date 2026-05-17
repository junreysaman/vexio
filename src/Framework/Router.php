<?php

declare(strict_types=1);

namespace Framework;

use App\Middleware\SessionMiddleware;
use ErrorException;
use Framework\Http\Request;
use Framework\Http\Response;
use LogicException;
use Throwable;

class Router
{
    private array $routes = [];
    private array $middlewares = [];
    private array $errorHandler = [];

    public function add(string $method, string $path, array $controller): void
    {
        $path = $this->normalizePath($path);

        $this->routes[] = [
            'path' => $path,
            'pattern' => $this->convertRouteToRegex($path),
            'method' => strtoupper($method),
            'controller' => $controller,
            'middlewares' => [],
        ];
    }

    public function dispatch(
        string $path,
        string $method,
        ?Container $container = null,
        ?Request $request = null,
        ?Response $response = null
    ): void {
        $path = $this->normalizePath($path);
        $method = strtoupper($method);
        $routeMethod = $method === 'HEAD' ? 'GET' : $method;
        $request ??= Request::capture();
        $response ??= new Response();

        try {
            foreach ($this->routes as $route) {
                if ($route['method'] !== $routeMethod) {
                    continue;
                }

                if (!preg_match($route['pattern'], $path, $matches)) {
                    continue;
                }

                array_shift($matches);

                [$class, $function] = $route['controller'];
                $action = function () use ($container, $class, $function, $matches, $request, $response) {
                    $controllerInstance = $container
                        ? $container->get($class)
                        : new $class();

                    return $controllerInstance->{$function}($request, $response, ...$matches);
                };

                foreach ([...$route['middlewares'], ...$this->middlewares] as $middleware) {
                    $middlewareInstance = $this->resolveMiddleware($middleware, $container);
                    $next = $action;
                    $action = fn() => $middlewareInstance->process($next);
                }

                $this->sendResult($action(), $response, $method !== 'HEAD');
                return;
            }

            $allowedMethods = $this->allowedMethodsFor($path);

            if ($allowedMethods !== []) {
                $response->header('Allow', implode(', ', $allowedMethods));

                if ($method === 'OPTIONS') {
                    $response->status(204)->send(false);
                    return;
                }

                $this->sendMethodNotAllowed($request, $response, $allowedMethods);
                return;
            }

            $this->dispatchNotFound($container, null, $request, $response);
        } catch (ErrorException $exception) {
            if ($exception->getCode() !== 404) {
                throw $exception;
            }

            $this->dispatchNotFound($container, $exception->getMessage(), $request, $response);
        } catch (Throwable $exception) {
            error_log((string) $exception);

            $message = filter_var($_ENV['APP_DEBUG'] ?? getenv('APP_DEBUG') ?: false, FILTER_VALIDATE_BOOLEAN)
                ? 'Unexpected error: ' . escape($exception->getMessage())
                : 'An unexpected error occurred.';

            if ($request->expectsJson()) {
                $response->error($message, 500)->send($method !== 'HEAD');
                return;
            }

            $response->html($message, 500)->send($method !== 'HEAD');
        }
    }

    public function addMiddleware(string|object $middleware): void
    {
        $this->middlewares[] = $middleware;
    }

    public function addRouteMiddleware(string|object $middleware, array $params = []): void
    {
        $lastRouteKey = array_key_last($this->routes);

        if ($lastRouteKey === null) {
            throw new LogicException('Cannot add route middleware before adding a route.');
        }

        $this->routes[$lastRouteKey]['middlewares'][] = [
            'class' => $middleware,
            'params' => $params,
        ];
    }

    public function setErrorHandler(array $controller): void
    {
        $this->errorHandler = $controller;
    }

    public function dispatchNotFound(
        ?Container $container,
        ?string $message = null,
        ?Request $request = null,
        ?Response $response = null
    ): void {
        $request ??= Request::capture();
        $response ??= new Response();

        if (!$this->errorHandler) {
            if ($request->expectsJson()) {
                $response->error($message ?? 'Not found.', 404)->send(!$request->isMethod('HEAD'));
                return;
            }

            $response->html('404 Not Found', 404)->send(!$request->isMethod('HEAD'));
            return;
        }

        [$class, $function] = $this->errorHandler;
        $controllerInstance = $container
            ? $container->get($class)
            : new $class();

        $action = fn() => $controllerInstance->{$function}($request, $response, $message);

        foreach ($this->middlewares as $middleware) {
            if ($middleware === SessionMiddleware::class) {
                continue;
            }

            $middlewareInstance = $this->resolveMiddleware($middleware, $container);
            $next = $action;
            $action = fn() => $middlewareInstance->process($next);
        }

        $this->sendResult($action(), $response, !$request->isMethod('HEAD'));
    }

    private function convertRouteToRegex(string $path): string
    {
        $path = rtrim($path, '/');
        $path = preg_replace('#\{([^}]+)\}#', '([^/]+)', $path);

        return '~^' . $path . '/?$~';
    }

    private function normalizePath(string $path): string
    {
        $path = '/' . trim($path, '/');

        return preg_replace('/\/{2,}/', '/', $path) ?: '/';
    }

    private function allowedMethodsFor(string $path): array
    {
        $methods = [];

        foreach ($this->routes as $route) {
            if (!preg_match($route['pattern'], $path)) {
                continue;
            }

            $methods[] = $route['method'];
        }

        $methods = array_values(array_unique($methods));

        if (in_array('GET', $methods, true) && !in_array('HEAD', $methods, true)) {
            $methods[] = 'HEAD';
        }

        if ($methods !== [] && !in_array('OPTIONS', $methods, true)) {
            $methods[] = 'OPTIONS';
        }

        sort($methods);

        return $methods;
    }

    private function sendMethodNotAllowed(Request $request, Response $response, array $allowedMethods): void
    {
        $message = 'Method not allowed.';

        if ($request->expectsJson()) {
            $response->error($message, 405, [
                'allowed_methods' => $allowedMethods,
            ])->send(!$request->isMethod('HEAD'));
            return;
        }

        $response->html($message, 405)->send(!$request->isMethod('HEAD'));
    }

    private function resolveMiddleware(string|object|array $middleware, ?Container $container): object
    {
        if (is_object($middleware)) {
            return $middleware;
        }

        if (is_array($middleware)) {
            $middlewareClass = $middleware['class'];
            $params = $middleware['params'];

            return $container
                ? $container->resolve($middlewareClass, ['allowedRoles' => $params])
                : new $middlewareClass($params);
        }

        return $container
            ? $container->get($middleware)
            : new $middleware();
    }

    private function sendResult(mixed $result, Response $response, bool $includeBody = true): void
    {
        if ($result instanceof Response) {
            $result->send($includeBody);
            return;
        }

        if (is_string($result) || is_numeric($result)) {
            $response->html((string) $result)->send($includeBody);
            return;
        }

        if (is_array($result)) {
            $response->json($result)->send($includeBody);
            return;
        }

        if ($result === null && $response->isEmpty()) {
            $response->noContent()->send(false);
            return;
        }

        if (!$response->isEmpty()) {
            $response->send($includeBody);
        }
    }
}
