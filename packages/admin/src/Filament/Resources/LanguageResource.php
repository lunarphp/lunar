<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Table;
use Lunar\Admin\Filament\Resources\LanguageResource\Pages\CreateLanguage;
use Lunar\Admin\Filament\Resources\LanguageResource\Pages\EditLanguage;
use Lunar\Admin\Filament\Resources\LanguageResource\Pages\ListLanguages;
use Lunar\Admin\Filament\Resources\LanguageResource\Schemas\LanguageForm;
use Lunar\Admin\Filament\Resources\LanguageResource\Tables\LanguageTable;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Core\Models\Contracts\Language as LanguageContract;

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

    public static function form(Schema $schema): Schema
    {
        return LanguageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LanguageTable::configure($table);
    }

    protected static function getDefaultPages(): array
    {
        return [
            'index' => ListLanguages::route('/'),
            'create' => CreateLanguage::route('/create'),
            'edit' => EditLanguage::route('/{record}/edit'),
        ];
    }
}
