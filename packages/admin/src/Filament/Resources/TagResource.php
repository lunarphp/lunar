<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Table;
use Lunar\Admin\Filament\Resources\TagResource\Pages\CreateTag;
use Lunar\Admin\Filament\Resources\TagResource\Pages\EditTag;
use Lunar\Admin\Filament\Resources\TagResource\Pages\ListTags;
use Lunar\Admin\Filament\Resources\TagResource\Schemas\TagForm;
use Lunar\Admin\Filament\Resources\TagResource\Tables\TagTable;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Core\Models\Contracts\Tag as TagContract;

class TagResource extends BaseResource
{
    protected static ?string $permission = 'settings';

    protected static ?string $model = TagContract::class;

    protected static ?int $navigationSort = 1;

    public static function getLabel(): string
    {
        return __('lunarpanel::tag.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunarpanel::tag.plural_label');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::tags');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('lunarpanel::global.sections.settings');
    }

    public static function form(Schema $schema): Schema
    {
        return TagForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TagTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTags::route('/'),
            'create' => CreateTag::route('/create'),
            'edit' => EditTag::route('/{record}/edit'),
        ];
    }
}
