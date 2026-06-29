<?php

namespace Lunar\Filament\Forms\Components;

use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\MorphToSelect\Type;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Filament\Forms\Components\Support\RecordSearch;

/**
 * One reusable `MorphToSelect` for the three Discount RelationManagers
 * that target a polymorphic discountable. Configure the target model
 * mix per call-site via `->targets([...])`.
 */
class DiscountTargetSelect extends MorphToSelect
{
    /**
     * @var array<int, class-string<Model>>
     */
    protected array $lunarTargets = [
        Product::class,
        ProductVariant::class,
        Collection::class,
        Brand::class,
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('lunar-filament::forms/selectors.discount_target.label'));
        $this->searchable();
        $this->types($this->resolveLunarTypes());
    }

    /**
     * @param  array<int, class-string<Model>>  $targets
     */
    public function targets(array $targets): static
    {
        $this->lunarTargets = $targets;

        $this->types($this->resolveLunarTypes());

        return $this;
    }

    /**
     * @return array<int, Type>
     */
    protected function resolveLunarTypes(): array
    {
        return array_map(
            fn (string $target): Type => $this->buildTypeFor($target),
            $this->lunarTargets,
        );
    }

    protected function buildTypeFor(string $target): Type
    {
        return match ($target) {
            Product::class => $this->productType(),
            ProductVariant::class => $this->productVariantType(),
            Collection::class => $this->collectionType(),
            Brand::class => $this->brandType(),
            default => Type::make($target)->titleAttribute('name'),
        };
    }

    protected function productType(): Type
    {
        $modelClass = Product::class;

        return Type::make($modelClass)
            ->titleAttribute('name')
            ->getSearchResultsUsing(static fn (string $search): array => RecordSearch::for($modelClass, $search)
                ->take(50)
                ->get()
                ->mapWithKeys(fn (Model $record): array => [$record->getKey() => $record->translate('name')])
                ->all())
            ->getOptionLabelUsing(static fn ($value): ?string => $modelClass::find($value)?->translate('name'));
    }

    protected function productVariantType(): Type
    {
        $variantClass = ProductVariant::class;
        $productClass = Product::class;

        return Type::make($variantClass)
            ->titleAttribute('sku')
            ->getSearchResultsUsing(static function (string $search) use ($variantClass, $productClass): array {
                $productIds = RecordSearch::for($productClass, $search)->take(50)->get()->pluck('id');

                return $variantClass::query()
                    ->with('product')
                    ->where(function ($q) use ($search, $productIds): void {
                        $q->where('sku', 'like', "%{$search}%");
                        if ($productIds->isNotEmpty()) {
                            $q->orWhereIn('product_id', $productIds);
                        }
                    })
                    ->take(50)
                    ->get()
                    ->mapWithKeys(fn (Model $record): array => [
                        $record->getKey() => trim(($record->product?->translate('name') ?? '—').' — '.($record->sku ?? '')),
                    ])
                    ->all();
            })
            ->getOptionLabelUsing(static function ($value) use ($variantClass): ?string {
                $variant = $variantClass::with('product')->find($value);

                return $variant
                    ? trim(($variant->product?->translate('name') ?? '—').' — '.($variant->sku ?? ''))
                    : null;
            });
    }

    protected function collectionType(): Type
    {
        $modelClass = Collection::class;

        return Type::make($modelClass)
            ->titleAttribute('name')
            ->getSearchResultsUsing(static fn (string $search): array => RecordSearch::for($modelClass, $search)
                ->with('ancestors')
                ->take(50)
                ->get()
                ->mapWithKeys(fn (Model $record): array => [
                    $record->getKey() => $record->breadcrumb->push($record->translate('name'))->filter()->implode(' > '),
                ])
                ->all())
            ->getOptionLabelUsing(static function ($value) use ($modelClass): ?string {
                $record = $modelClass::with('ancestors')->find($value);

                return $record?->breadcrumb->push($record->translate('name'))->filter()->implode(' > ');
            });
    }

    protected function brandType(): Type
    {
        $modelClass = Brand::class;

        return Type::make($modelClass)
            ->titleAttribute('name')
            ->getSearchResultsUsing(static fn (string $search): array => RecordSearch::for($modelClass, $search)
                ->take(50)
                ->get()
                ->mapWithKeys(fn (Model $record): array => [$record->getKey() => $record->name])
                ->all())
            ->getOptionLabelUsing(static fn ($value): ?string => $modelClass::find($value)?->name);
    }
}
