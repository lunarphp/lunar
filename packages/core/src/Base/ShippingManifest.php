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
        $this->options = $options;

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
    public function getOption(Cart $cart, $identifier): ?ShippingOption
    {
        if (blank($this->getOptionUsing)) {
            return ShippingManifest::getOptions($cart)->first(function ($option) use ($cart) {
                return $option->getIdentifier() == $cart->shippingAddress->shipping_option;
            });
        }

        return call_user_func($this->getOptionUsing, ...func_get_args());
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
}
