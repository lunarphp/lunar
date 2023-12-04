<?php

namespace Lunar\Admin\Support\FieldTypes;

use Filament\Forms\Components\Component;
use Livewire\Livewire;
use Lunar\Admin\Support\Synthesizers\TextSynth;
use Lunar\Models\Attribute;

abstract class BaseFieldType
{
    protected static string $synthesizer = TextSynth::class;

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
