<?php

namespace Lunar\Api\Contracts;

use Lunar\Api\Registry\SurfaceRegistry;

interface ApiManager
{
    public function storefront(string $version = 'v1'): SurfaceRegistry;

    public function admin(string $version = 'v1'): SurfaceRegistry;

    public function surface(string $surface, string $version): SurfaceRegistry;

    /** @return array<string, SurfaceRegistry> keyed `surface:version` */
    public function surfaces(): array;
}
