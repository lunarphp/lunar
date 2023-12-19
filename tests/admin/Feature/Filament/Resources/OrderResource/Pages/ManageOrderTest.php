<?php

use Illuminate\Support\Str;
use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\CustomerResource;
use Lunar\Admin\Filament\Resources\OrderResource\Pages\ManageOrder;
use Lunar\Admin\Livewire\Components\ActivityLogFeed as ActivityLogFeedComponent;
use Lunar\Admin\Livewire\Components\Tags as TagsComponent;
use Lunar\Base\ValueObjects\Cart\TaxBreakdown;
use Lunar\Base\ValueObjects\Cart\TaxBreakdownAmount;
use Lunar\DataTypes\Price;
use Lunar\Facades\Pricing;
use Lunar\Models\Country;
use Lunar\Models\Currency;
use Lunar\Models\Customer;
use Lunar\Models\Language;
use Lunar\Models\Order;
use Lunar\Models\OrderAddress;
use Lunar\Models\Price as ModelsPrice;
use Lunar\Models\ProductVariant;
use Lunar\Models\Transaction;

uses(\Lunar\Tests\Admin\Feature\Filament\TestCase::class)
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

        $itemTax = (new TaxBreakdown());
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
            'purchasable_type' => $variant::class,
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
        ->assertSeeLivewire(TagsComponent::class)
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
