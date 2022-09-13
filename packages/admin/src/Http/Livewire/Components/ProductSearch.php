<?php

namespace Lunar\Hub\Http\Livewire\Components;

use Lunar\Models\Product;
use Livewire\Component;

class ProductSearch extends Component
{
    public bool $showBrowser = false;

    public $searchTerm = null;

    public $maxResults = 50;

    public $existing;

    public $selected = [];

    public $exclude = [];

    public $ref = null;

    public $showBtn = true;

    protected $listeners = [
        'updatedExistingProductAssociations',
        'showBrowser',
    ];

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

    public function showBrowser($reference = null)
    {
        if ($reference && $reference == $this->ref) {
            $this->showBrowser = true;
            $this->selected = [];
            $this->searchTerm = null;
        }
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

    public function updatedExistingProductAssociations($selected)
    {
        $this->existing = collect($selected);
    }

    /**
     * Returns the computed search results.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getResultsProperty()
    {
        if (! $this->searchTerm) {
            return null;
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
