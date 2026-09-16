<?php

namespace Lunar\Api\Storefront\Resources\V1;

use Lunar\Api\OpenApi\Schema;
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

    public static function description(): string
    {
        return 'A value of a product option, such as Red for Colour, as carried by a variant.';
    }

    public function fields(): array
    {
        return [
            Field::translatable('name')->describe('The value name in the request locale, such as Red.'),
            Field::make('position')->type(Schema::integer())->describe('Display order within the option.'),
            Field::make('option', fn (ProductOptionValue $value, SerializationContext $context) => $value->option?->translate('name', $context->locale()))
                ->eagerLoad('option')
                ->type(Schema::string()->nullable())->describe('The option name in the request locale, such as Colour.'),
            Field::make('option_id', fn (ProductOptionValue $value) => $value->option?->public_id)->eagerLoad('option')
                ->type(Schema::string()->nullable())->describe('Public id of the option the value belongs to.'),
        ];
    }
}
