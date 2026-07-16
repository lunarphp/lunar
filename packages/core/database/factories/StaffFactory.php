<?php

namespace Lunar\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Lunar\Core\Models\Staff;

class StaffFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Staff::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'public_id' => (string) Str::ulid(),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'admin' => $this->faker->boolean(5),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }

    /**
     * Enable app authentication with a fixed test secret and eight
     * bcrypt-hashed random recovery codes (plaintexts are discarded;
     * tests needing known codes set the column explicitly).
     */
    public function withTwoFactor(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'app_authentication_secret' => 'JBSWY3DPEHPK3PXP',
            'app_authentication_recovery_codes' => Collection::times(
                8,
                fn (): string => Hash::make(Str::random(10).'-'.Str::random(10)),
            )->all(),
        ]);
    }
}
