<?php

namespace Lunar\Admin\Filament\Resources\CustomerResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Support\Concerns\CallsHooks;

class CustomerTable
{
    use CallsHooks;

    public static function configure(Table $table): Table
    {
        return self::callStaticLunarHook(
            'configureTable',
            $table
                ->columns(static::getColumns())
                ->filters([
                    SelectFilter::make('customer_group')
                        ->label(__('lunarpanel::customergroup.label'))
                        ->relationship(
                            name: 'customerGroups',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn (Builder $query) => $query->distinct(
                                ['id', 'name', 'handle', 'default']
                            )
                        )
                        ->multiple()
                        ->searchable()
                        ->preload(),
                ])
                ->recordActions([
                    ViewAction::make(),
                ])
                ->toolbarActions([
                    BulkActionGroup::make([
                        DeleteBulkAction::make(),
                    ]),
                ])
                ->selectCurrentPageOnly(),
        );
    }

    public static function getColumns(): array
    {
        return [
            TextColumn::make('first_name')
                ->label(__('lunarpanel::customer.table.first_name.label'))
                ->sortable()
                ->searchable(),
            TextColumn::make('last_name')
                ->label(__('lunarpanel::customer.table.last_name.label'))
                ->sortable()
                ->searchable(),
            TextColumn::make('company_name')
                ->label(__('lunarpanel::customer.table.company_name.label'))
                ->sortable()
                ->searchable(),
            TextColumn::make('tax_identifier')
                ->label(__('lunarpanel::customer.table.tax_identifier.label'))
                ->sortable(),
            TextColumn::make('account_ref')
                ->label(__('lunarpanel::customer.table.account_reference.label'))
                ->sortable(),
            TextColumn::make('customerGroups.name')
                ->label(__('lunarpanel::customergroup.label'))
                ->badge()
                ->limitList(1)
                ->tooltip(function (TextColumn $column, Model $record): ?string {
                    if ($record->customerGroups->count() <= $column->getListLimit()) {
                        return null;
                    }

                    return $record->customerGroups
                        ->map(fn ($customerGroup) => $customerGroup->name)
                        ->implode(', ');
                }),
        ];
    }
}
