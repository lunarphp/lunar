<?php

use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\DiscountResource;
use Lunar\Admin\Filament\Resources\DiscountResource\Pages\EditDiscount;
use Lunar\Core\Enums\DiscountRedemptionMethod;
use Lunar\Core\Models\Discount;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;

uses(TestCase::class)
    ->group('resource.discount');

beforeEach(function () {
    $this->asStaff();
});

it('can render discount edit page', function () {
    get(
        DiscountResource::getUrl(
            'edit',
            ['record' => Discount::factory()->create()]
        )
    )->assertSuccessful();
});

it('can edit discount', function () {
    $discount = Discount::factory()->create();
    Livewire::test(EditDiscount::class,
        ['record' => $discount->getKey()]
    )->fillForm([
        'name' => 'Updated Name',
        'handle' => 'updated_name',
    ])->call('save')->assertHasNoErrors();

    assertDatabaseHas(Discount::class, [
        'name' => 'Updated Name',
        'handle' => 'updated_name',
    ]);
});

it('can validate start and end date', function () {
    $discount = Discount::factory()->create();
    Livewire::test(EditDiscount::class,
        ['record' => $discount->getKey()]
    )->fillForm([
        'starts_at' => now(),
        'ends_at' => now()->subWeek(),
    ])->call('save')->assertHasFormErrors([
        'starts_at' => 'before',
    ]);
});

it('can change how a discount is redeemed', function () {
    $discount = Discount::factory()->create();

    Livewire::test(EditDiscount::class, ['record' => $discount->getKey()])
        ->assertFormSet(['redemption' => DiscountRedemptionMethod::Automatic->value])
        ->fillForm(['redemption' => DiscountRedemptionMethod::Coupon->value])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($discount->refresh()->redemption)->toBe(DiscountRedemptionMethod::Coupon);
});

it('shows the coupons relation manager only for coupon discounts', function () {
    Livewire::test(EditDiscount::class, ['record' => Discount::factory()->coupon('SAVE10')->create()->getKey()])
        ->assertSeeHtml('SAVE10');

    Livewire::test(EditDiscount::class, ['record' => Discount::factory()->create()->getKey()])
        ->assertDontSeeHtml('SAVE10');
});
