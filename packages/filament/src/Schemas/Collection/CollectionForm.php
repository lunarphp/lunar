<?php

namespace Lunar\Filament\Schemas\Collection;

use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Lunar\Filament\Forms\Components\Attributes;
use Lunar\Filament\Forms\Components\TranslatedText;
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
                    Section::make()->schema(static::getMainComponents()),
                    static::getAttributeDataComponent(),
                ])
                ->columns(1),
        );
    }

    public static function getMainComponents(): array
    {
        return [
            static::getNameComponent(),
            static::getShortDescriptionComponent(),
            static::getDescriptionComponent(),
        ];
    }

    public static function getNameComponent(): Component
    {
        return TranslatedText::make('name')
            ->label(__('lunar-filament::collection.form.name.label'))
            ->required();
    }

    public static function getShortDescriptionComponent(): Component
    {
        return TranslatedText::make('short_description')
            ->label(__('lunar-filament::collection.form.short_description.label'));
    }

    public static function getDescriptionComponent(): Component
    {
        return TranslatedText::make('description')
            ->label(__('lunar-filament::collection.form.description.label'))
            ->optionRichtext(true);
    }

    public static function getAttributeDataComponent(): Component
    {
        return Attributes::make();
    }
}
