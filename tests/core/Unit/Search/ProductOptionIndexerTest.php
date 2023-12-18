<?php

uses(\Lunar\Tests\TestCase::class);
use Lunar\Models\ProductOption;
use Lunar\Search\ProductOptionIndexer;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can return correct searchable data', function () {
    $productOption = ProductOption::factory()->create();

    $data = app(ProductOptionIndexer::class)->toSearchableArray($productOption);

    expect($data['name_en'])->toEqual($productOption->name->en);
    expect($data['label_en'])->toEqual($productOption->label->en);
});
