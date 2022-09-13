<?php

namespace Lunar\Tests\Unit\Actions\Taxes;

use Lunar\Actions\Taxes\GetTaxZonePostcode;
use Lunar\Models\TaxZonePostcode;
use Lunar\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @group lunar.actions
 */
class GetTaxZonePostcodeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_match_exact_postcode()
    {
        $uk = TaxZonePostcode::factory()->create([
            'postcode' => 'SW1A 0AA',
        ]);

        TaxZonePostcode::factory()->create([
            'postcode' => 'SW*',
        ]);

        $postcode = app(GetTaxZonePostcode::class)->execute('SW1A 0AA');

        $this->assertEquals($uk->id, $postcode->id);
    }

    /** @test */
    public function can_match_using_wildcards()
    {
        $postcodes = [
            // UK
            [
                'exact'          => 'SW1 1TX',
                'wildcard'       => 'SW*',
                'wildcard_tests' => [
                    'SW',
                    'SW2',
                    'SW3 3TT',
                ],
            ],
            // US
            [
                'exact'          => '90210',
                'wildcard'       => '90*',
                'wildcard_tests' => [
                    '90',
                    '902',
                    '9021',
                ],
            ],
            // Canada
            [
                'exact'          => 'A9A-9A9',
                'wildcard'       => 'A9A*',
                'wildcard_tests' => [
                    'A9A-8A8',
                    'A9A',
                    'A9A-7',
                ],
            ],
            // Costa Rica
            [
                'exact'          => '999-99',
                'wildcard'       => '999*',
                'wildcard_tests' => [
                    '999-98',
                    '999',
                ],
            ],
            // Argentina
            [
                'exact'          => 'A9999AAA',
                'wildcard'       => 'A9999*',
                'wildcard_tests' => [
                    'A9999BBB',
                    'A9999AAB',
                ],
            ],
        ];

        foreach ($postcodes as $postcode) {
            $exact = TaxZonePostcode::factory()->create([
                'postcode' => $postcode['exact'],
            ]);

            $wildcard = TaxZonePostcode::factory()->create([
                'postcode' => $postcode['wildcard'],
            ]);

            $resultA = app(GetTaxZonePostcode::class)->execute($postcode['exact']);
            $this->assertEquals($exact->id, $resultA->id);

            foreach ($postcode['wildcard_tests'] as $test) {
                $resultB = app(GetTaxZonePostcode::class)->execute($test);
                $this->assertEquals($wildcard->id, $resultB->id);
            }
        }
    }
}
