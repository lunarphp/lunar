<?php

namespace GetCandy\Tests\Unit\Observers;

use GetCandy\Models\Collection;
use GetCandy\Models\Language;
use GetCandy\Models\Url;
use GetCandy\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

/**
 * @group observers
 */
class CollectionObserverTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function creates_default_url_when_collection_is_created()
    {
        $language = Language::factory()->create([
            'default' => true,
        ]);

        $this->assertEquals(0, Url::count());

        $collection = Collection::factory()->create();

        $this->assertDatabaseHas((new Url)->getTable(), [
            'language_id' => $language->id,
            'slug' => Str::slug(
                $collection->translateAttribute('name')
            ),
            'default' => true,
        ]);
    }
}
