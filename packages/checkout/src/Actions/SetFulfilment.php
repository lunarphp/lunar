<?php

namespace Lunar\Checkout\Actions;

use Illuminate\Validation\ValidationException;
use Lunar\Checkout\Contracts\Actions\SetsFulfilment;
use Lunar\Checkout\DataTypes\CollectionPoint;
use Lunar\Checkout\Support\CollectionPoints;
use Lunar\Core\Contracts\ShippingManifest;
use Lunar\Core\DataTypes\ShippingOption;
use Lunar\Core\Models\Cart;

class SetFulfilment implements SetsFulfilment
{
    public function __construct(private ShippingManifest $shippingManifest) {}

    public function execute(Cart $cart, string $mode, ?string $collectionPoint = null): Cart
    {
        if (! in_array($mode, [CollectionPoints::DELIVERY, CollectionPoints::COLLECT], true)) {
            throw ValidationException::withMessages([
                'fulfilment' => 'The fulfilment mode must be delivery or collect.',
            ]);
        }

        $cart = $cart->calculate();
        $meta = $cart->meta?->getArrayCopy() ?? [];
        $meta[CollectionPoints::MODE_KEY] = $mode;

        if ($mode === CollectionPoints::DELIVERY) {
            unset($meta[CollectionPoints::POINT_KEY]);
            $this->writeMeta($cart, $meta);
            $this->leaveCollectOption($cart);

            return $cart->refresh()->calculate(force: true);
        }

        $this->writeMeta($cart, $meta);
        $this->storeCollectOption($cart);

        $offered = CollectionPoints::offered($cart);
        $handle = $collectionPoint
            ?? ($offered->count() === 1 ? $offered->first()->handle : ($meta[CollectionPoints::POINT_KEY]['handle'] ?? null));

        if ($handle !== null) {
            $point = $offered->first(fn (CollectionPoint $candidate): bool => $candidate->handle === $handle);

            if ($point === null) {
                if ($collectionPoint !== null) {
                    throw ValidationException::withMessages([
                        'collection_point' => 'The selected collection point is not available.',
                    ]);
                }

                // A stored point the provider no longer offers is forgotten.
                unset($meta[CollectionPoints::POINT_KEY]);
            } else {
                $meta[CollectionPoints::POINT_KEY] = $point->toArray();
            }

            $this->writeMeta($cart, $meta);
        }

        return $cart->refresh()->calculate(force: true);
    }

    /**
     * Lunar keeps the chosen option on the shipping address row, so the
     * collect option can only be stored once that row exists. Until then the
     * mode alone is recorded; `storeShippingAddress` re-runs this action.
     */
    private function storeCollectOption(Cart $cart): void
    {
        if ($cart->shippingAddress === null) {
            return;
        }

        $collect = $this->shippingManifest->getOptions($cart)->first(fn (ShippingOption $option): bool => $option->collect);

        if ($collect === null || $cart->shippingAddress->shipping_option === $collect->getIdentifier()) {
            return;
        }

        $cart->setShippingOption($collect);
    }

    /**
     * Never leave a collect option under a delivery mode. Hand the choice to
     * the first courier option in manifest order (what the client used to do
     * itself); with none available, null the stored option so the order
     * cannot be created until the customer chooses again.
     */
    private function leaveCollectOption(Cart $cart): void
    {
        if ($cart->shippingAddress === null || ! CollectionPoints::storedOptionCollects($cart)) {
            return;
        }

        $courier = $this->shippingManifest->getOptions($cart)->first(fn (ShippingOption $option): bool => ! $option->collect);

        if ($courier !== null) {
            $cart->setShippingOption($courier);

            return;
        }

        $cart->shippingAddress->update(['shipping_option' => null]);
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    private function writeMeta(Cart $cart, array $meta): void
    {
        $cart->meta = $meta;
        $cart->save();
    }
}
