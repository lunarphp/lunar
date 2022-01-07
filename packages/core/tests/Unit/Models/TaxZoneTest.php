<?php

namespace GetCandy\Tests\Unit\Models;

use GetCandy\Models\Country;
use GetCandy\Models\CustomerGroup;
use GetCandy\Models\State;
use GetCandy\Models\TaxZone;
use GetCandy\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @group getcandy.models
 */
class TaxZoneTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_make_a_tax_zone_class()
    {
        $data = [
            'name'          => 'UK Mainland',
            'zone_type'     => 'state',
            'price_display' => 'tax_inclusive',
            'active'        => true,
            'default'       => true,
        ];

        TaxZone::factory()->create($data);

        $this->assertDatabaseHas((new TaxZone())->getTable(), $data);
    }

    /** @test */
    public function tax_zone_can_have_countries()
    {
        $data = [
            'name'          => 'UK Mainland',
            'zone_type'     => 'state',
            'price_display' => 'tax_inclusive',
            'active'        => true,
            'default'       => true,
        ];

        $zone = TaxZone::factory()->create($data);

        $this->assertDatabaseHas((new TaxZone())->getTable(), $data);

        $country = Country::factory()->create();

        $this->assertCount(0, $zone->refresh()->countries);

        $zone->countries()->create([
            'country_id' => $country->id,
        ]);

        $this->assertCount(1, $zone->refresh()->countries);
    }

    /** @test */
    public function tax_zone_can_have_states()
    {
        $data = [
            'name'          => 'L.A.',
            'zone_type'     => 'state',
            'price_display' => 'tax_inclusive',
            'active'        => true,
            'default'       => true,
        ];

        $zone = TaxZone::factory()->create($data);

        $this->assertDatabaseHas((new TaxZone())->getTable(), $data);

        $country = Country::factory()->create();
        $state = State::factory()->create([
            'country_id' => $country->id,
        ]);

        $this->assertCount(0, $zone->refresh()->states);

        $zone->states()->create([
            'state_id' => $state->id,
        ]);

        $this->assertCount(1, $zone->refresh()->states);
    }

    /** @test */
    public function tax_zone_can_have_postcodes()
    {
        $data = [
            'name'          => 'L.A.',
            'zone_type'     => 'state',
            'price_display' => 'tax_inclusive',
            'active'        => true,
            'default'       => true,
        ];

        $zone = TaxZone::factory()->create($data);

        $this->assertDatabaseHas((new TaxZone())->getTable(), $data);

        $country = Country::factory()->create();

        $this->assertCount(0, $zone->refresh()->postcodes);

        $zone->postcodes()->create([
            'country_id' => $country->id,
            'postcode'   => 12345,
        ]);

        $this->assertCount(1, $zone->refresh()->postcodes);
    }

    /** @test */
    public function tax_zone_can_have_customer_groups()
    {
        $data = [
            'name'          => 'L.A.',
            'zone_type'     => 'state',
            'price_display' => 'tax_inclusive',
            'active'        => true,
            'default'       => true,
        ];

        $zone = TaxZone::factory()->create($data);

        $this->assertDatabaseHas((new TaxZone())->getTable(), $data);

        $country = Country::factory()->create();

        $this->assertCount(0, $zone->refresh()->customerGroups);

        $zone->customerGroups()->create([
            'customer_group_id' => CustomerGroup::factory()->create()->id,
        ]);

        $this->assertCount(1, $zone->refresh()->customerGroups);
    }
}
