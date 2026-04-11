<?php

use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\CustomerResource;
use Lunar\Admin\Filament\Resources\OrderResource\Pages\Components\OrderItemsTable;
use Lunar\Admin\Filament\Resources\OrderResource\Pages\ManageOrder;
use Lunar\Admin\Livewire\Components\ActivityLogFeed as ActivityLogFeedComponent;
use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Base\DataTransferObjects\PaymentCapture;
use Lunar\Base\DataTransferObjects\PaymentRefund;
use Lunar\Base\ValueObjects\Cart\TaxBreakdown;
use Lunar\Base\ValueObjects\Cart\TaxBreakdownAmount;
use Lunar\DataTypes\Price;
use Lunar\Events\RefundCompleted;
use Lunar\Events\RefundRequested;
use Lunar\Facades\Payments;
use Lunar\Facades\Pricing;
use Lunar\Models\Country;
use Lunar\Models\Currency;
use Lunar\Models\Customer;
use Lunar\Models\Language;
use Lunar\Models\Order;
use Lunar\Models\OrderAddress;
use Lunar\Models\OrderLine;
use Lunar\Models\Price as ModelsPrice;
use Lunar\Models\ProductVariant;
use Lunar\Models\Transaction;
use Lunar\PaymentTypes\AbstractPayment;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)
    ->group('resource.order');

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

    Config::set('lunar.payments.types.testing-refund', [
        'driver' => 'testing-refund',
        'authorized' => 'paid',
    ]);

    Payments::extend('testing-refund', fn () => new class extends AbstractPayment
    {
        public function authorize(): ?PaymentAuthorize
        {
            return new PaymentAuthorize(success: true);
        }

        public function refund(Lunar\Models\Contracts\Transaction $transaction, int $amount = 0, $notes = null): PaymentRefund
        {
            $refundTransaction = $transaction->order->transactions()->create([
                'parent_transaction_id' => $transaction->id,
                'success' => true,
                'type' => 'refund',
                'driver' => 'testing-refund',
                'amount' => $amount,
                'reference' => 'admin-refund-'.$amount,
                'status' => 'refunded',
                'notes' => $notes,
                'card_type' => $transaction->card_type,
                'last_four' => $transaction->last_four,
            ]);

            return new PaymentRefund(
                success: true,
                refundTransactionId: $refundTransaction->id,
                reference: $refundTransaction->reference,
                status: $refundTransaction->status,
            );
        }

        public function capture(Lunar\Models\Contracts\Transaction $transaction, $amount = 0): PaymentCapture
        {
            return new PaymentCapture(success: true);
        }
    });
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
        $price = $pricing->matched->price->value;
        $subTotal = $price * $quantity;
        $tax = (int) ($subTotal * .2);
        $options = $variant->values->map(fn ($value) => $value->translate('name'));

        $itemTax = (new TaxBreakdown);
        $itemTax->addAmount(new TaxBreakdownAmount(
            price: new Price(
                value: $tax,
                currency: $currency
            ),
            identifier: $currency->code,
            description: 'VAT',
            percentage: 20,
        ));

        $lines->push([
            'quantity' => $quantity,
            'purchasable_type' => $variant->getMorphClass(),
            'purchasable_id' => $variant->id,
            'type' => 'physical',
            'description' => $variant->product->translateAttribute('name'),
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
        ->assertSee($this->order->total->formatted)
        ->assertSee($this->order->customer->fullName)
        ->assertSee(CustomerResource::getUrl('edit', ['record' => $this->order->customer->id]))
        ->assertSee(__('lunarpanel::order.transactions.capture'))
        ->assertSee($this->order->captures->first()->amount->formatted)
        ->assertSee($this->order->meta['additional_info'])
        ->assertSee($firstItem->total->formatted)
        ->assertSee($firstItem->sub_total->formatted)
        ->assertSee($secondItem->total->formatted)
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

it('can update order status', function () {
    $status = collect(config('lunar.orders.statuses', []))
        ->keys()
        ->reject(fn ($status) => $status == $this->order->status)
        ->random();

    Livewire::test(ManageOrder::class, [
        'record' => $this->order->getRouteKey(),
    ])
        ->assertActionExists('update_status')
        ->callAction('update_status', [
            'status' => $status,
        ]);

    expect($this->order->refresh())
        ->status->toBe($status);
});

it('uses refund orchestration for the header refund action', function () {
    $capture = Transaction::factory()->create([
        'order_id' => $this->order->id,
        'driver' => 'testing-refund',
        'type' => 'capture',
        'success' => true,
        'amount' => 1500,
        'reference' => 'capture-header',
        'status' => 'captured',
        'card_type' => 'visa',
        'last_four' => '4242',
    ]);

    Event::fake([
        RefundRequested::class,
        RefundCompleted::class,
    ]);

    Livewire::test(ManageOrder::class, [
        'record' => $this->order->getRouteKey(),
    ])
        ->callAction('refund', [
            'transaction' => $capture->id,
            'amount' => '15.00',
            'notes' => 'Header refund',
            'confirm' => true,
        ])
        ->assertNotified();

    Event::assertDispatched(RefundRequested::class, function (RefundRequested $event) use ($capture) {
        return $event->refundRequest->transaction->is($capture)
            && $event->refundRequest->amount === 1500
            && $event->refundRequest->lineAllocations === null;
    });

    Event::assertDispatched(RefundCompleted::class, function (RefundCompleted $event) {
        return $event->paymentRefund->success
            && $event->paymentRefund->refundTransactionId !== null;
    });
});

it('uses refund orchestration for the bulk line refund action', function () {
    $capture = Transaction::factory()->create([
        'order_id' => $this->order->id,
        'driver' => 'testing-refund',
        'type' => 'capture',
        'success' => true,
        'amount' => 3000,
        'reference' => 'capture-bulk',
        'status' => 'captured',
        'card_type' => 'visa',
        'last_four' => '4242',
    ]);

    $lines = OrderLine::factory(2)->create([
        'order_id' => $this->order->id,
        'type' => 'physical',
        'total' => 1000,
        'sub_total' => 800,
        'tax_total' => 200,
        'discount_total' => 0,
    ]);

    Event::fake([
        RefundRequested::class,
        RefundCompleted::class,
    ]);

    Livewire::test(OrderItemsTable::class, [
        'record' => $this->order,
    ])
        ->selectTableRecords($lines->pluck('id')->all())
        ->callAction(TestAction::make('bulk_refund')->table()->bulk(), data: [
            'transaction' => $capture->id,
            'amount' => '20.00',
            'notes' => 'Bulk refund',
            'confirm' => true,
        ])
        ->assertNotified();

    Event::assertDispatched(RefundRequested::class, function (RefundRequested $event) use ($capture, $lines) {
        $allocations = collect($event->refundRequest->lineAllocations);

        return $event->refundRequest->transaction->is($capture)
            && $event->refundRequest->amount === 2000
            && $allocations->pluck('order_line_id')->all() === $lines->pluck('id')->all();
    });

    Event::assertDispatched(RefundCompleted::class);
});
