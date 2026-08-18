<?php

namespace Lunar\Filament\Support\Forms;

use Filament\Forms\Components\Field;
use Filament\Schemas\Components\Component;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Lunar\Core\Contracts\FieldType;
use Lunar\Core\Enums\FieldTypeEnum;
use Lunar\Core\Facades\FieldTypeManifest;
use Lunar\Core\FieldTypes\Text as TextFieldType;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\Language;
use Lunar\Filament\FieldTypes\Dropdown;
use Lunar\Filament\FieldTypes\File;
use Lunar\Filament\FieldTypes\ListField;
use Lunar\Filament\FieldTypes\Number;
use Lunar\Filament\FieldTypes\TextField;
use Lunar\Filament\FieldTypes\Toggle;
use Lunar\Filament\FieldTypes\TranslatedText;
use Lunar\Filament\FieldTypes\Vimeo;
use Lunar\Filament\FieldTypes\YouTube;
use Tiptap\Editor;

class AttributeData
{
    protected array $fieldTypes = [
        FieldTypeEnum::Dropdown->value => Dropdown::class,
        FieldTypeEnum::ListField->value => ListField::class,
        FieldTypeEnum::Text->value => TextField::class,
        FieldTypeEnum::TranslatedText->value => TranslatedText::class,
        FieldTypeEnum::Toggle->value => Toggle::class,
        FieldTypeEnum::YouTube->value => YouTube::class,
        FieldTypeEnum::Vimeo->value => Vimeo::class,
        FieldTypeEnum::Number->value => Number::class,
        FieldTypeEnum::File->value => File::class,
    ];

    public function getFilamentComponent(Attribute $attribute): Component
    {
        $fieldType = $this->fieldTypes[$attribute->type] ?? TextField::class;

        $coreFieldType = FieldTypeManifest::getType($attribute->type) ?? TextFieldType::class;

        /** @var Component $component */
        $component = $fieldType::getFilamentComponent($attribute);

        return $component
            ->label(
                $attribute->name
            )
            ->formatStateUsing(function ($state) use ($attribute, $coreFieldType) {
                $value = $this->unwrapFieldValue($state);

                if ($value === null) {
                    $value = $this->unwrapFieldValue((new $coreFieldType)->getValue());
                }

                if ($attribute->type === FieldTypeEnum::TranslatedText->value) {
                    return $this->normalizeTranslatedTextValue($value);
                }

                return is_string($value) && blank($value) ? null : $value;
            })
            ->mutateStateForValidationUsing(function ($state) {
                return $this->unwrapFieldValue($state);
            })
            ->mutateDehydratedStateUsing(function ($state) use ($attribute, $coreFieldType) {
                if ($state instanceof FieldType) {
                    return $state;
                }

                if ($attribute->type === FieldTypeEnum::TranslatedText->value) {
                    $state = $this->normalizeTranslatedTextValueForStorage($state);
                }

                $instance = new $coreFieldType;

                if (! blank($state)) {
                    $instance->setValue($state);
                }

                return $instance;
            })
            ->required($attribute->required);
    }

    public function registerFieldType(string $type, string $panelFieldType): static
    {
        $this->fieldTypes[$type] = $panelFieldType;

        return $this;
    }

    public function getFieldTypes(): Collection
    {
        return collect($this->fieldTypes)->keys();
    }

    /**
     * @param  array<string, mixed>  $configuration
     * @return array<string, mixed>
     */
    public function mutateConfigurationForForm(?string $type = null, array $configuration = []): array
    {
        $fieldType = $this->fieldTypes[$type] ?? null;

        return $fieldType ? $fieldType::mutateConfigurationForForm($configuration) : $configuration;
    }

    /**
     * Configuration components for a field type. A bridge class overrides by
     * returning its own components; otherwise the core field type's
     * renderer-agnostic descriptors are mapped onto Filament components, so
     * consumer-registered core field types get a config form with no bridge
     * class at all.
     *
     * @return array<int, Field|Component>
     */
    public function getConfigurationFields(?string $type = null): array
    {
        $fieldType = $this->fieldTypes[$type] ?? null;

        if ($fieldType && ($fields = $fieldType::getConfigurationFields()) !== []) {
            return $fields;
        }

        $coreFieldType = $type ? FieldTypeManifest::getType($type) : null;

        return $coreFieldType
            ? ConfigurationFieldMapper::map((new $coreFieldType)->getConfigurationFields())
            : [];
    }

    public function synthesizeLivewireProperties(): void
    {
        foreach ($this->fieldTypes as $fieldType) {
            $fieldType::synthesize();
        }
    }

    protected function unwrapFieldValue(mixed $value): mixed
    {
        if ($value instanceof FieldType) {
            return $this->unwrapFieldValue($value->getValue());
        }

        if ($value instanceof Arrayable) {
            $value = $value->toArray();
        }

        if (! is_array($value)) {
            return $value;
        }

        return collect($value)
            ->map(fn (mixed $item): mixed => $this->unwrapFieldValue($item))
            ->all();
    }

    protected function normalizeTranslatedTextValue(mixed $value): array
    {
        $defaults = $this->getTranslatedTextDefaults();

        if ($value === null || $value === []) {
            return $defaults;
        }

        if (! is_array($value)) {
            $defaultLanguage = array_key_first($defaults);

            if ($defaultLanguage === null) {
                return [];
            }

            return array_replace($defaults, [
                $defaultLanguage => $value,
            ]);
        }

        return array_replace(
            $defaults,
            collect($value)
                ->map(fn (mixed $item): string => filled($item) ? (string) $item : '')
                ->all(),
        );
    }

    protected function getTranslatedTextDefaults(): array
    {
        static $defaults = null;

        if (is_array($defaults)) {
            return $defaults;
        }

        $defaults = Language::query()
            ->orderBy('default', 'desc')
            ->get(['code'])
            ->mapWithKeys(fn (Language $language): array => [$language->code => ''])
            ->all();

        return $defaults;
    }

    protected function normalizeTranslatedTextValueForStorage(mixed $value): mixed
    {
        if ($value instanceof Arrayable) {
            $value = $value->toArray();
        }

        if (! is_array($value)) {
            return $value;
        }

        return collect($value)
            ->map(function (mixed $item): mixed {
                $item = $this->unwrapFieldValue($item);

                if (is_array($item)) {
                    return (new Editor)->setContent($item)->getHTML();
                }

                return $item ?? '';
            })
            ->all();
    }
}
