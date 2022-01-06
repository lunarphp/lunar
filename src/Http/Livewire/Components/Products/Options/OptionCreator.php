<?php

namespace GetCandy\Hub\Http\Livewire\Components\Products\Options;

use GetCandy\Hub\Http\Livewire\Traits\Notifies;
use GetCandy\Models\Language;
use GetCandy\Models\ProductOption;
use GetCandy\Models\ProductOptionValue;
use Illuminate\Support\Collection;
use Livewire\Component;

class OptionCreator extends Component
{
    use Notifies;

    /**
     * The name of the new option.
     *
     * @var array
     */
    public array $name = [];

    /**
     * The instance of the option to be created.
     *
     * @var \GetCandy\Models\ProductOption
     */
    public ProductOption $option;

    /**
     * The collection of new values to associate to the option.
     *
     * @var \Illuminate\Support\Collection
     */
    public Collection $values;

    /**
     * The collection of current languages.
     *
     * @var \Illuminate\Support\Collection
     */
    public Collection $languages;

    /**
     * Called on the initial component mount.
     *
     * @return void
     */
    public function mount()
    {
        $this->option = new ProductOption();
        $this->values = collect([
            new ProductOptionValue(),
            new ProductOptionValue(),
        ]);
        $this->languages = Language::get();
    }

    /**
     * Computed property to get the default language.
     *
     * @return void
     */
    public function getDefaultLanguageProperty()
    {
        return $this->languages->first(fn ($lang) => (bool) $lang->default);
    }

    /**
     * Method to commit and create the new ProductOption.
     *
     * @return void
     */
    public function create()
    {
        $this->validate([
            "name.{$this->defaultLanguage->code}" => 'string|required|max:255',
            'values'                              => 'array|min:1',
            'values.*.name.en'                    => 'required|string|max:255',
        ]);

        $this->option->name = $this->name;
        $this->option->save();

        $this->option->values()->createMany($this->values->toArray());

        // We pass just the ID as the model will be hydrated by Livewire
        // if we pass it through. By just passing the id we can always
        // be sure of what we're going to get.
        $this->emit('productOptionCreated', $this->option->id);

        $this->notify(
            __('adminhub::notifications.product-options.created')
        );

        $this->option = new ProductOption();
        $this->name = [];
        $this->values = collect();
    }

    /**
     * Push a value to the values collection, which will be associated to the option.
     *
     * @return void
     */
    public function addValue()
    {
        $this->values->push(new ProductOptionValue());
    }

    /**
     * Removes a value from the value collection.
     *
     * @param int $index
     *
     * @return void
     */
    public function removeValue($index)
    {
        $this->values->forget($index);
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.products.options.option-creator')
            ->layout('adminhub::layouts.base');
    }
}
