<?php

namespace Lunar\Admin\Filament\Resources\DiscountResource\RelationManagers;

use Filament\Forms\Components\Select;
use Filament\Tables;
use Filament\Tables\Table;
use Lunar\Admin\Support\RelationManagers\BaseRelationManager;

class BrandLimitationRelationManager extends BaseRelationManager
{
    protected static bool $isLazy = false;

    protected static string $relationship = 'brands';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function getDefaultTable(Table $table): Table
    {

        return $table
            ->description(
                __('lunarpanel::discount.relationmanagers.brands.description')
            )
            ->paginated(false)
            ->headerActions([
                Tables\Actions\AttachAction::make()->form(fn (Tables\Actions\AttachAction $action): array => [
                    $action->getRecordSelect(),
                    Select::make('type')
                        ->options(
                            fn () => [
                                'limitation' => __('lunarpanel::discount.relationmanagers.brands.form.type.options.limitation.label'),
                                'exclusion' => __('lunarpanel::discount.relationmanagers.brands.form.type.options.exclusion.label'),
                            ]
                        )->default('limitation'),
                ])->recordTitle(function ($record) {
                    return $record->name;
                })->preloadRecordSelect()
                    ->label(
                        __('lunarpanel::discount.relationmanagers.brands.actions.attach.label')
                    ),
            ])->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(
                        __('lunarpanel::discount.relationmanagers.brands.table.name.label')
                    ),
                Tables\Columns\TextColumn::make('pivot.type')
                    ->label(
                        __('lunarpanel::discount.relationmanagers.brands.table.type.label')
                    )->formatStateUsing(
                        fn (string $state) => __("lunarpanel::discount.relationmanagers.brands.table.type.{$state}.label")
                    ),
            ])->actions([
                Tables\Actions\DetachAction::make(),
            ]);
    }
}
