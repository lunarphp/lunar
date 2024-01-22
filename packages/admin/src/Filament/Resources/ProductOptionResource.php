<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Table;
use Lunar\Admin\Filament\Resources\ProductOptionResource\Pages;
use Lunar\Admin\Filament\Resources\ProductOptionResource\RelationManagers;
use Lunar\Admin\Support\Resources\BaseResource;
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
        ];
    }

    protected static function getNameFormComponent(): Component
    {
        return \Lunar\Admin\Support\Forms\Components\TranslatedText::make('name') // TODO: we need a custom field type for this
            ->label(__('lunarpanel::productoption.form.name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    protected static function getLabelFormComponent(): Component
    {
        return \Lunar\Admin\Support\Forms\Components\TranslatedText::make('label') // TODO: we need a custom field type for this
            ->label(__('lunarpanel::productoption.form.label.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    protected static function getHandleFormComponent(): Component
    {
        return Forms\Components\TextInput::make('handle')
            ->label(__('lunarpanel::productoption.form.handle.label'))
            ->required()
            ->maxLength(255);
    }

    public static function getDefaultTable(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->formatStateUsing(
                        fn (ProductOption $option) => $option->translate('name'),
                    )->label(__('lunarpanel::productoption.table.name.label')),
                Tables\Columns\TextColumn::make('label')
                    ->formatStateUsing(
                        fn (ProductOption $option) => $option->translate('label'),
                    )
                    ->label(__('lunarpanel::productoption.table.label.label')),
                Tables\Columns\TextColumn::make('handle')
                    ->label(__('lunarpanel::productoption.table.handle.label')),
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
            ->modifyQueryUsing(
                fn ($query) => $query->shared()
            )
            ->searchable();
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
