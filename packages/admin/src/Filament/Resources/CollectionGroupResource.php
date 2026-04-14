<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Lunar\Admin\Filament\Resources\CollectionGroupResource\Pages\EditCollectionGroup;
use Lunar\Admin\Filament\Resources\CollectionGroupResource\Pages\ListCollectionGroups;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Models\Contracts\CollectionGroup as CollectionGroupContract;

class CollectionGroupResource extends BaseResource
{
    protected static ?string $permission = 'catalog:manage-collections';

    protected static ?string $model = CollectionGroupContract::class;

    protected static ?int $navigationSort = 3;

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

    public static function getDefaultForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()->schema(
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
        return TextInput::make('name')
            ->label(__('lunarpanel::collectiongroup.form.name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus()
            ->unique(ignoreRecord: true)
            ->live(onBlur: true)
            ->afterStateUpdated(function (string $operation, $state, Set $set) {
                if ($operation !== 'create') {
                    return;
                }
                $set('handle', Str::slug($state));
            });
    }

    protected static function getHandleFormComponent(): Component
    {
        return TextInput::make('handle')
            ->label(__('lunarpanel::collectiongroup.form.handle.label'))
            ->unique(ignoreRecord: true)
            ->required()
            ->live(onBlur: true)
            ->afterStateUpdated(function (string $operation, $state, Set $set) {
                if ($operation !== 'create') {
                    return;
                }

                $set('handle', Str::snake(Str::lower($state)));
            })
            ->maxLength(255);
    }

    public static function table(Table $table): Table
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
            TextColumn::make('name')
                ->label(__('lunarpanel::collectiongroup.table.name.label')),
            TextColumn::make('handle')
                ->label(__('lunarpanel::collectiongroup.table.handle.label')),
            TextColumn::make('collections_count')
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

    public static function getDefaultPages(): array
    {
        return [
            'index' => ListCollectionGroups::route('/'),
            'edit' => EditCollectionGroup::route('/{record}/edit'),
        ];
    }
}
