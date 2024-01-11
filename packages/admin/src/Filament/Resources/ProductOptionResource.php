<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Table;
use Lunar\Admin\Filament\Resources\ProductOptionResource\Pages;
use Lunar\Admin\Filament\Resources\ProductOptionResource\RelationManagers;
use Lunar\Admin\Support\Forms\Components\TranslatedText;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Models\Language;
use Lunar\Models\ProductOption;

class ProductOptionResource extends BaseResource
{
    protected static ?string $permission = 'settings';

    protected static ?string $model = ProductOption::class;

    protected static ?int $navigationSort = 1;

    public static function getLabel(): string
    {
        return __('lunarpanel::productoption.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunarpanel::productoption.plural_label');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::product-options');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('lunarpanel::global.sections.settings');
    }

    protected static function getMainFormComponents(): array
    {
        return [
            static::getNameFormComponent(),
            static::getLabelFormComponent(),
            static::getHandleFormComponent(),
            static::getPositionFormComponent(),
        ];
    }

    protected static function getNameFormComponent(): Component
    {
        return TranslatedText::make('name')
            ->label(__('lunarpanel::productoption.form.name.label'))
            ->required()
            ->requireDefault()
            ->maxLength(255)
            ->autofocus();
    }

    protected static function getLabelFormComponent(): Component
    {
        return TranslatedText::make('label')
            ->label(__('lunarpanel::productoption.form.label.label'))
            ->required()
            ->requireDefault()
            ->maxLength(255)
            ->autofocus();
    }

    protected static function getHandleFormComponent(): Component
    {
        return Forms\Components\TextInput::make('handle')
            ->label(__('lunarpanel::productoption.form.handle.label'))
            ->required()
            ->unique(ignoreRecord: true)
            ->maxLength(255);
    }

    protected static function getPositionFormComponent(): Component
    {
        return Forms\Components\TextInput::make('position')
            ->label(__('lunarpanel::productoption.form.position.label'))
            ->numeric()
            ->minValue(1)
            ->maxValue(100)
            ->required();
    }

    public static function getDefaultTable(Table $table): Table
    {
        $language = Language::getDefault()->code;

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('lunarpanel::productoption.table.name.label'))
                    ->formatStateUsing(fn ($state) => $state->$language),
                Tables\Columns\TextColumn::make('label')
                    ->label(__('lunarpanel::productoption.table.label.label'))
                    ->formatStateUsing(fn ($state) => $state->$language),
                Tables\Columns\TextColumn::make('handle')
                    ->label(__('lunarpanel::productoption.table.handle.label')),
                Tables\Columns\TextColumn::make('position')
                    ->label(__('lunarpanel::productoption.table.position.label'))
                    ->sortable(),
            ])
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
            ])
            ->searchable()
            ->defaultSort('position', 'asc')
            ->reorderable('position');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ValuesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductOptions::route('/'),
            'create' => Pages\CreateProductOption::route('/create'),
            'edit' => Pages\EditProductOption::route('/{record}/edit'),
        ];
    }
}
