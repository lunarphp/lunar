<?php

namespace Lunar\Hub\Http\Livewire\Components\Products\ProductTypes;

use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;
use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Hub\Http\Livewire\Traits\WithLanguages;
use Lunar\Models\Attribute;
use Lunar\Models\AttributeGroup;
use Lunar\Models\Product;
use Lunar\Models\ProductType;
use Lunar\Models\ProductVariant;

abstract class AbstractProductType extends Component
{
    use Notifies;
    use WithLanguages;
    use WithPagination;

    /**
     * The current view of attributes we're assigning.
     *
     * @var string
     */
    public $view = 'products';

    /**
     * Instance of the parent product.
     */
    public ProductType $productType;

    /**
     * Attributes which are ready to be synced.
     */
    public Collection $selectedProductAttributes;

    /**
     * Attributes which are ready to be synced.
     */
    public Collection $selectedVariantAttributes;

    /**
     * The attribute search term.
     *
     * @var string
     */
    public $attributeSearch = '';

    public function addAttribute($id, $type)
    {
        $attributeReference = 'selectedProductAttributes';

        if ($type == 'variants') {
            $attributeReference = 'selectedVariantAttributes';
        }

        $this->{$attributeReference} = $this->{$attributeReference}->push(
            $this->getAvailableAttributes($type)->first(fn ($att) => $att->id == $id)
        );
    }

    public function removeAttribute($id, $type)
    {
        $attributeReference = 'selectedProductAttributes';

        if ($type == 'variants') {
            $attributeReference = 'selectedVariantAttributes';
        }

        $index = $this->{$attributeReference}->search(fn ($att) => $att->id == $id);

        $this->{$attributeReference}->forget($index);
    }

    public function updatedAttributeSearch()
    {
        $this->resetPage();
    }

    /**
     * Return whether variants are disabled.
     *
     * @return bool
     */
    public function getVariantsDisabledProperty()
    {
        return config('lunar-hub.products.disable_variants', false);
    }

    /**
     * Return attributes for a group.
     *
     * @param  string|int  $groupId
     * @param  string  $type
     * @return \Illuminate\Support\Collection
     */
    public function getAttributesForGroup($groupId, $type = 'products')
    {
        return $this->getAvailableAttributes($type)->filter(fn ($att) => $att->attribute_group_id == $groupId);
    }

    /**
     * Return the selected attributes from a given type and group.
     *
     * @param  string|int  $groupId
     * @param  string  $type
     * @return \Illuminate\Support\Collection
     */
    public function getSelectedAttributes($groupId, $type)
    {
        if ($type == 'products') {
            return $this->selectedProductAttributes->filter(
                fn ($att) => $att->attribute_group_id == $groupId
            );
        }

        return $this->selectedVariantAttributes->filter(
            fn ($att) => $att->attribute_group_id == $groupId
        );
    }

    /**
     * Get attribute groups for a given type.
     *
     * @param  string  $type
     * @return void
     */
    public function getGroups($type)
    {
        if ($type == 'products') {
            $type = Product::class;
        }

        if ($type == 'variants') {
            $type = ProductVariant::class;
        }

        return AttributeGroup::whereAttributableType($type)->with([
            'attributes',
        ])->get();
    }

    /**
     * Select all attributes in a group.
     *
     * @param  string|int  $groupId
     * @param  string  $type
     * @return void
     */
    public function selectAll($groupId, $type = 'products')
    {
        $attributes = $this->getAvailableAttributes($type)
            ->filter(fn ($att) => $att->attribute_group_id == $groupId);

        foreach ($attributes as $attribute) {
            if ($type == 'products') {
                $this->selectedProductAttributes->push($attribute);
            } else {
                $this->selectedVariantAttributes->push($attribute);
            }
        }
    }

    /**
     * Deselect all attributes in a group.
     *
     * @param  string|int  $groupId
     * @param  string  $type
     * @return void
     */
    public function deselectAll($groupId, $type = 'products')
    {
        if ($type == 'products') {
            $this->selectedProductAttributes = $this->selectedProductAttributes->reject(function ($att) use ($groupId) {
                return ! $att->system && $att->attribute_group_id == $groupId;
            });
        } else {
            $this->selectedVariantAttributes = $this->selectedVariantAttributes->reject(function ($att) use ($groupId) {
                return ! $att->system && $att->attribute_group_id == $groupId;
            });
        }
    }

    /**
     * Return available attributes given a type.
     *
     * @param  string  $type
     * @return \Illuminate\Contracts\Pagination\Paginator
     */
    public function getAvailableAttributes($type)
    {
        if ($type == 'products') {
            $type = Product::class;
            $existing = $this->selectedProductAttributes;
        }

        if ($type == 'variants') {
            $type = ProductVariant::class;
            $existing = $this->selectedVariantAttributes;
        }

        return Attribute::whereAttributeType($type)
            ->when(
                $this->attributeSearch,
                fn ($query, $search) => $query->where("name->{$this->defaultLanguage->code}", 'LIKE', '%'.$search.'%')
            )->whereSystem(false)
            ->whereNotIn('id', $existing->pluck('id')->toArray())
            ->get();
    }
}
