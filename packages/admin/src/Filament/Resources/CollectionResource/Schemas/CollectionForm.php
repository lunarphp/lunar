<?php

namespace Lunar\Admin\Filament\Resources\CollectionResource\Schemas;

use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Lunar\Admin\Support\Concerns\CallsHooks;
use Lunar\Admin\Support\Forms\Components\Attributes;

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
