<?php

namespace Lunar\Hub\Http\Livewire\Components\ProductOptions;

use Livewire\Component;
use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Hub\Http\Livewire\Traits\WithLanguages;
use Lunar\Models\ProductOption;

class OptionValueCreateModal extends Component
{
    use Notifies;
    use WithLanguages;

    /**
     * The parent product option for this new value.
     */
    public ?ProductOption $option = null;

    /**
     * Whether the form should be visible.
     *
     * @var bool
     */
    public $formVisible = false;

    /**
     * Data array for the name and translations.
     *
     * @var array
     */
    public $name = [];

    /**
     * Whether we can persist the form to create another.
     *
     * @var bool
     */
    public $canPersist = true;

    /**
     * Define any listeners.
     *
     * @var array
     */
    protected $listeners = [
        'option-manager.selected-option' => 'setOption',
        'variant-show.selected-option' => 'setOption',
    ];

    /**
     * Return the validation rules.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [];
        foreach ($this->languages as $language) {
            $rules["name.{$language->code}"] = ($language->default ? 'required' : 'nullable').'|max:255';
        }

        return $rules;
    }

    /**
     * Set our option by a given id.
     *
     * @param  string  $optionId
     * @return void
     */
    public function setOption($optionId)
    {
        $this->option = ProductOption::find($optionId);
        $this->formVisible = (bool) $optionId;
    }

    /**
     * Cancel the creation process.
     *
     * @return void
     */
    public function cancel()
    {
        $this->setOption(null);
    }

    /**
     * Save our new value to the database.
     *
     * @param  bool  $persist
     * @return void
     */
    public function addNewValue($persist = false)
    {
        $messages = [];

        foreach ($this->languages as $language) {
            $messages["name.{$language->code}.required"] = __('adminhub::validation.generic_required');
        }

        $this->validate(null, $messages);

        $value = $this->option->values()->create([
            'name' => $this->name,
        ]);

        if (! $persist) {
            $this->formVisible = false;
        }

        $this->name = [];

        $this->notify(
            __('adminhub::notifications.option-values.created')
        );

        $this->emit('option-value-create-modal.value-created', [
            'option' => $this->option->id,
            'value' => (string) $value->id,
        ]);
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.product-options.option-value-create-modal')
            ->layout('adminhub::layouts.base');
    }
}
