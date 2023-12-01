<?php

namespace Please\Container;

use Please\Container\Exceptions\ContainerException;
use Please\Container\Exceptions\NotFoundException;
use Psr\Container\ContainerInterface;
use ReflectionParameter;
use ReflectionClass;
use Closure;

class Container implements ContainerInterface
{
    /**
     * Holds the bindings of abstract classes/interfaces to concrete implementations.
     *
     * @var array
     */
    protected array $bindings = [];

    /**
     * Holds the instances of resolved abstract classes/interfaces.
     *
     * @var array
     */
    protected array $instances = [];

    /**
     * Binds an abstract class/interface to a concrete implementation.
     *
     * @param string|array $abstract String or array of abstract class/interface/alias to bind.
     * @param mixed $concrete The concrete implementation to bind. If not provided, the abstract class/interface itself will be used.
     * @param bool $shared Determines if the binding is shared or not. Default is false.
     */
    public function bind(string|array $abstract, mixed $concrete = null, bool $shared = false): void
    {
        if (is_null($concrete)) {
            $concrete = $abstract;
        }

        /**
         * Use case: `$container->bind('bar', Bar::class)`
         * We bind dependency as `bar` and as `Bar::class`
         */
        if (is_string($concrete) && $abstract !== $concrete && class_exists($concrete)) {
            $this->bindings[$concrete] = [
                'concrete' => $concrete,
                'shared' => $shared,
            ];
        }

        foreach ((array) $abstract as $item) {
            $this->bindings[$item] = [
                'concrete' => $concrete,
                'shared' => $shared,
            ];
        }
    }

    /**
     * Generates a singleton instance of a class.
     *
     * @param string $abstract The abstract class or interface.
     * @param mixed $concrete The concrete implementation of the class.
     */
    public function singleton(string $abstract, mixed $concrete = null): void
    {
        $this->bind($abstract, $concrete, true);
    }

    /**
     * Constructs an instance of the given abstract.
     *
     * @param string $abstract The abstract to construct an instance of.
     * @param array $parameters An array of parameters to pass to the constructor.
     * @return mixed The constructed instance of the abstract.
     *
     * @throws NotFoundException If the dependency cannot be resolved.
     */
    public function get(string $abstract, array $parameters = []): mixed
    {
        if (!isset($this->bindings[$abstract])) {
            throw new NotFoundException("Unable to resolve dependency: '{$abstract}'");
        }

        $concrete = $this->bindings[$abstract]['concrete'];
        $shared = $this->bindings[$abstract]['shared'];

        if ($shared && isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        $instance = $this->build($concrete, $parameters);

        if ($shared) {
            $this->instances[$abstract] = $instance;
        }

        return $instance;
    }

    /**
     * Check if the container has a binding or instance for the given abstract.
     *
     * @param string $abstract The abstract to check for.
     * @return bool Returns true if the container has a binding or instance for the given abstract, false otherwise.
     */
    public function has(string $abstract): bool
    {
        return isset($abstract, $this->bindings) || isset($abstract, $this->instances);
    }

    /**
     * Builds an instance of a class with the given concrete implementation and parameters.
     *
     * @param mixed $concrete The concrete implementation of the class to build.
     * @param array $parameters An array of parameters to be passed to the class constructor.
     * @return mixed An instance of the class.
     *
     * @throws ContainerException if the class is not instantiable.
     */
    protected function build(mixed $concrete, array $parameters = []): mixed
    {
        if ($concrete instanceof Closure) {
            return $concrete($this, $parameters);
        }

        if (!is_string($concrete) || !class_exists($concrete)) {
            return $concrete;
        }

        $reflector = new ReflectionClass($concrete);

        if (!$reflector->isInstantiable()) {
            throw new ContainerException("Class '{$concrete}' is not instantiable");
        }

        $constructor = $reflector->getConstructor();

        if (is_null($constructor)) {
            return new $concrete;
        }

        $dependencies = $constructor->getParameters();
        $resolvedDependencies = $this->resolveDependencies($dependencies, $parameters);

        return $reflector->newInstanceArgs($resolvedDependencies);
    }

    /**
     * Resolves the dependencies for a given array of dependencies and parameters.
     *
     * @param ReflectionParameter[] $dependencies The array of dependencies to be resolved.
     * @param array $parameters Optional. The array of parameters to be used for resolving dependencies.
     * @return array The resolved dependencies.
     */
    protected function resolveDependencies(array $dependencies, array $parameters = []): array
    {
        $resolvedDependencies = [];

        foreach ($dependencies as $dependency) {
            if (array_key_exists($dependency->name, $parameters)) {
                $resolvedDependencies[] = $parameters[$dependency->name];
                continue;
            }

            /** @var ?string getName() */
            $paramType = $dependency->getType();
            $paramTypeName = $paramType?->getName();

            if ($paramTypeName && array_key_exists($paramTypeName, $parameters)) {
                $resolvedDependencies[] = $parameters[$paramTypeName];
                continue;
            }

            if ($dependency->isDefaultValueAvailable()) {
                $resolvedDependencies[] = $dependency->getDefaultValue();
                continue;
            }

            if ($paramTypeName && isset($this->bindings[$paramTypeName])) {
                $resolvedDependencies[] = $this->get($paramTypeName);
                continue;
            }

            if (isset($this->bindings[$dependency->name])) {
                $resolvedDependencies[] = $this->get($dependency->name);
                continue;
            }

            throw new NotFoundException("Unable to resolve dependency: [{$paramTypeName}] \${$dependency->name}");
        }

        return $resolvedDependencies;
    }

    /**
     * Checks if the given value is instantiable.
     *
     * @param mixed $concrete The value to check.
     * @return bool Returns true if the value is instantiable, false otherwise.
     */
    protected function isInstantiable(mixed $concrete): bool
    {
        return $concrete instanceof Closure || (is_string($concrete) && class_exists($concrete));
    }
}