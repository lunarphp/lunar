<?php

namespace Lunar\Filament\Tables\ProductOption;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Lunar\Filament\Support\Concerns\CallsHooks;
use Lunar\Filament\Tables\Columns\TranslatedTextColumn;

class ProductOptionTable
{
    use CallsHooks;

    public static function configure(Table $table): Table
    {
        return self::callStaticLunarHook(
            'configureTable',
            $table
                ->columns(static::getColumns())
                ->filters([
                    Filter::make('shared')
                        ->query(fn (Builder $query): Builder => $query->where('shared', true)),
                ])
                ->recordActions([
                    EditAction::make(),
                ])
                ->toolbarActions([
                    BulkActionGroup::make([
                        DeleteBulkAction::make(),
                    ]),
                ])
                ->searchable(),
        );
    }

    public static function getColumns(): array
    {
        return [
            TranslatedTextColumn::make('name')
                ->label(__('lunar-filament::productoption.table.name.label'))
                ->searchable(),
            TranslatedTextColumn::make('label')
                ->label(__('lunar-filament::productoption.table.label.label'))
                ->searchable(),
            TextColumn::make('handle')
                ->label(__('lunar-filament::productoption.table.handle.label'))
                ->searchable(),
            IconColumn::make('shared')
                ->boolean()
                ->label(__('lunar-filament::productoption.table.shared.label')),
        ];
    }
}
