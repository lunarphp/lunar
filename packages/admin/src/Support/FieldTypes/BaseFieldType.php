<?php

namespace Lunar\Admin\Support\FieldTypes;

use Filament\Schemas\Components\Component;
use Livewire\Livewire;
use Lunar\Admin\Support\Synthesizers\TextSynth;
use Lunar\Models\Attribute;

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

    public static function getConfigurationFields(): array
    {
        return [];
    }

    /**
     * Whether a plain-string default_value can seed this field type's component.
     */
    public static function canHaveDefaultValue(): bool
    {
        return false;
    }

    /**
     * @param  array<string, mixed>  $configuration
     * @return array<mixed>
     */
    public static function getDefaultValueValidationRules(array $configuration): array
    {
        return [];
    }

    abstract public static function getFilamentComponent(Attribute $attribute): Component;

    public static function synthesize()
    {
        Livewire::propertySynthesizer(static::$synthesizer);
    }
}
