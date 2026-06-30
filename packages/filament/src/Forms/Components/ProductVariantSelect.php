<?php

namespace Lunar\Filament\Forms\Components;

use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Filament\Forms\Components\Concerns\ExcludesAttachedRecords;
use Lunar\Filament\Forms\Components\Concerns\SearchesLunarRecords;
use Lunar\Filament\Forms\Components\Support\RecordSearch;

class ProductVariantSelect extends Select
{
    use ExcludesAttachedRecords;
    use SearchesLunarRecords;

    protected ?Product $forProduct = null;

    protected bool $searchViaProduct = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('lunar-filament::forms/selectors.product_variant.label'));
        $this->placeholder(__('lunar-filament::forms/selectors.product_variant.placeholder'));
        $this->searchable();
        $this->getSearchResultsUsing(fn (string $search): array => $this->resolveVariantOptions($search));
        $this->getOptionLabelUsing(function ($value): ?string {
            $model = $this->lunarModel();
            $record = $model::find($value);

            return $record ? $this->optionLabel($record) : null;
        });
    }

    /**
     * @return class-string<Model>
     */
    public function lunarModel(): string
    {
        return ProductVariant::class;
    }

    public function forProduct(?Product $product): static
    {
        $this->forProduct = $product;

        return $this;
    }

    /**
     * Switch to the alternate search strategy: search products by name
     * first, then return all variants for the matching products.
     */
    public function searchViaProduct(bool $condition = true): static
    {
        $this->searchViaProduct = $condition;

        return $this;
    }

    public function optionLabel(Model $record): string
    {
        $productName = $record->product?->translate('name') ?? '—';

        return $record->sku
            ? "{$productName} — {$record->sku}"
            : $productName;
    }

    /**
     * @return array<int|string, string>
     */
    protected function resolveVariantOptions(string $search): array
    {
        if ($this->forProduct) {
            $variantModel = $this->lunarModel();

            return $variantModel::query()
                ->where('product_id', $this->forProduct->getKey())
                ->where(function (Builder $query) use ($search): void {
                    $query->where('sku', 'like', "%{$search}%");
                })
                ->take($this->resolveOptionsLimit())
                ->get()
                ->mapWithKeys(fn (Model $record): array => [
                    $record->getKey() => $this->optionLabel($record),
                ])
                ->all();
        }

        if ($this->searchViaProduct) {
            return $this->searchViaProductStrategy($search);
        }

        return $this->directVariantSearch($search);
    }

    /**
     * @return array<int|string, string>
     */
    protected function directVariantSearch(string $search): array
    {
        $variantModel = $this->lunarModel();
        $productModel = Product::class;

        $productIds = RecordSearch::for($productModel, $search)
            ->take($this->resolveOptionsLimit())
            ->get()
            ->pluck('id');

        return $variantModel::query()
            ->where(function (Builder $query) use ($search, $productIds): void {
                $query->where('sku', 'like', "%{$search}%");

                if ($productIds->isNotEmpty()) {
                    $query->orWhereIn('product_id', $productIds);
                }
            })
            ->take($this->resolveOptionsLimit())
            ->get()
            ->mapWithKeys(fn (Model $record): array => [
                $record->getKey() => $this->optionLabel($record),
            ])
            ->all();
    }

    /**
     * @return array<int|string, string>
     */
    protected function searchViaProductStrategy(string $search): array
    {
        $productModel = Product::class;
        $variantModel = $this->lunarModel();

        $productIds = RecordSearch::for($productModel, $search)
            ->take($this->resolveOptionsLimit())
            ->get()
            ->pluck('id');

        if ($productIds->isEmpty()) {
            return [];
        }

        return $variantModel::query()
            ->whereIn('product_id', $productIds)
            ->take($this->resolveOptionsLimit())
            ->get()
            ->mapWithKeys(fn (Model $record): array => [
                $record->getKey() => $this->optionLabel($record),
            ])
            ->all();
    }
}
