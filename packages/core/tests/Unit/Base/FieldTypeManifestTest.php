<?php

namespace GetCandy\Tests\Unit\Base;

use GetCandy\Base\FieldTypeManifest;
use GetCandy\Base\FieldTypeManifestInterface;
use GetCandy\Exceptions\FieldTypes\FieldTypeMissingException;
use GetCandy\Exceptions\FieldTypes\InvalidFieldTypeException;
use GetCandy\Models\Channel;
use GetCandy\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;

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
