<?php

namespace Lunar\Api\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Lunar\Api\Contracts\ApiManager;
use Lunar\Api\Http\Responses\Envelope;
use Lunar\Api\Registry\SurfaceRegistry;
use Lunar\Api\Resources\SerializationContext;

/**
 * `GET /{surface}/{version}/_schema`: every registered resource with its
 * fields, includes, filters, sorts and routes, as the caller may see them.
 */
class SchemaController
{
    public function __construct(
        protected ApiManager $api,
        protected Router $router,
    ) {}

    public function __invoke(Request $request, string $surface, string $version): JsonResponse
    {
        $registry = $this->api->surface($surface, $version);
        $context = new SerializationContext($registry, principal: $request->user());

        return Envelope::item(
            self::describe($registry, $context, $this->router),
            [],
            ['self' => $request->fullUrl()],
        );
    }

    /** @return array<string, mixed> */
    public static function describe(SurfaceRegistry $registry, SerializationContext $context, Router $router): array
    {
        $resources = [];

        foreach ($registry->definitions() as $definition) {
            $resources[] = $definition->schema($context) + [
                'routes' => self::routesFor($router, $registry, $definition->type()),
            ];
        }

        return [
            'surface' => $registry->surface,
            'version' => $registry->version,
            'resources' => $resources,
        ];
    }

    /** @return array<int, array{method: string, uri: string, name: string}> */
    private static function routesFor(Router $router, SurfaceRegistry $registry, string $type): array
    {
        $prefix = "lunar.api.{$registry->surface}.{$registry->version}.{$type}.";
        $routes = [];

        foreach ($router->getRoutes()->getRoutes() as $route) {
            /** @var Route $route */
            $name = $route->getName();

            if ($name === null || ! str_starts_with($name, $prefix)) {
                continue;
            }

            $routes[] = [
                'method' => implode('|', array_diff($route->methods(), ['HEAD'])),
                'uri' => '/'.$route->uri(),
                'name' => $name,
            ];
        }

        return $routes;
    }
}
