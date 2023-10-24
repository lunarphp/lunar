<?php

namespace Lunar\Base;

use Closure;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;
use Lunar\DataTypes\ShippingOption;
use Lunar\Models\Cart;

class ShippingManifest implements ShippingManifestInterface
{
    /**
     * The collection of available shipping options.
     */
    public Collection $options;

    public ?Closure $getOptionUsing = null;

    /**
     * Initiate the class.
     */
    public function __construct()
    {
        $this->options = collect();
    }

    /**
     * {@inheritDoc}
     */
    public function addOption(ShippingOption $option)
    {
        $exists = $this->options->first(function ($opt) use ($option) {
            return $opt->getIdentifier() == $option->getIdentifier();
        });

        // Does this option already exist?
        if (! $exists) {
            $this->options->push($option);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addOptions(Collection $options)
    {
        $this->options = $this->options->merge($options);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function clearOptions()
    {
        $this->options = collect();

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getOptionUsing(Closure $closure): self
    {
        $this->getOptionUsing = $closure;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getOptions(Cart $cart): Collection
    {
        app(Pipeline::class)
            ->send($cart)
            ->through(
                app(ShippingModifiers::class)->getModifiers()->toArray()
            )->thenReturn();

        return $this->options;
    }

    /**
     * {@inheritDoc}
     */
    public function getOption(Cart $cart, string $identifier): ?ShippingOption
    {
        if (filled($this->getOptionUsing)) {
            $shippingOption = ($this->getOptionUsing)($cart, $identifier);

            if ($shippingOption) {
                return $shippingOption;
            }
        }

        return $this->getOptions($cart)
            ->where('identifier', $identifier)
            ->first();
    }

    /**
     * {@inheritDoc}
     */
    public function getShippingOption(Cart $cart): ?ShippingOption
    {
        $address = $cart->shippingAddress ?: $cart->dummyShippingAddress;
        
        if (! $shippingOption = $address?->shipping_option) {
            return null;
        }

        return ShippingManifest::getOption($cart, $shippingOption);
    }
}
