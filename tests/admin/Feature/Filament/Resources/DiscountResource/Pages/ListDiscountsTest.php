<?php

use function Pest\Laravel\get;

uses(\Lunar\Tests\Admin\Feature\Filament\TestCase::class)
    ->group('resource.discount');

beforeEach(function () {
    $this->asStaff();
});

it('can list discounts', function () {
    get(
        \Lunar\Admin\Filament\Resources\DiscountResource::getUrl('index')
    )->assertSuccessful();
});

it('can create a discount', function () {
    $discount = \Lunar\Models\Discount::factory()->create();
    \Livewire\Livewire::test(
        \Lunar\Admin\Filament\Resources\DiscountResource\Pages\ListDiscounts::class
    )->callAction('create', [
        'name' => 'Discount A',
        'handle' => 'discount_a',
        'starts_at' => now(),
        'type' => \Lunar\DiscountTypes\BuyXGetY::class,
    ])->assertHasNoErrors();
});
