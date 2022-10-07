<?php

namespace Lunar\Hub\Http\Livewire\Components\Settings\Product\Options;

use Illuminate\Validation\Rule;
use Livewire\Component;
use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Hub\Http\Livewire\Traits\WithLanguages;
use Lunar\Models\ProductOption;

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

    /**
     * Return the validation rules.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'productOption.handle' => [
                'required',
                Rule::unique(ProductOption::class, 'handle')->ignore(1, 'id'),
            ],
        ];

        foreach ($this->languages as $language) {
            $rules["productOption.name.{$language->code}"] = ($language->default ? 'required' : 'nullable').'|max:255';
        }

        return $rules;
    }

    /**
     * {@inheritDoc}
     */
    public function mount()
    {
        $this->productOption = $this->productOption ?: new ProductOption();
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
            ->layout('adminhub::layouts.base');
    }
}
