<?php

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\OrderResource\Pages\Components\OrderFulfilments;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Fulfilment;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\States\Fulfilment\InProgress;
use Lunar\Filament\Support\Facades\LunarFilament;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

class AdminShipNotification extends Notification
{
    public function __construct(public Fulfilment $fulfilment) {}

    public function via(): array
    {
        return ['mail'];
    }
}

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
    $fulfilment = $this->order->createFulfilment([$this->line->id => 5]);

    Livewire::test(OrderFulfilments::class, ['record' => $this->order])
        ->assertOk()
        // No reference set, so the card falls back to the id-based label.
        ->assertSee('Fulfilment #'.$fulfilment->id);
});

it('ships a fulfilment and records multiple trackings', function () {
    $fulfilment = $this->order->createFulfilment([$this->line->id => 2]);

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
    $fulfilment = $this->order->createFulfilment([$this->line->id => 1]);

    Livewire::test(OrderFulfilments::class, ['record' => $this->order])
        ->callAction('ship', data: [
            'tracking' => [
                ['carrier' => 'royal-mail', 'shipping_method' => 'tracked-24', 'tracking_number' => 'RM123456789GB'],
            ],
        ], arguments: ['fulfilment' => $fulfilment->id])
        ->assertHasNoActionErrors();

    $tracking = $fulfilment->refresh()->trackings->first();

    expect($tracking->carrier)->toBe('royal-mail')
        ->and($tracking->shipping_method)->toBe('tracked-24')
        ->and($tracking->shippingMethodLabel())->toBe('Tracked 24')
        ->and($tracking->url)->toContain('RM123456789GB');
});

it('removes a tracking reference', function () {
    $fulfilment = $this->order->createFulfilment([$this->line->id => 2])->ship([
        ['tracking_number' => 'TRK-1'],
        ['tracking_number' => 'TRK-2'],
    ]);
    $tracking = $fulfilment->trackings->first();

    Livewire::test(OrderFulfilments::class, ['record' => $this->order])
        ->callAction('removeTracking', arguments: ['tracking' => $tracking->id])
        ->assertHasNoActionErrors();

    expect($fulfilment->refresh()->trackings)->toHaveCount(1);
});

it('adds a tracking reference to a shipped fulfilment', function () {
    $fulfilment = $this->order->createFulfilment([$this->line->id => 2])->ship([
        ['tracking_number' => 'TRK-1'],
    ]);

    Livewire::test(OrderFulfilments::class, ['record' => $this->order])
        ->callAction('addTracking', data: ['tracking_number' => 'TRK-2'], arguments: ['fulfilment' => $fulfilment->id])
        ->assertHasNoActionErrors();

    expect($fulfilment->refresh()->trackings)->toHaveCount(2);
});

it('splits a pre-ship parcel into a new one via inline mode', function () {
    $source = $this->order->createFulfilment([$this->line->id => 4]);

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
    $source = $this->order->createFulfilment([$this->line->id => 4]);

    Livewire::test(OrderFulfilments::class, ['record' => $this->order])
        ->call('startSplit', $source->id)
        ->call('confirmSplit')
        ->assertSet('splittingId', $source->id);

    expect($this->order->fulfilments()->count())->toBe(1);
});

it('merges a parcel into the only other one, auto-selecting the target', function () {
    $source = $this->order->createFulfilment([$this->line->id => 2]);
    $target = $this->order->createFulfilment([$this->line->id => 3]);

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
    $source = $this->order->createFulfilment([$this->line->id => 3]);
    $targetA = $this->order->createFulfilment([$this->line->id => 1]);
    $targetB = $this->order->createFulfilment([$this->line->id => 1]);

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

    $fulfilment = $this->order->createFulfilment([$this->line->id => 2]);
    $line = $fulfilment->lines()->with('orderLine')->first();

    $component = new OrderFulfilments;
    $component->record = $this->order;

    $labels = collect($component->lineDetails($line))->pluck('label');

    expect($labels)->toContain('Warehouse')
        ->and($labels)->toContain(__('lunarpanel::order.fulfilments.fields.unit_price'));
});

