<?php

namespace Lunar\Blog\Panel\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lunar\Blog\Models\Article;
use Lunar\Blog\Panel\Support\RecordOption;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;

/**
 * Data source for the article form's related-record pickers. Products are found
 * by variant SKU (how spares are identified); articles by title. Returns the
 * RecordOption shape the picker renders.
 */
class BlogArticleSearchController
{
    private const LIMIT = 20;

    public function __invoke(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q', ''));

        if ($term === '') {
            return response()->json(['results' => []]);
        }

        $results = match ($request->query('type')) {
            'product' => $this->products($term),
            'article' => $this->articles($term, (int) $request->query('exclude', 0)),
            default => [],
        };

        return response()->json(['results' => $results]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function products(string $term): array
    {
        $productIds = ProductVariant::query()
            ->whereLike('sku', "%{$term}%", caseSensitive: false)
            ->limit(self::LIMIT * 2)
            ->pluck('product_id')
            ->unique()
            ->take(self::LIMIT);

        return Product::query()
            ->whereKey($productIds)
            ->with(['variants', 'thumbnail'])
            ->get()
            ->map(fn (Product $product): array => RecordOption::fromProduct($product)->toArray())
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function articles(string $term, int $excludeId): array
    {
        return Article::query()
            ->whereLike('title', "%{$term}%", caseSensitive: false)
            ->when($excludeId > 0, fn ($query) => $query->whereKeyNot($excludeId))
            ->limit(self::LIMIT)
            ->get()
            ->map(fn (Article $article): array => RecordOption::fromArticle($article)->toArray())
            ->all();
    }
}
