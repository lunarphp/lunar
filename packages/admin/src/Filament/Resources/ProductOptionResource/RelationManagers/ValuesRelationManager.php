<?php

namespace Lunar\Admin\Filament\Resources\ProductOptionResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Support\Tables\Columns\TranslatedTextColumn;
use Lunar\Admin\Support\Forms\Components\TranslatedText;

class ValuesRelationManager extends RelationManager
{
    protected static string $relationship = 'values';

    public function getTableRecordTitle(Model $record): ?string
    {
        return $record->translate('name');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TranslatedText::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table

            ->columns([
                TranslatedTextColumn::make('name'),
                Tables\Columns\TextColumn::make('position'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('position', 'asc')
            ->reorderable('position');
    }
}
