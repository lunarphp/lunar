<?php

use Livewire\Livewire;
use Lunar\Admin\Filament\Widgets\Dashboard\Orders\LatestOrdersTable;
use Lunar\Models\Currency;
use Lunar\Models\Language;
use Lunar\Models\Order;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)
    ->group('widgets.dashboard');

beforeEach(function () {
    $this->asStaff();

    Language::factory()->create(['default' => true]);
    Currency::factory()->create(['default' => true]);
});

it('only lists placed orders', function () {
    $placed = Order::factory()->create([
        'placed_at' => now()->subDay(),
    ]);

    $unplaced = Order::factory()->create([
        'placed_at' => null,
        'status' => 'awaiting-payment',
    ]);

    Livewire::test(LatestOrdersTable::class)
        ->assertCanSeeTableRecords([$placed])
        ->assertCanNotSeeTableRecords([$unplaced]);
});
