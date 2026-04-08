<?php

uses(TestCase::class);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\ProductOption;
use Lunar\Search\ProductOptionIndexer;
use Lunar\Tests\Core\TestCase;

uses(RefreshDatabase::class);

test('can return correct searchable data', function () {
    $productOption = ProductOption::factory()->create();

    $data = app(ProductOptionIndexer::class)->toSearchableArray($productOption);

    expect($data['name_en'])->toEqual($productOption->name->en)
        ->and($data['label_en'])->toEqual($productOption->label->en);
});
