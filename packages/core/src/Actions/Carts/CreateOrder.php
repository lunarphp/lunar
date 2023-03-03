<?php

namespace Lunar\Actions\Carts;

use Illuminate\Support\Facades\DB;
use Lunar\Actions\AbstractAction;
use Lunar\Actions\Orders\GenerateOrderReference;
use Lunar\DataTypes\ShippingOption;
use Lunar\Jobs\Orders\MarkAsNewCustomer;
use Lunar\Models\Cart;
use Lunar\Models\Currency;
use Lunar\Models\Order;

class CreateOrder extends AbstractAction
{
    /**
     * Execute the action.
     *
     * @param  \Lunar\Models\Cart  $cart
     * @return void
     */
    public function execute(
        Cart $cart
    ) {
        return DB::transaction(function () use ($cart) {
            $order = Order::create([
                'user_id' => $cart->user_id,
                'channel_id' => $cart->channel_id,
                'status' => config('lunar.orders.draft_status'),
                'reference' => null,
                'customer_reference' => null,
                'sub_total' => $cart->subTotal->value,
                'total' => $cart->total->value,
                'discount_total' => $cart->discountTotal?->value,
                'discount_breakdown' => [],
                'shipping_total' => $cart->shippingTotal?->value ?: 0,
                'tax_breakdown' => $cart->taxBreakdown->map(function ($tax) {
                    return [
                        'description' => $tax['description'],
                        'identifier' => $tax['identifier'],
                        'percentage' => $tax['amounts']->min('percentage'),
                        'total' => $tax['total']->value,
                    ];
                })->values(),
                'tax_total' => $cart->taxTotal->value,
                'currency_code' => $cart->currency->code,
                'exchange_rate' => $cart->currency->exchange_rate,
                'compare_currency_code' => Currency::getDefault()?->code,
                'meta' => $cart->meta,
            ]);

            $order->update([
                'reference' => app(GenerateOrderReference::class)->execute($order),
            ]);

            $orderLines = $cart->lines->map(function ($line) {
                return [
                    'cart_line_id' => $line->id,
                    'purchasable_type' => $line->purchasable_type,
                    'purchasable_id' => $line->purchasable_id,
                    'type' => $line->purchasable->getType(),
                    'description' => $line->purchasable->getDescription(),
                    'option' => $line->purchasable->getOption(),
                    'identifier' => $line->purchasable->getIdentifier(),
                    'unit_price' => $line->unitPrice->value,
                    'unit_quantity' => $line->purchasable->getUnitQuantity(),
                    'quantity' => $line->quantity,
                    'sub_total' => $line->subTotal->value,
                    'discount_total' => $line->discountTotal?->value,
                    'tax_breakdown' => $line->taxBreakdown->amounts->map(function ($amount) {
                        return [
                            'description' => $amount->description,
                            'identifier' => $amount->identifier,
                            'percentage' => $amount->percentage,
                            'total' => $amount->price->value,
                        ];
                    })->values(),
                    'tax_total' => $line->taxAmount->value,
                    'total' => $line->total->value,
                    'notes' => null,
                    'meta' => $line->meta,
                ];
            });

            $addresses = collect();

            $cart->addresses->each(function ($address) use ($addresses) {
                $data = $address->toArray();
                $addresses->push(
                    collect($data)->except('cart_id')
                );
            });

            // If we have a shipping address with a shipping option.
            if (($shippingAddress = $cart->shippingAddress) &&
                ($shippingOption = $cart->getShippingOption())
            ) {
                $orderLines->push([
                    'purchasable_type' => ShippingOption::class,
                    'purchasable_id' => 1,
                    'type' => 'shipping',
                    'description' => $shippingOption->getDescription(),
                    'option' => $shippingOption->getOption(),
                    'identifier' => $shippingOption->getIdentifier(),
                    'unit_price' => $shippingOption->price->value,
                    'unit_quantity' => $shippingOption->getUnitQuantity(),
                    'quantity' => 1,
                    'sub_total' => $shippingAddress->shippingSubTotal->value,
                    'discount_total' => $shippingAddress->shippingSubTotal->discountTotal?->value ?: 0,
                    'tax_breakdown' => $shippingAddress->taxBreakdown->amounts->map(function ($amount) {
                        return [
                            'description' => $amount->description,
                            'identifier' => $amount->identifier,
                            'percentage' => $amount->percentage,
                            'total' => $amount->price->value,
                        ];
                    })->values(),
                    'tax_total' => $shippingAddress->shippingTaxTotal->value,
                    'total' => $shippingAddress->shippingTotal->value,
                    'notes' => null,
                    'meta' => [],
                ]);
            }

            $cartLinesMappedToOrderLines = [];
            foreach ($orderLines as $orderLine) {
                $orderLineModel = $order->lines()->create(collect($orderLine)->except(['cart_line_id'])->all());

                if (isset($orderLine['cart_line_id'])) {
                    $cartLinesMappedToOrderLines[$orderLine['cart_line_id']] = $orderLineModel->id;
                }
            }

            $discountBreakdown = ($cart->discountBreakdown ?? collect())->map(function ($discount) use ($cartLinesMappedToOrderLines) {
                return (object) [
                    'discount_id' => $discount->discount->id,
                    'lines' => $discount->lines->map(function ($discountLine) use ($cartLinesMappedToOrderLines) {
                        return [
                            'qty' => $discountLine->quantity,
                            'id' => $cartLinesMappedToOrderLines[$discountLine->line->id],
                        ];
                    })->values()->all(),
                    'total' => $discount->price->value,
                ];
            })->values()->all();

            $order->update([
                'discount_breakdown' => $discountBreakdown,
            ]);

            $order->addresses()->createMany($addresses->toArray());

            $cart->order()->associate($order);

            $cart->discounts?->each(function ($discount) {
                $discount->markAsUsed()->discount->save();
            });

            $cart->save();

            MarkAsNewCustomer::dispatch($order->id);

            return $this;
        });
    }
}
