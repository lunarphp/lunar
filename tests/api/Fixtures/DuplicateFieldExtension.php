<?php

namespace Lunar\Tests\Api\Fixtures;

use Lunar\Api\Resources\Field;
use Lunar\Api\Resources\ResourceExtension;
use Lunar\Api\Storefront\Resources\V1\ProductResource;

class DuplicateFieldExtension extends ResourceExtension
{
    public function extends(): string
    {
        return ProductResource::class;
    }

    public function fields(): array
    {
        return [Field::make('name')];
    }
}
