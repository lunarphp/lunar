<?php

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\CustomerResource;
use Lunar\Admin\Filament\Resources\OrderResource\Pages\ManageOrder;
use Lunar\Admin\Livewire\Components\ActivityLogFeed as ActivityLogFeedComponent;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Facades\CustomerNotifications;
use Lunar\Core\Facades\Pricing;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderAddress;
use Lunar\Core\Models\Price as ModelsPrice;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\Transaction;
use Lunar\Core\ValueObjects\Cart\TaxBreakdown;
use Lunar\Core\ValueObjects\Cart\TaxBreakdownAmount;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)
    ->group('resource.order');

class FakeAdminOrderUpdateNotification extends Notification
{
    public function __construct(public Order $order, public ?string $message = null) {}

    public function via(): array
    {
        return ['mail'];
    }
}

beforeEach(function () {
    $this->asStaff();

    $currency = Currency::factory()->create([
        'default' => true,
    ]);

    $country = Country::factory()->create();

    Language::factory()->create();

    $this->order = Order::factory()
        ->for(Customer::factory())
        ->has(OrderAddress::factory()->state([
            'type' => 'shipping',
            'country_id' => $country->id,
        ]), 'shippingAddress')
        ->has(OrderAddress::factory()->state([
            'type' => 'billing',
            'country_id' => $country->id,
        ]), 'billingAddress')
        ->create([
            'currency_code' => $currency->code,
            'meta' => [
                'additional_info' => Str::random(),
            ],
        ]);

});

it('can render order manage page', function () {
    $currency = Currency::getDefault();

    $variants = ProductVariant::factory(5)
        ->has(ModelsPrice::factory()->state([
            'currency_id' => $currency->id,
        ]))->create();

    $lines = collect();

    foreach ($variants as $variant) {
        $quantity = rand(1, 5);

        $pricing = Pricing::for($variant, $quantity)->get();
        $price = $pricing->matched->price;
        $subTotal = $price * $quantity;
        $tax = (int) ($subTotal * .2);
        $options = $variant->values->map(fn ($value) => $value->translate('name'));

        $itemTax = (new TaxBreakdown);
        $itemTax->addAmount(new TaxBreakdownAmount(
            price: new PriceValue(value: $tax, currency: $currency),
            identifier: $currency->code,
            description: 'VAT',
            percentage: 20,
        ));

        $lines->push([
            'quantity' => $quantity,
            'purchasable_type' => $variant->getMorphClass(),
            'purchasable_id' => $variant->id,
            'type' => 'physical',
            'description' => $variant->product->translate('name'),
            'identifier' => $variant->sku,
            'option' => $options->join(', '),
            'unit_price' => $price,
            'unit_quantity' => $variant->unit_quantity,
            'sub_total' => $subTotal,
            'discount_total' => 0,
            'tax_total' => $tax,
            'total' => $subTotal + $tax,
            'tax_breakdown' => $itemTax,
        ]);
    }

    $this->order->transactions()->save(Transaction::factory()->create([
        'driver' => 'offline',
        'type' => 'capture',
        'amount' => $lines->sum('total'),
    ]));

    $lines = $this->order->lines()->createMany($lines->toArray());

    $firstItem = $lines->first();
    $secondItem = $lines->skip(1)->take(1)->first();

    expect($firstItem)
        ->not->toBe($secondItem);

    Livewire::test(ManageOrder::class, [
        'record' => $this->order->getRouteKey(),
    ])
        ->assertSuccessful()
        ->assertSeeLivewire(ActivityLogFeedComponent::class)
        ->assertSee($this->order->tags)
        ->assertSee($this->order->shippingAddress->line_one)
        ->assertSee($this->order->shippingAddress->line_one)
        ->assertSee($this->order->format('total'))
        ->assertSee($this->order->customer->fullName)
        ->assertSee(CustomerResource::getUrl('edit', ['record' => $this->order->customer->id]))
        ->assertSee(__('lunarpanel::order.transactions.capture'))
        ->assertSee($this->order->captures->first()->format('amount'))
        ->assertSee($this->order->meta['additional_info'])
        ->assertSee($firstItem->format('total'))
        ->assertSee($firstItem->format('sub_total'))
        ->assertSee($secondItem->format('total'))
        ->assertSee($this->order->reference);
});

it('can download order pdf', function () {
    Livewire::test(ManageOrder::class, [
        'record' => $this->order->getRouteKey(),
    ])
        ->assertActionExists('download_pdf')
        ->callAction('download_pdf')
        ->assertFileDownloaded("Order-{$this->order->reference}.pdf");
});

it('can close an order', function () {
    Livewire::test(ManageOrder::class, [
        'record' => $this->order->getRouteKey(),
    ])
        ->assertActionExists('close_order')
        ->callAction('close_order');

    expect($this->order->refresh()->isClosed())->toBeTrue();
});

it('can cancel an unfulfilled order', function () {
    Livewire::test(ManageOrder::class, [
        'record' => $this->order->getRouteKey(),
    ])
        ->assertActionExists('cancel_order')
        ->callAction('cancel_order', data: ['reason' => 'items-unavailable', 'note' => 'No stock', 'notify' => false]);

    $this->order->refresh();

    expect($this->order->isCancelled())->toBeTrue()
        ->and($this->order->cancel_reason)->toBe('items-unavailable')
        ->and($this->order->isClosed())->toBeTrue();
});

it('can reopen a closed order', function () {
    $this->order->forceFill(['closed_at' => now()])->save();

    Livewire::test(ManageOrder::class, [
        'record' => $this->order->getRouteKey(),
    ])
        ->assertActionExists('reopen_order')
        ->callAction('reopen_order');

    expect($this->order->refresh()->isOpen())->toBeTrue();
});

it('renders the order page when an address has no country', function () {
    $order = Order::factory()
        ->for(Customer::factory())
        ->has(OrderAddress::factory()->state(['type' => 'shipping', 'country_id' => null]), 'shippingAddress')
        ->has(OrderAddress::factory()->state(['type' => 'billing', 'country_id' => null]), 'billingAddress')
        ->create(['currency_code' => Currency::getDefault()->code]);

    Livewire::test(ManageOrder::class, [
        'record' => $order->getRouteKey(),
    ])->assertSuccessful();
});

it('hides the notify customer action while no notifications are registered', function () {
    CustomerNotifications::forget('order-update');

    Livewire::test(ManageOrder::class, [
        'record' => $this->order->getRouteKey(),
    ])->assertActionHidden('notify_customer');
});

it('can notify the customer with a chosen notification', function () {
    activity()->enableLogging();
    CustomerNotifications::register('order-update', FakeAdminOrderUpdateNotification::class, 'Order update');
    NotificationFacade::fake();

    $this->order->billingAddress->update(['contact_email' => 'buyer@example.com']);

    Livewire::test(ManageOrder::class, [
        'record' => $this->order->getRouteKey(),
    ])
        ->assertActionExists('notify_customer')
        ->callAction('notify_customer', data: [
            'notification' => 'order-update',
            'message' => 'Sorry for the delay',
            'recipients' => ['buyer@example.com'],
        ]);

    NotificationFacade::assertSentOnDemand(FakeAdminOrderUpdateNotification::class);

    CustomerNotifications::forget('order-update');
});
