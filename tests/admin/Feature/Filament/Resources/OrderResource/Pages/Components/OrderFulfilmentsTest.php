<?php

use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\OrderResource\Pages\Components\OrderFulfilments;
use Lunar\Core\Facades\Fulfilments;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderLine;
use Lunar\Filament\Support\Facades\LunarFilament;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)->group('resource.order');

beforeEach(function () {
    $this->asStaff();

    $currency = Currency::factory()->create(['default' => true]);
    Language::factory()->create(['default' => true, 'code' => 'en']);
    Location::factory()->default()->create();

    $this->order = Order::factory()->create(['currency_code' => $currency->code]);
    $this->line = OrderLine::factory()->create([
        'order_id' => $this->order->id,
        'type' => 'physical',
        'quantity' => 5,
    ]);
});

it('renders the fulfilments panel', function () {
    $fulfilment = Fulfilments::create($this->order, [$this->line->id => 5]);

    Livewire::test(OrderFulfilments::class, ['record' => $this->order])
        ->assertOk()
        // No reference set, so the card falls back to the id-based label.
        ->assertSee('Fulfilment #'.$fulfilment->id);
});

it('ships a fulfilment and records multiple trackings', function () {
    $fulfilment = Fulfilments::create($this->order, [$this->line->id => 2]);

    Livewire::test(OrderFulfilments::class, ['record' => $this->order])
        ->callAction('ship', data: [
            'tracking' => [
                ['tracking_number' => 'TRK-1', 'shipping_method' => 'Standard'],
                ['tracking_number' => 'TRK-2'],
            ],
        ], arguments: ['fulfilment' => $fulfilment->id])
        ->assertHasNoActionErrors();

    expect((string) $fulfilment->refresh()->state)->toBe('shipped')
        ->and($fulfilment->trackings)->toHaveCount(2);
});

it('ships with a registered carrier and derives the tracking url', function () {
    $fulfilment = Fulfilments::create($this->order, [$this->line->id => 1]);

    Livewire::test(OrderFulfilments::class, ['record' => $this->order])
        ->callAction('ship', data: [
            'tracking' => [
                ['carrier' => 'royal-mail', 'shipping_method' => 'Tracked 24', 'tracking_number' => 'RM123456789GB'],
            ],
        ], arguments: ['fulfilment' => $fulfilment->id])
        ->assertHasNoActionErrors();

    $tracking = $fulfilment->refresh()->trackings->first();

    expect($tracking->carrier)->toBe('royal-mail')
        ->and($tracking->shipping_method)->toBe('Tracked 24')
        ->and($tracking->url)->toContain('RM123456789GB');
});

it('adds a tracking reference to a shipped fulfilment', function () {
    $fulfilment = Fulfilments::ship(Fulfilments::create($this->order, [$this->line->id => 2]), [
        ['tracking_number' => 'TRK-1'],
    ]);

    Livewire::test(OrderFulfilments::class, ['record' => $this->order])
        ->callAction('addTracking', data: ['tracking_number' => 'TRK-2'], arguments: ['fulfilment' => $fulfilment->id])
        ->assertHasNoActionErrors();

    expect($fulfilment->refresh()->trackings)->toHaveCount(2);
});

it('splits a pre-ship parcel into a new one via inline mode', function () {
    $source = Fulfilments::create($this->order, [$this->line->id => 4]);

    Livewire::test(OrderFulfilments::class, ['record' => $this->order])
        ->call('startSplit', $source->id)
        ->assertSet('splitQuantities', [$this->line->id => 0])
        ->set('splitQuantities.'.$this->line->id, 1)
        ->call('confirmSplit')
        ->assertSet('splittingId', null);

    expect($this->order->fulfilments()->count())->toBe(2)
        ->and($source->refresh()->lines()->first()->quantity)->toBe(3);
});

it('keeps split mode open and creates nothing when no quantity is chosen', function () {
    $source = Fulfilments::create($this->order, [$this->line->id => 4]);

    Livewire::test(OrderFulfilments::class, ['record' => $this->order])
        ->call('startSplit', $source->id)
        ->call('confirmSplit')
        ->assertSet('splittingId', $source->id);

    expect($this->order->fulfilments()->count())->toBe(1);
});

it('merges a parcel into the only other one, auto-selecting the target', function () {
    $source = Fulfilments::create($this->order, [$this->line->id => 2]);
    $target = Fulfilments::create($this->order, [$this->line->id => 3]);

    Livewire::test(OrderFulfilments::class, ['record' => $this->order])
        ->call('startMerge', $source->id)
        ->assertSet('mergeTargetId', $target->id)
        ->assertSet('mergeQuantities', [$this->line->id => 2])
        ->call('confirmMerge')
        ->assertSet('mergingId', null);

    expect($this->order->fulfilments()->count())->toBe(1)
        ->and($target->refresh()->lines()->first()->quantity)->toBe(5);
});

it('merges selected quantities into a chosen target when several exist', function () {
    $source = Fulfilments::create($this->order, [$this->line->id => 3]);
    $targetA = Fulfilments::create($this->order, [$this->line->id => 1]);
    $targetB = Fulfilments::create($this->order, [$this->line->id => 1]);

    Livewire::test(OrderFulfilments::class, ['record' => $this->order])
        ->call('startMerge', $source->id)
        ->set('mergeQuantities.'.$this->line->id, 2)
        ->set('mergeTargetId', $targetB->id)
        ->call('confirmMerge');

    expect($this->order->fulfilments()->count())->toBe(3)
        ->and($source->refresh()->lines()->first()->quantity)->toBe(1)
        ->and($targetB->refresh()->lines()->first()->quantity)->toBe(3);
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

it('changes a fulfilment location', function () {
    $other = Location::factory()->create();
    $fulfilment = Fulfilments::create($this->order, [$this->line->id => 2]);

    Livewire::test(OrderFulfilments::class, ['record' => $this->order])
        ->callAction('changeLocation', data: ['location' => $other->id], arguments: ['fulfilment' => $fulfilment->id])
        ->assertHasNoActionErrors();

    expect($fulfilment->refresh()->location_id)->toBe($other->id);
});

it('returns a shipped fulfilment', function () {
    $fulfilment = Fulfilments::ship(Fulfilments::create($this->order, [$this->line->id => 2]));

    Livewire::test(OrderFulfilments::class, ['record' => $this->order])
        ->callAction('return', arguments: ['fulfilment' => $fulfilment->id])
        ->assertHasNoActionErrors();

    expect((string) $fulfilment->refresh()->state)->toBe('returned');
});
