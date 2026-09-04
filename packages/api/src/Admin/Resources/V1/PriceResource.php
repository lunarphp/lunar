<?php

namespace Lunar\Api\Admin\Resources\V1;

use Lunar\Api\Resources\Field;
use Lunar\Api\Resources\Resource;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Models\Price;

class PriceResource extends Resource
{
    public static function type(): string
    {
        return 'prices';
    }

    public static function model(): string
    {
        return Price::class;
    }

    public function fields(): array
    {
        return [
            Field::make('price', fn (Price $price) => $price->currency ? new PriceValue($price->price, $price->currency) : null)->eagerLoad('currency'),
            Field::make('list_price', fn (Price $price) => $price->list_price !== null && $price->currency ? new PriceValue($price->list_price, $price->currency) : null)->eagerLoad('currency'),
            Field::make('currency', fn (Price $price) => $price->currency?->code)->eagerLoad('currency'),
            Field::make('min_quantity'),
            Field::make('customer_group_id', fn (Price $price) => $price->customerGroup?->public_id)->eagerLoad('customerGroup'),
        ];
    }
}
