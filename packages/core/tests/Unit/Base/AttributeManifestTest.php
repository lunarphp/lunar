<?php

namespace Lunar\Tests\Unit\Base;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Lunar\Base\AttributeManifest;
use Lunar\Base\AttributeManifestInterface;
use Lunar\Models\Channel;
use Lunar\Tests\TestCase;

/**
 * @group core.attribute-manifest
 */
class AttributeManifestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_instantiate_class()
    {
        $manifest = app(AttributeManifestInterface::class);

        $this->assertInstanceOf(AttributeManifest::class, $manifest);
    }

    /** @test */
    public function can_return_types()
    {
        $manifest = app(AttributeManifestInterface::class);

        $this->assertInstanceOf(Collection::class, $manifest->getTypes());
    }

    /** @test */
    public function has_base_types_set()
    {
        $manifest = app(AttributeManifestInterface::class);

        $this->assertInstanceOf(Collection::class, $manifest->getTypes());

        $this->assertNotEmpty($manifest->getTypes());
    }

    /** @test */
    public function can_add_type()
    {
        $manifest = app(AttributeManifestInterface::class);

        $manifest->addType(Channel::class);

        $this->assertNotNull($manifest->getType('channel'));
    }
}
