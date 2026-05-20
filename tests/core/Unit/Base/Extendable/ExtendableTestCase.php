<?php

namespace Lunar\Tests\Core\Unit\Base\Extendable;

use Lunar\Core\Facades\ModelManifest;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductOption;
use Lunar\Core\Models\ProductOptionValue;
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
