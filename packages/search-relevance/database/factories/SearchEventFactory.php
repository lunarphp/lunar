<?php

namespace Lunar\SearchRelevance\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Lunar\SearchRelevance\Models\SearchEvent;
use Lunar\SearchRelevance\Models\SearchQuery;

class SearchEventFactory extends Factory
{
    protected $model = SearchEvent::class;

    public function definition(): array
    {
        return [
            'search_id' => SearchQuery::factory(),
            'product_id' => 1,
            'position' => 1,
            'type' => 'click',
            'source' => 'organic',
            'session_id' => fn (array $attributes) => SearchQuery::find($attributes['search_id'])?->session_id ?? 'session:unknown',
            'created_at' => now(),
        ];
    }

    public function basket(): static
    {
        return $this->state(['type' => 'basket']);
    }

    public function purchase(): static
    {
        return $this->state(['type' => 'purchase']);
    }
}
