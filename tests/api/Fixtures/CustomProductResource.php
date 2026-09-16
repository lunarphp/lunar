<?php

namespace Lunar\Tests\Api\Fixtures;

use Lunar\Api\Resources\Field;
use Lunar\Api\Storefront\Resources\V1\ProductResource;

/** A host's replacement for the built-in resource. */
class CustomProductResource extends ProductResource
{
    public function fields(): array
    {
        return [
            ...parent::fields(),
            Field::make('custom', fn () => 'yes'),
        ];
    }
}
