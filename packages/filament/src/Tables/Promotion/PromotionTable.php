<?php

namespace Lunar\Filament\Tables\Promotion;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Lunar\Core\Models\Promotion;
use Lunar\Filament\Support\Concerns\CallsHooks;

class PromotionTable
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
                ->searchable(),
        );
    }

    public static function getColumns(): array
    {
        return [
            TextColumn::make('name')
                ->label(__('lunar-filament::promotion.table.name.label'))
                ->getStateUsing(fn (Promotion $record): ?string => $record->translate('name'))
                ->searchable(),
            TextColumn::make('handle')
                ->label(__('lunar-filament::promotion.table.handle.label'))
                ->searchable(),
            TextColumn::make('discounts_count')
                ->counts('discounts')
                ->label(__('lunar-filament::promotion.table.discounts_count.label')),
            TextColumn::make('starts_at')
                ->label(__('lunar-filament::promotion.table.starts_at.label'))
                ->dateTime()
                ->sortable()
                ->placeholder('-'),
            TextColumn::make('ends_at')
                ->label(__('lunar-filament::promotion.table.ends_at.label'))
                ->dateTime()
                ->sortable()
                ->placeholder('-'),
            TextColumn::make('public_id')
                ->label(__('lunar-filament::components.public_id.label'))
                ->toggleable(isToggledHiddenByDefault: true)
                ->copyable(),
        ];
    }
}
