<?php

namespace Lunar\Filament\Tables\Discount;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Lunar\Core\Models\Discount;
use Lunar\Filament\Support\Concerns\CallsHooks;

class DiscountTable
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
            TextColumn::make('status')
                ->formatStateUsing(function ($state) {
                    return __("lunar-filament::discount.table.status.{$state}.label");
                })
                ->label(__('lunar-filament::discount.table.status.label'))
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    Discount::ACTIVE => 'success',
                    Discount::EXPIRED => 'danger',
                    Discount::PENDING => 'gray',
                    Discount::SCHEDULED => 'info',
                })
                ->toggleable(),
            TextColumn::make('name')
                ->label(__('lunar-filament::discount.table.name.label'))
                ->searchable()
                ->sortable()
                ->toggleable(),
            TextColumn::make('type')
                ->formatStateUsing(function ($state) {
                    return (new $state)->getName();
                })
                ->label(__('lunar-filament::discount.table.type.label'))
                ->toggleable(),
            TextColumn::make('starts_at')
                ->label(__('lunar-filament::discount.table.starts_at.label'))
                ->date()
                ->sortable()
                ->toggleable(),
            TextColumn::make('ends_at')
                ->label(__('lunar-filament::discount.table.ends_at.label'))
                ->date()
                ->sortable()
                ->toggleable(),
            TextColumn::make('coupon')
                ->label(__('lunar-filament::discount.table.coupon.label'))
                ->searchable()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('created_at')
                ->label(__('lunar-filament::discount.table.created_at.label'))
                ->date()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('public_id')
                ->label(__('lunar-filament::components.public_id.label'))
                ->toggleable(isToggledHiddenByDefault: true)
                ->copyable(),
        ];
    }
}
