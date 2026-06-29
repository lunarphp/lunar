<?php

use Filament\Forms\Components\Select;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Product;
use Lunar\Filament\Forms\Components\CustomerGroupSelect;
use Lunar\Filament\Forms\Components\CustomerSelect;
use Lunar\Filament\Forms\Components\DiscountTargetSelect;
use Lunar\Filament\Forms\Components\ProductSelect;
use Lunar\Tests\Filament\TestCase;

uses(TestCase::class);

it('instantiates CustomerSelect with the customer model', function () {
    $component = CustomerSelect::make('customer_id')->excludeAttached();

    expect($component)->toBeInstanceOf(CustomerSelect::class)
        ->and($component->lunarModel())->toBe(Customer::class)
        ->and($component->isSearchable())->toBeTrue();
});

it('renders a customer label from name fields', function () {
    $customer = Customer::factory()->create([
        'first_name' => 'Ada',
        'last_name' => 'Lovelace',
        'company_name' => 'Analytical Engines',
    ]);

    expect(CustomerSelect::make('customer_id')->optionLabel($customer))
        ->toBe('Ada Lovelace — Analytical Engines');
});

it('instantiates CustomerGroupSelect bound to the customerGroup relationship', function () {
    $component = CustomerGroupSelect::make('customer_group_id');

    expect($component)->toBeInstanceOf(CustomerGroupSelect::class)
        ->and($component->lunarModel())->toBe(CustomerGroup::class)
        ->and($component->getRelationshipName())->toBe('customerGroup')
        ->and($component->isPreloaded())->toBeTrue();
});

it('instantiates DiscountTargetSelect with the default four targets', function () {
    $component = DiscountTargetSelect::make('discountable');

    expect($component)->toBeInstanceOf(DiscountTargetSelect::class)
        ->and($component->isSearchable())->toBeTrue();
});

it('lets DiscountTargetSelect restrict the target list', function () {
    $component = DiscountTargetSelect::make('discountable')
        ->targets([Product::class]);

    expect($component)->toBeInstanceOf(DiscountTargetSelect::class);
});

it('applies a ProductSelect to an existing Select instance', function () {
    $host = Select::make('product_id');

    $result = ProductSelect::applyTo($host);

    expect($result)->toBe($host)
        ->and($result->isSearchable())->toBeTrue();
});
