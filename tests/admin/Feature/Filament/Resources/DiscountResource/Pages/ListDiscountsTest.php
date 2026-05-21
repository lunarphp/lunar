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
