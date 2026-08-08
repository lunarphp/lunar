<?php

namespace Lunar\Filament\FieldTypes;

use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Component;
use Lunar\Core\Models\Attribute;
use Lunar\Filament\Synthesizers\DropdownSynth;

class Dropdown extends BaseFieldType
{
    protected static string $synthesizer = DropdownSynth::class;

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
            ->required((bool) $attribute->required)
            ->helperText(null);
    }
}
