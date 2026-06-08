<?php

use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\OrderResource\Pages\Components\OrderFulfilments;
use Lunar\Core\Facades\Fulfilments;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderLine;
use Lunar\Filament\Support\Facades\LunarFilament;
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

it('renders the fulfilments panel', function () {
    Fulfilments::create($this->order, [$this->line->id => 5]);

    Livewire::test(OrderFulfilments::class, ['record' => $this->order])
        ->assertOk()
        ->assertSee($this->order->fulfilments()->first()->reference);
});

it('ships a fulfilment and records tracking', function () {
    $fulfilment = Fulfilments::create($this->order, [$this->line->id => 2]);

    Livewire::test(OrderFulfilments::class, ['record' => $this->order])
        ->callAction('ship', data: ['tracking_number' => 'TRK-1'], arguments: ['fulfilment' => $fulfilment->id])
        ->assertHasNoActionErrors();

    expect((string) $fulfilment->refresh()->state)->toBe('shipped')
        ->and($fulfilment->tracking_number)->toBe('TRK-1');
});

it('splits a pre-ship parcel into a new one', function () {
    $source = Fulfilments::create($this->order, [$this->line->id => 4]);

    Livewire::test(OrderFulfilments::class, ['record' => $this->order])
        ->callAction('split', data: ['qty_'.$this->line->id => 1], arguments: ['fulfilment' => $source->id])
        ->assertHasNoActionErrors();

    expect($this->order->fulfilments()->count())->toBe(2)
        ->and($source->refresh()->lines()->first()->quantity)->toBe(3);
});

it('merges selected parcels into the target', function () {
    $target = Fulfilments::create($this->order, [$this->line->id => 3]);
    $source = Fulfilments::create($this->order, [$this->line->id => 2]);

    Livewire::test(OrderFulfilments::class, ['record' => $this->order])
        ->callAction('merge', data: ['sources' => [$source->id]], arguments: ['fulfilment' => $target->id])
        ->assertHasNoActionErrors();

    expect($this->order->fulfilments()->count())->toBe(1)
        ->and($target->refresh()->lines()->first()->quantity)->toBe(5);
});

it('lets developers extend the expanded line details', function () {
    LunarFilament::extensions([
        OrderFulfilments::class => new class
        {
            public function extendFulfilmentLineDetails(array $rows, $line): array
            {
                $rows[] = ['label' => 'Warehouse', 'value' => 'Bay 12'];

                return $rows;
            }
        },
    ]);

    $fulfilment = Fulfilments::create($this->order, [$this->line->id => 2]);
    $line = $fulfilment->lines()->with('orderLine')->first();

    $component = new OrderFulfilments;
    $component->record = $this->order;

    $labels = collect($component->lineDetails($line))->pluck('label');

    expect($labels)->toContain('Warehouse')
        ->and($labels)->toContain(__('lunarpanel::order.fulfilments.fields.unit_price'));
});

it('returns a shipped fulfilment', function () {
    $fulfilment = Fulfilments::ship(Fulfilments::create($this->order, [$this->line->id => 2]));

    Livewire::test(OrderFulfilments::class, ['record' => $this->order])
        ->callAction('return', arguments: ['fulfilment' => $fulfilment->id])
        ->assertHasNoActionErrors();

    expect((string) $fulfilment->refresh()->state)->toBe('returned');
});
