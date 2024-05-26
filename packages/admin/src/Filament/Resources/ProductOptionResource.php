<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Lunar\Admin\Filament\Resources\ProductOptionResource\Pages;
use Lunar\Admin\Filament\Resources\ProductOptionResource\RelationManagers;
use Lunar\Admin\Support\Forms\Components\TranslatedText;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Admin\Support\Tables\Columns\TranslatedTextColumn;
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
        ];
    }

    protected static function getNameFormComponent(): Component
    {
        return TranslatedText::make('name')
            ->label(__('lunarpanel::productoption.form.name.label'))
            ->required()
            ->maxLength(255)
            ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                if ($operation !== 'create') {
                    return;
                }
                $set('handle', Str::slug($state[Language::getDefault()->code]));
            })
            ->live(onBlur: true)
            ->autofocus();
    }

    protected static function getLabelFormComponent(): Component
    {
        return TranslatedText::make('label')
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
            ->maxLength(255)
            ->disabled(fn ($context, $record) => $context == 'edit' && (! $record->shared));
    }

    public static function getDefaultTable(Table $table): Table
    {
        return $table
            ->columns([
                TranslatedTextColumn::make('name')
                    ->label(__('lunarpanel::productoption.table.name.label')),
                TranslatedTextColumn::make('label')
                    ->label(__('lunarpanel::productoption.table.label.label')),
                Tables\Columns\TextColumn::make('handle')
                    ->label(__('lunarpanel::productoption.table.handle.label')),
                Tables\Columns\BooleanColumn::make('shared')
                    ->label(__('lunarpanel::productoption.table.shared.label')),
            ])
            ->filters([
                Tables\Filters\Filter::make('shared')
                    ->query(fn (Builder $query): Builder => $query->where('shared', true)),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
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
