<?php

namespace Lunar\Hub\Http\Livewire\Components\Products\Options;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;
use Livewire\Component;
use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Models\Language;
use Lunar\Models\ProductOption;
use Lunar\Models\ProductOptionValue;

class OptionCreator extends Component
{
    use Notifies;

    /**
     * The name of the new option.
     */
    public array $name = [];

    /**
     * The instance of the option to be created.
     */
    public ProductOption $option;

    /**
     * The collection of new values to associate to the option.
     */
    public Collection $values;

    /**
     * The collection of current languages.
     */
    public Collection $languages;

    /**
     * Define the validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        return [
            "name.{$this->defaultLanguage->code}" => 'string|required|max:255',
            'values' => 'array|min:1',
            "values.*.name.{$this->defaultLanguage->code}" => 'required|string|max:255',
        ];
    }

    /**
     * Define the validation attributes.
     *
     * @return array
     */
    protected function validationAttributes()
    {
        $attributes = [
            "name.{$this->defaultLanguage->code}" => lang(key: 'inputs.name', lower: true),
        ];

        foreach ($this->values as $key => $value) {
            $sequence = (int) $key + 1;
            $attributes["values.{$key}.name.{$this->defaultLanguage->code}"] = lang(key: 'inputs.value', lower: true)." #{$sequence}";
        }

        return $attributes;
    }

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
        $this->validate();

        $handle = Str::slug(
            $this->name[$this->defaultLanguage->code]
        );

        $this->withValidator(function (Validator $validator) use ($handle) {
            $validator->after(function ($validator) use ($handle) {
                if (ProductOption::whereHandle($handle)->exists()) {
                    $validator->errors()->add(
                        'option_handle',
                        __('adminhub::validation.name_taken')
                    );
                }
            });
        })->validate();

        $this->option->handle = $handle;
        $this->option->name = $this->name;
        $this->option->label = $this->name;

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
     * @param  int  $index
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
