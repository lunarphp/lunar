<?php

uses(TestCase::class);
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\FieldTypes\Text;
use Lunar\Models\Collection;
use Lunar\Tests\Core\TestCase;

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
