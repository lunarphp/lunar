<?php

namespace GetCandy\Hub\Http\Livewire\Components\Settings\Attributes;

use GetCandy\Hub\Http\Livewire\Traits\ConfirmsDelete;
use GetCandy\Hub\Http\Livewire\Traits\Notifies;
use GetCandy\Hub\Http\Livewire\Traits\WithLanguages;
use GetCandy\Models\Attribute;
use Illuminate\Support\Facades\DB;

class AttributeShow extends AbstractAttribute
{
    use Notifies, WithLanguages, ConfirmsDelete;

    public bool $manualHandle = true;

    /**
     * The current channel we're showing.
     *
     * @var \GetCandy\Models\Attribute
     */
    public Attribute $attribute;

    /**
     * Returns validation rules.
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
                        ->where('id', '!=', $this->attribute->id)
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
     * Validates the LiveWire request, updates the model and dispatches and event.
     *
     * @return void
     */
    public function update()
    {
        $this->validate();

        $this->attribute->save();

        $this->notify(
            'Attribute successfully updated.',
            'hub.attributes.index'
        );
    }

    /**
     * Soft deletes a channel.
     *
     * @return void
     */
    public function delete()
    {
        if (! $this->canDelete) {
            return;
        }

        DB::transaction(function () {
            $this->attribute->delete();
        });

        $this->notify(
            'Attribute successfully deleted.',
            'hub.attributes.index'
        );
    }

    public function getCanDeleteProperty()
    {
        return $this->deleteConfirm === $this->attribute->handle;
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.settings.attributes.show')
            ->layout('adminhub::layouts.base');
    }
}
