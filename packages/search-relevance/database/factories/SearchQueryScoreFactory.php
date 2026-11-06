<?php

namespace Lunar\SearchRelevance\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Lunar\Core\Models\Product;
use Lunar\SearchRelevance\Models\SearchQueryScore;

class SearchQueryScoreFactory extends Factory
{
    protected $model = SearchQueryScore::class;

    public function definition(): array
    {
        return [
            'model_type' => Product::class,
            'normalised_query' => 'cable tie',
            'product_id' => 1,
            'score' => 10.0,
            'relative' => 1.0,
            'sessions' => 3,
            'version' => 'n1:database:keyword',
            'updated_at' => now(),
        ];
    }
}
