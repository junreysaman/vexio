<?php

declare(strict_types=1);

namespace Framework;

use ReflectionClass;
use ReflectionNamedType;
use Framework\Exceptions\ContainerException;

class Container
{
    private array $definitions = [];
    private array $resolved = [];

    public function addDefinitions(array $newDefinitions): void
    {
        $this->definitions = [...$this->definitions, ...$newDefinitions];
    }

    public function get(string $id)
    {
        // Already resolved? Return cached
        if (isset($this->resolved[$id])) {
            return $this->resolved[$id];
        }

        // If definition exists, use factory
        if (isset($this->definitions[$id])) {
            $factory = $this->definitions[$id];
            $dependency = $factory($this);
            $this->resolved[$id] = $dependency;
            return $dependency;
        }

        // Otherwise, try to auto-resolve
        if (class_exists($id)) {
            $dependency = $this->resolve($id);
            $this->resolved[$id] = $dependency;
            return $dependency;
        }

        throw new ContainerException("Class {$id} does not exist in Container and cannot be resolved.");
    }

    public function resolve(string $className, array $parameters = [])
{
    $reflectionClass = new ReflectionClass($className);

    if (!$reflectionClass->isInstantiable()) {
        throw new ContainerException("Class $className is not instantiable");
    }

    $constructor = $reflectionClass->getConstructor();
    if (!$constructor) {
        return new $className();
    }

    $dependencies = [];
    foreach ($constructor->getParameters() as $parameter) {
        $name = $parameter->getName();
        $type = $parameter->getType();

        // If parameter passed manually, use it
        if (array_key_exists($name, $parameters)) {
            $dependencies[] = $parameters[$name];
            continue;
        }

        // If no type
        if (!$type) {
            if ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
                continue;
            }
            throw new ContainerException("Cannot resolve \${$name} in {$className}");
        }

        // Built-in type (string, int, array, etc.)
        if ($type instanceof ReflectionNamedType && $type->isBuiltin()) {
            if ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
                continue;
            }
            throw new ContainerException(
                "Cannot autowire built-in parameter \${$name} in {$className}"
            );
        }

        // Otherwise, resolve class dependency
        $dependencies[] = $this->get($type->getName());
    }

    return $reflectionClass->newInstanceArgs($dependencies);
}




}
