<?php

namespace GetCandy\Tests\Unit\Base;

use GetCandy\Models\Product;
use GetCandy\Tests\TestCase;

/**
 * @group reference
 */
class MacroableModelTest extends TestCase
{
    protected $model;

    public function setUp(): void
    {
        parent::setUp();

        $this->model = new Product();
    }

    /** @test */
    public function can_register_a_new_macro()
    {
        $this->model::macro('newMethod', function () {
            return 'newValue';
        });

        $this->assertEquals('newValue', $this->model->newMethod());
        $this->assertEquals('newValue', $this->model::newMethod());
    }

    /** @test */
    public function can_register_a_new_macro_and_be_invoked()
    {
        $this->model::macro('newMethod', new class() {
            public function __invoke()
            {
                return 'newValue';
            }
        });

        $this->assertEquals('newValue', $this->model->newMethod());
        $this->assertEquals('newValue', $this->model::newMethod());
    }
}
