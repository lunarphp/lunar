<?php

uses(\Lunar\Tests\TestCase::class);
use Lunar\FieldTypes\Text;
use Lunar\Models\Collection;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can make a collection', function () {
    $collection = Collection::factory()
        ->create([
            'attribute_data' => collect([
                'name' => new Text('Red Products'),
            ]),
        ]);

    expect('Red Products')->toEqual($collection->translateAttribute('name'));
});
