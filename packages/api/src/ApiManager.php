<?php

namespace Lunar\Api;

use Illuminate\Contracts\Container\Container;
use Lunar\Api\Contracts\ApiManager as ApiManagerContract;
use Lunar\Api\Registry\SurfaceRegistry;

/**
 * Holds the resource registry of every surface version. Stateless after boot,
 * so it is a singleton.
 */
class ApiManager implements ApiManagerContract
{
    /** @var array<string, SurfaceRegistry> */
    protected array $surfaces = [];

    public function __construct(protected Container $container) {}

    public function storefront(string $version = 'v1'): SurfaceRegistry
    {
        return $this->surface('storefront', $version);
    }

    public function admin(string $version = 'v1'): SurfaceRegistry
    {
        return $this->surface('admin', $version);
    }

    public function surface(string $surface, string $version): SurfaceRegistry
    {
        return $this->surfaces["{$surface}:{$version}"] ??= new SurfaceRegistry($surface, $version, $this->container);
    }

    public function surfaces(): array
    {
        return $this->surfaces;
    }
}
