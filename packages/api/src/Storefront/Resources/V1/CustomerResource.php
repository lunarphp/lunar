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

    public static function description(): string
    {
        return 'The customer record behind an authenticated storefront user.';
    }

    public function fields(): array
    {
        return [
            Field::make('title')->nullable()->describe('Honorific, such as Mr or Dr.'),
            Field::make('first_name')->describe('Given name.'),
            Field::make('last_name')->describe('Family name.'),
            Field::make('company_name')->nullable()->describe('Company the customer buys on behalf of.'),
            Field::make('tax_identifier')->nullable()->describe('VAT or tax registration number.'),
            Field::make('account_ref')->nullable()->describe('The account reference in an external system.'),
            Field::make('created_at')->describe('When the customer record was created.'),
            Field::make('updated_at')->describe('When the customer record was last updated.'),
        ];
    }
}
