<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Resources\LanguageResource\Pages;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Models\Language;

class LanguageResource extends BaseResource
{
    protected static ?string $permission = 'settings:core';

    protected static ?string $model = Language::class;

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
        return Forms\Components\TextInput::make('name')
            ->label(__('lunarpanel::language.form.name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    protected static function getCodeFormComponent(): Component
    {
        return Forms\Components\TextInput::make('code')
            ->label(__('lunarpanel::language.form.code.label'))
            ->required()
            ->minLength(2)
            ->maxLength(2);
    }

    protected static function getDefaultFormComponent(): Component
    {
        return Forms\Components\Toggle::make('default')
            ->label(__('lunarpanel::language.form.default.label'));
    }

    protected static function getDefaultTable(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')
                ->iconPosition(IconPosition::After)
                ->iconColor('success')
                ->icon(function (Model $model) {
                    return $model->default ? 'heroicon-o-check-circle' : '';
                })
                ->label(__('lunarpanel::language.table.name.label')),
            Tables\Columns\TextColumn::make('code')
                ->label(__('lunarpanel::language.table.code.label')),
        ]);
    }

    public static function getDefaultRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLanguages::route('/'),
            'create' => Pages\CreateLanguage::route('/create'),
            'edit' => Pages\EditLanguage::route('/{record}/edit'),
        ];
    }
}
