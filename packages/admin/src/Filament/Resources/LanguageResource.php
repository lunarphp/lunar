<?php

namespace Lunar\Admin\Filament\Resources;

use Awcodes\BadgeableColumn\Components\Badge;
use Awcodes\BadgeableColumn\Components\BadgeableColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Resources\LanguageResource\Pages\CreateLanguage;
use Lunar\Admin\Filament\Resources\LanguageResource\Pages\EditLanguage;
use Lunar\Admin\Filament\Resources\LanguageResource\Pages\ListLanguages;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Models\Contracts\Language as LanguageContract;

class LanguageResource extends BaseResource
{
    protected static ?string $permission = 'settings:core';

    protected static ?string $model = LanguageContract::class;

    protected static ?int $navigationSort = 1;

    public static function getLabel(): string
    {
        return __('lunarpanel::language.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunarpanel::language.plural_label');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::languages');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('lunarpanel::global.sections.settings');
    }

    protected static function getMainFormComponents(): array
    {
        return [
            static::getNameFormComponent(),
            static::getCodeFormComponent(),
            static::getDefaultFormComponent(),
        ];
    }

    protected static function getNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('lunarpanel::language.form.name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    protected static function getCodeFormComponent(): Component
    {
        return TextInput::make('code')
            ->label(__('lunarpanel::language.form.code.label'))
            ->required()
            ->minLength(2)
            ->maxLength(5);
    }

    protected static function getDefaultFormComponent(): Component
    {
        return Toggle::make('default')
            ->label(__('lunarpanel::language.form.default.label'));
    }

    protected static function getDefaultTable(Table $table): Table
    {
        return $table->columns([
            BadgeableColumn::make('name')
                ->separator('')
                ->suffixBadges([
                    Badge::make('default')
                        ->label(__('lunarpanel::language.table.default.label'))
                        ->color('gray')
                        ->visible(fn (Model $record) => $record->default),
                ])
                ->label(__('lunarpanel::language.table.name.label')),
            TextColumn::make('code')
                ->label(__('lunarpanel::language.table.code.label')),
        ]);
    }

    public static function getDefaultRelations(): array
    {
        return [
            //
        ];
    }

    public static function getDefaultPages(): array
    {
        return [
            'index' => ListLanguages::route('/'),
            'create' => CreateLanguage::route('/create'),
            'edit' => EditLanguage::route('/{record}/edit'),
        ];
    }
}
