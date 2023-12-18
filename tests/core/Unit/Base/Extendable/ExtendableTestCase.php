<?php

namespace Lunar\Tests\Core\Unit\Base\Extendable;

use Lunar\Facades\ModelManifest;
use Lunar\Models\Product;
use Lunar\Models\ProductOption;
use Lunar\Models\ProductOptionValue;
use Lunar\Tests\Core\TestCase;

class ExtendableTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        ModelManifest::register(collect([
            Product::class => \Lunar\Tests\Core\Stubs\Models\Product::class,
            ProductOption::class => \Lunar\Tests\Core\Stubs\Models\ProductOption::class,
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
