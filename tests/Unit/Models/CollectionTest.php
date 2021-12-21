<?php

namespace GetCandy\Tests\Unit\Models;

use GetCandy\FieldTypes\Text;
use GetCandy\Models\Collection;
use GetCandy\Tests\TestCase;
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

    /** @test */
    public function has_image_transformations_loaded_from_config()
    {
        $collection = Collection::factory()->create();
        $collection->registerAllMediaConversions();

        $conversions = $collection->mediaConversions;

        $this->assertIsArray($conversions);

        $transforms = config('getcandy.media.transformations');

        $this->assertCount(count($transforms), $conversions);
    }
}
