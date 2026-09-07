<?php

namespace Lunar\Tests\Api\Fixtures;

use Closure;
use Illuminate\Support\Facades\Route;
use Lunar\Api\Resources\Field;
use Lunar\Api\Resources\Filter;
use Lunar\Api\Resources\ResourceExtension;
use Lunar\Api\Resources\Sort;
use Lunar\Api\Storefront\Resources\V1\ProductResource;
use Lunar\Core\Models\Product;

/** What a reviews add-on would register against the storefront products resource. */
class ReviewsProductExtension extends ResourceExtension
{
    public function extends(): string
    {
        return ProductResource::class;
    }

    public function fields(): array
    {
        return [
            Field::make('average_rating', fn (Product $product) => 4.5),
            Field::make('review_count', fn (Product $product) => $product->variants_count)->eagerLoad(withCount: ['variants']),
            Field::make('cost_price', fn () => 1000)->requires('catalog:manage-products'),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::scope('featured'),
        ];
    }

    public function sorts(): array
    {
        return [
            Sort::column('rating', 'created_at'),
        ];
    }

    public function routes(): ?Closure
    {
        return function (): void {
            Route::get('{id}/reviews', fn (string $id) => response()->json(['data' => ['product' => $id, 'reviews' => []]]))->name('reviews');
        };
    }
}
