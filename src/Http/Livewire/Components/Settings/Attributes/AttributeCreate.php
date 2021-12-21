<?php

namespace GetCandy\Hub\Http\Livewire\Components\Settings\Attributes;

use GetCandy\FieldTypes\Text;
use GetCandy\Hub\Http\Livewire\Traits\Notifies;
use GetCandy\Hub\Http\Livewire\Traits\WithLanguages;
use GetCandy\Models\Attribute;
use GetCandy\Models\ProductType;

class AttributeCreate extends AbstractAttribute
{
    use Notifies, WithLanguages;

    /**
     * The empty attribute model.
     *
     * @var \GetCandy\Models\Attribute
     */
    public Attribute $attribute;

    public function mount()
    {
        $this->attribute = new Attribute([
            'name' => [],
            'attribute_type' => ProductType::class,
            'type' => Text::class,
            'configuration' => [],
            'attribute_group_id' => null,
            'section' => 'main',
            'position' => 1,
            'system' => false,
            'required' => false,
        ]);
    }

    /**
     * Define the validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        $rules = [
            'attribute.handle' => [
                'required',
                function ($attribute, $value, $fail) {
                    $exists = Attribute::whereHandle($value)
                        ->whereAttributeType($this->attribute->attribute_type)
                        ->exists();
                    if ($exists) {
                        $fail('The '.$attribute.' is invalid.');
                    }
                },
            ],
            'attribute.position' => 'numeric',
            'attribute.section' => 'string',
            'attribute.system' => 'boolean',
            'attribute.required' => 'boolean',
            'attribute.attribute_type' => 'required',
            'attribute.type' => 'required',
            'attribute.configuration' => 'array',
            'attribute.attribute_group_id' => 'required',
            'attribute.configuration.type' => 'nullable|string',
        ];

        foreach ($this->languages as $language) {
            $rules["attribute.name.{$language->code}"] = $language->default ? 'required|string' : 'nullable|string';
        }

        return $rules;
    }

    /**
     * Create the currency.
     *
     * @return void
     */
    public function create()
    {
        $additionalRules = [];

        if ($this->configType == 'text') {
            $additionalRules['attribute.configuration.type'] = 'required|string';
        }

        $this->validate(array_merge($this->rules(), $additionalRules));

        // dd($this->attribute);
        $this->attribute->save();
        $this->notify(
            __('adminhub::settings.attributes.form.notify.created'),
            'hub.attributes.index'
        );
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.settings.attributes.create')
            ->layout('adminhub::layouts.base');
    }
}
