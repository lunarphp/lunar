<?php

namespace Lunar\Filament\RelationManagers\TaxRate;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;
use Lunar\Core\Models\TaxRateAmount;

class TaxRateAmountRelationManager extends RelationManager
{
    protected static bool $isLazy = false;

    protected static string $relationship = 'taxRateAmounts';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('tax_class_id')
                ->required()
                ->unique(
                    TaxRateAmount::class,
                    'tax_class_id',
                    ignoreRecord: true,
                    modifyRuleUsing: fn (Unique $rule) => $rule->when(
                        $this->getOwnerRecord(),
                        fn ($query, $value) => $query->where('tax_rate_id', $value->id)
                    )
                )
                ->relationship(name: 'taxClass', titleAttribute: 'name'),
            TextInput::make('percentage')->numeric()->required(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->description(
                __('lunar-filament::relationmanagers.tax_rate_amounts.table.description')
            )
            ->paginated(false)
            ->headerActions([
                CreateAction::make('create'),
            ])->columns([
                TextColumn::make('taxClass.name')->label(
                    __('lunar-filament::relationmanagers.tax_rate_amounts.table.tax_class.label')
                ),
                TextColumn::make('percentage')->label(
                    __('lunar-filament::relationmanagers.tax_rate_amounts.table.percentage.label')
                ),
            ])->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
