<?php

namespace Lunar\Hub\Http\Livewire\Components\Settings\Product\Options;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;
use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Hub\Http\Livewire\Traits\WithLanguages;
use Lunar\Models\ProductOption;
use Lunar\Models\ProductOptionValue;

class OptionsIndex extends Component
{
    use Notifies;
    use WithLanguages;

    public ProductOption $newProductOption;

    /**
     * The type property.
     *
     * @var string
     */
    public $type;

    /**
     * The sorted product options.
     *
     * @var Collection
     */
    public Collection $sortedProductOptions;

    /**
     * Whether we should show the panel to create a new group.
     *
     * @var bool
     */
    public $showOptionCreate = false;

    /**
     * The option id to use for creating an attribute.
     *
     * @var int|null
     */
    public $valueCreateOptionId = null;

    /**
     * The id of the option to delete.
     *
     * @var int|null
     */
    public $deleteOptionId;

    /**
     * The id of the attribute to edit.
     *
     * @var int|null
     */
    public $editOptionValueId = null;

    /**
     * {@inheritDoc}
     */
    protected $listeners = [
        'option-edit.created' => 'refreshGroups',
        'option-edit.updated' => 'resetGroupEdit',
    ];

    public function rules()
    {
        $rules = [];
        foreach ($this->languages as $language) {
            $rules["newProductOption.name.{$language->code}"] = ($language->default ? 'required' : 'nullable').'|max:255';
        }

        return $rules;
    }

    /**
     * {@inheritDoc}
     */
    public function mount()
    {
        $this->newProductOption = new ProductOption;
        $this->sortedProductOptions = $this->productOptions;
    }

    public function createOption()
    {
        $handle = Str::slug(
            $this->newProductOption->translate('name')
        );
        $this->newProductOption->handle = $handle;
        $this->newProductOption->save();

        $this->showOptionCreate = false;
        $this->newProductOption = new ProductOption;
        $this->sortedProductOptions = $this->productOptions;

        $this->notify('Product option created');
    }

    /**
     * Return the product options.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getProductOptionsProperty()
    {
        return ProductOption::orderBy('position')->withCount(['values'])->get();
    }

    /**
     * Return the option to be used when creating an attribute.
     *
     * @return \Lunar\Models\ProductOption
     */
    public function getValueCreateOptionProperty()
    {
        return ProductOption::find($this->valueCreateOptionId);
    }

    /**
     * Sort the options.
     *
     * @param  array  $groups
     * @return void
     */
    public function sortGroups($groups)
    {
        DB::transaction(function () use ($groups) {
            $this->sortedProductOptions = $this->productOptions->map(function ($group) use ($groups) {
                $updatedOrder = collect($groups['items'])->first(function ($updated) use ($group) {
                    return $updated['id'] == $group->id;
                });
                $group->position = $updatedOrder['order'];
                $group->save();

                return $group;
            })->sortBy('position');
        });
        $this->notify(
            __('adminhub::notifications.attribute-groups.reordered')
        );
    }

    /**
     * Refresh the options.
     *
     * @return void
     */
    public function refreshGroups()
    {
        $this->sortedProductOptions = ProductOption::orderBy('position')->get();
        $this->showOptionCreate = false;
    }

    /**
     * Return the option marked for deletion.
     *
     * @return \Lunar\Models\ProductOption|null
     */
    public function getOptionToDeleteProperty(): ?ProductOption
    {
        return ProductOption::find($this->deleteOptionId);
    }

    /**
     * Return the option value to edit.
     *
     * @return \Lunar\Models\Attribute
     */
    public function getOptionValueToEditProperty()
    {
        return ProductOptionValue::find($this->editOptionValueId);
    }

    /**
     * Reset the option value edit state.
     *
     * @return void
     */
    public function resetOptionValueEdit()
    {
        $this->optionValueToDelete = null;
        $this->valueCreateOptionId = null;
        $this->editOptionValueId = null;
        $this->refreshGroups();
    }

    public function deleteOption()
    {
        $level = 'error';
        $notificationText = 'product-options.not.deleted';
        if ($this->optionToDelete->values->isEmpty()) {
            $notificationText = 'product-options.deleted';
            $level = 'success';
            Db::transaction(function () {
                $this->optionToDelete->delete();
            });
        }

        $this->notify(
            __('adminhub::notifications.'.$notificationText), null, [], $level
        );

        $this->deleteOptionId = null;
        $this->refreshGroups();
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.settings.product.options.index')
            ->layout('adminhub::layouts.base');
    }
}
