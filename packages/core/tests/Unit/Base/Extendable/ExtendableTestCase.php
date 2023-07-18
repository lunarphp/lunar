<?php

namespace Lunar\Tests\Unit\Base\Extendable;

use Lunar\Facades\ModelManifest;
use Lunar\Models\Product;
use Lunar\Models\ProductOption;
use Lunar\Models\ProductOptionValue;
use Lunar\Tests\TestCase;

class ExtendableTestCase extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        ModelManifest::register(collect([
            Product::class => \Lunar\Tests\Stubs\Models\Product::class,
            ProductOption::class => \Lunar\Tests\Stubs\Models\ProductOption::class,
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
