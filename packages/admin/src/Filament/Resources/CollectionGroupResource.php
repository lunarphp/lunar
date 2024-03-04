<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Forms\Form;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Lunar\Admin\Filament\Resources\CollectionGroupResource\Pages;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Models\CollectionGroup;

class CollectionGroupResource extends BaseResource
{
    protected static ?string $permission = 'catalog:manage-products';

    protected static ?string $model = CollectionGroup::class;

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Collections';

    public static function getLabel(): string
    {
        return __('lunarpanel::collectiongroup.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunarpanel::collectiongroup.plural_label');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::collections');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('lunarpanel::global.sections.catalog');
    }

    public static function getDefaultForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()->schema(
                    static::getMainFormComponents()
                )->columns(2),
            ]);
    }

    protected static function getMainFormComponents(): array
    {
        return [
            static::getNameFormComponent(),
            static::getHandleFormComponent(),
        ];
    }

    protected static function getNameFormComponent(): Component
    {
        return Forms\Components\TextInput::make('name')
            ->label(__('lunarpanel::collectiongroup.form.name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus()
            ->unique(ignoreRecord: true)
            ->live(onBlur: true)
            ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                if ($operation !== 'create') {
                    return;
                }
                $set('handle', Str::slug($state));
            });
    }

    protected static function getHandleFormComponent(): Component
    {
        return Forms\Components\TextInput::make('handle')
            ->label(__('lunarpanel::collectiongroup.form.handle.label'))
            ->unique(ignoreRecord: true)
            ->required()
            ->maxLength(255);
    }

    public static function table(Table $table): Table
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
            Tables\Columns\TextColumn::make('name')
                ->label(__('lunarpanel::collectiongroup.table.name.label')),
            Tables\Columns\TextColumn::make('handle')
                ->label(__('lunarpanel::collectiongroup.table.handle.label')),
            Tables\Columns\TextColumn::make('collections_count')
                ->counts('collections')
                ->formatStateUsing(
                    fn ($state) => number_format($state, 0)
                )
                ->label(__('lunarpanel::collectiongroup.table.collections_count.label')),
        ];
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCollectionGroups::route('/'),
            'edit' => Pages\EditCollectionGroup::route('/{record}/edit'),
        ];
    }
}
