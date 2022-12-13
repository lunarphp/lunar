<?php

namespace Lunar\Hub\Http\Livewire\Components\Settings\Product\Options;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
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

    public array $values = [];

    public function mount()
    {
        $this->buildValueTree();
    }

    protected function buildValueTree()
    {
        $this->values = $this->productOption->refresh()->values->map(function ($value) {
            return [
                'id' => $value->id,
                'value' => $value->translate('name'),
                'position' => $value->position,
            ];
        })->toArray();
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
                'unique:' . ProductOption::class . ',handle,' . $this->productOption->id,
            ],
        ];

        foreach ($this->languages as $language) {
            $rules["productOption.name.{$language->code}"] = ($language->default ? 'required' : 'nullable').'|max:255';
        }

        return $rules;
    }

    /**
     * Sort the option values.
     *
     * @param  array  $optionValues
     * @return void
     */
    public function sortOptionValues(array $optionValues)
    {
        DB::transaction(function () use ($optionValues) {
            foreach ($optionValues['items'] as $item) {
                ProductOptionValue::whereId($item['id'])->update([
                    'position' => $item['order'],
                    'product_option_id' => $item['parent'],
                ]);
            }
        });

        $this->buildValueTree();

        $this->notify(
            __('adminhub::notifications.attributes.reordered')
        );
    }

    public function create()
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
