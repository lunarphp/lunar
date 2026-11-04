<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Models\Currency;
use Lunar\Shipping\Models\ShippingMethod;
use Lunar\Shipping\Panel\DiscountTypeForms\ShippingDiscountForm;
use Lunar\Tests\Shipping\PanelTestCase;

uses(PanelTestCase::class, RefreshDatabase::class)->group('shipping', 'shipping-panel');

beforeEach(function () {
    $this->gbp = Currency::factory()->create(['code' => 'GBP', 'decimal_places' => 2, 'default' => true]);
    $this->jpy = Currency::factory()->create(['code' => 'JPY', 'decimal_places' => 0, 'default' => false]);
    $this->form = app(ShippingDiscountForm::class);
});

test('it targets no buckets and names its component', function () {
    expect($this->form->targetBuckets())->toBe([])
        ->and($this->form->component())->toBe('shipping::ShippingDiscountForm');
});

test('stored minor-unit prices decode to major units per currency and encode back', function () {
    $method = ShippingMethod::factory()->create();

    $form = $this->form->toForm(['methods' => [
        ['shipping_method_id' => $method->id, 'type' => 'fixed', 'prices' => ['GBP' => 250, 'JPY' => 300]],
        ['shipping_method_id' => null, 'type' => 'percentage', 'percentage' => 15],
    ]]);

    expect($form['methods'][0]['prices'])->toBe(['GBP' => 2.5, 'JPY' => 300.0])
        ->and($form['methods'][1]['shipping_method_id'])->toBeNull()
        ->and($form['methods'][1]['percentage'])->toBe(15);

    $stored = $this->form->toStorage(['methods' => [
        ['shipping_method_id' => (string) $method->id, 'type' => 'fixed', 'prices' => ['GBP' => '2.50', 'JPY' => '300']],
        ['shipping_method_id' => '', 'type' => 'percentage', 'percentage' => '15', 'prices' => ['GBP' => '9']],
    ]]);

    expect($stored['methods'][0])->toBe(['shipping_method_id' => $method->id, 'type' => 'fixed', 'prices' => ['GBP' => 250, 'JPY' => 300]])
        ->and($stored['methods'][1])->toBe(['shipping_method_id' => null, 'type' => 'percentage', 'percentage' => 15.0]);
});

test('rules validate the rule list without the data prefix', function () {
    $rules = $this->form->rules();

    expect($rules)->toHaveKeys(['methods', 'methods.*.type', 'methods.*.percentage', 'methods.*.prices.GBP', 'methods.*.prices.JPY']);

    $validator = validator(['methods' => [['type' => 'percentage', 'percentage' => 150]]], $rules);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('methods.0.percentage'))->toBeTrue();
});

test('the summary reads the stored rules', function () {
    expect($this->form->summary(['methods' => []], $this->gbp))->toBeNull()
        ->and($this->form->summary(['methods' => [['type' => 'fixed', 'prices' => ['GBP' => 0]]]], $this->gbp))->toBe('Free shipping')
        ->and($this->form->summary(['methods' => [['type' => 'fixed', 'prices' => ['GBP' => 299]], ['type' => 'fixed', 'prices' => ['GBP' => 199]]]], $this->gbp))->toBe('Shipping from £1.99')
        ->and($this->form->summary(['methods' => [['type' => 'percentage', 'percentage' => 12.5]]], $this->gbp))->toBe('12.5% off shipping')
        ->and($this->form->summary(['methods' => [['type' => 'percentage', 'percentage' => 10], ['type' => 'fixed', 'prices' => ['GBP' => 0]]]], $this->gbp))->toBeNull();
});
