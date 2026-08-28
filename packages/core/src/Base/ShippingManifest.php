<?php

namespace Lunar\Base;

use Closure;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;
use Lunar\DataTypes\ShippingOption;
use Lunar\Models\Contracts\Cart;

class ShippingManifest implements ShippingManifestInterface
{
    /**
     * The collection of available shipping options.
     */
    public Collection $options;

    public ?Closure $getOptionUsing = null;

    /**
     * Whether the shipping modifiers are currently being run.
     */
    protected bool $resolving = false;

    /**
     * The cart the options currently in the manifest were resolved for.
     */
    protected ?int $resolvedFor = null;

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
        // Publishing an identifier that is already here replaces it. Skipping
        // instead meant the first price ever computed for an identifier was the
        // only one that counted: a modifier re-pricing the same option for a
        // changed destination or basket had its answer discarded.
        $index = $this->options->search(
            fn ($opt) => $opt->getIdentifier() == $option->getIdentifier()
        );

        if ($index === false) {
            $this->options->push($option);
        } else {
            $this->options->put($index, $option);
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
        // A modifier is free to calculate the cart, which runs ApplyShipping
        // and lands back here. Hand back what has been resolved so far rather
        // than running the modifiers again.
        if ($this->resolving) {
            return $this->options;
        }

        // The manifest is a singleton, so in any process that handles more than
        // one cart - a queue job, a console command, an Octane worker - options
        // priced for the last cart are still here. Start clean when the cart
        // changes; options added for THIS cart, including any published before
        // the first resolve, are left alone.
        if ($this->resolvedFor !== null && $this->resolvedFor !== $cart->id) {
            $this->options = collect();
        }

        $this->resolvedFor = $cart->id;

        $this->resolving = true;

        try {
            app(Pipeline::class)
                ->send($cart)
                ->through(
                    app(ShippingModifiers::class)->getModifiers()->toArray()
                )->thenReturn();
        } finally {
            $this->resolving = false;
        }

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
        if (! $cart->shippingAddress?->shipping_option) {
            return null;
        }

        return ShippingManifest::getOption($cart, $cart->shippingAddress->shipping_option);
    }
}
