<?php

namespace Lunar\Admin\Support\FieldTypes;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Component;
use Illuminate\Validation\Rule;
use Lunar\Admin\Support\Synthesizers\DropdownSynth;
use Lunar\Models\Attribute;

class Dropdown extends BaseFieldType
{
    protected static string $synthesizer = DropdownSynth::class;

    public static function canHaveDefaultValue(): bool
    {
        return true;
    }

    /**
     * @param  array<string, mixed>  $configuration
     * @return array<mixed>
     */
    public static function getDefaultValueValidationRules(array $configuration): array
    {
        $lookups = static::mutateConfigurationForForm($configuration)['lookups'] ?? [];

        return [Rule::in(array_values($lookups))];
    }

    /**
     * @param  array<string, mixed>  $configuration
     * @return array<string, mixed>
     */
    public static function mutateConfigurationForForm(array $configuration): array
    {
        $lookups = $configuration['lookups'] ?? [];

        if (! is_array($lookups)) {
            return $configuration;
        }

        $configuration['lookups'] = collect($lookups)
            ->mapWithKeys(function (mixed $lookup, mixed $key): array {
                if (! is_array($lookup)) {
                    return [$key => $lookup];
                }

                $label = $lookup['label'] ?? $lookup['key'] ?? null;
                $value = $lookup['value'] ?? $label;

                if (blank($label)) {
                    return [];
                }

                return [$label => $value];
            })
            ->all();

        return $configuration;
    }

    public static function getFilamentComponent(Attribute $attribute): Component
    {
        return Select::make($attribute->handle)
            ->options(
                collect($attribute->configuration->get('lookups'))->mapWithKeys(
                    fn ($lookup) => [$lookup['value'] => $lookup['label'] ?? $lookup['value']]
                )
            )
            ->when(filled($attribute->validation_rules), fn (Select $component) => $component->rules($attribute->validation_rules))
            ->required((bool) $attribute->required)
            ->helperText($attribute->translate('description'));
    }

    public static function getConfigurationFields(): array
    {
        return [
            KeyValue::make('lookups')->label(
                __('lunarpanel::fieldtypes.dropdown.form.lookups.label')
            )
                ->keyLabel(__('lunarpanel::fieldtypes.dropdown.form.lookups.key_label'))
                ->valueLabel(__('lunarpanel::fieldtypes.dropdown.form.lookups.value_label'))
                ->afterStateHydrated(function (KeyValue $component, $state): void {
                    $component->state(static::mutateConfigurationForForm([
                        'lookups' => is_array($state) ? $state : [],
                    ])['lookups'] ?? []);
                })
                ->mutateDehydratedStateUsing(function ($state) {
                    return collect($state)->map(function ($value, $label) {
                        return [
                            'label' => $label ?? $value,
                            'value' => $value,
                        ];
                    })->values()->toArray();
                }),
        ];
    }
}
