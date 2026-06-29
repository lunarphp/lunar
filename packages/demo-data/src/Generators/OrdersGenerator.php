<?php

namespace Lunar\DemoData\Generators;

use Illuminate\Support\Collection;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\States\Fulfilment\ReadyForCollection;
use Lunar\Core\ValueObjects\Cart\TaxBreakdown;
use Lunar\DemoData\Support\DemoContext;

/**
 * Builds orders from source records — lines, transactions and fulfilments —
 * and lets the observers settle the derived payment_status / fulfilment_status.
 * Nothing derived is hand-set.
 *
 * A fixed coverage set guarantees every payment status, every fulfilment
 * status, all three fulfilment methods, plus on-hold / cancelled / closed at
 * any scale; larger scales layer a natural distribution on top.
 */
class OrdersGenerator implements Generator
{
    protected DemoContext $context;

    protected Currency $currency;

    /** @var Collection<int, Customer> */
    protected Collection $customers;

    /** @var Collection<int, ProductVariant> */
    protected Collection $variants;

    protected ProductVariant $digital;

    protected int $customerCursor = 0;

    protected int $sequence = 0;

    public function generate(DemoContext $context): void
    {
        $context->reseed();

        $this->context = $context;
        $this->currency = $context->get('currency') ?? Currency::query()->where('default', true)->firstOrFail();
        $this->customers = Customer::query()->orderBy('id')->get();
        $this->variants = ProductVariant::query()
            ->with('product')
            ->where('shippable', true)
            ->whereHas('prices', fn ($q) => $q->where('currency_id', $this->currency->id))
            ->orderBy('id')
            ->limit(50)
            ->get();

        if ($this->variants->isEmpty()) {
            return;
        }

        $this->digital = $this->digitalVariant();

        $this->coverage();
        $this->naturalDistribution();
    }

    /**
     * The fixed scenarios that pin every status, method and lifecycle state.
     */
    protected function coverage(): void
    {
        $physical = fn (int $i = 0) => $this->variants[$i % $this->variants->count()];

        // Pending payment, unfulfilled — a brand-new order.
        $this->scenario('DEMO-PENDING', [['variant' => $physical(0), 'quantity' => 1]]);

        // Authorized — an intent, no capture.
        $this->scenario('DEMO-AUTHORIZED', [['variant' => $physical(1), 'quantity' => 1]], function (Order $order) {
            $this->intent($order, $order->total);
        });

        // Partially paid + partially fulfilled — half captured, one of two shipped.
        $this->scenario('DEMO-PART-PAID', [['variant' => $physical(2), 'quantity' => 2]], function (Order $order) {
            $this->capture($order, intdiv($order->total, 2));
            $line = $order->lines->first();
            $order->createFulfilment([$line->id => 1], ['method' => 'shipping'])->ship($this->tracking());
        });

        // Paid + fulfilled by shipping (with tracking).
        $this->scenario('DEMO-PAID-SHIPPED', [
            ['variant' => $physical(3), 'quantity' => 1],
            ['variant' => $physical(4), 'quantity' => 1],
        ], function (Order $order) {
            $this->capture($order, $order->total);
            $order->createFulfilment($this->coverAll($order), ['method' => 'shipping'])->ship($this->tracking());
        });

        // Paid + fulfilled by collection (Pending -> ReadyForCollection -> Collected).
        $this->scenario('DEMO-PAID-COLLECTION', [['variant' => $physical(5), 'quantity' => 1]], function (Order $order) {
            $this->capture($order, $order->total);
            $fulfilment = $order->createFulfilment($this->coverAll($order), ['method' => 'collection']);
            $fulfilment->transition(ReadyForCollection::class);
            $fulfilment->fulfil();
        });

        // Paid + fulfilled by digital provisioning.
        $this->scenario('DEMO-PAID-DIGITAL', [['variant' => $this->digital, 'quantity' => 1, 'digital' => true]], function (Order $order) {
            $this->capture($order, $order->total);
            $order->createFulfilment($this->coverAll($order), ['method' => 'digital'])->fulfil();
        });

        // Partially refunded — fully shipped, part of the money returned.
        $this->scenario('DEMO-PART-REFUNDED', [['variant' => $physical(6), 'quantity' => 1]], function (Order $order) {
            $this->capture($order, $order->total);
            $order->createFulfilment($this->coverAll($order), ['method' => 'shipping'])->ship($this->tracking());
            $this->refund($order, intdiv($order->total, 4));
        });

        // Refunded + returned — shipped, returned, fully refunded.
        $this->scenario('DEMO-REFUNDED', [['variant' => $physical(7), 'quantity' => 1]], function (Order $order) {
            $this->capture($order, $order->total);
            $order->createFulfilment($this->coverAll($order), ['method' => 'shipping'])->ship($this->tracking())->markReturned();
            $this->refund($order, $order->total);
        });

        // Partially returned — two shipped in separate parcels, one returned.
        $this->scenario('DEMO-PART-RETURNED', [['variant' => $physical(8), 'quantity' => 2]], function (Order $order) {
            $this->capture($order, $order->total);
            $line = $order->lines->first();
            $order->createFulfilment([$line->id => 1], ['method' => 'shipping'])->ship($this->tracking())->markReturned();
            $order->createFulfilment([$line->id => 1], ['method' => 'shipping'])->ship($this->tracking());
        });

        // Voided — only a failed intent.
        $this->scenario('DEMO-VOIDED', [['variant' => $physical(9), 'quantity' => 1]], function (Order $order) {
            $this->intent($order, $order->total, success: false);
        });

        // On hold — paid, with a held parcel blocking dispatch.
        $this->scenario('DEMO-ON-HOLD', [['variant' => $physical(10), 'quantity' => 1]], function (Order $order) {
            $this->capture($order, $order->total);
            $order->createFulfilment($this->coverAll($order), ['method' => 'shipping'])->hold('awaiting_stock', 'Backordered with the supplier.');
        });

        // Cancelled order.
        $this->scenario('DEMO-CANCELLED', [['variant' => $physical(11), 'quantity' => 1]], function (Order $order) {
            $order->cancel('customer_request', 'Customer changed their mind.', notify: false);
        });

        // Closed (archived) — settled and fulfilled, then archived.
        $this->scenario('DEMO-CLOSED', [['variant' => $physical(12), 'quantity' => 1]], function (Order $order) {
            $this->capture($order, $order->total);
            $order->createFulfilment($this->coverAll($order), ['method' => 'shipping'])->ship($this->tracking());
            $order->close();
        });
    }

