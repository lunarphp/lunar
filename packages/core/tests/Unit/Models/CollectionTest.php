<?php

namespace Lunar\Tests\Unit\Models;

use Lunar\FieldTypes\Text;
use Lunar\Models\Collection;
use Lunar\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CollectionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_make_a_collection()
    {
        $collection = Collection::factory()
            ->create([
                'attribute_data' => collect([
                    'name' => new Text('Red Products'),
                ]),
            ]);

        $this->assertEquals($collection->translateAttribute('name'), 'Red Products');
    }
}
