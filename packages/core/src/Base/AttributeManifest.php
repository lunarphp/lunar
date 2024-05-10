<?php

namespace Lunar\Base;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Lunar\Models\Attribute;
use Lunar\Models\Brand;
use Lunar\Models\Collection as ModelsCollection;
use Lunar\Models\Customer;
use Lunar\Models\Product;
use Lunar\Models\ProductOption;
use Lunar\Models\ProductVariant;

class AttributeManifest
{
    /**
     * A collection of available attribute types.
     */
    protected Collection $types;

    protected Collection $searchableAttributes;

    protected $baseTypes = [
        Product::class,
        ProductVariant::class,
        ProductOption::class,
        ModelsCollection::class,
        Customer::class,
        Brand::class,
        // Order::class,
    ];

    /**
     * Initialise the class.
     */
    public function __construct()
    {
        $this->types = collect();
        $this->searchableAttributes = collect();

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

    public function getSearchableAttributes(string $attributeType)
    {
        $attributes = $this->searchableAttributes->get($attributeType, null);

        if ($attributes) {
            return $attributes;
        }

        $attributes = Attribute::whereAttributeType($attributeType)
            ->whereSearchable(true)
            ->get();

        $this->searchableAttributes->put(
            $attributeType,
            $attributes
        );

        return $attributes;
    }
}
