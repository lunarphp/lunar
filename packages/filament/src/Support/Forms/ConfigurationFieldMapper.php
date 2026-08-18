<?php

namespace Lunar\Filament\Support\Forms;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

/**
 * Maps the renderer-agnostic configuration descriptors a core field type
 * declares (FieldType::getConfigurationFields()) onto Filament form
 * components, so a field type describes its settings once and every admin
 * UI renders them. Unknown descriptor types are skipped for forward
 * compatibility.
 */
class ConfigurationFieldMapper
{
    /**
     * @param  array<int, array{key: string, type: string, label: string, hint?: string, suggestions?: array<int, string>, options?: array<int, array{label: string, value: string}>}>  $descriptors
     * @return array<int, Field>
     */
    public static function map(array $descriptors): array
    {
        return array_values(array_filter(array_map(
            fn (array $descriptor): ?Field => static::component($descriptor),
            $descriptors,
        )));
    }

    /**
     * @param  array{key: string, type: string, label: string, hint?: string, suggestions?: array<int, string>, options?: array<int, array{label: string, value: string}>}  $descriptor
     */
    protected static function component(array $descriptor): ?Field
    {
        $key = $descriptor['key'];

        $component = match ($descriptor['type']) {
            'text' => TextInput::make($key)->nullable(),
            'number' => TextInput::make($key)->nullable()->numeric(),
            'toggle' => Toggle::make($key),
            'select' => Select::make($key)
                ->options(collect($descriptor['options'] ?? [])->pluck('label', 'value')->all())
                ->nullable(),
            'tags' => TagsInput::make($key)
                ->suggestions($descriptor['suggestions'] ?? [])
                ->reorderable(),
            'lookups' => static::lookups($key),
            default => null,
        };

        return $component
            ?->label($descriptor['label'])
            ->helperText($descriptor['hint'] ?? null);
    }

    /**
     * Lookups store as rows of `{label, value}` but edit as a KeyValue map,
     * so the component normalises on hydrate and folds back on dehydrate.
     * Legacy `label => value` map data hydrates unchanged.
     */
    protected static function lookups(string $key): KeyValue
    {
        return KeyValue::make($key)
            ->keyLabel(__('lunar::fieldtypes.lookup_label'))
            ->valueLabel(__('lunar::fieldtypes.lookup_value'))
            ->reorderable()
            ->afterStateHydrated(function (KeyValue $component, $state): void {
                $component->state(
                    collect(is_array($state) ? $state : [])
                        ->mapWithKeys(function (mixed $lookup, mixed $mapKey): array {
                            if (! is_array($lookup)) {
                                return [$mapKey => $lookup];
                            }

                            $label = $lookup['label'] ?? $lookup['key'] ?? null;

                            return blank($label) ? [] : [$label => $lookup['value'] ?? $label];
                        })
                        ->all()
                );
            })
            ->mutateDehydratedStateUsing(fn ($state) => collect($state)
                ->map(fn ($value, $label) => [
                    'label' => $label ?? $value,
                    'value' => $value,
                ])
                ->values()
                ->toArray());
    }
}
