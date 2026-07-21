<?php

namespace Lunar\Core\Manifests;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Collection as ModelsCollection;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductType;
use Lunar\Core\Models\ProductVariant;

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
        ProductType::class,
        ModelsCollection::class,
        Customer::class,
        Brand::class,
        CustomerGroup::class,
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

    /**
     * @return Collection
     */
    public function getSearchableAttributes(string $attributeType)
    {
        $attributes = $this->searchableAttributes->get($attributeType, null);

        if ($attributes) {
            return $attributes;
        }

        $attributes = Attribute::query()
            ->whereHas('models', fn ($query) => $query->where('model_type', $attributeType))
            ->where('searchable', true)
            ->get();

        $this->searchableAttributes->put(
            $attributeType,
            $attributes
        );

        return $attributes;
    }
}
