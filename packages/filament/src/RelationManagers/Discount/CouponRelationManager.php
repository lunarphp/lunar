<?php

namespace Lunar\Filament\RelationManagers\Discount;

use Closure;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Contracts\Actions\Discounts\CreatesCoupon;
use Lunar\Core\Contracts\Actions\Discounts\DeletesCoupon;
use Lunar\Core\Enums\DiscountRedemptionMethod;
use Lunar\Core\Models\Coupon;
use Lunar\Filament\RelationManagers\BaseRelationManager;

class CouponRelationManager extends BaseRelationManager
{
    protected static bool $isLazy = false;

    protected static string $relationship = 'coupons';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('lunar-filament::discount.relationmanagers.coupons.title');
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageName): bool
    {
        return $ownerRecord->redemption === DiscountRedemptionMethod::Coupon;
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function getDefaultForm(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')
                ->label(__('lunar-filament::discount.relationmanagers.coupons.form.code.label'))
                ->required()
                ->maxLength(255)
                ->dehydrateStateUsing(fn (?string $state) => filled($state) ? mb_strtoupper(trim($state)) : $state)
                ->rule(fn () => function (string $attribute, mixed $value, Closure $fail) {
                    // Stored codes are uppercased by the cast, so compare the uppercased input.
                    if (Coupon::where('code', mb_strtoupper(trim((string) $value)))->exists()) {
                        $fail(__('validation.unique', ['attribute' => $attribute]));
                    }
                }),
            TextInput::make('max_uses')
                ->label(__('lunar-filament::discount.relationmanagers.coupons.form.max_uses.label'))
                ->integer()
                ->minValue(1),
        ]);
    }

    public function getDefaultTable(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withMax('redemptions as last_redeemed_at', 'redeemed_at'))
            ->headerActions([
                CreateAction::make()
                    ->label(__('lunar-filament::discount.relationmanagers.coupons.actions.create.label'))
                    ->using(fn (array $data, CreatesCoupon $createsCoupon): Coupon => $createsCoupon->execute($this->getOwnerRecord(), $data)),
            ])
            ->columns([
                TextColumn::make('code')
                    ->label(__('lunar-filament::discount.relationmanagers.coupons.table.code.label'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('uses')
                    ->label(__('lunar-filament::discount.relationmanagers.coupons.table.uses.label'))
                    ->sortable(),
                TextColumn::make('max_uses')
                    ->label(__('lunar-filament::discount.relationmanagers.coupons.table.limit.label'))
                    ->placeholder(__('lunar-filament::discount.relationmanagers.coupons.table.limit.unlimited'))
                    ->sortable(),
                TextColumn::make('email')
                    ->label(__('lunar-filament::discount.relationmanagers.coupons.table.email.label'))
                    ->searchable(),
                TextColumn::make('batch')
                    ->label(__('lunar-filament::discount.relationmanagers.coupons.table.batch.label')),
                TextColumn::make('last_redeemed_at')
                    ->label(__('lunar-filament::discount.relationmanagers.coupons.table.last_redeemed.label'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                DeleteAction::make()
                    ->using(function (Coupon $record, DeletesCoupon $deletesCoupon): bool {
                        $deletesCoupon->execute($record);

                        return true;
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->using(fn (EloquentCollection $records, DeletesCoupon $deletesCoupon) => $records->each(
                            fn (Coupon $coupon) => $deletesCoupon->execute($coupon),
                        )),
                ]),
            ]);
    }
}
