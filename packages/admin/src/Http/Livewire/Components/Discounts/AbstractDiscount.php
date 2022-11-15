<?php

namespace Lunar\Hub\Http\Livewire\Components\Discounts;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Livewire\Component;
use Lunar\Facades\Discounts;
use Lunar\Hub\Editing\DiscountTypes;
use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Hub\Http\Livewire\Traits\WithLanguages;
use Lunar\Models\Brand;
use Lunar\Models\Collection as ModelsCollection;
use Lunar\Models\Currency;
use Lunar\Models\Discount;

abstract class AbstractDiscount extends Component
{
    use WithLanguages;
    use Notifies;

    /**
     * The instance of the discount.
     *
     * @var Discount
     */
    public Discount $discount;

    public Collection $collections;

    /**
     * The brands to restrict the coupon for.
     *
     * @var array
     */
    public array $selectedBrands = [];

    public array $selectedCollections = [];

    /**
     * The current currency for editing
     *
     * @var Currency
     */
    public Currency $currency;

    /**
     * Returns the currencies computed property.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCurrenciesProperty()
    {
        return Currency::get();
    }

    /**
     * {@inheritDoc}
     */
    public function dehydrate()
    {
        $this->emit('parentComponentErrorBag', $this->getErrorBag());
    }

    /**
     * {@inheritDoc}
     */
    protected $listeners = [
        'discountData.updated' => 'syncDiscountData',
        'discount.conditions' => 'syncConditions',
        'discount.purchasables' => 'syncPurchasables',
        'collectionSearch.selected' => 'selectCollections',
    ];

    public function mount()
    {
        $this->currency = Currency::getDefault();
        $this->selectedBrands = $this->discount->brands->pluck('id')->toArray();
        $this->selectedCollections = $this->discount->collections->pluck('id')->toArray();
    }

    /**
     * Map and add the selected collections.
     *
     * @param  array  $collectionIds
     * @return void
     */
    public function selectCollections($collectionIds)
    {
        $selectedCollections = ModelsCollection::findMany($collectionIds)->map(function ($collection) {
            return [
                'id' => $collection->id,
                'group_id' => $collection->collection_group_id,
                'group_name' => $collection->group->name,
                'name' => $collection->translateAttribute('name'),
                'thumbnail' => optional($collection->thumbnail)->getUrl(),
                'position' => optional($collection->pivot)->position,
                'breadcrumb' => $collection->breadcrumb,
                'type' => 'restriction',
            ];
        });

        $this->collections = $this->collections->count()
            ? $this->collections->merge($selectedCollections)
            : $selectedCollections;
    }

    /**
     * Get the collection attribute data.
     *
     * @return void
     */
    public function getAttributeDataProperty()
    {
        return $this->discount->attribute_data;
    }

    /**
     * Return the available discount types.
     *
     * @return array
     */
    public function getDiscountTypesProperty()
    {
        return Discounts::getTypes();
    }

    /**
     * Return the component for the selected discount type.
     *
     * @return Component
     */
    public function getDiscountComponent()
    {
        return (new DiscountTypes)->getComponent($this->discount->type);
    }

    /**
     * Sync the discount data with what's provided.
     *
     * @param  array  $data
     * @return void
     */
    public function syncDiscountData(array $data)
    {
        $this->discount->data = $data;
    }

    /**
     * Remove the collection by it's index.
     *
     * @param  int|string  $index
     * @return void
     */
    public function removeCollection($index)
    {
        $this->collections->forget($index);
    }

    /**
     * Return a list of available countries.
     *
     * @return Collection
     */
    public function getBrandsProperty()
    {
        return Brand::orderBy('name')->get();
    }

    /**
     * Return the category tree.
     *
     * @return Collection
     */
    public function getCollectionTreeProperty()
    {
        return ModelsCollection::get()->toTree();
    }

    /**
     * Save the discount.
     *
     * @return RedirectResponse
     */
    public function save()
    {
        $redirect = ! $this->discount->id;

        $this->validate();
        $this->discount->save();

        $this->discount->brands()->sync(
            $this->selectedBrands
        );

        $this->discount->collections()->sync(
            $this->selectedCollections
        );

        $this->emit('discount.saved', $this->discount->id);

        $this->notify(
            __('adminhub::notifications.discount.saved')
        );

        if ($redirect) {
            redirect()->route('hub.discounts.show', $this->discount->id);
        }
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.discounts.show')
            ->layout('adminhub::layouts.app');
    }
}
