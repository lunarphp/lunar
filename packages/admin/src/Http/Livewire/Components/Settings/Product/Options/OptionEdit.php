<?php

namespace Lunar\Hub\Http\Livewire\Components\Settings\Product\Options;

use Lunar\Facades\DB;
use Livewire\Component;
use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Hub\Http\Livewire\Traits\WithLanguages;
use Lunar\Models\ProductOption;
use Lunar\Models\ProductOptionValue;

class OptionEdit extends Component
{
    use WithLanguages;
    use Notifies;

    /**
     * The option to edit.
     *
     * @var \Lunar\Models\ProductOption
     */
    public ?ProductOption $productOption = null;

    public ?ProductOptionValue $productOptionValue = null;

    /**
     * The option value ID to edit.
     *
     * @var int|null
     */
    public $optionValueIdToEdit = null;

    /**
     * The new option value to create.
     */
    public ProductOptionValue $newProductOptionValue;

    /**
     * The option values.
     */
    public array $values = [];

    /**
     * Whether to show the value create modal.
     *
     * @var bool
     */
    public $showValueCreate = false;

    /**
     * The ID for the product option value we want to remove.
     *
     * @var int|null
     */
    public $optionValueToDeleteId = null;

    /**
     * {@inheritDoc}
     */
    public function mount()
    {
        $this->newProductOptionValue = new ProductOptionValue();
        $this->buildValueTree();
    }

    /**
     * Build out the option value tree
     *
     * @return void
     */
    protected function buildValueTree()
    {
        $this->values = $this->productOption->refresh()
            ->values()->withCount(['variants'])->get()->map(function ($value) {
                return [
                    'id' => $value->id,
                    'value' => $value->translate('name'),
                    'variants_count' => $value->variants_count,
                    'position' => $value->position,
                ];
            })->toArray();
    }

    public function getOptionValueToDeleteProperty()
    {
        return ProductOptionValue::withCount(['variants'])->find(
            $this->optionValueToDeleteId
        );
    }

    /**
     * Return the validation rules.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'values' => 'array',
            'productOption.handle' => [
                'required',
                'unique:'.ProductOption::class.',handle,'.$this->productOption->id,
            ],
        ];

        foreach ($this->languages as $language) {
            $rules["productOption.name.{$language->code}"] = ($language->default ? 'required' : 'nullable').'|max:255';
            $rules["newProductOptionValue.name.{$language->code}"] = 'nullable|max:255';
            $rules["productOptionValue.name.{$language->code}"] = 'nullable|max:255';
        }

        return $rules;
    }

    /**
     * Sort the option values.
     *
     * @return void
     */
    public function sortOptionValues(array $optionValues)
    {
        $values = collect();

        $items = collect($optionValues['items']);

        foreach ($this->values as $value) {
            // Get the new position
            $item = $items->first(
                fn ($item) => $item['id'] == $value['id']
            );

            $value['position'] = $item['order'];
            $values->push($value);
        }

        $this->values = $values->sortBy('position')->values()->toArray();
    }

    public function updatedOptionValueIdToEdit($id)
    {
        $this->productOptionValue = $id ? ProductOptionValue::find($id) : null;
    }

    public function updateOptionValue()
    {
        $this->productOptionValue->save();
        $this->optionValueIdToEdit = null;

        $this->notify(
            __('adminhub::notifications.product_option_value.updated')
        );

        $this->buildValueTree();
    }

    /**
     * Remove the selected product option value
     *
     * @return void
     */
    public function deleteOptionValue()
    {
        $this->optionValueToDelete->delete();
        $this->optionValueToDeleteId = null;
        $this->notify(
            __('adminhub::notifications.product_option_value.deleted')
        );
        $this->buildValueTree();
    }

    public function savePositions()
    {
        DB::transaction(function () {
            foreach ($this->values as $item) {
                ProductOptionValue::whereId($item['id'])->update([
                    'position' => $item['position'],
                ]);
            }
        });

        $this->notify(
            __('adminhub::notifications.attributes.reordered')
        );
    }

    public function save()
    {
        $this->validate();

        if ($this->productOption->id) {
            $this->productOption->save();
            $this->emit('option-edit.updated', $this->productOption->id);
            $this->notify(
                __('adminhub::notifications.attribute-groups.updated')
            );

            return;
        }

        if (! $this->productOption->position) {
            $this->productOption->position = ProductOption::count() + 1;
        }

        $this->productOption->save();

        $this->emit('option-edit.created', $this->productOption->id);

        $this->productOption = new ProductOption();

        $this->notify(
            __('adminhub::notifications.attribute-groups.created')
        );
    }

    public function createOptionValue()
    {
        $rules = [];

        foreach ($this->languages as $language) {
            $rules["newProductOptionValue.name.{$language->code}"] = ($language->default ? 'required' : 'nullable').'|max:255';
        }

        $this->validateOnly('newProductOptionValue', $rules);

        $this->newProductOptionValue->product_option_id = $this->productOption->id;
        $this->newProductOptionValue->save();

        $this->newProductOptionValue = new ProductOptionValue();

        $this->buildValueTree();

        $this->notify('Product option value created');
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.settings.product.options.option-edit')
            ->layout('adminhub::layouts.settings');
    }
}