    /**
     * Layer extra, naturally-distributed orders on top of the coverage set to
     * reach the configured volume at medium/large scales.
     */
    protected function naturalDistribution(): void
    {
        $extra = $this->context->count('orders') - Order::query()->where('reference', 'like', 'DEMO-%')->count();
        $faker = $this->context->faker;

        for ($i = 0; $i < $extra; $i++) {
            $lines = collect(range(1, $faker->numberBetween(1, 3)))
                ->map(fn () => [
                    'variant' => $this->variants[$faker->numberBetween(0, $this->variants->count() - 1)],
                    'quantity' => $faker->numberBetween(1, 3),
                ])->all();

            $this->scenario('DEMO-NAT-'.str_pad((string) $i, 5, '0', STR_PAD_LEFT), $lines, function (Order $order) use ($faker) {
                $roll = $faker->numberBetween(1, 100);

                if ($roll <= 15) {
                    $this->intent($order, $order->total); // authorized, awaiting capture

                    return;
                }

                $this->capture($order, $order->total);

                if ($roll <= 35) {
                    return; // paid, not yet fulfilled
                }

                $fulfilment = $order->createFulfilment($this->coverAll($order), ['method' => 'shipping']);

                if ($roll <= 90) {
                    $fulfilment->ship($this->tracking());
                }

                if ($roll > 95) {
                    $this->refund($order, intdiv($order->total, 3));
                }
            });
        }
    }

    /**
     * Build (or skip, if it already exists) an order with the given lines, then
     * hand it to the decorator to apply transactions / fulfilments.
     *
     * @param  array<int, array{variant: ProductVariant, quantity: int, digital?: bool}>  $lines
     */
    protected function scenario(string $reference, array $lines, ?\Closure $decorate = null): void
    {
        if (Order::query()->where('reference', $reference)->exists()) {
            return;
        }

        $order = $this->buildOrder($reference, $lines);

        if ($decorate) {
            $decorate($order);
        }
    }

    /**
     * @param  array<int, array{variant: ProductVariant, quantity: int, digital?: bool}>  $lines
     */
    protected function buildOrder(string $reference, array $lines): Order
    {
        $customer = $this->nextCustomer();
        $faker = $this->context->faker;

        $rows = collect($lines)->map(function (array $spec) {
            $unit = $this->unitPrice($spec['variant']);
            $total = $unit * $spec['quantity'];
            $tax = $total - (int) round($total / 1.2); // 20% tax-inclusive

            return [
                'variant' => $spec['variant'],
                'quantity' => $spec['quantity'],
                'digital' => $spec['digital'] ?? false,
                'unit_price' => $unit,
                'sub_total' => $total - $tax,
                'tax_total' => $tax,
                'total' => $total,
            ];
        });

        $order = Order::create([
            'channel_id' => $this->context->get('channel')?->id,
            'customer_id' => $customer?->id,
            'reference' => $reference,
            'currency_code' => $this->currency->code,
            'compare_currency_code' => $this->currency->code,
            'exchange_rate' => 1,
            'sub_total' => $rows->sum('sub_total'),
            'tax_total' => $rows->sum('tax_total'),
            'discount_total' => 0,
            'shipping_total' => 0,
            'tax_breakdown' => new TaxBreakdown,
            'total' => $rows->sum('total'),
            'placed_at' => $faker->dateTimeBetween('-60 days', 'now'),
        ]);

        foreach ($rows as $row) {
            /** @var ProductVariant $variant */
            $variant = $row['variant'];

            $order->lines()->create([
                'purchasable_type' => $variant->getMorphClass(),
                'purchasable_id' => $variant->id,
                'type' => $row['digital'] ? 'digital' : 'physical',
                'requires_shipping' => ! $row['digital'],
                'requires_fulfilment' => true,
                'description' => $variant->product->translate('name') ?? $variant->sku,
                'option' => null,
                'identifier' => $variant->sku,
                'unit_price' => $row['unit_price'],
                'unit_quantity' => 1,
                'quantity' => $row['quantity'],
                'sub_total' => $row['sub_total'],
                'tax_total' => $row['tax_total'],
                'tax_breakdown' => new TaxBreakdown,
                'discount_total' => 0,
                'total' => $row['total'],
            ]);
        }

        $this->addresses($order, $customer);

        return $order->refresh();
    }

