<?php

namespace GetCandy\Hub\Http\Livewire\Components\Products;

use GetCandy\Hub\Tables\Columns\AttributeColumn;
use GetCandy\Hub\Tables\Columns\ThumbnailColumn;
use GetCandy\Models\Product;
use Illuminate\Contracts\Database\Query\Builder;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\EditAction;
use GetCandy\Hub\Tables\Columns\SkuColumn;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use GetCandy\Hub\Tables\Columns\TextColumn;
use GetCandy\Hub\Tables\GetCandyTable;
use Illuminate\Support\Collection;

class ProductsIndex extends GetCandyTable
{
    /**
     * {@inheritDoc}
     */
    public function isTableSearchable(): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    protected function getTableQuery(): Builder
    {
        return Product::query();
    }

    /**
     * {@inheritDoc}
     */
    protected function applySearchToTableQuery(Builder $query): Builder
    {
        if (filled($searchQuery = $this->getTableSearchQuery())) {
            $query->whereIn('id', Product::search($searchQuery)->keys());
        }

        return $query;
    }

    /**
     * {@inheritDoc}
     */
    protected function getBaseTableColumns(): array
    {
        return [
            $this->statusColumn(),
            $this->thumbnailColumn(),
            $this->attributeColumn('name')->url(fn (Product $record): string => route('hub.products.show', ['product' => $record])),
            TextColumn::make('brand'),
            TextColumn::make('productType.name'),
            SkuColumn::make('sku')->label('SKU')
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getBaseTableActions(): array
    {
        return [
            ActionGroup::make([
                RestoreAction::make(),
                EditAction::make()->url(fn (Product $record): string => route('hub.products.show', ['product' => $record])),
            ])
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getBaseTableBulkActions(): array
    {
        return [
            BulkAction::make('delete')
            ->action(fn (Collection $records) => $records->each->delete())
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getBaseTableFilters(): array
    {
        return [
            SelectFilter::make('brand')->options(
                Product::distinct()->pluck('brand')->mapWithKeys(function ($brand) {
                   return [$brand => $brand];
                }),
            ),
            SelectFilter::make('status')
            ->options([
                'published' => 'Published',
                'unpublished' => 'Unpublished',
            ]),
            TernaryFilter::make('trashed')
            ->placeholder('Without trashed records')
            ->trueLabel('With trashed records')
            ->falseLabel('Only trashed records')
            ->queries(
                true: fn (Builder $query) => $query->withTrashed(),
                false: fn (Builder $query) => $query->onlyTrashed(),
                blank: fn (Builder $query) => $query->withoutTrashed(),
            )
        ];
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.products.index')
            ->layout('adminhub::layouts.base');
    }
}
