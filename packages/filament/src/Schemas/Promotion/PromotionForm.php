<?php

namespace Lunar\Filament\Schemas\Promotion;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Lunar\Filament\Forms\Components\TranslatedText;
use Lunar\Filament\Support\Concerns\CallsHooks;

class PromotionForm
{
    use CallsHooks;

    public static function configure(Schema $schema): Schema
    {
        return self::callStaticLunarHook(
            'configureForm',
            $schema
                ->components([
                    Section::make()->schema(static::getMainComponents()),
                ])
                ->columns(1),
        );
    }

    public static function getMainComponents(): array
    {
        return [
            static::getNameComponent(),
            static::getHandleComponent(),
            static::getDescriptionComponent(),
            Group::make([
                static::getStartsAtComponent(),
                static::getEndsAtComponent(),
            ])->columns(2),
            static::getPublicIdComponent(),
        ];
    }

    public static function getNameComponent(): Component
    {
        return TranslatedText::make('name')
            ->label(__('lunar-filament::promotion.form.name.label'))
            ->required();
    }

    public static function getHandleComponent(): Component
    {
        return TextInput::make('handle')
            ->label(__('lunar-filament::promotion.form.handle.label'))
            ->required()
            ->unique(ignoreRecord: true)
            ->maxLength(255);
    }

    public static function getDescriptionComponent(): Component
    {
        return TranslatedText::make('description')
            ->label(__('lunar-filament::promotion.form.description.label'))
            ->optionRichtext(true);
    }

    public static function getStartsAtComponent(): Component
    {
        return DateTimePicker::make('starts_at')
            ->label(__('lunar-filament::promotion.form.starts_at.label'))
            ->before(fn ($get) => $get('ends_at'));
    }

    public static function getEndsAtComponent(): Component
    {
        return DateTimePicker::make('ends_at')
            ->label(__('lunar-filament::promotion.form.ends_at.label'));
    }

    public static function getPublicIdComponent(): Component
    {
        return TextEntry::make('public_id')
            ->label(__('lunar-filament::components.public_id.label'))
            ->copyable()
            ->visible(fn ($record): bool => (bool) $record);
    }
}
