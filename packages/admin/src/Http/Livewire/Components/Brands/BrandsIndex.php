<?php

namespace GetCandy\Hub\Http\Livewire\Components\Brands;

use GetCandy\Hub\Http\Livewire\Traits\Notifies;
use GetCandy\Models\Brand;
use GetCandy\Models\Language;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Livewire\Component;

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

        $brand->urls()->create([
            'slug' => Str::slug($this->brand['name']),
            'default' => true,
            'language_id' => Language::getDefault()->id,
        ]);

        $this->brand = null;
        $this->slug = null;

        $this->showCreateForm = false;

        $this->notify(
            __('adminhub::notifications.brands.added')
        );

        $this->redirectRoute('hub.brands.index');
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.brands.index', [
            'brands' => Brand::paginate(20),
        ])->layout('adminhub::layouts.base');
    }
}
