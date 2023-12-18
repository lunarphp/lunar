<?php

namespace Unit\Base\Extendable;

use Lunar\Facades\ModelManifest;
use Lunar\Models\Product;
use Lunar\Models\ProductOption;
use Lunar\Models\ProductOptionValue;
use Lunar\Tests\TestCase;

class ExtendableTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        ModelManifest::register(collect([
            Product::class => \Stubs\Models\Product::class,
            ProductOption::class => \Stubs\Models\ProductOption::class,
        ]));

        Product::factory()->count(20)->create();

        ProductOption::factory()
            ->has(ProductOptionValue::factory()->count(3), 'values')
            ->create([
                'name' => [
                    'en' => 'Size',
                ],
            ]);
    }
}
