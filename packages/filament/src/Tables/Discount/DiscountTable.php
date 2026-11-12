<?php

namespace Lunar\Filament\Tables\Discount;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Lunar\Core\Enums\DiscountRedemptionMethod;
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
                ->modifyQueryUsing(fn (Builder $query) => $query->withCount('coupons')->withMin('coupons', 'code'))
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
                    // A discount can outlive the package that registered its type;
                    // show the stored class rather than fataling the whole table.
                    return class_exists($state) ? (new $state)->getName() : $state;
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
            TextColumn::make('redemption')
                ->formatStateUsing(fn (DiscountRedemptionMethod $state) => __("lunar-filament::discount.form.redemption.options.{$state->value}"))
                ->label(__('lunar-filament::discount.table.redemption.label'))
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('coupon')
                ->label(__('lunar-filament::discount.table.coupon.label'))
                ->state(fn (Discount $record) => match (true) {
                    $record->coupons_count === 1 => $record->coupons_min_code,
                    $record->coupons_count > 1 => __('lunar-filament::discount.table.coupon.count', ['count' => $record->coupons_count]),
                    default => null,
                })
                ->searchable(query: fn (Builder $query, string $search) => $query->whereHas(
                    'coupons',
                    fn (Builder $coupons) => $coupons->where('code', 'like', "%{$search}%")
                ))
                ->sortable(query: fn (Builder $query, string $direction) => $query->orderBy('coupons_count', $direction))
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('created_at')
                ->label(__('lunar-filament::discount.table.created_at.label'))
                ->date()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }
}
