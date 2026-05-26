<?php

namespace Lunar\Filament\Support\Forms;

use Filament\Schemas\Components\Component;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Lunar\Core\FieldTypes\Dropdown as DrodownFieldType;
use Lunar\Core\FieldTypes\FieldType;
use Lunar\Core\FieldTypes\File as FileFieldType;
use Lunar\Core\FieldTypes\ListField as ListFieldFieldType;
use Lunar\Core\FieldTypes\Number as NumberFieldType;
use Lunar\Core\FieldTypes\Text as TextFieldType;
use Lunar\Core\FieldTypes\Toggle as ToggleFieldType;
use Lunar\Core\FieldTypes\TranslatedText as TranslatedTextFieldType;
use Lunar\Core\FieldTypes\Vimeo as VimeoFieldType;
use Lunar\Core\FieldTypes\YouTube as YouTubeFieldType;
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
        DrodownFieldType::class => Dropdown::class,
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
        $fieldType = $this->fieldTypes[
        $attribute->type
        ] ?? TextField::class;

        /** @var Component $component */
        $component = $fieldType::getFilamentComponent($attribute);

        return $component
            ->label(
                $attribute->translate('name')
            )
            ->formatStateUsing(function ($state) use ($attribute) {
                $value = $this->unwrapFieldValue($state);

                if ($value === null) {
                    $value = $this->unwrapFieldValue((new $attribute->type)->getValue());
                }

                if ($attribute->type === TranslatedTextFieldType::class) {
                    return $this->normalizeTranslatedTextValue($value);
                }

                return is_string($value) && blank($value) ? null : $value;
            })
            ->mutateStateForValidationUsing(function ($state) {
                return $this->unwrapFieldValue($state);
            })
            ->mutateDehydratedStateUsing(function ($state) use ($attribute) {
                if ($state instanceof FieldType) {
                    return $state;
                }

                if ($attribute->type === TranslatedTextFieldType::class) {
                    $state = $this->normalizeTranslatedTextValueForStorage($state);
                }

                $instance = new $attribute->type;

                if (! blank($state)) {
                    $instance->setValue($state);
                }

                return $instance;
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

    /**
     * @param  array<string, mixed>  $configuration
     * @return array<string, mixed>
     */
    public function mutateConfigurationForForm(?string $type = null, array $configuration = []): array
    {
        $fieldType = $this->fieldTypes[$type] ?? null;

        return $fieldType ? $fieldType::mutateConfigurationForForm($configuration) : $configuration;
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
