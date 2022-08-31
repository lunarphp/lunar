<?php

namespace GetCandy\Hub\Http\Livewire\Components\Brands;

use GetCandy\Hub\Http\Livewire\Traits\HasImages;
use GetCandy\Hub\Http\Livewire\Traits\HasUrls;
use GetCandy\Hub\Http\Livewire\Traits\Notifies;
use GetCandy\Hub\Http\Livewire\Traits\WithLanguages;
use GetCandy\Models\Brand;
use Livewire\Component;
use Livewire\WithFileUploads;

class BrandShow extends Component
{
    use Notifies;
    use HasImages;
    use WithFileUploads;
    use HasUrls;
    use WithLanguages;

    /**
     * The current brand we're showing.
     *
     * @var Brand
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
        return array_merge([], $this->getHasImagesListeners());
    }

    /**
     * Return the model with media.
     *
     * @return \GetCandy\Models\Brand
     */
    public function getMediaModel()
    {
        return $this->brand;
    }

    /**
     * Return the model with URLs.
     *
     * @return \GetCandy\Models\Brand
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
        return [
            'brand.name' => 'required|string|max:255',
        ];
    }

    /**
     * Validates the LiveWire request, updates the model and dispatches and event.
     *
     * @return void
     */
    public function update()
    {
        $this->validate();
        $this->brand->save();

        $this->updateImages();
        $this->saveUrls();

        $this->notify(
            __('adminhub::notifications.brands.updated'),
            'hub.brands.index'
        );
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
