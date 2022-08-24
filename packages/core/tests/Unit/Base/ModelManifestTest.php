<?php

namespace GetCandy\Tests\Unit\Base;

use GetCandy\Base\ModelManifest;
use GetCandy\Base\ModelManifestInterface;
use GetCandy\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @group model-manifest
 */
class ModelManifestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_instantiate_class()
    {
        $manifest = app(ModelManifestInterface::class);

        $this->assertInstanceOf(ModelManifest::class, $manifest);
    }

    /** @test */
    public function can_register_models()
    {
        $this->expectNotToPerformAssertions();
    }

    /** @test */
    public function can_get_registered_model_from_base_model()
    {
        $this->expectNotToPerformAssertions();
    }

    /** @test */
    public function can_get_morph_class_base_model()
    {
        $this->expectNotToPerformAssertions();
    }

    /** @test */
    public function can_get_list_of_registered_base_models()
    {
        $this->expectNotToPerformAssertions();
    }
}
