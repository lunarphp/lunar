<?php

namespace Lunar\Base;

use Lunar\Models\Collection as ModelsCollection;
use Lunar\Models\Customer;
use Lunar\Models\Order;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AttributeManifest
{
    /**
     * A collection of available attribute types.
     *
     * @var \Illuminate\Support\Collection
     */
    protected Collection $types;

    protected $baseTypes = [
        Product::class,
        ProductVariant::class,
        ModelsCollection::class,
        Customer::class,
        // Order::class,
    ];

    /**
     * Initialise the class.
     */
    public function __construct()
    {
        $this->types = collect();

        foreach ($this->baseTypes as $type) {
            $this->addType($type);
        }
    }

    public function addType($type, $key = null)
    {
        $this->types->prepend(
            $type,
            $key ?: Str::lower(
                class_basename($type)
            )
        );
    }

    public function getTypes(): Collection
    {
        return $this->types;
    }

    public function getType($key)
    {
        return $this->types[$key] ?? null;
    }
}
