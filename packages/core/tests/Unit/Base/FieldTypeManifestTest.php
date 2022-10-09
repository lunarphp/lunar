<?php

namespace Lunar\Tests\Unit\Base;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Lunar\Base\FieldTypeManifest;
use Lunar\Base\FieldTypeManifestInterface;
use Lunar\Exceptions\FieldTypes\FieldTypeMissingException;
use Lunar\Exceptions\FieldTypes\InvalidFieldTypeException;
use Lunar\Models\Channel;
use Lunar\Tests\TestCase;

/**
 * @group core.fieldtype-manifest
 */
class FieldTypeManifestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_instantiate_class()
    {
        $manifest = app(FieldTypeManifestInterface::class);

        $this->assertInstanceOf(FieldTypeManifest::class, $manifest);
    }

    /** @test */
    public function can_return_types()
    {
        $manifest = app(FieldTypeManifestInterface::class);

        $this->assertInstanceOf(Collection::class, $manifest->getTypes());
    }

    /** @test */
    public function has_base_types_set()
    {
        $manifest = app(FieldTypeManifestInterface::class);

        $this->assertInstanceOf(Collection::class, $manifest->getTypes());

        $this->assertNotEmpty($manifest->getTypes());
    }

    /** @test */
    public function cannot_add_non_fieldtype()
    {
        $manifest = app(FieldTypeManifestInterface::class);

        $this->expectException(
            InvalidFieldTypeException::class
        );

        $manifest->add(Channel::class);

        $this->expectException(
            FieldTypeMissingException::class
        );

        $manifest->add(Foo::class);
    }
}
