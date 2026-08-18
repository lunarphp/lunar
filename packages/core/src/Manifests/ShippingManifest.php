<?php

namespace Lunar\Core\Manifests;

use Closure;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;
use Lunar\Core\Contracts\ShippingManifest as ShippingManifestContract;
use Lunar\Core\DataTypes\ShippingOption;
use Lunar\Core\Models\Cart;
use Lunar\Core\Modifiers\ShippingModifiers;

class ShippingManifest implements ShippingManifestContract
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
     *
     * Adding an identifier that is already present REPLACES the stored option
     * in place. Options are re-resolved against the live cart on every
     * getOptions() pipeline run; keeping the first instance would pin prices
     * from an earlier cart state (a previous shipping address, an earlier
     * request on a long-lived worker) over the freshly resolved ones.
     */
    public function addOption(ShippingOption $option)
    {
        $index = $this->options->search(function ($opt) use ($option) {
            return $opt->getIdentifier() == $option->getIdentifier();
        });

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
        if (! $cart->shippingAddress?->shipping_option) {
            return null;
        }

        return ShippingManifest::getOption($cart, $cart->shippingAddress->shipping_option);
    }
}
