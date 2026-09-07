<?php

namespace Lunar\Api\Admin\Http\Controllers\V1;

use Illuminate\Http\Request;
use Lunar\Api\Http\Controllers\ResourceController;
use Lunar\Api\Registry\SurfaceRegistry;
use Lunar\Api\Resources\SerializationContext;
use Lunar\Api\Resources\Translations;

abstract class Controller extends ResourceController
{
    protected function registry(): SurfaceRegistry
    {
        return $this->api->admin('v1');
    }

    protected function context(Request $request): SerializationContext
    {
        return new SerializationContext(
            registry: $this->registry(),
            translations: Translations::Map,
            principal: $request->user(),
        );
    }
}
