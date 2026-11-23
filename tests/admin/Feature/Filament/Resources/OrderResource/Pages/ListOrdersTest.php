<?php

use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\OrderResource\Pages\ListOrders;
use Lunar\Core\Enums\PaymentTerms;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Order;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)
    ->group('resource.order');

beforeEach(function () {
    $this->asStaff();

    Currency::factory()->create(['default' => true]);
    Language::factory()->create();
});

it('labels an order awaiting payment by its terms', function () {
    $order = Order::factory()->create([
        'placed_at' => now(),
        'total' => 5000,
        'payment_terms' => PaymentTerms::OnPickup,
    ]);

    Livewire::test(ListOrders::class)
        ->assertTableColumnFormattedStateSet('payment_status', 'Pay on pickup', $order);
});
