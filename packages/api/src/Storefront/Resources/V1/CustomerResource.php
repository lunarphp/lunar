<?php

namespace Lunar\Api\Storefront\Resources\V1;

use Lunar\Api\Resources\Field;
use Lunar\Api\Resources\Resource;
use Lunar\Core\Models\Customer;

class CustomerResource extends Resource
{
    public static function type(): string
    {
        return 'customers';
    }

    public static function model(): string
    {
        return Customer::class;
    }

    public function fields(): array
    {
        return [
            Field::make('title'),
            Field::make('first_name'),
            Field::make('last_name'),
            Field::make('company_name'),
            Field::make('tax_identifier'),
            Field::make('account_ref'),
            Field::make('created_at'),
            Field::make('updated_at'),
        ];
    }
}
