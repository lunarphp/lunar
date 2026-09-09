<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Discount;
use Lunar\Core\Models\Staff;
use Lunar\Shipping\DiscountTypes\ShippingDiscount;
use Lunar\Shipping\Models\ShippingMethod;
use Lunar\Tests\Shipping\PanelTestCase;

uses(PanelTestCase::class)->group('shipping', 'shipping-panel');

beforeEach(function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');
    Currency::factory()->create(['code' => 'GBP', 'decimal_places' => 2, 'default' => true]);
});

it('offers the shipping discount type with its own form component and no target buckets', function () {
    ShippingMethod::factory()->create(['name' => 'Standard']);

    $this->get(route('panel.discounts.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('types', fn ($types) => collect($types)->contains(
                fn ($type) => $type['class'] === ShippingDiscount::class
                    && $type['label'] === 'Shipping Price'
                    && $type['component'] === 'shipping::ShippingDiscountForm'
                    && $type['buckets'] === []
            ))
            ->where('shippingMethods.0.name', 'Standard')
        );
});

it('decodes the stored rules for editing and encodes them on update', function () {
    $method = ShippingMethod::factory()->create();
    $discount = Discount::factory()->create([
        'type' => ShippingDiscount::class,
        'data' => ['methods' => [['shipping_method_id' => $method->id, 'type' => 'fixed', 'prices' => ['GBP' => 499]]]],
    ]);

    $this->get(route('panel.discounts.edit', $discount))
        ->assertInertia(fn (Assert $page) => $page
            ->where('type.component', 'shipping::ShippingDiscountForm')
            ->where('discount.data.methods.0.prices.GBP', 4.99)
        );

    $this->put(route('panel.discounts.update', $discount), [
        'name' => $discount->name,
        'handle' => $discount->handle,
        'type' => ShippingDiscount::class,
        'starts_at' => $discount->starts_at->toDateTimeString(),
        'data' => ['methods' => [['shipping_method_id' => null, 'type' => 'fixed', 'prices' => ['GBP' => '0']]]],
    ])->assertRedirect()->assertSessionHasNoErrors();

    expect($discount->refresh()->data['methods'])->toBe([['shipping_method_id' => null, 'type' => 'fixed', 'prices' => ['GBP' => 0]]]);
});

it('does not share the method list outside the discount pages', function () {
    $this->get(route('panel.dashboard'))
        ->assertInertia(fn (Assert $page) => $page->where('shippingMethods', null));
});
