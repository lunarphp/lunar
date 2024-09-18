<?php

namespace Lunar\Database\Factories;

use Lunar\Models\Language;
use Lunar\Models\Product;
use Lunar\Models\Url;

class UrlFactory extends BaseFactory
{
    protected $model = Url::class;

    public function definition(): array
    {
        return [
            'slug' => $this->faker->slug,
            'default' => true,
            'language_id' => Language::factory(),
            'element_type' => Product::morphName(),
            'element_id' => 1,
        ];
    }
}
