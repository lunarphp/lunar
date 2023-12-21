<?php

uses(\Lunar\Tests\Admin\Feature\Filament\TestCase::class)
    ->group('actions');

beforeEach(function () {
    $this->asStaff(admin: true);

});

it('can render', function () {
    \Livewire\Livewire::test(Filament\Livewire\GlobalSearch::class)
        ->assertSeeHtml('search');
});

it('can search customer', function () {

    \Lunar\Models\Language::factory()->create([
        'default' => true,
    ]);

    $record = \Lunar\Models\Customer::factory()->create([
        'account_ref' => 'X67HB'
    ]);

    \Livewire\Livewire::test(Filament\Livewire\GlobalSearch::class)
        ->set('search', $record->account_ref)
        ->assertDispatched('open-global-search-results')
        ->assertSee($record->account_ref);
});

it('can search order', function () {

    \Lunar\Models\Language::factory()->create([
        'default' => true,
    ]);

    $currency = \Lunar\Models\Currency::factory()->create([
        'default' => true,
    ]);

    $country = \Lunar\Models\Country::factory()->create();

    $record = \Lunar\Models\Order::factory()
        ->for(\Lunar\Models\Customer::factory())
        ->has(\Lunar\Models\OrderAddress::factory()->state([
            'type' => 'shipping',
            'country_id' => $country->id,
        ]), 'shippingAddress')
        ->has(\Lunar\Models\OrderAddress::factory()->state([
            'type' => 'billing',
            'country_id' => $country->id,
        ]), 'billingAddress')
        ->create([
            'currency_code' => $currency->code,
            'meta' => [
                'additional_info' => Str::random(),
            ],
        ]);

        \Livewire\Livewire::test(Filament\Livewire\GlobalSearch::class)
        ->set('search', $record->reference)
        ->assertDispatched('open-global-search-results')
        ->assertSee($record->reference);
});

it('can search collection', function () {

    \Lunar\Models\Language::factory()->create([
        'default' => true,
    ]);

    $record = \Lunar\Models\Collection::factory()->create();

    \Livewire\Livewire::test(Filament\Livewire\GlobalSearch::class)
        ->set('search', $record->translateAttribute('name'))
        ->assertDispatched('open-global-search-results')
        ->assertSee($record->translateAttribute('name'));
});

it('can search brand', function () {
    \Lunar\Models\Language::factory()->create([
        'default' => true,
    ]);

    $brand = \Lunar\Models\Brand::factory()->create();

    \Livewire\Livewire::test(Filament\Livewire\GlobalSearch::class)
        ->set('search', $brand->name)
        ->assertDispatched('open-global-search-results')
        ->assertSee($brand->name);
});

it('can search product', function () {

    \Lunar\Models\Language::factory()->create([
        'default' => true,
    ]);

    \Lunar\Models\Currency::factory()->create([
        'default' => true,
    ]);

    $record = \Lunar\Models\Product::factory()->create();

    \Lunar\Models\ProductVariant::factory()->create([
        'product_id' => $record->id,
    ]);

    \Livewire\Livewire::test(Filament\Livewire\GlobalSearch::class)
        ->set('search', $record->variants->first()->sku)
        ->assertDispatched('open-global-search-results')
        ->assertSee($record->variants->first()->sku);
});