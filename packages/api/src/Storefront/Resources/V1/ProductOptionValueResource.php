<?php

namespace Lunar\Api\Storefront\Resources\V1;

use Lunar\Api\Resources\Field;
use Lunar\Api\Resources\Resource;
use Lunar\Api\Resources\SerializationContext;
use Lunar\Core\Models\ProductOptionValue;

class ProductOptionValueResource extends Resource
{
    public static function type(): string
    {
        return 'product-option-values';
    }

    public static function model(): string
    {
        return ProductOptionValue::class;
    }

    public function fields(): array
    {
        return [
            Field::translatable('name'),
            Field::make('position'),
            Field::make('option', fn (ProductOptionValue $value, SerializationContext $context) => $value->option?->translate('name', $context->locale()))
                ->eagerLoad('option'),
            Field::make('option_id', fn (ProductOptionValue $value) => $value->option?->public_id)->eagerLoad('option'),
        ];
    }
}
