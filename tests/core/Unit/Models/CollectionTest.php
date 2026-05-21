<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\FieldTypes\Text;
use Lunar\Core\Models\Collection;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class)->group('cross-db');

uses(RefreshDatabase::class);

test('can make a collection', function () {
    $collection = Collection::factory()
        ->create([
            'attribute_data' => collect([
                'name' => new Text('Red Products'),
            ]),
        ]);

    expect('Red Products')->toEqual($collection->translateAttribute('name'));
});
