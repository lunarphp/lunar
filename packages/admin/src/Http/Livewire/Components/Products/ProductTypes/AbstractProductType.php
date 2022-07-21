<?php

namespace GetCandy\Hub\Http\Livewire\Components\Products\ProductTypes;

use GetCandy\Base\Traits\WithModelAttributeGroup;
use GetCandy\Hub\Http\Livewire\Traits\Notifies;
use GetCandy\Hub\Http\Livewire\Traits\WithLanguages;
use GetCandy\Models\Attribute;
use GetCandy\Models\AttributeGroup;
use GetCandy\Models\Product;
use GetCandy\Models\ProductType;
use GetCandy\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

abstract class AbstractProductType extends Component
{
    use Notifies;
    use WithPagination;
    use WithLanguages;
    use WithModelAttributeGroup;


    public string $activeTab = 'products';

    /**
     * The current view of attributes we're assigning.
     *
     * @var string
     */
    public $view = 'products';

    /**
     * Instance of the parent product.
     *
     * @var \GetCandy\Models\ProductType
     */
    public ProductType $productType;

    /**
     * Attributes which are ready to be synced.
     *
     * @var \Illuminate\Support\Collection
     */
    public Collection $selectedProductAttributes;

    /**
     * Attributes which are ready to be synced.
     *
     * @var \Illuminate\Support\Collection
     */
    public Collection $selectedVariantAttributes;

    /**
     * The attribute search term.
     *
     * @var string
     */
    public $attributeSearch = '';


    public bool $showGroupCreate = false;

    public bool $showGroupAssign = false;

    public ?int $selectedGroupId = null;

    public ?int $removeGroupId = null;

    public ?int $attachValueToGroupId = null;


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

    public function getTabsProperty(): Collection
    {
        return collect([
            'products' => __('adminhub::partials.product-type.product_custom_attributes_btn'),
            //'variants' => __('adminhub::partials.product-type.variant_attributes_btn'),
        ])->merge(class_basename($this) === 'ProductTypeShow' ? $this->getTranslatedGroupNames() : []);
    }

    public function getSortedGroupsProperty(): Collection
    {
        if (!$this->productType->attribute_data) {
            return collect();
        }
        
        $handle = Str::replace('model_', '', $this->activeTab);
        $groupPositions = collect($this->productType->attribute_data->get($handle))->get('groupIds');
        $groups = AttributeGroup::whereHandle($handle)
            ->get()
            ->flatMap(fn ($group) => $this->getAttributeGroupFromModel($group)->attributes)
            ->filter(fn ($group) => $this->filterOnlyAssignedGroups($group))
            ->sortBy(fn (Model $group) => collect($groupPositions)->search($group->id));

        $groups->each(fn (Model $group) => $group->values = $this->sortGroupValues($group, $handle));
        return $groups ?? collect();
    }


    /**
     * @todo Refactor to use action
     */
    public function assignGroup()
    {
        $this->validate([
            'selectedGroupId' => 'required',
        ]);
        $group = AttributeGroup::whereHandle(Str::replace('model_', '', $this->activeTab))->first();

        $this->productType->attribute_data ??= collect();
        $this->productType->attribute_data->put($group->handle, $this->prepareAttributeModelData($group));
        $this->productType->save();

        $this->notify(
            __('adminhub::catalogue.product-types.show.updated_message'),
        );

        $this->emitSelf('refreshComponent');
        $this->showGroupAssign = false;
    }

    /**
     * @todo Refactor to use action
     *
     * @param  array  $group
     */
    public function sortableGroups(array $group): void
    {
        $handle = AttributeGroup::whereHandle(
            Str::replace('model_', '', $this->activeTab)
        )->value('handle');

        $sortedGroupIds = collect($group['items'])->pluck('id');
        $data = collect($this->productType->attribute_data->get($handle))->put('groupIds', $sortedGroupIds);

        $this->productType->attribute_data->put($handle, $data);
        $this->productType->save();
    }

    /**
     * @todo Refactor to use action
     *
     * @param  array  $group
     */
    public function sortableGroupValues(array $group): void
    {
        // $handle = AttributeGroup::whereHandle(
        //     Str::replace('model_', '', $this->activeTab)
        // )->value('handle');

        $sortedGroupValuesIds = collect($group['items'])->pluck('id');
        $this->productType->attribute_data->transform(function (array $data) use ($group, $sortedGroupValuesIds) {
            $data[$group['owner']]['values'] = $sortedGroupValuesIds;
            return $data;
        });

        $this->productType->save();
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

        return AttributeGroup
            ::whereType('default')
            ->whereAttributableType($type)
            ->with(['attributes'])->get();
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
            ->paginate(25);
    }

    protected function beforeRender(): void
    {
        $this->selectedGroupId ??= $this->availableGroupOptions->filter(
            fn($group) => !$group['disabled']
        )->value('id');
    }
}
