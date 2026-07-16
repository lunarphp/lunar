<?php

use Filament\Livewire\GlobalSearch;
use Livewire\Livewire;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderAddress;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)
    ->group('actions');

beforeEach(function () {
    Config::set('lunar.filament.scout_enabled', false);

    $this->asStaff(admin: true);
});

it('can render', function () {
    Livewire::test(GlobalSearch::class)
        ->assertSeeHtml('search');
});

it('can search customer', function () {

    Language::factory()->create([
        'default' => true,
    ]);

    $record = Customer::factory()->create([
        'account_ref' => 'X67HB',
    ]);

    Livewire::test(GlobalSearch::class)
        ->set('search', $record->account_ref)
        ->assertDispatched('open-global-search-results')
        ->assertSee($record->account_ref);
});

it('can search order', function () {

    Language::factory()->create([
        'default' => true,
    ]);

    $currency = Currency::factory()->create([
        'default' => true,
    ]);

    $country = Country::factory()->create();

    $record = Order::factory()
        ->for(Customer::factory())
        ->has(OrderAddress::factory()->state([
            'type' => 'shipping',
            'country_id' => $country->id,
        ]), 'shippingAddress')
        ->has(OrderAddress::factory()->state([
            'type' => 'billing',
            'country_id' => $country->id,
        ]), 'billingAddress')
        ->create([
            'currency_code' => $currency->code,
            'meta' => [
                'additional_info' => Str::random(),
            ],
        ]);

    Livewire::test(GlobalSearch::class)
        ->set('search', $record->reference)
        ->assertDispatched('open-global-search-results')
        ->assertSee($record->reference);
});

it('can search collection', function () {

    Language::factory()->create([
        'default' => true,
    ]);

    $record = Collection::factory()->create();

    Livewire::test(GlobalSearch::class)
        ->set('search', $record->group->name)
        ->assertDispatched('open-global-search-results')
        ->assertSee($record->translateAttribute('name'));
});

it('can search brand', function () {
    Language::factory()->create([
        'default' => true,
    ]);

    $brand = Brand::factory()->create();

    Livewire::test(GlobalSearch::class)
        ->set('search', $brand->name)
        ->assertDispatched('open-global-search-results')
        ->assertSee($brand->name);
});

it('can search product', function () {

    Language::factory()->create([
        'default' => true,
    ]);

    Currency::factory()->create([
        'default' => true,
    ]);

    $record = Product::factory()->create();

    ProductVariant::factory()->create([
        'product_id' => $record->id,
    ]);

    Livewire::test(GlobalSearch::class)
        ->set('search', $record->variants->first()->sku)
        ->assertDispatched('open-global-search-results')
        ->assertSee($record->variants->first()->sku);
});
