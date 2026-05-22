<?php

namespace Lunar\Filament\RelationManagers\Discount;

use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Filament\RelationManagers\BaseRelationManager;

class BrandLimitationRelationManager extends BaseRelationManager
{
    protected static bool $isLazy = false;

    protected static string $relationship = 'brands';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('lunar-filament::brand.plural_label');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function getDefaultTable(Table $table): Table
    {

        return $table
            ->description(
                __('lunar-filament::discount.relationmanagers.brands.description')
            )
            ->paginated(false)
            ->headerActions([
                AttachAction::make()->form(fn (AttachAction $action): array => [
                    $action->getRecordSelect(),
                    Select::make('type')
                        ->options(
                            fn () => [
                                'limitation' => __('lunar-filament::discount.relationmanagers.brands.form.type.options.limitation.label'),
                                'exclusion' => __('lunar-filament::discount.relationmanagers.brands.form.type.options.exclusion.label'),
                            ]
                        )->default('limitation'),
                ])->recordTitle(function ($record) {
                    return $record->name;
                })->preloadRecordSelect()
                    ->label(
                        __('lunar-filament::discount.relationmanagers.brands.actions.attach.label')
                    )
                    ->recordSelectSearchColumns(['name']),
            ])->columns([
                TextColumn::make('name')
                    ->label(
                        __('lunar-filament::discount.relationmanagers.brands.table.name.label')
                    ),
                TextColumn::make('pivot.type')
                    ->label(
                        __('lunar-filament::discount.relationmanagers.brands.table.type.label')
                    )->formatStateUsing(
                        fn (string $state) => __("lunar-filament::discount.relationmanagers.brands.table.type.{$state}.label")
                    ),
            ])->recordActions([
                DetachAction::make(),
            ])->toolbarActions([
                DetachBulkAction::make(),
            ]);
    }
}
