<?php

namespace Lunar\Hub\Http\Livewire\Components\Brands;

use Illuminate\Validation\Validator;
use Livewire\Component;
use Livewire\WithFileUploads;
use Lunar\Hub\Http\Livewire\Traits\HasImages;
use Lunar\Hub\Http\Livewire\Traits\HasSlots;
use Lunar\Hub\Http\Livewire\Traits\HasUrls;
use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Hub\Http\Livewire\Traits\WithAttributes;
use Lunar\Hub\Http\Livewire\Traits\WithLanguages;
use Lunar\Models\Attribute;
use Lunar\Models\Brand;

class BrandShow extends Component
{
    use HasSlots;
    use Notifies;
    use HasImages;
    use WithFileUploads;
    use HasUrls;
    use WithLanguages;
    use WithAttributes;

    /**
     * The current brand we're showing.
     */
    public Brand $brand;

    /**
     * Defines the confirmation text when deleting a brand.
     *
     * @var string|null
     */
    public $deleteConfirm = null;

    /**
     * @return array
     */
    protected function getListeners()
    {
        return array_merge(
            [],
            $this->getHasImagesListeners(),
            $this->getHasSlotsListeners(),
        );
    }

    /**
     * Returns any custom validation messages.
     *
     * @return array
     */
    protected function getValidationMessages()
    {
        return array_merge(
            [],
            $this->withAttributesValidationMessages(),
        );
    }

    /**
     * Return the model with media.
     *
     * @return \Lunar\Models\Brand
     */
    public function getMediaModel()
    {
        return $this->brand;
    }

    /**
     * Return the model with URLs.
     *
     * @return \Lunar\Models\Brand
     */
    public function getHasUrlsModel()
    {
        return $this->brand;
    }

    /**
     * Returns validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        return array_merge(
            [
                'brand.name' => 'required|string|max:255',
            ],
            $this->hasUrlsValidationRules(),
            $this->withAttributesValidationRules(),
        );
    }

    /**
     * Get the collection attribute data.
     *
     * @return void
     */
    public function getAttributeDataProperty()
    {
        return $this->brand->attribute_data;
    }

    /**
     * Returns all available attributes.
     *
     * @return void
     */
    public function getAvailableAttributesProperty()
    {
        return Attribute::whereAttributeType(Brand::class)->orderBy('position')->get();
    }

    /**
     * {@inheritDoc}
     */
    protected function validationAttributes()
    {
        $attributes = [];

        return array_merge(
            $attributes,
            $this->getUrlsValidationAttributes()
        );
    }

    /**
     * Validates the LiveWire request, updates the model and dispatches and event.
     *
     * @return void
     */
    public function update()
    {
        $this->withValidator(function (Validator $validator) {
            $validator->after(function ($validator) {
                if ($validator->errors()->count()) {
                    $this->notify(
                        __('adminhub::validation.generic'),
                        level: 'error'
                    );
                }
            });
        })->validate(null, $this->getValidationMessages());

        $this->brand->attribute_data = $this->prepareAttributeData();
        $this->brand->save();

        $this->updateImages();
        $this->saveUrls();
        $this->updateSlots();

        $this->notify(
            __('adminhub::notifications.brands.updated'),
            'hub.brands.index'
        );
    }

    /**
     * Return the number of products associated to the brand.
     *
     * @return int
     */
    public function getProductsCountProperty()
    {
        return $this->brand->products()->withTrashed()->count();
    }

    /**
     * Soft deletes a brand.
     *
     * @return void
     */
    public function delete()
    {
        if (! $this->canDelete) {
            return;
        }

        if ($this->productsCount > 0) {
            $this->notify(
                message: __('adminhub::notifications.brands.delete_protected'),
                level: 'error',
            );

            $this->deleteConfirm = null;

            return;
        }

        $this->brand->delete();

        $this->notify(
            __('adminhub::notifications.brands.deleted'),
            'hub.brands.index'
        );
    }

    /**
     * Returns whether we have met the criteria to allow deletion.
     *
     * @return bool
     */
    public function getCanDeleteProperty()
    {
        return $this->deleteConfirm === $this->brand->name;
    }

    /*
     * Returns the model which has slots associated.
     *
     * @return \Lunar\Models\Customer
     */
    protected function getSlotModel()
    {
        return $this->brand;
    }

    /**
     * Returns the contexts for any slots.
     *
     * @return array
     */
    protected function getSlotContexts()
    {
        return ['brand.show'];
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.brands.show')
            ->layout('adminhub::layouts.base');
    }
}
