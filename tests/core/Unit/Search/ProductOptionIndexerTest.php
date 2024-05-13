<?php

uses(\Lunar\Tests\Core\TestCase::class);
use Lunar\Models\ProductOption;
use Lunar\Search\ProductOptionIndexer;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can return correct searchable data', function () {
    $productOption = ProductOption::factory()->create();

    $data = app(ProductOptionIndexer::class)->toSearchableArray($productOption);

    $this->assertEquals($productOption->name->en, $data['name_en']);
    $this->assertEquals($productOption->label->en, $data['label_en']);
});