    protected function addresses(Order $order, ?Customer $customer): void
    {
        $source = $customer?->addresses()->first();
        $faker = $this->context->faker;

        foreach (['billing', 'shipping'] as $type) {
            $order->addresses()->create([
                'type' => $type,
                'country_id' => $source?->country_id,
                'title' => $source?->title ?? $customer?->title,
                'first_name' => $source?->first_name ?? $customer?->first_name ?? $faker->firstName(),
                'last_name' => $source?->last_name ?? $customer?->last_name ?? $faker->lastName(),
                'line_one' => $source?->line_one ?? $faker->buildingNumber().' '.$faker->streetName(),
                'city' => $source?->city ?? $faker->city(),
                'postcode' => $source?->postcode ?? strtoupper($faker->bothify('??## #??')),
                'contact_email' => $source?->contact_email ?? $faker->safeEmail(),
            ]);
        }
    }

    /**
     * Every line of the order at its full quantity — the common "fulfil it all" case.
     *
     * @return array<int, int>
     */
    protected function coverAll(Order $order): array
    {
        return $order->lines->mapWithKeys(fn ($line) => [$line->id => $line->quantity])->all();
    }

    protected function intent(Order $order, int $amount, bool $success = true): void
    {
        $order->transactions()->create([
            'type' => 'intent',
            'success' => $success,
            'amount' => $amount,
            'driver' => 'offline',
            'reference' => $order->reference.'-INT-'.(++$this->sequence),
            'status' => $success ? 'authorized' : 'failed',
            'card_type' => 'visa',
            'last_four' => '4242',
        ]);
    }

    protected function capture(Order $order, int $amount): void
    {
        $order->transactions()->create([
            'type' => 'capture',
            'success' => true,
            'amount' => $amount,
            'driver' => 'offline',
            'reference' => $order->reference.'-CAP-'.(++$this->sequence),
            'status' => 'settled',
            'card_type' => 'visa',
            'last_four' => '4242',
        ]);
    }

    protected function refund(Order $order, int $amount): void
    {
        $order->transactions()->create([
            'type' => 'refund',
            'success' => true,
            'amount' => $amount,
            'driver' => 'offline',
            'reference' => $order->reference.'-REF-'.(++$this->sequence),
            'status' => 'refunded',
        ]);
    }

    /**
     * @return array<string, string>
     */
    protected function tracking(): array
    {
        $faker = $this->context->faker;

        return [
            'carrier' => $faker->randomElement(['Royal Mail', 'DPD', 'Evri']),
            'tracking_number' => strtoupper($faker->bothify('??######??')),
            'tracking_url' => 'https://tracking.example.test/'.$faker->bothify('########'),
        ];
    }

    protected function nextCustomer(): ?Customer
    {
        if ($this->customers->isEmpty()) {
            return null;
        }

        return $this->customers[$this->customerCursor++ % $this->customers->count()];
    }

    protected function unitPrice(ProductVariant $variant): int
    {
        return (int) ($variant->prices()
            ->where('currency_id', $this->currency->id)
            ->orderBy('min_quantity')
            ->value('price') ?? 0);
    }

    /**
     * A non-shippable product backing the digital fulfilment scenario. Its line
     * carries requires_fulfilment explicitly (a core variant derives that from
     * shippability), so the digital method can claim it.
     */
    protected function digitalVariant(): ProductVariant
    {
        $variant = ProductVariant::query()->with('product')->where('sku', 'DIGITAL-GIFT-CARD')->first();

        if ($variant) {
            return $variant;
        }

        $product = Product::create([
            'product_type_id' => $this->variants->first()->product->product_type_id,
            'brand_id' => $this->variants->first()->product->brand_id,
            'status' => 'published',
            'name' => collect(['en' => 'Digital Gift Card']),
            'description' => collect(['en' => 'A digital gift card delivered by email.']),
            'short_description' => collect(['en' => 'Delivered by email.']),
            'attribute_data' => collect(),
        ]);

        $product->scheduleChannel($this->context->get('channel'), now());

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'tax_class_id' => $this->context->get('taxClass')->id,
            'sku' => 'DIGITAL-GIFT-CARD',
            'unit_quantity' => 1,
            'shippable' => false,
            'attribute_data' => collect(),
        ]);

        $variant->prices()->create([
            'price' => 2500,
            'currency_id' => $this->currency->id,
            'min_quantity' => 1,
        ]);

        $variant->setRelation('product', $product);

        return $variant;
    }
}
