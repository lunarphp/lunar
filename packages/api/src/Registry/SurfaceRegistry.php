<?php

namespace Lunar\Api\Registry;

use Closure;
use Illuminate\Contracts\Container\Container;
use Lunar\Api\Exceptions\ResourceDefinitionException;
use Lunar\Api\Resources\Resource;
use Lunar\Api\Resources\ResourceExtension;

/**
 * The resources one surface version serves. Built-ins register with
 * `resource()`, add-ons widen them with `extend()`, and a host swaps a
 * built-in's own fields with `replace()`. Extensions are keyed by the built-in
 * class so they keep applying to a replacement.
 */
final class SurfaceRegistry
{
    /** @var array<class-string<resource>, class-string<resource>> built-in => effective class */
    private array $resources = [];

    /** @var array<class-string<resource>, array<int, class-string<ResourceExtension>>> */
    private array $extensions = [];

    /** @var array<class-string<resource>, ResourceDefinition> */
    private array $definitions = [];

    /** @var array<int, Closure> */
    private array $routeRegistrars = [];

    public function __construct(
        public readonly string $surface,
        public readonly string $version,
        private readonly Container $container,
    ) {}

    /**
     * @param  class-string<resource>  ...$classes
     */
    public function resource(string ...$classes): static
    {
        foreach ($classes as $class) {
            if (! is_subclass_of($class, Resource::class)) {
                throw new ResourceDefinitionException("[{$class}] is not a ".Resource::class.'.');
            }

            foreach ($this->resources as $key => $effective) {
                if ($key !== $class && $effective::type() === $class::type()) {
                    throw new ResourceDefinitionException("[{$class}] declares type [{$class::type()}] which [{$key}] already serves on {$this->surface} {$this->version}.");
                }
            }

            $this->resources[$class] ??= $class;
        }

        $this->definitions = [];

        return $this;
    }

    /**
     * @param  class-string<resource>  $resource
     * @param  class-string<ResourceExtension>  ...$extensions
     */
    public function extend(string $resource, string ...$extensions): static
    {
        foreach ($extensions as $extension) {
            if (! is_subclass_of($extension, ResourceExtension::class)) {
                throw new ResourceDefinitionException("[{$extension}] is not a ".ResourceExtension::class.'.');
            }

            $this->extensions[$resource][] = $extension;
        }

        $this->definitions = [];

        return $this;
    }

    /**
     * Serve `$replacement` in place of the built-in. It must extend the built-in
     * so every field the surface promises is still there.
     *
     * @param  class-string<resource>  $resource
     * @param  class-string<resource>  $replacement
     */
    public function replace(string $resource, string $replacement): static
    {
        if (! isset($this->resources[$resource])) {
            throw new ResourceDefinitionException("[{$resource}] is not registered on {$this->surface} {$this->version}.");
        }

        if (! is_subclass_of($replacement, $resource)) {
            throw new ResourceDefinitionException("[{$replacement}] must extend [{$resource}] to replace it.");
        }

        $this->resources[$resource] = $replacement;
        $this->definitions = [];

        return $this;
    }

    /** Register extra endpoints under the surface prefix. */
    public function routes(Closure $registrar): static
    {
        $this->routeRegistrars[] = $registrar;

        return $this;
    }

    /** @return array<int, Closure> */
    public function routeRegistrars(): array
    {
        return $this->routeRegistrars;
    }

    public function has(string $classOrType): bool
    {
        return $this->keyFor($classOrType) !== null;
    }

    /**
     * Built-in class => effective class.
     *
     * @return array<class-string<resource>, class-string<resource>>
     */
    public function resources(): array
    {
        return $this->resources;
    }

    /** @return array<int, string> */
    public function types(): array
    {
        return array_values(array_map(fn (string $class) => $class::type(), $this->resources));
    }

    /**
     * The merged definition for a resource, by built-in class, replacement
     * class, or wire type.
     */
    public function definition(string $classOrType): ResourceDefinition
    {
        $key = $this->keyFor($classOrType);

        if ($key === null) {
            throw new ResourceDefinitionException("No resource [{$classOrType}] is registered on {$this->surface} {$this->version}.");
        }

        return $this->definitions[$key] ??= $this->build($key);
    }

    /**
     * Every definition, built. Called once at boot so a mis-registered
     * extension fails the process, not the first request.
     *
     * @return array<class-string<resource>, ResourceDefinition>
     */
    public function definitions(): array
    {
        foreach (array_keys($this->resources) as $key) {
            $this->definition($key);
        }

        return $this->definitions;
    }

    /** @return class-string<resource>|null */
    private function keyFor(string $classOrType): ?string
    {
        if (isset($this->resources[$classOrType])) {
            return $classOrType;
        }

        foreach ($this->resources as $key => $effective) {
            if ($effective === $classOrType || $effective::type() === $classOrType) {
                return $key;
            }
        }

        // A class further down a replacement chain resolves to the built-in it descends from.
        foreach (array_keys($this->resources) as $key) {
            if (class_exists($classOrType) && is_subclass_of($classOrType, $key)) {
                return $key;
            }
        }

        return null;
    }

    /** @param  class-string<resource>  $key */
    private function build(string $key): ResourceDefinition
    {
        $effective = $this->resources[$key];

        $extensionClasses = array_unique(array_merge(
            $this->extensions[$key] ?? [],
            $effective !== $key ? ($this->extensions[$effective] ?? []) : [],
        ));

        $extensions = array_map(fn (string $class) => $this->container->make($class), $extensionClasses);

        foreach ($extensions as $extension) {
            $target = $extension->extends();

            if ($target !== $key && $target !== $effective && ! is_subclass_of($effective, $target)) {
                throw new ResourceDefinitionException($extension::class." extends [{$target}] but was registered against [{$key}].");
            }
        }

        return new ResourceDefinition($this->container->make($effective), $key, $extensions);
    }
}
