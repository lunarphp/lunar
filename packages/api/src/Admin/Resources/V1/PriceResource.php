<?php

namespace Lunar\Api\Admin\Resources\V1;

use Lunar\Api\OpenApi\Schema;
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

    public static function description(): string
    {
        return 'A price of a variant for one currency, quantity break and customer group.';
    }

    public function fields(): array
    {
        return [
            Field::make('price', fn (Price $price) => $price->currency ? new PriceValue($price->price, $price->currency) : null)->eagerLoad('currency')
                ->type(Schema::money()->nullable())->describe('The selling price.'),
            Field::make('list_price', fn (Price $price) => $price->list_price !== null && $price->currency ? new PriceValue($price->list_price, $price->currency) : null)->eagerLoad('currency')
                ->type(Schema::money()->nullable())->describe('The recommended or was price, when one is set.'),
            Field::make('currency', fn (Price $price) => $price->currency?->code)->eagerLoad('currency')
                ->type(Schema::string()->nullable())->describe('ISO 4217 code of the price currency.'),
            Field::make('min_quantity')->type(Schema::integer())->describe('The quantity from which this price applies.'),
            Field::make('customer_group_id', fn (Price $price) => $price->customerGroup?->public_id)->eagerLoad('customerGroup')
                ->type(Schema::string()->nullable())->describe('Public id of the customer group the price is for, or null for every group.'),
        ];
    }
}
