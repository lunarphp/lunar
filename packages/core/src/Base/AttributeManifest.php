<?php

namespace GetCandy\Base;

use GetCandy\Models\Collection as ModelsCollection;
use GetCandy\Models\Order;
use GetCandy\Models\Product;
use GetCandy\Models\ProductVariant;
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
        Order::class,
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
