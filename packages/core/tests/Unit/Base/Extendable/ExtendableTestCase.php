<?php

namespace GetCandy\Tests\Unit\Base\Extendable;

use GetCandy\Facades\ModelManifest;
use GetCandy\Models\Product;
use GetCandy\Models\ProductOption;
use GetCandy\Models\ProductOptionValue;
use GetCandy\Tests\TestCase;

class ExtendableTestCase extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        ModelManifest::register(collect([
            Product::class => \GetCandy\Tests\Stubs\Models\Product::class,
            ProductOption::class => \GetCandy\Tests\Stubs\Models\ProductOption::class,
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
