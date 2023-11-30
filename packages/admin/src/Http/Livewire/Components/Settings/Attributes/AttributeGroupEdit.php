<?php

namespace Lunar\Hub\Http\Livewire\Components\Settings\Attributes;

use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Hub\Http\Livewire\Traits\WithLanguages;
use Lunar\Models\AttributeGroup;

class AttributeGroupEdit extends Component
{
    use Notifies;
    use WithLanguages;

    /**
     * The type of attributable this is.
     *
     * @var string
     */
    public $attributableType;

    /**
     * The new attribute group.
     *
     * @var AttributeGroup
     */
    public ?AttributeGroup $attributeGroup = null;

    /**
     * {@inheritDoc}
     */
    public function rules()
    {
        $rules = [];

        $this->languages->each(function ($language) use (&$rules) {
            $rules["attributeGroup.name.{$language->code}"] = array_merge(
                ['string', 'max:255'],
                $language->default ? ['required'] : []
            );
        });

        return $rules;
    }

    /**
     * {@inheritDoc}
     */
    protected function validationAttributes()
    {
        return [
            "attributeGroup.name.{$this->defaultLanguage->code}" => lang(key: 'inputs.name', lower: true),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function mount()
    {
        $this->attributeGroup = $this->attributeGroup ?: new AttributeGroup();
    }

    public function create()
    {
        $this->validate();

        $this->attributeGroup->handle = Str::handle("{$this->attributeGroup->translate('name')}");

        $this->validate([
            'attributeGroup.handle' => [
                'required', 
                Rule::unique(AttributeGroup::class, 'handle')
                    ->ignore(AttributeGroup::class)
                    ->where(fn ($query) => $query->where('attributable_type', $this->attributableType))
            ]
        ]);

        if ($this->attributeGroup->id) {
            $this->attributeGroup->save();
            $this->emit('attribute-group-edit.updated', $this->attributeGroup->id);
            $this->notify(
                __('adminhub::notifications.attribute-groups.updated')
            );

            return;
        }

        $this->attributeGroup->attributable_type = $this->attributableType;
        $this->attributeGroup->position = AttributeGroup::whereAttributableType(
            $this->attributableType
        )->count() + 1;

        $this->attributeGroup->save();

        $this->emit('attribute-group-edit.created', $this->attributeGroup->id);

        $this->attributeGroup = new AttributeGroup();

        $this->notify(
            __('adminhub::notifications.attribute-groups.created')
        );
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.settings.attributes.attribute-group-edit')
            ->layout('adminhub::layouts.base');
    }
}
