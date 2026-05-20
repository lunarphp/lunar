<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\AttributeGroup;
use Lunar\Core\Models\ProductType;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

test('can make a product type', function () {
    $productType = ProductType::factory()
        ->has(
            Attribute::factory()->for(AttributeGroup::factory())->count(1),
            'mappedAttributes',
        )
        ->create([
            'name' => 'Bob',
        ]);

    expect($productType->name)->toEqual('Bob');
});
