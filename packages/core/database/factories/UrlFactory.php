<?php

namespace Lunar\Core\Database\Factories;

use Illuminate\Support\Str;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\Url;

class UrlFactory extends BaseFactory
{
    protected $model = Url::class;

    public function definition(): array
    {
        return [
            'public_id' => (string) Str::ulid(),
            'slug' => $this->faker->slug,
            'default' => true,
            'language_id' => Language::factory(),
            'element_type' => Product::morphName(),
            'element_id' => 1,
        ];
    }
}
