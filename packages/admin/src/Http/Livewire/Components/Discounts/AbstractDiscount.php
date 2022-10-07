<?php

namespace Lunar\Hub\Http\Livewire\Components\Discounts;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Livewire\Component;
use Lunar\Facades\Discounts;
use Lunar\Hub\Editing\DiscountTypes;
use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Hub\Http\Livewire\Traits\WithLanguages;
use Lunar\Models\Collection as ModelsCollection;
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
        'collectionSearch.selected' => 'selectCollections',
    ];

    public function mount()
    {
        $this->syncCollections();
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
     * Sync the discount collections with the UI.
     *
     * @return void
     */
    public function syncCollections()
    {
        $this->collections = $this->discount->collections()
        ->with(['collection.group', 'collection.thumbnail'])
        ->get()
        ->map(function ($dc) {
            return [
                'id' => $dc->collection->id,
                'group_id' => $dc->collection->collection_group_id,
                'group_name' => $dc->collection->group->name,
                'name' => $dc->collection->translateAttribute('name'),
                'thumbnail' => optional($dc->collection->thumbnail)->getUrl(),
                'breadcrumb' => $dc->collection->breadcrumb,
            ];
        });
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
     * Save the discount.
     *
     * @return RedirectResponse
     */
    public function save()
    {
        $redirect = ! $this->discount->id;

        $this->validate();
        $this->discount->save();

        $existing = $this->discount->collections()->get();

        $collectionsToRemove = $existing->filter(function ($collection) {
            return ! $this->collections->pluck('id')->contains($collection->collection_id);
        })->pluck('collection_id');

        $this->discount->collections()->whereIn('collection_id', $collectionsToRemove->toArray())->delete();

        $newCollections = $this->collections->filter(function ($collection) use ($existing) {
            return ! $existing->pluck('collection_id')->contains($collection['id']);
        })->map(function ($collection) {
            return [
                'collection_id' => $collection['id'],
                'discount_id' => $this->discount->id,
                'type' => 'restriction',
            ];
        });

        $this->discount->collections()->createMany($newCollections->toArray());

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
