<?php

use Livewire\Livewire;
use Lunar\Admin\Filament\Widgets\Dashboard\Orders\PopularProductsTable;
use Lunar\Models\Currency;
use Lunar\Models\Language;
use Lunar\Models\Order;
use Lunar\Models\OrderLine;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)
    ->group('widgets.dashboard');

beforeEach(function () {
    $this->asStaff();

    Language::factory()->create(['default' => true]);
    Currency::factory()->create(['default' => true]);
});

it('lists physical and digital products but not shipping lines', function () {
    $order = Order::factory()->create([
        'placed_at' => now()->subDay(),
    ]);

    $physical = OrderLine::factory()->create([
        'order_id' => $order->id,
        'type' => 'physical',
        'identifier' => 'PHYSICAL-SKU',
    ]);

    $digital = OrderLine::factory()->create([
        'order_id' => $order->id,
        'type' => 'digital',
        'identifier' => 'DIGITAL-SKU',
    ]);

    OrderLine::factory()->create([
        'order_id' => $order->id,
        'type' => 'shipping',
        'identifier' => 'SHIPPING-OPTION',
    ]);

    Livewire::test(PopularProductsTable::class)
        ->assertSee('PHYSICAL-SKU')
        ->assertSee('DIGITAL-SKU')
        ->assertDontSee('SHIPPING-OPTION');
});
