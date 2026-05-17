<?php

declare(strict_types=1);

namespace Framework;

use Framework\Http\Request;
use Framework\Http\Response;

class App
{
    private Router $router;
    private Container $container;

    public function __construct(?string $containerDefinitionsPath = null)
    {
        $this->router = new Router();
        $this->container = new Container();

        if ($containerDefinitionsPath) {
            $containerDefinitions = include $containerDefinitionsPath;
            $this->container->addDefinitions($containerDefinitions);
        }
    }

    public function run(): void
    {
        $request = Request::capture();
        $response = new Response();

        $this->router->dispatch(
            $request->path(),
            $request->method(),
            $this->container,
            $request,
            $response
        );
    }

    public function get(string $path, array $controller): self
    {
        $this->router->add('GET', $path, $controller);
        return $this;
    }

    public function post(string $path, array $controller): self
    {
        $this->router->add('POST', $path, $controller);
        return $this;
    }

    public function put(string $path, array $controller): self
    {
        $this->router->add('PUT', $path, $controller);
        return $this;
    }

    public function patch(string $path, array $controller): self
    {
        $this->router->add('PATCH', $path, $controller);
        return $this;
    }

    public function delete(string $path, array $controller): self
    {
        $this->router->add('DELETE', $path, $controller);
        return $this;
    }

    public function options(string $path, array $controller): self
    {
        $this->router->add('OPTIONS', $path, $controller);
        return $this;
    }

    /**
     * Add global middleware by class name or instance.
     */
    public function addMiddleware(string|object $middleware): self
    {
        $this->router->addMiddleware($middleware);
        return $this;
    }

    /**
     * Add middleware to the latest route.
     */
    public function add(string|object $middleware, array $args = []): self
    {
        $this->router->addRouteMiddleware($middleware, $args);
        return $this;
    }

    public function setErrorHandler(array $controller): self
    {
        $this->router->setErrorHandler($controller);
        return $this;
    }
}
