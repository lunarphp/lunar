<?php

namespace Lunar\Admin\Filament\Resources\ProductOptionResource\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Support\Forms\Components\TranslatedText;
use Lunar\Admin\Support\RelationManagers\BaseRelationManager;
use Lunar\Admin\Support\Tables\Columns\TranslatedTextColumn;

class ValuesRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'values';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('lunarpanel::relationmanagers.values.title');
    }

    public function getTableRecordTitle(Model $record): ?string
    {
        return $record->translate('name');
    }

    public function getDefaultForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                TranslatedText::make('name')
                    ->label(__('lunarpanel::relationmanagers.values.form.name.label'))
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function getDefaultTable(Table $table): Table
    {
        return $table

            ->columns([
                TranslatedTextColumn::make('name')
                    ->label(__('lunarpanel::relationmanagers.values.table.name.label')),
                TextColumn::make('position')
                    ->label(__('lunarpanel::relationmanagers.values.table.position.label')),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('position', 'asc')
            ->reorderable('position');
    }
}
