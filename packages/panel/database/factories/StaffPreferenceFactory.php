<?php

namespace Lunar\Panel\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Lunar\Core\Models\Staff;
use Lunar\Panel\Models\StaffPreference;

class StaffPreferenceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = StaffPreference::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'staff_id' => Staff::factory(),
            'key' => 'dashboard',
            'value' => ['range' => '30d', 'widgets' => []],
        ];
    }
}
