<?php

namespace Lunar\Base;

use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;
use Lunar\DataTypes\ShippingOption;
use Lunar\Models\Cart;

class ShippingManifest implements ShippingManifestInterface
{
    /**
     * The collection of available shipping options.
     *
     * @var \Illuminate\Support\Collection
     */
    public Collection $options;

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
