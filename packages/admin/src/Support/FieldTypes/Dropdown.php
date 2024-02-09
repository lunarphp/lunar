<?php

namespace Lunar\Admin\Support\FieldTypes;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Lunar\Admin\Support\Synthesizers\DropdownSynth;
use Lunar\Models\Attribute;

class Dropdown extends BaseFieldType
{
    protected static string $synthesizer = DropdownSynth::class;

    public static function getFilamentComponent(Attribute $attribute): Component
    {
        return Select::make($attribute->handle)->options(
            collect($attribute->configuration->get('lookups'))->mapWithKeys(
                fn ($lookup) => [$lookup['value'] => $lookup['label'] ?? $lookup['value']]
            )
        )
            ->helperText($attribute->translate('description'));
    }

    public static function getConfigurationFields(): array
    {
        return [
            KeyValue::make('lookups')->label(
                __('lunarpanel::fieldtypes.dropdown.form.lookups.label')
            )->formatStateUsing(function ($state) {
                return collect($state)->mapWithKeys(
                    fn ($lookup) => [$lookup['label'] => $lookup['value'] ?? $lookup['label']]
                )->toArray();
            })->mutateDehydratedStateUsing(function ($state) {
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
