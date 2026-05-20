<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Models\ProductOption;
use Lunar\Core\Models\ProductOptionValue;
use Lunar\Core\Search\ProductOptionIndexer;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class)->group('search', 'indexer');

uses(RefreshDatabase::class);

test('can return correct searchable data', function () {
    $productOption = ProductOption::factory()->create();

    $value = ProductOptionValue::factory()->create([
        'product_option_id' => $productOption->id,
        'name' => [
            'en' => 'Small',
        ],
    ]);

    $data = app(ProductOptionIndexer::class)->toSearchableArray($productOption);

    expect($data['name_en'])->toEqual($productOption->name->en)
        ->and($data['label_en'])->toEqual($productOption->label->en)
        ->and($data['option_'.$value->id.'_en'])->toEqual('Small');
});
