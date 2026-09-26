<?php

namespace Lunar\Api\OpenApi;

use Composer\InstalledVersions;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Lunar\Api\Registry\SurfaceRegistry;
use WeakMap;

/**
 * Builds the OpenAPI 3.1 document of a surface version from its registry and
 * the routes registered under its name prefix. The result is memoised per
 * registry until the registry changes, which only happens at boot.
 */
final class Generator
{
    public const OPENAPI_VERSION = '3.1.0';

    /** @var WeakMap<SurfaceRegistry, array{revision: int, document: Document}> */
    private WeakMap $documents;

    public function __construct(
        private readonly Router $router,
        private readonly Container $container,
        private readonly Repository $config,
    ) {
        $this->documents = new WeakMap;
    }

    public function generate(SurfaceRegistry $registry): Document
    {
        $cached = $this->documents[$registry] ?? null;

        if ($cached !== null && $cached['revision'] === $registry->revision()) {
            return $cached['document'];
        }

        $document = $this->build($registry);
        $this->documents[$registry] = ['revision' => $registry->revision(), 'document' => $document];

        return $document;
    }

    private function build(SurfaceRegistry $registry): Document
    {
        $profile = SurfaceProfile::for($registry, $this->config);
        $components = new ComponentBuilder($registry, $profile);
        $operations = new OperationBuilder(
            $registry,
            $profile,
            $components,
            $this->container,
            (int) $this->config->get('lunar.api.pagination.max_include_depth', 3),
        );

        $paths = [];

        foreach ($this->routes($registry) as $route) {
            $path = $operations->path($route);

            foreach (array_diff($route->methods(), ['HEAD']) as $method) {
                $paths[$path][strtolower($method)] = $operations->operation($route, strtolower($method));
            }
        }

        $document = [
            'openapi' => self::OPENAPI_VERSION,
            'info' => [
                'title' => "{$profile->label()} API",
                'version' => $registry->version,
                'description' => $this->config->get("lunar.api.{$registry->surface}.description")
                    ?? "The Lunar {$registry->surface} API. Generated from the resource registry; add-on fields and endpoints appear automatically.",
                'x-lunar-release' => self::release(),
                'license' => ['name' => 'MIT', 'identifier' => 'MIT'],
            ],
            'servers' => $this->servers($profile),
            // An empty root security list states that operations are public unless they say otherwise.
            'security' => $profile->globalSecurityScheme !== null ? [[$profile->globalSecurityScheme => []]] : [],
        ];

        $document['tags'] = $this->tags($registry, $paths);
        $document['paths'] = $paths;
        $document['components'] = self::pruneUnused($components->components(), $paths, array_keys($registry->definitions()));

        $result = new Document($document);

        foreach ($registry->documentTaps() as $tap) {
            $tap($result);
        }

        return $result;
    }

    /** @return array<int, Route> */
    private function routes(SurfaceRegistry $registry): array
    {
        $prefix = "lunar.api.{$registry->surface}.{$registry->version}.";
        $routes = [];

        foreach ($this->router->getRoutes()->getRoutes() as $route) {
            $name = $route->getName();

            if ($name !== null && str_starts_with($name, $prefix)) {
                $routes[] = $route;
            }
        }

        return $routes;
    }

    /** @return array<int, array<string, string>> */
    private function servers(SurfaceProfile $profile): array
    {
        $configured = $this->config->get("lunar.api.{$profile->surface}.servers");

        if (is_array($configured) && $configured !== []) {
            return array_values(array_map(fn (array $server) => array_filter([
                'url' => (string) $server['url'],
                'description' => $server['description'] ?? null,
            ]), $configured));
        }

        return [[
            'url' => rtrim((string) $this->config->get('app.url', ''), '/').'/'.$profile->prefix,
            'description' => (string) $this->config->get('app.name', 'Lunar'),
        ]];
    }

    /**
     * Registered resources in registration order, then any route tag no
     * resource claims (an add-on route registered outside a resource prefix).
     *
     * @param  array<string, array<string, array<string, mixed>>>  $paths
     * @return array<int, array<string, string>>
     */
    private function tags(SurfaceRegistry $registry, array $paths): array
    {
        $tags = [];

        foreach ($registry->definitions() as $definition) {
            $tag = ['name' => $definition->type(), 'x-group' => $definition->resource::label()];

            if ($definition->resource::description() !== '') {
                $tag['description'] = $definition->resource::description();
            }

            $tags[$definition->type()] = $tag;
        }

        foreach ($paths as $operations) {
            foreach ($operations as $operation) {
                foreach ($operation['tags'] ?? [] as $name) {
                    $tags[$name] ??= ['name' => $name, 'x-group' => ucfirst(str_replace('-', ' ', $name))];
                }
            }
        }

        return array_values($tags);
    }

    /**
     * Drop shared schemas and error responses nothing references. Resource
     * schemas always stay: they are the registry, and the data model pages.
     *
     * @param  array<string, mixed>  $components
     * @param  array<string, mixed>  $paths
     * @param  array<int, string>  $resources
     * @return array<string, mixed>
     */
    private static function pruneUnused(array $components, array $paths, array $resources): array
    {
        $resourceNames = array_map(fn (string $class) => ComponentBuilder::nameForType($class::type()), $resources);

        do {
            $references = self::references(['paths' => $paths, 'components' => $components]);
            $removed = false;

            foreach (['schemas', 'responses'] as $section) {
                foreach (array_keys($components[$section] ?? []) as $name) {
                    if ($section === 'schemas' && in_array($name, $resourceNames, true)) {
                        continue;
                    }

                    if (! in_array("#/components/{$section}/{$name}", $references, true)) {
                        unset($components[$section][$name]);
                        $removed = true;
                    }
                }
            }
        } while ($removed);

        return $components;
    }

    /**
     * @param  array<string, mixed>  $node
     * @return array<int, string>
     */
    private static function references(array $node): array
    {
        $found = [];

        array_walk_recursive($node, function (mixed $value, string|int $key) use (&$found): void {
            if ($key === '$ref' && is_string($value)) {
                $found[] = $value;
            }
        });

        return array_values(array_unique($found));
    }

    public static function release(): string
    {
        foreach (['lunarphp/api', 'lunarphp/lunar', 'lunarphp/core'] as $package) {
            if (InstalledVersions::isInstalled($package)) {
                return InstalledVersions::getPrettyVersion($package) ?? 'dev';
            }
        }

        return 'dev';
    }
}
