<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Table;
use Lunar\Admin\Filament\Resources\LanguageResource\Pages\CreateLanguage;
use Lunar\Admin\Filament\Resources\LanguageResource\Pages\EditLanguage;
use Lunar\Admin\Filament\Resources\LanguageResource\Pages\ListLanguages;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Core\Models\Language;
use Lunar\Filament\Schemas\Language\LanguageForm;
use Lunar\Filament\Support\Resolver;
use Lunar\Filament\Tables\Language\LanguageTable;

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

    public static function form(Schema $schema): Schema
    {
        return Resolver::form(LanguageForm::class, $schema);
    }

    public static function table(Table $table): Table
    {
        return Resolver::table(LanguageTable::class, $table);
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
