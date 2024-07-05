<?php

namespace Lunar\Admin\Filament\Resources\TaxRateResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Lunar\Admin\Events\ProductCustomerGroupsUpdated;

class TaxRateAmountRelationManager extends RelationManager
{
    protected static bool $isLazy = false;

    protected static string $relationship = 'taxRateAmounts';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->description(
                __('lunarpanel::relationmanagers.tax_rate_amounts.table.description')
            )
            ->paginated(false)
            ->headerActions([
            ])->columns([
                Tables\Columns\TextColumn::make('taxClass.name')->label(
                    __('lunarpanel::relationmanagers.tax_rate_amounts.table.tax_class.label')
                ),
            ])->actions([
                Tables\Actions\EditAction::make()->after(
                    fn () => ProductCustomerGroupsUpdated::dispatch(
                        $this->getOwnerRecord()
                    )
                ),
            ]);
    }
}
