<?php

namespace Lunar\Hub\Http\Livewire\Components;

use Livewire\Component;
use Lunar\Models\ProductVariant;

class ProductVariantSearch extends Component
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
        'updatedExistingProductVariantAssociations',
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
        return ProductVariant::whereIn('id', $this->selected)->get();
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

    public function selectProductVariant($id)
    {
        $this->selected[] = $id;
    }

    public function removeProductVariant($id)
    {
        $index = collect($this->selected)->search($id);
        unset($this->selected[$index]);
        $this->selected = collect($this->selected)->values();
    }

    public function updatedExistingProductVariantAssociations($selected)
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

        return ProductVariant::where('sku', 'like', '%'.$this->searchTerm.'%')->paginate($this->maxResults);
    }

    public function triggerSelect()
    {
        $this->emit('productVariantSearch.selected', $this->selected, $this->ref);
        $this->showBrowser = false;
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.product-variant-search')
            ->layout('adminhub::layouts.base');
    }
}
