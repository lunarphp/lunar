<?php

use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\OrderResource\Pages\Components\FulfilmentsTable;
use Lunar\Core\Facades\Fulfilments;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderLine;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)->group('resource.order');

beforeEach(function () {
    $this->asStaff();

    $currency = Currency::factory()->create(['default' => true]);
    Language::factory()->create(['default' => true, 'code' => 'en']);

    $this->order = Order::factory()->create(['currency_code' => $currency->code]);
    $this->line = OrderLine::factory()->create([
        'order_id' => $this->order->id,
        'type' => 'physical',
        'quantity' => 5,
    ]);
});

it('renders the fulfilments table without a create action', function () {
    Livewire::test(FulfilmentsTable::class, ['record' => $this->order])
        ->assertOk()
        ->assertActionDoesNotExist('create_fulfilment');
});

it('ships a fulfilment and records tracking', function () {
    $fulfilment = Fulfilments::create($this->order, [$this->line->id => 2]);

    Livewire::test(FulfilmentsTable::class, ['record' => $this->order])
        ->callTableAction('ship', $fulfilment, data: ['tracking_number' => 'TRK-1'])
        ->assertHasNoTableActionErrors();

    expect((string) $fulfilment->refresh()->state)->toBe('shipped')
        ->and($fulfilment->tracking_number)->toBe('TRK-1');
});

it('only offers ship/split on a pending parcel and return on a shipped one', function () {
    $pending = Fulfilments::create($this->order, [$this->line->id => 2]);
    $shipped = Fulfilments::ship(Fulfilments::create($this->order, [$this->line->id => 1]));

    Livewire::test(FulfilmentsTable::class, ['record' => $this->order])
        ->assertTableActionVisible('ship', $pending)
        ->assertTableActionVisible('split', $pending)
        ->assertTableActionHidden('return', $pending)
        ->assertTableActionVisible('return', $shipped)
        ->assertTableActionHidden('ship', $shipped);
});

it('splits a pre-ship parcel into a new one', function () {
    $source = Fulfilments::create($this->order, [$this->line->id => 4]);

    Livewire::test(FulfilmentsTable::class, ['record' => $this->order])
        ->callTableAction('split', $source, data: ['qty_'.$this->line->id => 1])
        ->assertHasNoTableActionErrors();

    expect($this->order->fulfilments()->count())->toBe(2)
        ->and($source->refresh()->lines()->first()->quantity)->toBe(3);
});
