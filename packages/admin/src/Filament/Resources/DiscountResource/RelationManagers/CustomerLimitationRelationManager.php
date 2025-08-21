<?php

namespace Lunar\Admin\Filament\Resources\DiscountResource\RelationManagers;

use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Support\RelationManagers\BaseRelationManager;

use function Filament\Support\generate_search_column_expression;

class CustomerLimitationRelationManager extends BaseRelationManager
{
    protected static bool $isLazy = false;

    protected static string $relationship = 'customers';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('lunarpanel::customer.plural_label');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function getDefaultTable(Table $table): Table
    {

        return $table
            ->description(
                __('lunarpanel::discount.relationmanagers.customers.description')
            )
            ->paginated(false)
            ->headerActions([
                Tables\Actions\AttachAction::make()->form(fn (Tables\Actions\AttachAction $action): array => [
                    $action->getRecordSelect(),
                ])->recordTitle(function ($record) {
                    return $record->full_name;
                })->preloadRecordSelect()
                    ->recordSelectOptionsQuery(function ($query, $search) {
                        if (! filled($search)) {
                            return $query;
                        }

                        foreach (explode(' ', $search) as $word) {
                            $query->where(function ($query) use ($word) {
                                foreach (['first_name', 'last_name', 'company_name'] as $index => $column) {
                                    $query->{$index == 0 ? 'where' : 'orWhere'}(generate_search_column_expression($query->qualifyColumn($column), true, $query->getConnection()), 'like', "%{$word}%");
                                }
                            });
                        }
                    })
                    ->label(
                        __('lunarpanel::discount.relationmanagers.customers.actions.attach.label')
                    ),
            ])->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label(
                        __('lunarpanel::discount.relationmanagers.customers.table.name.label')
                    ),
            ])->actions([
                Tables\Actions\DetachAction::make(),
            ]);
    }
}
