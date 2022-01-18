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
            'name'    => 'Clothing',
            'default' => false,
        ]);
    }

    /** @test */
    public function can_get_default_tax_class()
    {
        $taxClassA = TaxClass::factory()->create([
            'name'    => 'Tax Class A',
            'default' => false,
        ]);

        $taxClassB = TaxClass::factory()->create([
            'name'    => 'Tax Class B',
            'default' => true,
        ]);

        $this->assertEquals($taxClassB->id, TaxClass::getDefault()->id);
    }
}
