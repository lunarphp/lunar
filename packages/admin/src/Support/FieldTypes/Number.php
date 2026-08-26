<?php

namespace Lunar\Admin\Support\FieldTypes;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Lunar\Admin\Support\Synthesizers\NumberSynth;
use Lunar\Models\Attribute;

class Number extends BaseFieldType
{
    protected static string $synthesizer = NumberSynth::class;

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
        return ['numeric'];
    }

    public static function getFilamentComponent(Attribute $attribute): Component
    {
        $min = (int) $attribute->configuration->get('min');
        $max = (int) $attribute->configuration->get('max');

        $input = TextInput::make($attribute->handle)
            ->numeric()
            ->when(filled($attribute->validation_rules), fn (TextInput $component) => $component->rules($attribute->validation_rules))
            ->required((bool) $attribute->required)
            ->helperText($attribute->translate('description'));

        if ($min) {
            $input->minValue($min);
        }

        if ($max) {
            $input->maxValue($max);
        }

        return $input;
    }

    public static function getConfigurationFields(): array
    {
        return [
            Grid::make(2)->schema([
                TextInput::make('min')
                    ->label(
                        __('lunarpanel::fieldtypes.number.form.min.label')
                    )->nullable()->numeric(),
                TextInput::make('max')->label(
                    __('lunarpanel::fieldtypes.number.form.max.label')
                )->nullable()->numeric(),
            ]),
        ];
    }
}