it('lets developers reshape the more-actions menu per method', function () {
    LunarFilament::extensions([
        OrderFulfilments::class => new class
        {
            public function extendFulfilmentActions(array $actions, $fulfilment): array
            {
                // Digital parcels don't move between physical locations.
                if ($fulfilment->method === 'digital') {
                    unset($actions['changeLocation']);
                }

                $actions['verify'] = ['label' => 'Verify', 'icon' => 'heroicon-m-check', 'color' => null, 'wire' => 'noop'];

                return $actions;
            }
        },
    ]);

    Location::factory()->create();
    $fulfilment = $this->order->createFulfilment([$this->line->id => 2], ['method' => 'digital']);

    $component = new OrderFulfilments;
    $component->record = $this->order;

    $actions = $component->moreActions($fulfilment->refresh()->load('lines'));

    expect($actions)->toHaveKey('verify')
        ->and($actions)->not->toHaveKey('changeLocation');
});

it('changes a fulfilment location', function () {
    $other = Location::factory()->create();
    $fulfilment = $this->order->createFulfilment([$this->line->id => 2]);

    Livewire::test(OrderFulfilments::class, ['record' => $this->order])
        ->callAction('changeLocation', data: ['location' => $other->id], arguments: ['fulfilment' => $fulfilment->id])
        ->assertHasNoActionErrors();

    expect($fulfilment->refresh()->location_id)->toBe($other->id);
});

it('lists the states a pending fulfilment can move to', function () {
    $fulfilment = $this->order->createFulfilment([$this->line->id => 2]);

    $component = new OrderFulfilments;
    $component->record = $this->order;

    $names = $component->statusTransitions($fulfilment->load('lines'))->pluck('name');

    // Forward steps only — reverting (pending) / cancelling are not menu moves.
    expect($names)->toContain('in-progress', 'shipped')
        ->and($names)->not->toContain('pending')
        ->and($names)->not->toContain('cancelled')
        ->and($names)->not->toContain('returned');
});

it('moves a fulfilment to a new state via the transition action', function () {
    $fulfilment = $this->order->createFulfilment([$this->line->id => 2]);

    Livewire::test(OrderFulfilments::class, ['record' => $this->order])
        ->callAction('transition', arguments: ['fulfilment' => $fulfilment->id, 'state' => 'in-progress'])
        ->assertHasNoActionErrors();

    expect((string) $fulfilment->refresh()->state)->toBe('in-progress');
});

it('cancels a shipped fulfilment back to pending via the cancel action', function () {
    $fulfilment = $this->order->createFulfilment([$this->line->id => 2])->ship();

    Livewire::test(OrderFulfilments::class, ['record' => $this->order])
        ->callAction('cancelFulfilment', arguments: ['fulfilment' => $fulfilment->id])
        ->assertHasNoActionErrors();

    expect((string) $fulfilment->refresh()->state)->toBe('pending')
        ->and($fulfilment->shipped_at)->toBeNull();
});

it('cancels an in-progress fulfilment back to pending', function () {
    $fulfilment = $this->order->createFulfilment([$this->line->id => 2]);
    $fulfilment->transition(InProgress::class);

    Livewire::test(OrderFulfilments::class, ['record' => $this->order])
        ->callAction('cancelFulfilment', arguments: ['fulfilment' => $fulfilment->id])
        ->assertHasNoActionErrors();

    expect((string) $fulfilment->refresh()->state)->toBe('pending');
});

it('holds and releases a fulfilment via the actions', function () {
    $fulfilment = $this->order->createFulfilment([$this->line->id => 2]);

    Livewire::test(OrderFulfilments::class, ['record' => $this->order])
        ->callAction('hold', data: ['reason' => 'out-of-stock', 'note' => 'Restock Friday'], arguments: ['fulfilment' => $fulfilment->id])
        ->assertHasNoActionErrors();

    expect($fulfilment->refresh()->isOnHold())->toBeTrue()
        ->and($fulfilment->hold_reason)->toBe('out-of-stock');

    Livewire::test(OrderFulfilments::class, ['record' => $this->order])
        ->callAction('release', arguments: ['fulfilment' => $fulfilment->id])
        ->assertHasNoActionErrors();

    expect($fulfilment->refresh()->isOnHold())->toBeFalse();
});

it('omits shipped from the update-status menu while held', function () {
    $fulfilment = $this->order->createFulfilment([$this->line->id => 2]);
    $fulfilment->hold();

    $component = new OrderFulfilments;
    $component->record = $this->order;

    $names = $component->statusTransitions($fulfilment->refresh()->load('lines'))->pluck('name');

    expect($names)->not->toContain('shipped')
        ->and($names)->toContain('in-progress');
});

