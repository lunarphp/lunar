<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Lunar\Admin\Filament\Resources\TagResource\Pages\CreateTag;
use Lunar\Admin\Filament\Resources\TagResource\Pages\EditTag;
use Lunar\Admin\Filament\Resources\TagResource\Pages\ListTags;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Models\Contracts\Tag as TagContract;

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

    protected static function getMainFormComponents(): array
    {
        return [
            static::getValueFormComponent(),
        ];
    }

    protected static function getValueFormComponent(): Component
    {
        return TextInput::make('value')
            ->label(__('lunarpanel::tag.form.value.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    public static function getDefaultTable(Table $table): Table
    {
        return $table
            ->columns(static::getTableColumns())
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected static function getTableColumns(): array
    {
        return [
            TextColumn::make('value')
                ->label(__('lunarpanel::tag.table.value.label')),
        ];
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getDefaultPages(): array
    {
        return [
            'index' => ListTags::route('/'),
            'create' => CreateTag::route('/create'),
            'edit' => EditTag::route('/{record}/edit'),
        ];
    }
}
