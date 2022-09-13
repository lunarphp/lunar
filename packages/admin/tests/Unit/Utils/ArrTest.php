<?php

namespace Lunar\Hub\Tests\Unit\Utils;

use Lunar\Hub\Tests\TestCase;
use Lunar\Utils\Arr;

/**
 * @group hub.utils
 */
class ArrTest extends TestCase
{
    /** @test */
    public function passing_empty_set_yields_empty_permutations()
    {
        $this->assertEmpty(Arr::permutate([]));
    }

    /** @test */
    public function can_get_permutations_of_an_array()
    {
        $sets = [
            [
                'incoming' => [
                    'colour' => [
                        'Blue',
                        'Red',
                    ],
                ],
                'expected' => [
                    'Blue',
                    'Red',
                ],
            ],
            [
                'incoming' => [
                    'colour' => [
                        'Blue',
                        'Red',
                    ],
                    'material' => [
                        'Leather',
                        'Cotton',
                    ],
                ],
                'expected' => [
                    [
                        'colour'   => 'Blue',
                        'material' => 'Leather',
                    ],
                    [
                        'colour'   => 'Blue',
                        'material' => 'Cotton',
                    ],
                    [
                        'colour'   => 'Red',
                        'material' => 'Leather',
                    ],
                    [
                        'colour'   => 'Red',
                        'material' => 'Cotton',
                    ],
                ],
            ],
            [
                'incoming' => [
                    'colour' => [
                        'Blue',
                        'Red',
                    ],
                    'material' => [
                        'Leather',
                        'Cotton',
                    ],
                    'size' => [
                        'Small',
                        'Medium',
                    ],
                ],
                'expected' => [
                    [
                        'colour'   => 'Blue',
                        'material' => 'Leather',
                        'size'     => 'Small',
                    ],
                    [
                        'colour'   => 'Blue',
                        'material' => 'Leather',
                        'size'     => 'Medium',
                    ],
                    [
                        'colour'   => 'Blue',
                        'material' => 'Cotton',
                        'size'     => 'Small',
                    ],
                    [
                        'colour'   => 'Blue',
                        'material' => 'Cotton',
                        'size'     => 'Medium',
                    ],
                    [
                        'colour'   => 'Red',
                        'material' => 'Leather',
                        'size'     => 'Small',
                    ],
                    [
                        'colour'   => 'Red',
                        'material' => 'Leather',
                        'size'     => 'Medium',
                    ],
                    [
                        'colour'   => 'Red',
                        'material' => 'Cotton',
                        'size'     => 'Small',
                    ],
                    [
                        'colour'   => 'Red',
                        'material' => 'Cotton',
                        'size'     => 'Medium',
                    ],
                ],
            ],
        ];

        foreach ($sets as $set) {
            $permutations = Arr::permutate($set['incoming']);

            $this->assertCount(count($set['expected']), $permutations);

            $this->assertSame(
                json_encode($permutations),
                json_encode($set['expected'])
            );
        }
    }
}
