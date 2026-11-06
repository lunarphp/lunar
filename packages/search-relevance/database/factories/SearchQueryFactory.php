<?php

namespace Lunar\SearchRelevance\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Lunar\Core\Models\Product;
use Lunar\SearchRelevance\Models\SearchQuery;

class SearchQueryFactory extends Factory
{
    protected $model = SearchQuery::class;

    public function definition(): array
    {
        $shown = [1, 2, 3, 4, 5];

        return [
            'id' => (string) Str::ulid(),
            'model_type' => Product::class,
            'raw_query' => 'cable ties',
            'normalised_query' => 'cable tie',
            'filters_hash' => md5('[]'),
            'session_id' => 'session:'.Str::random(20),
            'customer_id' => null,
            'version' => 'n1:database:keyword',
            'mode' => 'shadow',
            'result_count' => count($shown),
            'shown' => $shown,
            'ranked' => null,
            'features' => null,
            'created_at' => now(),
        ];
    }
}
