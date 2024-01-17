<?php

namespace Lunar\Admin\Filament\Resources\ProductResource\Widgets;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;
use Lunar\Models\ProductOption;

class ProductOptionsWidget extends BaseWidget implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public ?Model $record;

    public function query()
    {
        return $this->record->productOptions()
            ->with('values', function ($query) {
                $query->whereHas('variants', function ($relation) {
                    $relation->whereIn($relation->getModel()->getTable().'.id', $this->record->variants()->pluck('id'));
                });
            });
    }

    public function table(Table $table)
    {
        return $table->query(
            fn () => $this->query()
        )->columns([
            TextColumn::make('name')
                ->formatStateUsing(
                    fn (ProductOption $productOption) => $productOption->translate('name')
                ),
            TextColumn::make('values')->formatStateUsing(
                fn (ProductOption $productOption) => $productOption->values->map(
                    fn ($value) => $value->translate('name')
                )->join(', ')
            ),
        ]);
    }

    protected static string $view = 'lunarpanel::resources.product-resource.widgets.product-options';
}
