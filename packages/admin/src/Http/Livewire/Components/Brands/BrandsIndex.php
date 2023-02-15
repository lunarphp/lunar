<?php

namespace Lunar\Hub\Http\Livewire\Components\Brands;

use Illuminate\Support\Arr;
use Livewire\Component;
use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Models\Brand;
use Lunar\Models\Language;

class BrandsIndex extends Component
{
    use Notifies;

    /**
     * The new brand we're making.
     *
     * @var array
     */
    public $brand = null;

    /**
     * Whether we should show the create form.
     *
     * @var bool
     */
    public $showCreateForm = false;

    /**
     * {@inheritDoc}
     */
    protected $listeners = [
        'addBrand',
    ];

    /**
     * Return the validation rules.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'brand.name' => 'required|string|max:255',
        ];
    }

    /**
     * Get the default language code.
     *
     * @return void
     */
    public function getDefaultLanguageProperty()
    {
        return Language::getDefault()->code;
    }

    /**
     * Add a brand ready for saving.
     *
     * @return void
     */
    public function addBrand()
    {
        $this->showCreateForm = true;
    }

    /**
     * Create the new brand.
     *
     * @return void
     */
    public function createBrand()
    {
        $rules = Arr::only($this->rules(), ['brand.name']);

        $this->validate($rules, [
            'brand.name.required' => __('adminhub::validation.generic_required'),
        ]);

        /** @var Brand $brand */
        $brand = Brand::create([
            'name' => $this->brand['name'],
        ]);

        $this->brand = null;

        $this->showCreateForm = false;

        $this->notify(
            __('adminhub::notifications.brands.added')
        );

        $this->redirectRoute('hub.brands.index');
    }

    public function resetForm(): void
    {
        $this->showCreateForm = false;
        $this->reset();
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.brands.index')
            ->layout('adminhub::layouts.base');
    }
}
