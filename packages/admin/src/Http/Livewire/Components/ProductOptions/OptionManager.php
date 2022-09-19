<?php

namespace Lunar\Hub\Http\Livewire\Components\ProductOptions;

use Illuminate\Support\Collection;
use Livewire\Component;
use Lunar\Models\ProductOption;

class OptionManager extends Component
{
    /**
     * The options to display.
     *
     * @var Collection
     */
    public Collection $options;

    /**
     * Which option values we have selected.
     *
     * @var array
     */
    public array $selectedValues = [];

    /**
     * The selected option id when creating a new value.
     *
     * @var string
     */
    public ?string $selectedOption = null;

    /**
     * Define listeners.
     *
     * @var array
     */
    protected $listeners = [
        'option-value-create-modal.value-created' => 'refreshOptions',
        'products.options.updated'                => 'syncOptions',
    ];

    /**
     * Watch when selected values update.
     *
     * @param  array  $val
     * @return void
     */
    public function updatedSelectedValues($val)
    {
        $this->emit('option-manager.selectedValues', $val);
    }

    /**
     * Toggle all values within a product option.
     *
     * @param  string  $optionId
     * @return void
     */
    public function toggle($optionId)
    {
        $option = ProductOption::find($optionId);

        $current = collect($this->selectedValues);

        $existing = $current->filter(fn ($value) => $option->values->contains($value));

        if ($existing->count() != $option->values->count()) {
            $this->selectedValues = $current->merge(
                $option->values->map(fn ($value) => (string) $value->id)
            )->unique()->values()->toArray();
        } else {
            $existing->each(function ($value, $key) {
                unset($this->selectedValues[$key]);
            });
        }

        $this->emit('option-manager.selectedValues', $this->selectedValues);
    }

    /**
     * Resync options with the ui.
     *
     * @param  array  $ids
     * @return void
     */
    public function syncOptions($ids)
    {
        $this->options = ProductOption::with('values')->findMany($ids);
    }

    /**
     * Refresh our options and emit any changes.
     *
     * @param  array  $event
     * @return void
     */
    public function refreshOptions($event)
    {
        $this->selectedValues[] = $event['value'];
        $this->emit('option-manager.selectedValues', $this->selectedValues);
        $this->options = ProductOption::with('values')->findMany($this->options->pluck('id'));
    }

    /**
     * Method for when the selected option changes.
     *
     * @param  string  $val
     * @return void
     */
    public function updatedSelectedOption($val)
    {
        $this->emit('option-manager.selected-option', $val);
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.product-options.option-manager')
            ->layout('adminhub::layouts.base');
    }
}
