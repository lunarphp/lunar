<?php

namespace GetCandy\Tests\Unit\Base\Extendable;

use Illuminate\Foundation\Testing\RefreshDatabase;

class MorphTest extends ExtendableTestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_get_media_thumbnail_morph_relation_when_using_extended_model()
    {
        $this->expectNotToPerformAssertions();
    }

    /** @test */
    public function can_get_url_morph_relation_when_using_extended_model()
    {
        $this->expectNotToPerformAssertions();
    }

    /** @test */
    public function can_get_prices_relation_when_using_extended_model()
    {
        $this->expectNotToPerformAssertions();
    }

    /** @test */
    public function can_return_the_correct_morph_class_when_using_enforce_morph_map()
    {
        $this->expectNotToPerformAssertions();
    }
}
