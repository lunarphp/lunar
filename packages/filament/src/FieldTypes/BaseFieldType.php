<?php

namespace Lunar\Filament\FieldTypes;

use Filament\Forms\Components\Field;
use Filament\Schemas\Components\Component;
use Livewire\Livewire;
use Lunar\Core\Models\Attribute;
use Lunar\Filament\Synthesizers\TextSynth;

abstract class BaseFieldType
{
    protected static string $synthesizer = TextSynth::class;

    /**
     * @param  array<string, mixed>  $configuration
     * @return array<string, mixed>
     */
    public static function mutateConfigurationForForm(array $configuration): array
    {
        return $configuration;
    }

    /**
     * Override to replace the config form derived from the core field type's
     * descriptors (see ConfigurationFieldMapper) with bespoke components; an
     * empty array means "use the descriptors".
     *
     * @return array<int, Field>
     */
    public static function getConfigurationFields(): array
    {
        return [];
    }

    abstract public static function getFilamentComponent(Attribute $attribute): Component;

    public static function synthesize()
    {
        Livewire::propertySynthesizer(static::$synthesizer);
    }
}
