<?php

namespace GetCandy\Hub\Http\Livewire\Components\Settings\Product\Features;

use GetCandy\Facades\AttributeManifest;
use \GetCandy\Hub\Http\Livewire\Traits\Notifies;
use GetCandy\Hub\Http\Livewire\Traits\WithLanguages;
use GetCandy\Models\Attribute;
use GetCandy\Models\ProductFeature;
use GetCandy\Models\ProductFeatureValue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

class FeaturesIndex extends Component
{
    use Notifies;
    use WithLanguages;

    /**
     * The type property.
     *
     * @var string
     */
    public $type;

    /**
     * The sorted product features.
     *
     * @var Collection
     */
    public Collection $sortedProductFeatures;

    /**
     * Whether we should show the panel to create a new group.
     *
     * @var bool
     */
    public $showFeatureCreate = false;

    /**
     * The feature id to use for creating an attribute.
     *
     * @var int|null
     */
    public $valueCreateFeatureId = null;

    /**
     * The id of the feature to edit.
     *
     * @var int|null
     */
    public $editFeatureId;

    /**
     * The id of the feature to delete.
     *
     * @var int|null
     */
    public $deleteFeatureId;

    /**
     * The id of the attribute to edit.
     *
     * @var int|null
     */
    public $editFeatureValueId = null;

    /**
     * The ID of the attribute we want to delete.
     *
     * @var int|null
     */
    public $deleteFeatureValueId = null;

    /**
     * {@inheritDoc}
     */
    protected $listeners = [
        'feature-edit.created'       => 'refreshGroups',
        'feature-edit.updated'       => 'resetGroupEdit',
        'feature-value-edit.created' => 'resetFeatureValueEdit',
        'feature-value-edit.updated' => 'resetFeatureValueEdit',
        'feature-value-edit.closed'  => 'resetFeatureValueEdit',
    ];

    /**
     * {@inheritDoc}
     */
    public function mount()
    {
        $this->sortedProductFeatures = $this->productFeatures;
    }

    /**
     * Return the product features
     *
     * @return \Illuminate\Support\Collection
     */
    public function getProductFeaturesProperty()
    {
        return ProductFeature::orderBy('position')->get();
    }

    /**
     * Return the feature to be used when creating an attribute.
     *
     * @return \GetCandy\Models\ProductFeature
     */
    public function getValueCreateFeatureProperty()
    {
        return ProductFeature::find($this->valueCreateFeatureId);
    }

    /**
     * Sort the features.
     *
     * @param  array  $groups
     * @return void
     */
    public function sortGroups($groups)
    {
        DB::transaction(function () use ($groups) {
            $this->sortedProductFeatures = $this->productFeatures->map(function ($group) use ($groups) {
                $updatedOrder = collect($groups['items'])->first(function ($updated) use ($group) {
                    return $updated['id'] == $group->id;
                });
                $group->position = $updatedOrder['order'];
                $group->save();

                return $group;
            })->sortBy('position');
        });
        $this->notify(
            __('adminhub::notifications.attribute-groups.reordered')
        );
    }

    /**
     * Sort the feature values.
     *
     * @param  array  $featureValues
     * @return void
     */
    public function sortFeatureValues(array $featureValues)
    {
        DB::transaction(function () use ($featureValues) {
            foreach ($featureValues['items'] as $item) {
                ProductFeatureValue::whereId($item['id'])->update([
                    'position'           => $item['order'],
                    'product_feature_id' => $item['parent'],
                ]);
            }
        });

        $this->refreshGroups();

        $this->notify(
            __('adminhub::notifications.attributes.reordered')
        );
    }

    /**
     * Refresh the features.
     *
     * @return void
     */
    public function refreshGroups()
    {
        $this->sortedProductFeatures = ProductFeature::orderBy('position')->get();

        $this->showFeatureCreate = false;
    }

    /**
     * Return the computed property for the feature to edit.
     *
     * @return \GetCandy\Models\ProductFeature|null
     */
    public function getFeatureToEditProperty()
    {
        return ProductFeature::find($this->editFeatureId);
    }

    /**
     * Return the feature marked for deletion.
     *
     * @return \GetCandy\Models\ProductFeature|null
     */
    public function getFeatureToDeleteProperty(): ?ProductFeature
    {
        return ProductFeature::find($this->deleteFeatureId);
    }

    /**
     * Return the feature value to edit.
     *
     * @return \GetCandy\Models\Attribute
     */
    public function getFeatureValueToEditProperty()
    {
        return ProductFeatureValue::find($this->editFeatureValueId);
    }

    /**
     * Return the feature value to delete.
     *
     * @return \GetCandy\Models\ProductFeatureValue|null
     */
    public function getFeatureValueToDeleteProperty(): ?ProductFeatureValue
    {
        return ProductFeatureValue::find($this->deleteFeatureValueId);
    }

    /**
     * Reset the group editing state.
     *
     * @return void
     */
    public function resetGroupEdit()
    {
        $this->editFeatureId = null;
    }

    /**
     * Reset the feature value edit state.
     *
     * @return void
     */
    public function resetFeatureValueEdit()
    {
        $this->featureValueToDelete = null;
        $this->valueCreateFeatureId = null;
        $this->editFeatureValueId = null;
        $this->refreshGroups();
    }

    /**
     * Delete the feature value.
     *
     * @return void
     */
    public function deleteFeatureValue()
    {
        DB::transaction(function () {
            $this->featureValueToDelete->delete();
        });

        $this->notify(
            __('adminhub::notifications.attributes.deleted')
        );

        $this->resetFeatureValueEdit();
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.settings.product.features.index')
            ->layout('adminhub::layouts.base');
    }
}
