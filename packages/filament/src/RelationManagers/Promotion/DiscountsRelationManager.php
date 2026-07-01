<?php

namespace Lunar\Filament\RelationManagers\Promotion;

use Filament\Actions\AssociateAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Filament\RelationManagers\BaseRelationManager;

class DiscountsRelationManager extends BaseRelationManager
{
    protected static bool $isLazy = false;

    protected static string $relationship = 'discounts';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('lunar-filament::promotion.relationmanagers.discounts.title');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function getDefaultTable(Table $table): Table
    {
        return $table
            ->description(
                __('lunar-filament::promotion.relationmanagers.discounts.description')
            )
            ->recordTitleAttribute('name')
            ->headerActions([
                AssociateAction::make()
                    ->label(__('lunar-filament::promotion.relationmanagers.discounts.actions.associate.label'))
                    ->recordSelectSearchColumns(['name', 'handle'])
                    ->preloadRecordSelect(),
            ])
            ->columns([
                TextColumn::make('name')
                    ->label(__('lunar-filament::promotion.relationmanagers.discounts.table.name.label')),
                TextColumn::make('handle')
                    ->label(__('lunar-filament::promotion.relationmanagers.discounts.table.handle.label')),
                TextColumn::make('status')
                    ->label(__('lunar-filament::promotion.relationmanagers.discounts.table.status.label'))
                    ->badge(),
                TextColumn::make('starts_at')
                    ->label(__('lunar-filament::promotion.relationmanagers.discounts.table.starts_at.label'))
                    ->dateTime()
                    ->placeholder('-'),
                TextColumn::make('ends_at')
                    ->label(__('lunar-filament::promotion.relationmanagers.discounts.table.ends_at.label'))
                    ->dateTime()
                    ->placeholder('-'),
            ])
            ->recordActions([
                DissociateAction::make(),
            ])
            ->toolbarActions([
                DissociateBulkAction::make(),
            ]);
    }
}
