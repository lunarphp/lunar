<?php

namespace Lunar\Shipping\Tests\Unit\Actions\Carts;

use Lunar\Shipping\Resolvers\PostcodeResolver;
use Lunar\Shipping\Tests\TestCase;

/**
 * @group lunar.postcodes
 */
class PostcodeResolverTest extends TestCase
{
    /** @test */
    public function can_get_postcode_query_parts()
    {
        $postcode = 'ABC 123';

        $parts = (new PostcodeResolver())->getParts($postcode);

        $this->assertContains('ABC123', $parts);
        $this->assertContains('ABC', $parts);
        $this->assertContains('AB', $parts);

        $postcode = 'NW1 1TX';

        $parts = (new PostcodeResolver())->getParts($postcode);

        $this->assertContains('NW11TX', $parts);
        $this->assertContains('NW1', $parts);
        $this->assertContains('NW', $parts);

        $postcode = 90210;

        $parts = (new PostcodeResolver())->getParts($postcode);
        $this->assertContains('90210', $parts);
        $this->assertContains('90', $parts);
    }
}
