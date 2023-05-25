<?php

namespace Lunar\Hub\Http\Livewire\Components\Products\Options;

use Livewire\Component;
use Lunar\Hub\Http\Livewire\Traits\WithLanguages;
use Lunar\Models\ProductOption;

class OptionSelector extends Component
{
    use WithLanguages;

    /**
     * The selected options that have been chosen.
     */
    public array $selected = [];

    /**
     * The search term for product options.
     *
     * @var string
     */
    public $searchTerm = null;

    public bool $hasOptions = false;

    /**
     * Whether the main panel is visible.
     *
     * @var bool
     */
    public $mainPanelVisible = true;

    /**
     * Whether the create panel should be visible.
     *
     * @var bool
     */
    public $createPanelVisible = false;

    /**
     * Define listeners.
     *
     * @var array
     */
    protected $listeners = [
        'productOptionCreated' => 'selectNewOption',
        'toggleOptionSelector' => 'toggleOptionSelector',
    ];

    public function mount($openPanel = true)
    {
        $this->mainPanelVisible = $openPanel;
        $this->hasOptions = count($this->selected) > 0;
    }

    public function toggleOptionSelector($toggle = null)
    {
        $this->mainPanelVisible = $toggle === null ? ! $this->mainPanelVisible : $toggle;
    }

    public function updatedMainPanelVisible($val)
    {
        if ($this->hasOptions) {
            return;
        }

        $this->emit('productOptionSelectorPanelToggled', $val);
    }

    /**
     * Returns the options based on search terms.
     *
     * @return \Illuminate\Support\Collection|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getOptionsProperty()
    {
        if (! $this->searchTerm) {
            return ProductOption::latest()->limit(25)->get();
        }

        return ProductOption::search($this->searchTerm)->paginate(25);
    }

    /**
     * Select an option by it's given id.
     *
     * @param  string  $optionId
     * @return void
     */
    public function selectNewOption($optionId)
    {
        $this->select($optionId);
        $this->createPanelVisible = false;
    }

    /**
     * Add an option into the selected array.
     *
     * @param  string  $optionId
     * @return void
     */
    public function select($optionId)
    {
        $this->selected[] = $optionId;
    }

    /**
     * Remove an option from the selected array.
     *
     * @param  string  $optionId
     * @return void
     */
    public function deselect($optionId)
    {
        $index = collect($this->selected)->search($optionId);
        unset($this->selected[$index]);
        $this->selected = collect($this->selected)->values()->toArray();
    }

    /**
     * Get the selected model objects from the selected ids.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getSelectedModelsProperty()
    {
        return ProductOption::whereIn('id', $this->selected)->get();
    }

    /**
     * Handle the submission to use product options.
     *
     * @return void
     */
    public function submitOptions()
    {
        $this->emit('useProductOptions', $this->selected);
        $this->mainPanelVisible = false;
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.products.options.option-selector')
            ->layout('adminhub::layouts.base');
    }
}
