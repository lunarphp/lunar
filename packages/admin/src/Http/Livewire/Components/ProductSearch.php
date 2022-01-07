<?php

namespace GetCandy\Hub\Http\Livewire\Components;

use GetCandy\Models\Product;
use Livewire\Component;

class ProductSearch extends Component
{
    public bool $showBrowser = false;

    public $searchTerm = null;

    public $maxResults = 50;

    public $existing;

    public $selected = [];

    public function rules()
    {
        return [
            'searchTerm' => 'required|string|max:255',
        ];
    }

    public function getSelectedModelsProperty()
    {
        return Product::whereIn('id', $this->selected)->withTrashed()->get();
    }

    public function getExistingIdsProperty()
    {
        return $this->existing->pluck('id');
    }

    public function updatedShowBrowser()
    {
        $this->selected = [];
        $this->searchTerm = null;
    }

    public function selectProduct($id)
    {
        $this->selected[] = $id;
    }

    public function removeProduct($id)
    {
        $index = collect($this->selected)->search($id);
        unset($this->selected[$index]);
        $this->selected = collect($this->selected)->values();
    }

    /**
     * Returns the computed search results.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getResultsProperty()
    {
        if (!$this->searchTerm) {
            return;
        }

        return Product::search($this->searchTerm)->paginate($this->maxResults);
    }

    public function triggerSelect()
    {
        $this->emit('product-search.selected', $this->selected);
        $this->showBrowser = false;
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.product-search')
            ->layout('adminhub::layouts.base');
    }
}
