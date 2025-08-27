<?php

namespace Lunar\Admin\Support\Forms;

use Filament\Forms\Components\Component;
use Illuminate\Support\Collection;
use Lunar\Admin\Support\FieldTypes\Dropdown;
use Lunar\Admin\Support\FieldTypes\File;
use Lunar\Admin\Support\FieldTypes\ListField;
use Lunar\Admin\Support\FieldTypes\Number;
use Lunar\Admin\Support\FieldTypes\TextField;
use Lunar\Admin\Support\FieldTypes\Toggle;
use Lunar\Admin\Support\FieldTypes\TranslatedText;
use Lunar\Admin\Support\FieldTypes\Vimeo;
use Lunar\Admin\Support\FieldTypes\YouTube;
use Lunar\Base\FieldType as FieldTypeContract;
use Lunar\FieldTypes\Dropdown as DropdownFieldType;
use Lunar\FieldTypes\File as FileFieldType;
use Lunar\FieldTypes\ListField as ListFieldFieldType;
use Lunar\FieldTypes\Number as NumberFieldType;
use Lunar\FieldTypes\Text as TextFieldType;
use Lunar\FieldTypes\Toggle as ToggleFieldType;
use Lunar\FieldTypes\TranslatedText as TranslatedTextFieldType;
use Lunar\FieldTypes\Vimeo as VimeoFieldType;
use Lunar\FieldTypes\YouTube as YouTubeFieldType;
use Lunar\Models\Attribute;

class AttributeData
{
    protected array $fieldTypes = [
        DropdownFieldType::class => Dropdown::class,
        ListFieldFieldType::class => ListField::class,
        TextFieldType::class => TextField::class,
        TranslatedTextFieldType::class => TranslatedText::class,
        ToggleFieldType::class => Toggle::class,
        YouTubeFieldType::class => YouTube::class,
        VimeoFieldType::class => Vimeo::class,
        NumberFieldType::class => Number::class,
        FileFieldType::class => File::class,
    ];

    public function getFilamentComponent(Attribute $attribute): Component
    {
        $fieldType = $this->fieldTypes[$attribute->type] ?? TextField::class;

        /** @var Component $component */
        $component = $fieldType::getFilamentComponent($attribute);

        return $component
            ->label($attribute->translate('name'))
            ->formatStateUsing(function ($state) use ($attribute) {
                if (! $state instanceof FieldTypeContract || (get_class($state) !== $attribute->type)) {
                    $state = new $attribute->type;
                }

                $value = $state->getValue();

                // Only normalize nested-array states (e.g. repeater/builder), preserve scalar lists
                if (is_array($state) && $state !== [] && is_array(reset($state))) {
                    $state = array_values(array_filter($state, 'is_array'));
                }

                return $value;
            })
            ->mutateStateForValidationUsing(function ($state) {
                if ($state instanceof \Lunar\Base\FieldType) {
                    return $state->getValue();
                }

                return $state;
            })
            ->mutateDehydratedStateUsing(function ($state) use ($attribute) {

                // Normalize arrays before wrapping into FieldType instance
                if (is_array($state)) {
                    $state = array_values(array_filter($state, 'is_array'));
                }

                if (! $state instanceof FieldTypeContract || ! ($state instanceof $attribute->type)) {
                    $field = (new $attribute->type);
                    $field->setValue($state);

                    return $field;
                }

                return $state;
            })
            ->required($attribute->required)
            ->default($attribute->default_value);
    }

    public function registerFieldType(string $coreFieldType, string $panelFieldType): static
    {
        $this->fieldTypes[$coreFieldType] = $panelFieldType;

        return $this;
    }

    public function getFieldTypes(): Collection
    {
        return collect($this->fieldTypes)->keys();
    }

    public function getConfigurationFields(?string $type = null): array
    {
        $fieldType = $this->fieldTypes[$type] ?? null;

        return $fieldType ? $fieldType::getConfigurationFields() : [];
    }

    public function synthesizeLivewireProperties(): void
    {
        foreach ($this->fieldTypes as $fieldType) {
            $fieldType::synthesize();
        }
    }
}
