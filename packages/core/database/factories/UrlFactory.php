<?php

namespace GetCandy\Database\Factories;

use GetCandy\Models\Language;
use GetCandy\Models\Product;
use GetCandy\Models\Url;
use Illuminate\Database\Eloquent\Factories\Factory;

class UrlFactory extends Factory
{
    protected $model = Url::class;

    public function definition(): array
    {
        return [
            'slug'         => $this->faker->slug,
            'default'      => true,
            'language_id'  => Language::factory(),
            'element_type' => Product::class,
            'element_id'   => 1,
        ];
    }
}
