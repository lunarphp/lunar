<?php

namespace Lunar\Admin\Filament\Widgets\Products;

use Closure;
use Filament\Tables;
use Filament\Widgets\TableWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Lunar\Admin\Filament\Resources\ProductVariantResource;
use Lunar\Models\ProductOptionValue;
use Lunar\Models\ProductVariant;

class VariantSwitcherTable extends TableWidget
{
    public ?Model $record;

    protected function getTableQuery(): Builder|Relation|null
    {
        return ProductVariant::where('product_id', $this->record->id);
    }

    protected function getTableFilters(): array
    {
        $optionValues = ProductOptionValue::whereHas(
            'variants',
            fn ($query) => $query->whereIn(
                'variant_id',
                $this->getTableQuery()->pluck('id')
            ))
            ->with(['option'])
            ->get()
            ->groupBy('product_option_id');

        $filters = [];

        foreach ($optionValues as $values) {
            $option = $values->first()->option;

            $filters[] = Tables\Filters\SelectFilter::make(
                $option->handle
            )->label($option->translate('name'))
                ->options(
                    $values->mapWithKeys(
                        fn ($value) => [$value->id => $value->translate('name')]
                    )
                )->modifyQueryUsing(function (Builder $query, array $data) {
                    $value = $data['value'];

                    return $query->when(
                        $value,
                        function ($query) use ($value) {
                            $query->whereHas('values', function ($relation) use ($value) {
                                $table = $relation->getQuery()->from;

                                $relation->where("{$table}.id", '=', $value);
                            });
                        }
                    );
                });
        }

        return $filters;
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('sku')
                ->label(
                    __('lunarpanel::widgets.variant_switcher.table.sku.label')
                )->searchable(),
            Tables\Columns\TextColumn::make('values')
                ->label(
                    __('lunarpanel::widgets.variant_switcher.table.values.label')
                )
                ->formatStateUsing(
                    function (Model $record) {
                        return $record->values->map(
                            fn ($value) => $value->translate('name')
                        )->join(', ');
                    }
                ),
        ];
    }

    protected function getTableRecordUrlUsing(): ?Closure
    {
        return function (Model $record) {
            return ProductVariantResource::getUrl('edit', [
                'record' => $record,
            ]);
        };
    }

    protected function getTableHeading(): string|Htmlable|null
    {
        return '';
    }
}
