<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Table;
use Lunar\Admin\Filament\Resources\TagResource\Pages;
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
        return Forms\Components\TextInput::make('value')
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
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected static function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('value')
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
            'index' => Pages\ListTags::route('/'),
            'create' => Pages\CreateTag::route('/create'),
            'edit' => Pages\EditTag::route('/{record}/edit'),
        ];
    }
}
