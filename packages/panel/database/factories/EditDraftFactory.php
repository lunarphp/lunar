<?php

namespace Lunar\Panel\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\Staff;
use Lunar\Panel\Models\EditDraft;

/**
 * Fixture-style drafts (pruning, listing, cleanup tests). Behavioural tests
 * should create drafts through DraftManager instead, so base-snapshot capture
 * and normalisation stay exercised.
 */
class EditDraftFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = EditDraft::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'draftable_type' => Customer::morphName(),
            'draftable_id' => Customer::factory(),
            'staff_id' => Staff::factory(),
            'data' => ['first_name' => $this->faker->firstName()],
            'base_snapshot' => ['first_name' => $this->faker->firstName()],
        ];
    }
}
