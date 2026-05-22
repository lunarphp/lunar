<?php

namespace Lunar\Filament\Schemas\Collection;

use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Lunar\Filament\Forms\Components\Attributes;
use Lunar\Filament\Support\Concerns\CallsHooks;

class CollectionForm
{
    use CallsHooks;

    public static function configure(Schema $schema): Schema
    {
        return self::callStaticLunarHook(
            'configureForm',
            $schema
                ->components([
                    static::getAttributeDataComponent(),
                ])
                ->columns(1),
        );
    }

    public static function getAttributeDataComponent(): Component
    {
        return Attributes::make();
    }
}
