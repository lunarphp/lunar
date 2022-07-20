<?php

namespace GetCandy\Hub\Http\Livewire\Components\Products\ProductTypes;

use GetCandy\Base\Traits\WithModelAttributeGroup;
use GetCandy\Hub\Actions\ProductType\AssignGroup;
use GetCandy\Hub\Actions\ProductType\CreateGroup;
use GetCandy\Hub\Http\Livewire\Traits\HasActions;
use GetCandy\Models\Attribute;
use GetCandy\Models\AttributeGroup;
use GetCandy\Models\Product;
use GetCandy\Models\ProductType;
use GetCandy\Models\ProductVariant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * @property Collection $availableGroupOptions
 */
class ProductTypeShow extends AbstractProductType
{
    use HasActions;
    use WithModelAttributeGroup;

    public bool $deleteDialogVisible = false;

    public string $activeTab = 'products';

    public bool $showGroupCreate = false;

    public bool $showGroupAssign = false;

    public ?int $selectedGroupId = null;

    public function mount()
    {
        // @todo improve actions
        /*$this->registerActions([
            CreateGroup::class,
            AssignGroup::class,
        ]);*/

        $systemProductAttributes = Attribute::system(Product::class)->get();
        $systemVariantAttributes = Attribute::system(ProductVariant::class)->get();
        $this->selectedProductAttributes = $this->productType->mappedAttributes
            ->filter(fn ($att) => $att->attribute_type == Product::class)
            ->merge($systemProductAttributes);

        $this->selectedVariantAttributes = $this->productType->mappedAttributes
            ->filter(fn ($att) => $att->attribute_type == ProductVariant::class)
            ->merge($systemVariantAttributes);
    }

    /**
     * Register the validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        return [
            'productType.name' => [
                'required',
                'string',
                'unique:'.get_class($this->productType).',name,'.$this->productType->id,
            ],
        ];
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
            'hub.product-type.show',
            [$this->productType]
        );
    }

    /**
     * @todo Refactor to use action
     *
     * @param  array  $group
     */
    public function sortGroups(array $group): void
    {
        $handle = AttributeGroup::whereHandle(
            Str::replace('model_', '', $this->activeTab)
        )->value('handle');

        $sortedGroupIds = collect($group['items'])->pluck('id');
        $sortedData = collect($this->productType->attribute_data->get($handle))->sortBy(
            fn (array $values, $groupId) => $sortedGroupIds->search($groupId)
        );
        $this->productType->attribute_data->put($handle, $sortedData);
        $this->productType->save();
    }

    /**
     * @todo Refactor to use action
     *
     * @param  array  $group
     */
    public function sortGroupValues(array $group): void
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
     * Method to handle product type saving.
     *
     * @return void
     */
    public function update()
    {
        $this->validate();

        $this->productType->save();

        $this->productType->mappedAttributes()->sync(
            array_merge(
                $this->selectedProductAttributes->pluck('id')->toArray(),
                $this->selectedVariantAttributes->pluck('id')->toArray()
            )
        );

        $this->notify(
            __('adminhub::catalogue.product-types.show.updated_message'),
            'hub.product-types.index'
        );
    }

    public function getTabsProperty(): Collection
    {
        return collect([
            'products' => __('adminhub::partials.product-type.product_custom_attributes_btn'),
            //'variants' => __('adminhub::partials.product-type.variant_attributes_btn'),
        ])->merge($this->getTranslatedGroupNames());
    }

    public function getSortedGroupsProperty(): Collection
    {
        $groups = AttributeGroup::whereHandle(Str::replace('model_', '', $this->activeTab))
            ->get()
            ->flatMap(fn ($group) => $this->getAttributeGroupFromModel($group)->attributes)
            ->filter(fn ($group) => $this->filterOnlyAssignedGroups($group));

        return $groups ?? collect();
    }

    public function getCanDeleteProperty()
    {
        return ! $this->isTheOnlyProductType && ! $this->productType->products()->count();
    }

    /**
     * Returns whether this is the only Product type in the system.
     *
     * @return bool
     */
    public function getIsTheOnlyProductTypeProperty()
    {
        return ProductType::count() == 1;
    }

    /**
     * Delete the variant.
     *
     * @return void
     */
    public function delete()
    {
        if (! $this->canDelete) {
            $this->notify(
                __('adminhub::catalogue.product-types.show.delete.disabled_message')
            );
            $this->deleteDialogVisible = false;

            return;
        }

        DB::transaction(fn () => $this->productType->delete());

        $this->notify(
            __('adminhub::catalogue.product-types.show.delete.delete_notification'),
            'hub.product-types.index'
        );
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $this->selectedGroupId ??= $this->availableGroupOptions->filter(
            fn($group) => !$group['disabled']
        )->value('id');

        return view('adminhub::livewire.components.products.product-types.show')
            ->layout('adminhub::layouts.base');
    }
}