it('undoes a return back to shipped via the action', function () {
    $fulfilment = $this->order->createFulfilment([$this->line->id => 2])->ship()->markReturned();

    Livewire::test(OrderFulfilments::class, ['record' => $this->order])
        ->callAction('undoReturn', arguments: ['fulfilment' => $fulfilment->id])
        ->assertHasNoActionErrors();

    expect((string) $fulfilment->refresh()->state)->toBe('shipped');
});

it('returns a shipped fulfilment', function () {
    $fulfilment = $this->order->createFulfilment([$this->line->id => 2])->ship();

    Livewire::test(OrderFulfilments::class, ['record' => $this->order])
        ->callAction('return', arguments: ['fulfilment' => $fulfilment->id])
        ->assertHasNoActionErrors();

    expect((string) $fulfilment->refresh()->state)->toBe('returned');
});

it('fulfils a collection parcel via the no-tracking fulfil action', function () {
    $fulfilment = $this->order->createFulfilment([$this->line->id => 5], ['method' => 'collection']);

    Livewire::test(OrderFulfilments::class, ['record' => $this->order])
        ->callAction('fulfil', arguments: ['fulfilment' => $fulfilment->id])
        ->assertHasNoActionErrors();

    expect((string) $fulfilment->refresh()->state)->toBe('collected')
        ->and($fulfilment->shipped_at)->not->toBeNull();
});

it('routes the terminal status to fulfil for collection and ship for shipping', function () {
    $component = Livewire::test(OrderFulfilments::class, ['record' => $this->order])->instance();

    $collection = $this->order->createFulfilment([$this->line->id => 2], ['method' => 'collection']);
    $shipping = $this->order->createFulfilment([$this->line->id => 2]);

    $collectionTransitions = $component->statusTransitions($collection->refresh()->load('lines'));
    $shippingTransitions = $component->statusTransitions($shipping->refresh()->load('lines'));

    expect($collectionTransitions->firstWhere('name', 'collected')['action'])->toBe('fulfil')
        ->and($collectionTransitions->pluck('name'))->not->toContain('shipped')
        ->and($shippingTransitions->firstWhere('name', 'shipped')['action'])->toBe('ship');
});

it('shows the notify toggle on the ship modal only when a shipped notification is configured', function () {
    $fulfilment = $this->order->createFulfilment([$this->line->id => 1]);

    // Nothing configured for the shipped state — progressive disclosure hides it.
    config(['lunar.orders.notifications' => []]);
    Livewire::test(OrderFulfilments::class, ['record' => $this->order])
        ->mountAction('ship', arguments: ['fulfilment' => $fulfilment->id])
        ->assertSchemaComponentDoesNotExist('notify');

    // Configure one and the toggle appears.
    config(['lunar.orders.notifications' => ['shipped' => [AdminShipNotification::class]]]);
    Livewire::test(OrderFulfilments::class, ['record' => $this->order])
        ->mountAction('ship', arguments: ['fulfilment' => $fulfilment->id])
        ->assertSchemaComponentExists('notify');
});

it('sends the per-parcel notification when shipping with the notify toggle on', function () {
    config(['lunar.orders.notifications' => ['shipped' => [AdminShipNotification::class]]]);
    NotificationFacade::fake();

    $fulfilment = $this->order->createFulfilment([$this->line->id => 1]);

    Livewire::test(OrderFulfilments::class, ['record' => $this->order])
        ->callAction('ship', data: [
            'tracking' => [['tracking_number' => 'TRK-1']],
            'notify' => true,
        ], arguments: ['fulfilment' => $fulfilment->id])
        ->assertHasNoActionErrors();

    NotificationFacade::assertSentTo($this->order->fresh(), AdminShipNotification::class);
});

it('suppresses the per-parcel notification when the ship notify toggle is unticked', function () {
    config(['lunar.orders.notifications' => ['shipped' => [AdminShipNotification::class]]]);
    NotificationFacade::fake();

    $fulfilment = $this->order->createFulfilment([$this->line->id => 1]);

    Livewire::test(OrderFulfilments::class, ['record' => $this->order])
        ->callAction('ship', data: [
            'tracking' => [['tracking_number' => 'TRK-1']],
            'notify' => false,
        ], arguments: ['fulfilment' => $fulfilment->id])
        ->assertHasNoActionErrors();

    NotificationFacade::assertNotSentTo($this->order->fresh(), AdminShipNotification::class);
});
