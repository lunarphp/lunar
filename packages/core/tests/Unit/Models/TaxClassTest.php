<?php

namespace GetCandy\Tests\Unit\Models;

use GetCandy\Models\TaxClass;
use GetCandy\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @group getcandy.models
 */
class TaxClassTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_make_a_tax_class()
    {
        TaxClass::factory()->create([
            'name' => 'Clothing',
        ]);

        $this->assertDatabaseHas((new TaxClass())->getTable(), [
            'name' => 'Clothing',
        ]);
    }
}
