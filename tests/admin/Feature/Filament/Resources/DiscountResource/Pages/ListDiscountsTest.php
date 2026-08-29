<?php

use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\DiscountResource;
use Lunar\Admin\Filament\Resources\DiscountResource\Pages\ListDiscounts;
use Lunar\Core\DiscountTypes\BuyXGetY;
use Lunar\Core\Models\Discount;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

use function Pest\Laravel\get;

uses(TestCase::class)
    ->group('resource.discount');

beforeEach(function () {
    $this->asStaff();
});

it('can list discounts', function () {
    get(
        DiscountResource::getUrl('index')
    )->assertSuccessful();
});

it('can create a discount', function () {
    $discount = Discount::factory()->create();
    Livewire::test(
        ListDiscounts::class
    )->callAction('create', [
        'name' => 'Discount A',
        'handle' => 'discount_a',
        'starts_at' => now(),
        'type' => BuyXGetY::class,
    ])->assertHasNoErrors();
});

it('lists a discount whose type class is not installed', function () {
    // A discount outlives the package that registered its type — the list must
    // degrade to the stored class name rather than fatal on instantiation.
    $discount = Discount::factory()->create([
        'type' => 'Acme\\Discounts\\UninstalledType',
    ]);

    Livewire::test(ListDiscounts::class)
        ->assertSuccessful()
        ->assertSee('Acme\\Discounts\\UninstalledType');

    expect($discount->refresh()->type)->toBe('Acme\\Discounts\\UninstalledType');
});
