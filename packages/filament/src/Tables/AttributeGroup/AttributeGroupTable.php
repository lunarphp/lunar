<?php

namespace Lunar\Filament\Tables\AttributeGroup;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Lunar\Filament\Support\Concerns\CallsHooks;

class AttributeGroupTable
{
    use CallsHooks;

    public static function configure(Table $table): Table
    {
        return self::callStaticLunarHook(
            'configureTable',
            $table
                ->columns(static::getColumns())
                ->filters([])
                ->recordActions([
                    EditAction::make(),
                ])
                ->toolbarActions([
                    BulkActionGroup::make([
                        DeleteBulkAction::make(),
                    ]),
                ])
                ->defaultSort('position', 'asc')
                ->reorderable('position'),
        );
    }

    public static function getColumns(): array
    {
        return [
            TextColumn::make('name')
                ->label(__('lunar-filament::attributegroup.table.name.label')),
            TextColumn::make('handle')
                ->label(__('lunar-filament::attributegroup.table.handle.label')),
            TextColumn::make('position')
                ->label(__('lunar-filament::attributegroup.table.position.label'))
                ->sortable(),
        ];
    }
}
